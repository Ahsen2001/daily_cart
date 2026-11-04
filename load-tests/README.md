# DailyCart staging load suite

This suite uses [k6](https://grafana.com/docs/k6/latest/) for HTTP workloads and a read-only Laravel command for queue metrics. It must run only against disposable local or staging data.

## Service-level targets

| Metric | Target |
|---|---:|
| HTTP p50 | under 300 ms |
| HTTP p95 | under 800 ms |
| HTTP p99 | under 1,500 ms |
| HTTP/business error rate | under 1% |
| Queue wait p50 | at most 2 seconds |
| Queue wait p95 | at most 5 seconds |
| Queue wait p99 | at most 15 seconds |
| New failed jobs during run | 0 |

Targets can be overridden with the `TARGET_*` k6 variables and `LOAD_TARGET_*` Laravel variables. Keep overrides documented with the test result.

## Safety

- `LOAD_ENVIRONMENT` must be `local` or `staging`.
- Local runs may target only localhost.
- Non-local runs require the exact hostname in `STAGING_HOST_ALLOWLIST`.
- Cart, checkout, rider-location and PayHere scenarios remain disabled unless `ALLOW_MUTATIONS=true`.
- Use dedicated load-test customers, riders, deliveries, products and payments. Never use production credentials or data.
- Checkout creates cash-on-delivery orders and consumes stock in staging. Restore staging from its disposable fixture after testing.

## Run read-only catalog traffic

PowerShell:

```powershell
$env:LOAD_ENVIRONMENT='local'
$env:BASE_URL='http://127.0.0.1:8000'
$env:DURATION='5m'
k6 run --summary-export=storage/app/load-tests/k6-summary.json load-tests/k6/dailycart.js
```

Use `ENABLE_CATALOG=false` to isolate authenticated workloads. Catalog arrival rate can be expressed over a larger window with `CATALOG_RATE` and `CATALOG_TIME_UNIT`, for example one iteration every five seconds.

For staging, set `LOAD_ENVIRONMENT=staging`, `BASE_URL`, and `STAGING_HOST_ALLOWLIST` to the exact staging hostname.

## Authenticated and mutating scenarios

Tokens are comma-separated. Supply at least one dedicated token per intended virtual user.

```powershell
$env:CUSTOMER_TOKENS='token-1,token-2'
$env:ALLOW_MUTATIONS='true'
$env:ENABLE_CHECKOUT='true'
$env:PRODUCT_ID='123'
$env:CHECKOUT_VUS='2'
$env:CHECKOUT_ITERATIONS='3'
```

Rider updates are enabled with `ENABLE_RIDER_LOCATIONS=true` and additionally require aligned comma-separated `RIDER_TOKENS`, `RIDER_DELIVERY_IDS`, and `RIDER_TRACKING_TOKENS`. The referenced deliveries must already be active and assigned to those riders.

PayHere callback load is enabled with `ENABLE_PAYHERE_CALLBACKS=true` and requires a URL-encoded, sandbox callback in `PAYHERE_CALLBACK_PAYLOAD`. Repeating one valid callback intentionally verifies callback idempotency. Use only a PayHere sandbox payment created for load testing.

## Queue wait and failed jobs

Run this command in a second terminal for the same duration as k6:

```powershell
php artisan dailycart:load-queue-metrics --duration=300 --interval=2 --output=storage/app/load-tests/queue-summary.json
```

The command samples the age of the oldest ready job as the queue-wait indicator, calculates p50/p95/p99, records the maximum, and fails when a target is exceeded or any new failed job appears. It is blocked in production and never modifies queue records.

## Recommended execution stages

1. Smoke: 1 minute at 1 request/second.
2. Baseline: 5 minutes at the expected normal rate.
3. Load: 15 minutes at the expected peak rate.
4. Stress: increase rates gradually until an agreed threshold fails.
5. Soak: 1–4 hours at 60–80% of expected peak.

Save the k6 summary, queue summary, application logs, worker counts, database CPU/connections and host CPU/memory with every result. A run passes only when both k6 and the queue monitor exit successfully.
