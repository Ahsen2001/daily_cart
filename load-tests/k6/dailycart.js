import http from 'k6/http';
import { check, group, sleep } from 'k6';
import { Counter, Rate, Trend } from 'k6/metrics';
import {
  baseUrl,
  mutatingScenarioEnabled,
  targets,
  tokenFor,
  tokens,
} from './config.js';

const customerTokens = tokens('CUSTOMER_TOKENS');
const riderTokens = tokens('RIDER_TOKENS');
const deliveryIds = tokens('RIDER_DELIVERY_IDS');
const trackingTokens = tokens('RIDER_TRACKING_TOKENS');
const productId = __ENV.PRODUCT_ID || '';
const payHerePayload = __ENV.PAYHERE_CALLBACK_PAYLOAD || '';

const businessErrors = new Rate('dailycart_business_errors');
const checkoutDuration = new Trend('dailycart_checkout_duration', true);
const riderUpdateDuration = new Trend('dailycart_rider_update_duration', true);
const duplicatePayHereCallbacks = new Counter('dailycart_payhere_duplicate_callbacks');

const thresholds = {
  http_req_duration: [
    `p(50)<${targets.p50}`,
    `p(95)<${targets.p95}`,
    `p(99)<${targets.p99}`,
  ],
  http_req_failed: [`rate<${targets.errorRate}`],
  checks: ['rate>0.99'],
  dailycart_business_errors: [`rate<${targets.errorRate}`],
  dailycart_checkout_duration: [`p(95)<${Math.max(targets.p95, 1200)}`, `p(99)<${Math.max(targets.p99, 2000)}`],
  dailycart_rider_update_duration: [`p(95)<${targets.p95}`, `p(99)<${targets.p99}`],
};

const scenarios = {
  catalog: {
    executor: 'constant-arrival-rate',
    exec: 'catalog',
    rate: Number(__ENV.CATALOG_RATE || 10),
    timeUnit: '1s',
    duration: __ENV.DURATION || '5m',
    preAllocatedVUs: Number(__ENV.CATALOG_VUS || 10),
    maxVUs: Number(__ENV.CATALOG_MAX_VUS || 50),
    tags: { workload: 'catalog' },
  },
};

if (customerTokens.length > 0) {
  scenarios.notifications = {
    executor: 'constant-vus',
    exec: 'notifications',
    vus: Math.min(customerTokens.length, Number(__ENV.NOTIFICATION_VUS || 5)),
    duration: __ENV.DURATION || '5m',
    tags: { workload: 'notifications' },
  };
}

if (mutatingScenarioEnabled('checkout', 'ENABLE_CHECKOUT', [productId, customerTokens[0]])) {
  scenarios.checkout = {
    executor: 'per-vu-iterations',
    exec: 'checkout',
    vus: Math.min(customerTokens.length, Number(__ENV.CHECKOUT_VUS || customerTokens.length)),
    iterations: Number(__ENV.CHECKOUT_ITERATIONS || 3),
    maxDuration: __ENV.CHECKOUT_MAX_DURATION || '10m',
    tags: { workload: 'checkout' },
  };
}

if (mutatingScenarioEnabled('rider location', 'ENABLE_RIDER_LOCATIONS', [riderTokens[0], deliveryIds[0], trackingTokens[0]])) {
  scenarios.rider_locations = {
    executor: 'constant-vus',
    exec: 'riderLocations',
    vus: Math.min(riderTokens.length, deliveryIds.length, trackingTokens.length),
    duration: __ENV.DURATION || '5m',
    tags: { workload: 'rider_locations' },
  };
}

if (mutatingScenarioEnabled('PayHere callback', 'ENABLE_PAYHERE_CALLBACKS', [payHerePayload])) {
  scenarios.payhere_callbacks = {
    executor: 'constant-arrival-rate',
    exec: 'payHereCallbacks',
    rate: Number(__ENV.PAYHERE_RATE || 2),
    timeUnit: '1s',
    duration: __ENV.DURATION || '5m',
    preAllocatedVUs: 2,
    maxVUs: 10,
    tags: { workload: 'payhere_callbacks' },
  };
}

export const options = {
  discardResponseBodies: true,
  scenarios,
  thresholds,
  summaryTrendStats: ['avg', 'min', 'med', 'p(50)', 'p(90)', 'p(95)', 'p(99)', 'max'],
};

function headers(token, extra = {}) {
  return {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
    ...extra,
  };
}

function record(response, expected, name) {
  const ok = check(response, {
    [`${name}: expected status`]: (result) => expected.includes(result.status),
  });
  businessErrors.add(!ok, { endpoint: name });
  return ok;
}

export function catalog() {
  group('public catalog', () => {
    record(http.get(`${baseUrl}/api/v1/catalog/home`, { tags: { endpoint: 'catalog_home' } }), [200], 'catalog home');
    record(http.get(`${baseUrl}/api/v1/products?per_page=20`, { tags: { endpoint: 'products' } }), [200], 'products');
    record(http.get(`${baseUrl}/api/v1/stores?per_page=20`, { tags: { endpoint: 'stores' } }), [200], 'stores');
  });
  sleep(0.2);
}

export function notifications() {
  const token = tokenFor(customerTokens);
  record(
    http.get(`${baseUrl}/api/v1/notifications`, { headers: headers(token), tags: { endpoint: 'notifications' } }),
    [200],
    'notifications',
  );
  sleep(1);
}

export function checkout() {
  const token = tokenFor(customerTokens);
  const requestHeaders = headers(token);
  const added = http.post(
    `${baseUrl}/api/v1/cart`,
    JSON.stringify({ product_id: Number(productId), quantity: 1 }),
    { headers: requestHeaders, tags: { endpoint: 'cart_add' } },
  );
  if (!record(added, [200, 201], 'cart add')) return;

  const quote = http.post(
    `${baseUrl}/api/v1/checkout/quote`,
    JSON.stringify({ delivery_district: __ENV.DELIVERY_DISTRICT || 'Colombo' }),
    { headers: requestHeaders, tags: { endpoint: 'checkout_quote' } },
  );
  if (!record(quote, [200], 'checkout quote')) return;

  const idempotencyKey = `k6-${__VU}-${__ITER}-${Date.now()}`;
  const startedAt = Date.now();
  const order = http.post(
    `${baseUrl}/api/v1/orders`,
    JSON.stringify({
      delivery_address: __ENV.DELIVERY_ADDRESS || 'K6 staging load-test address',
      delivery_district: __ENV.DELIVERY_DISTRICT || 'Colombo',
      scheduled_delivery_at: new Date(Date.now() + 60 * 60 * 1000).toISOString(),
      payment_method: 'cash_on_delivery',
    }),
    {
      headers: headers(token, { 'Idempotency-Key': idempotencyKey }),
      tags: { endpoint: 'checkout_order' },
    },
  );
  checkoutDuration.add(Date.now() - startedAt);
  record(order, [201], 'checkout order');
}

export function riderLocations() {
  const index = (__VU - 1) % Math.min(riderTokens.length, deliveryIds.length, trackingTokens.length);
  const now = new Date();
  const pointId = `00000000-0000-4000-8000-${String(__VU).padStart(4, '0')}${String(__ITER).padStart(8, '0')}`;
  const startedAt = Date.now();
  const response = http.post(
    `${baseUrl}/api/v1/rider/deliveries/${deliveryIds[index]}/location-updates`,
    JSON.stringify({
      tracking_token: trackingTokens[index],
      points: [{
        point_uuid: pointId,
        latitude: Number(__ENV.RIDER_LATITUDE || 7.2906),
        longitude: Number(__ENV.RIDER_LONGITUDE || 80.6337),
        accuracy_meters: 10,
        heading: 90,
        speed: 5,
        recorded_at: now.toISOString(),
        sequence_number: __ITER + 1,
      }],
    }),
    { headers: headers(riderTokens[index]), tags: { endpoint: 'rider_location_batch' } },
  );
  riderUpdateDuration.add(Date.now() - startedAt);
  record(response, [202], 'rider location batch');
  sleep(Number(__ENV.RIDER_INTERVAL_SECONDS || 5));
}

export function payHereCallbacks() {
  const response = http.post(
    `${baseUrl}/payment/payhere/notify`,
    payHerePayload,
    {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', Accept: 'text/plain' },
      tags: { endpoint: 'payhere_callback' },
    },
  );
  duplicatePayHereCallbacks.add(1);
  record(response, [200], 'PayHere callback');
}
