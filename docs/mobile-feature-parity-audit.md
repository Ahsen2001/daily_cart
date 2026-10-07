# DailyCart mobile feature parity audit

Audit date: 2026-10-07

Scope: customer, vendor, and rider Flutter applications compared with the role-appropriate web workflows and `/api/v1` endpoints. Administration remains web-only. No database data or schema was changed during this audit.

## Result summary

| Area | Customer | Vendor | Rider |
| --- | --- | --- | --- |
| Authentication, verification, approval | Implemented | Implemented | Implemented |
| Dashboard and role navigation | Implemented | Implemented | Implemented |
| Profile, photo, password | Implemented | Implemented | Implemented |
| Notifications and preferences | Enhanced action center | Enhanced action center | Enhanced action center |
| Order/delivery activity timeline | Implemented from API history | Available in order notifications | Available in delivery notifications |
| Support tickets and replies | Implemented | Implemented | Implemented |
| Role-specific commerce workflow | Implemented | Implemented | Implemented |
| Ratings | Products and rider rating | Product-review visibility | Customer ratings and reporting |
| Offline/network experience | Retry plus cached notification fallback and global offline banner | Same | Same |

## Customer parity

Implemented mobile flows include catalog home, categories, product search/details/reviews, wishlist, cart and bulk cart, addresses, delivery quote/schedule, coupons, loyalty, checkout, PayHere, bank-transfer slips, order history/details/receipt/tracking/cancellation, wallet, refunds, subscriptions, scheduled orders, product reviews, rider ratings, promotions, policies, notifications, and support.

The customer notification center now covers the web notification families: order lifecycle, rider assignment/delivery, payments/refunds/bank transfer, invoices, account, support, and promotions. Order-linked notifications can expose the actual status-history timeline returned by the API.

Remaining parity candidate:

- The public web experience has store directory and store-detail pages. There is no equivalent mobile stores endpoint in `routes/api.php`, so a correct mobile store browser needs an API contract before implementation. Product shopping remains available through the existing catalog APIs.

## Vendor parity

Implemented mobile flows include dashboard, profile/store data, catalog CRUD, product images/variants/inventory, orders and order status, earnings, wallet and payout requests, refunds, coupons, promotions, subscription orders, scheduled orders, reports, product reviews, notifications, and support.

The action center categorizes new orders, delivery updates, payment/refund/payout events, approval state, reviews, low stock, and support. Relevant notifications route to vendor orders, earnings, support, or the server-provided supported route.

## Rider parity

Implemented mobile flows include dashboard, availability, profile, assigned deliveries, delivery acceptance/status/proof, navigation map/location updates, earnings, reports, notifications, support, and customer ratings. Riders can see rating statistics and report a rating for administrator review.

The action center categorizes assignments/reassignments, delivery activity, earnings, ratings/moderation, account state, and support.

## UI, accessibility, and resilience review

- Notification content is constrained on wider screens and remains single-column on phones.
- All/Unread/Action controls, category chips, search, empty results, loading, retry, and cached-data warnings are present.
- Notification controls and timelines expose semantic labels; icon actions include tooltips.
- A global live-region banner reports offline state across all three apps.
- The API client retries safe idempotent requests for transient connection, timeout, and gateway failures.
- Notification data is cached independently per customer/vendor/rider flavor and is only replaced after a successful refresh.

## Platform validation expectations

- Android: analyze, tests, and one debug APK build per customer/vendor/rider flavor.
- iOS: analyze and project/configuration verification can run on Windows. A real iOS compile, signing validation, and simulator/device test require macOS with Xcode; this limitation must not be reported as a successful iOS build.
- Push delivery still requires valid platform Firebase files, APNs capabilities/provisioning on iOS, Android notification permission/channel configuration, and a reachable production API.
