# DailyCart Laravel API v1 Contract

Contract version: **1.4.0**  
Frozen: **2026-07-20**  
Canonical base path: **`/api/v1`**

This document is the source of truth for the API currently implemented in
`routes/api.php` and `App\Http\Controllers\Api\v1`. The machine-readable route
surface is stored in `route-contract.json` and enforced by
`ApiV1RouteContractTest`.

The contract guarantees only the endpoints and fields documented here.
Additional JSON fields may be returned and clients must ignore fields they do
not understand.

## Versioning policy

- Backward-compatible optional fields and new endpoints may be added to v1.
- Removing or renaming an endpoint or field, changing an HTTP method, changing
  a field type or nullability, or strengthening authentication requirements is
  a breaking change.
- A breaking change requires a new API version such as `/api/v2`, or an
  explicitly coordinated v1 contract revision before deployment.
- Every route-surface change must update `route-contract.json` and the route
  contract test in the same change.
- Web routes are not part of this API contract.

## Request conventions

- Production base URL: `https://dailycart.lk/api/v1`
- Local Android emulator example: `http://10.0.2.2:8000/api/v1`
- Default media type: `application/json`
- Delivery proof uploads use `multipart/form-data`.
- Protected endpoints require `Authorization: Bearer <sanctum-token>`.
- JSON object keys use `snake_case`.
- IDs are JSON integers.
- Money values are JSON numbers with LKR as the default currency.
- Dates and timestamps use Laravel ISO-8601 JSON serialization.
- Clients must send `Accept: application/json`.

## Authentication and authorization

Role registration uses separate Customer, Vendor, and Rider endpoints.
`POST /register` remains a backward-compatible Customer-only alias and rejects
a Vendor or Rider `role` value. Login tokens contain these common abilities:

- `auth`
- `profile`
- `verification`
- one role ability: `customer`, `vendor`, or `rider`

Customer commerce routes additionally require the Customer role plus verified
email and phone.
Vendor and Rider routes additionally require the corresponding role, verified
email and phone, and an approved role profile.

Authentication and registration return one response shape:

```json
{
  "success": true,
  "message": "Human-readable result",
  "token_type": "Bearer",
  "access_token": "plain-text-token",
  "token": "plain-text-token",
  "expires_at": "2026-07-27T12:00:00Z",
  "requires_verification": true,
  "requires_approval": false,
  "user": {}
}
```

`token` is retained as a compatibility alias for `access_token`. Clients must
store `expires_at`, validate the session through `GET /profile` at startup, and
clear local credentials after a `401` or logout.

Authentication state is represented as follows:

| Status | Meaning |
| --- | --- |
| `401` | Missing, expired, or invalid bearer token |
| `403` | Token ability, verification, role, approval, or ownership rejected |
| `404` | Route-bound record or required role profile not found |
| `422` | Request validation or business-rule validation failed |
| `429` | Login, registration, or OTP rate limit exceeded |

## Error contract

Validation and business-rule errors use Laravel's JSON validation envelope:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": [
      "Validation message."
    ]
  }
}
```

Other failures use:

```json
{
  "message": "Human-readable error message."
}
```

Clients must branch on the HTTP status and may display `message`. They must not
parse English message text to determine application state.

## Shared response schemas

### User

| Field | Type | Nullable |
| --- | --- | --- |
| `id` | integer | no |
| `name` | string | no |
| `email` | string | no |
| `phone` | string | yes |
| `role` | `Customer\|Vendor\|Rider\|Admin\|Super Admin` | yes |
| `email_verified_at` | ISO-8601 string | yes |
| `phone_verified_at` | ISO-8601 string | yes |
| `is_email_verified`, `is_phone_verified` | boolean | no |
| `is_approved` | boolean | no |
| `approval_status` | string | yes |
| `status` | string | no |
| `created_at` | ISO-8601 string | no |

### Product

| Field | Type | Nullable |
| --- | --- | --- |
| `id`, `vendor_id`, `category_id` | integer | no |
| `name`, `slug` | string | no |
| `brand`, `description`, `image`, `weight`, `sku` | string | yes |
| `price`, `discount_price` | number | no |
| `unit_type`, `status` | string | no |
| `stock_quantity` | integer | no |
| `is_featured`, `is_subscription_eligible` | boolean | no |

### Order

| Field | Type | Nullable |
| --- | --- | --- |
| `id`, `customer_id`, `vendor_id` | integer | no |
| `order_number`, `currency`, `delivery_address` | string | no |
| `subtotal`, `discount_amount`, `delivery_fee` | number | no |
| `service_charge`, `tax_amount`, `total_amount` | number | no |
| `delivery_latitude`, `delivery_longitude` | number | yes |
| `order_status`, `payment_status` | string | no |
| `placed_at`, `scheduled_delivery_at` | ISO-8601 string | yes |
| `timeline` | array of `{status, remarks, timestamp}` | no |

### Standard pagination

Products and customer orders use:

```json
{
  "pagination": {
    "total": 100,
    "count": 15,
    "per_page": 15,
    "current_page": 1,
    "total_pages": 7
  }
}
```

Vendor orders and Rider deliveries currently expose Laravel paginator objects
inside their named response property. Their inner records are legacy raw
Eloquent serialization and should be replaced with explicit resources before
mobile feature completion.

## Public endpoints

### Authentication

| Method and path | Request | Success |
| --- | --- | --- |
| `POST /register` | Customer-only compatibility alias. Common registration payload; optional `role` may only be `customer`. | `201 AuthenticationResponse` |
| `POST /register/customer` | Common registration payload. | `201 AuthenticationResponse` |
| `POST /register/vendor` | Common payload plus `store_name`; optional `business_registration_no`; required home-base fields. | `201 AuthenticationResponse` |
| `POST /register/rider` | Common payload plus `vehicle_type`; optional `vehicle_number`, `license_number`; required home-base fields. | `201 AuthenticationResponse` |
| `POST /login` | `email` required email; `password` required string; `device_name` optional string ≤100 | `200 AuthenticationResponse` |
| `POST /password/forgot` | `email` required email | `200 {"success": true, "message": string}` |
| `POST /password/reset` | `email`; six-digit `code`; confirmed `password` | `200 {"success": true, "message": string}` |

The common registration payload is `name`, unique `email`, unique `phone`,
confirmed `password`, and optional `device_name`. Home-base fields are
`address`, `city`, `district`, and `province`, with optional paired
`latitude`/`longitude` and `formatted_address`. Rider `vehicle_type` is one of
`bicycle`, `motorbike`, `three_wheeler`, or `van`.

Vendor and Rider accounts begin pending approval. All newly registered
accounts must verify both email and phone. Password recovery always returns the
same forgot response, whether or not the email exists, and a successful reset
revokes every existing API token for the user.

Invalid credentials return `422`; suspended users return `403`.

### Catalog

| Method and path | Query | Success |
| --- | --- | --- |
| `GET /categories` | none | `200 {"categories": Category[]}` |
| `GET /catalog/home` | none | `200` with the five mobile home product shelves |
| `GET /products` | `page`; optional catalog filters and `sort` | `200 {"products": Product[], "pagination": Pagination}` |
| `GET /products/{product}` | integer route ID | `200 {"product": Product}` |

Supported `sort` values are `price_low_high`, `price_high_low`, `latest`,
`highest_rated`, and `most_sold`. Catalog filters are `category_id`, `search`,
`min_price`, `max_price`, `rating`, `available`, `brand`, `featured`, and
`discounted`. Only customer-visible products are returned. Category entries
include a customer-visible `products_count` and a display-ready image URL.
`GET /catalog/home` returns `featured`, `best_selling`, `new_arrivals`,
`flash_deals`, and `recommended` arrays in one request to avoid mobile startup
request fan-out.

### Delivery pricing

| Method and path | Request | Success |
| --- | --- | --- |
| `GET /delivery/zones` | none | `200 {"zones": Zone[]}` |
| `GET /delivery/promotions` | none | `200 {"promotions": DeliveryPromotion[]}` |
| `POST /delivery/estimate` | `subtotal` required number ≥0; optional `district`, `province`, `distance_meters` integer ≥0, `coupon_code` | `200 {"delivery": DeliveryEstimate, "service_charge": number, "customer_total": number}` |

`DeliveryEstimate` guarantees `delivery_fee`, `estimated_delivery_minutes`,
`free_delivery_eligible`, `rule_scope`, and `rule_id`.

## Authenticated account endpoints

| Method and path | Ability | Request | Success |
| --- | --- | --- | --- |
| `POST /logout` | `auth` | empty | `200 {"message": string}` |
| `GET /profile` | `profile` | empty | `200 {"user": User}` |
| `POST /email/verification-otp` | `verification` | empty | `200 {"message": string}` |
| `POST /email/verification-otp/verify` | `verification` | `code` required six digits | `200 {"message": string, "user": User}` |
| `POST /phone/verification-otp` | `verification` | empty | `200 {"message": string}` |
| `POST /phone/verification-otp/verify` | `verification` | `code` required six digits | `200 {"message": string, "user": User}` |
| `POST /notifications/device-tokens` | role ability | token, installation `device_id`, `app_role`, `android\|ios`, optional version | `201/200 {"device_token": DeviceToken}` |
| `PATCH /notifications/device-tokens` | role ability | registration fields plus optional `old_device_token` | `200 {"device_token": DeviceToken}` |
| `DELETE /notifications/device-tokens` | role ability | `device_id` and/or `device_token`, `app_role` | `200 {"revoked_count": integer}` |
| `GET /notifications/preferences` | role ability | empty | `200 {"preferences": NotificationPreferences}` |
| `PATCH /notifications/preferences` | role ability | optional preference booleans | `200 {"preferences": NotificationPreferences}` |

Private account, order, payment, and delivery pushes are queued to active
device tokens for the authenticated user and app role. FCM topics are reserved
for public promotions and use
`dailycart_public_promotions_{customer|vendor|rider}`. Notification records
include structured `data`, `app_role`, and an app-relative `deep_link`.

## Customer endpoints

All endpoints in this section require the `customer` ability plus verified
email and phone.

### Cart

| Method and path | Request | Success |
| --- | --- | --- |
| `GET /cart` | empty | `200 {"cart": CartItem[], "totals": CartTotals}` |
| `POST /cart` | `product_id` required existing ID; `quantity` required integer ≥1; `product_variant_id` optional existing ID | `200 {"message": string, "item": CartItemSummary}` |
| `PATCH /cart-items/{item}` | `quantity` required integer ≥1 | `200 {"message": string}` |
| `DELETE /cart-items/{item}` | empty | `200 {"message": string}` |
| `DELETE /cart/clear` | empty | `200 {"message": string}` |

`CartItem` guarantees `id`, `product_id`, `product_name`, `quantity`,
`unit_price`, `total_price`, nullable `variant_id`, and nullable
`variant_name`. `CartTotals` guarantees `subtotal` and `item_count`.

The accepted variant field name is **`product_variant_id`**, not `variant_id`.

### Quote and orders

| Method and path | Request/query | Success |
| --- | --- | --- |
| `POST /checkout/quote` | optional `coupon_code`, `loyalty_points` integer ≥0, `delivery_district`, `delivery_distance_meters` integer ≥0 | `200 {"quote": Quote}` |
| `POST /orders` | checkout payload below | `201 {"message": string, "orders": Order[]}` |
| `GET /orders` | optional `page` | `200 {"orders": Order[], "pagination": Pagination}` |
| `GET /orders/{order}` | integer route ID | `200 {"order": Order}` |

`Quote` guarantees `subtotal`, `discount`, `loyalty_points`,
`loyalty_discount`, `delivery_fee`, `service_charge`, `grand_total`,
`estimated_delivery_minutes`, `free_delivery_eligible`, and
`delivery_rule_scope`.

Checkout payload:

| Field | Rule |
| --- | --- |
| `delivery_address` | required string ≤1000 |
| `delivery_district` | optional string ≤255 |
| `delivery_latitude` | optional number between -90 and 90 |
| `delivery_longitude` | optional number between -180 and 180 |
| `delivery_distance_meters` | optional integer ≥0 |
| `scheduled_delivery_at` | required date accepted by the delivery schedule service |
| `payment_method` | required: `cash_on_delivery`, `card`, `bank_transfer`, or `wallet` |
| `coupon_code` | optional string ≤255 |
| `loyalty_points` | optional integer ≥0 |

One cart can produce multiple orders when it contains products from multiple
vendors.

## Rider endpoints

All Rider endpoints require a Rider token, verified email and phone, the Rider
role, and an approved Rider profile.

| Method and path | Request/query | Success |
| --- | --- | --- |
| `GET /rider/deliveries` | optional `page` | `200 {"deliveries": LaravelPaginator<Delivery>}` |
| `GET /rider/deliveries/{delivery}` | integer route ID | `200 {"delivery": Delivery}` |
| `PATCH /rider/deliveries/{delivery}/status` | status payload below | `200 {"message": string, "delivery": Delivery}` |
| `POST /rider/location` | `latitude` and `longitude` required numbers | `200 {"message": string}` |

Status values are `accepted`, `picked_up`, `on_the_way`, `delivered`, and
`failed`. `failed_reason` is required for `failed`. `proof_image` is required
for `delivered`; `customer_signature` and `note` are optional. Image statuses
must be submitted as multipart form data.

Riders may access only deliveries assigned to their Rider profile.

## Vendor endpoints

All Vendor endpoints require a Vendor token, verified email and phone, the
Vendor role, and an approved Vendor profile.

| Method and path | Request/query | Success |
| --- | --- | --- |
| `GET /vendor/overview` | empty | `200 {"summary": VendorSummary}` |
| `GET /vendor/orders` | optional `page` | `200 {"orders": LaravelPaginator<Order>}` |
| `GET /vendor/wallet` | empty | `200 {"wallet": VendorWallet}` |

`VendorSummary` guarantees `total_products`, `pending_products`,
`approved_products`, `total_orders`, `completed_orders`, `cancelled_orders`,
`revenue`, `earnings`, `low_stock_products`, and `customer_reviews`.

`VendorWallet` guarantees numeric `balance`, `pending_balance`, `total_earned`,
and `total_withdrawn`.

## Change checklist

Before changing v1:

1. Determine whether the change is backward compatible.
2. Update this document and `route-contract.json`.
3. Update Laravel feature/contract tests.
4. Update the Flutter model and service consuming the endpoint.
5. Run `php artisan test --filter=ApiV1RouteContractTest`.
6. Run the relevant API flow tests and `flutter analyze`.
