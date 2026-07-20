# DailyCart Mobile to Laravel API v1 Gap Matrix

Reviewed: **2026-07-20**

This matrix compares the Flutter services in `dailycart_mobile/lib/services`
with the frozen Laravel v1 contract. A Flutter service method is not evidence
that its server endpoint exists.

## Freeze decision

- Laravel `/api/v1` is canonical.
- Flutter must adapt to implemented v1 paths and payloads where the backend
  already provides the required capability.
- A missing capability requires a new Laravel endpoint, tests, and a contract
  update before the mobile screen is considered functional.
- Web URLs and controllers must never be called directly from the mobile apps.

The mobile base URL has been aligned to `/api/v1`. Any deployment environment
still using `/api` must update its `API_BASE_URL`.

## Authentication corrections completed in contract 1.1

- Separate Customer, Vendor, and Rider registration endpoints now persist
  role-specific profile fields.
- Flutter uses `GET /profile` and the separate email and phone OTP endpoints.
- Mobile password recovery uses `/password/forgot` and `/password/reset`.
- Authentication responses include token expiration, verification state, and
  approval state.
- Flutter validates stored sessions at startup, centrally clears sessions on
  `401`, and guards routes by authentication and role.

## Implemented but currently mismatched

| Area | Flutter call | Frozen Laravel v1 | Required correction |
| --- | --- | --- | --- |
| Add cart variant | request key `variant_id` | request key `product_variant_id` | Rename Flutter payload key. |
| Update cart item | `PATCH /cart/items/{id}` | `PATCH /cart-items/{id}` | Change Flutter path. |
| Remove cart item | `DELETE /cart/items/{id}` | `DELETE /cart-items/{id}` | Change Flutter path. |
| Clear cart | `DELETE /cart` | `DELETE /cart/clear` | Change Flutter path. |
| Checkout | `POST /checkout` | `POST /orders` | Change Flutter endpoint and parse an order array. |
| Rider delivered proof | `POST /rider/deliveries/{id}/delivered` | multipart `PATCH /rider/deliveries/{id}/status` with `status=delivered` | Consolidate Flutter delivery status submission. |
| Vendor dashboard | `GET /vendor/dashboard` | `GET /vendor/overview` | Change Flutter path and map `summary`. |
| Vendor earnings | `GET /vendor/earnings` | `GET /vendor/wallet` | Use wallet only if its four totals satisfy the screen; otherwise add a contracted earnings endpoint. |
| Product filters | sends price, rating, availability and brand filters | only category, search and sort are implemented | Either remove unsupported filters from requests or implement them server-side before enabling the UI. |

## Matching implemented capabilities

These route families exist, although their model parsing still requires
contract tests:

- `POST /register/customer`
- `POST /register/vendor`
- `POST /register/rider`
- `POST /login`
- `POST /logout`
- `POST /password/forgot`
- `POST /password/reset`
- `GET /profile`
- Email and phone verification OTP endpoints
- `GET /categories`
- `GET /products`
- `GET /products/{id}`
- `GET /cart`
- `POST /cart`
- `GET /orders`
- `GET /orders/{id}`
- `GET /rider/deliveries`
- `GET /rider/deliveries/{id}`
- `PATCH /rider/deliveries/{id}/status`
- `POST /rider/location`
- `GET /vendor/orders`

## Flutter capabilities with no Laravel v1 endpoint

### Customer account

- Profile update, photo update, and password update
- Address list/create/update/delete/default
- Generic device-token registration

### Customer shopping

- Featured products
- Best-selling products
- New arrivals
- Flash deals
- Recommended products
- Dedicated product search endpoint
- Wishlist list/add/remove/move-to-cart
- Product reviews and customer's review history
- Promotion list/detail
- Coupon apply/remove/available/validate
- Loyalty balance/history

### Orders and payments

- Order cancellation
- Order status polling endpoint
- PayHere payment initiation
- Payment status endpoint

### Customer communication

- Notification list/read/read-all/delete
- Support ticket list/create/detail/reply/close

### Rider

- Rider dashboard
- Rider profile read/update
- Rider earnings

### Vendor

- Vendor profile read/update
- Vendor order detail
- Confirm, packed, and cancel order actions
- Vendor product CRUD
- Product image upload
- Inventory update
- Vendor reviews
- Detailed vendor earnings

## Backend contract debt

These existing endpoints work but should be normalized before the mobile
feature-completion phase:

1. `GET /rider/deliveries` returns a raw Laravel paginator containing raw
   Eloquent models.
2. `GET /rider/deliveries/{id}` returns a raw Delivery graph.
3. `GET /vendor/orders` returns a raw Laravel paginator containing raw Order
   graphs.
4. Categories, zones, and delivery promotions return raw model serialization.
5. Error responses do not include stable machine-readable application error
   codes.
6. There is no idempotency key contract for order creation or payment actions.
7. There is no documented API deprecation/sunset response header.

The next API implementation phase should introduce explicit resources for
these responses without removing currently documented fields.

## Recommended implementation order

1. Correct the implemented-path Flutter mismatches.
2. Add profile, address, notification device-token, and role dashboard APIs.
3. Complete customer cart/checkout/payment/order actions.
4. Complete Vendor order/product/inventory APIs.
5. Complete Rider dashboard/earnings/proof APIs.
6. Add customer wishlist, reviews, coupons, loyalty, promotions, and support.
7. Add contract tests for every newly implemented endpoint before enabling its
   mobile screen.
