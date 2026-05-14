# DailyCart Cart, Wishlist, And Checkout

This feature uses customer-only routes, Blade/Tailwind pages, form requests, services, backend stock validation, and `DB::transaction()` for order placement.

## Artisan Commands

```bash
php artisan make:controller Customer/CartController
php artisan make:controller Customer/WishlistController
php artisan make:controller Customer/CheckoutController

php artisan make:request AddToCartRequest
php artisan make:request UpdateCartItemRequest
php artisan make:request CheckoutRequest
php artisan make:request ApplyCouponRequest

php artisan make:service CartService
php artisan make:service CouponService
php artisan make:service OrderService
php artisan make:service PaymentService
php artisan make:migration add_checkout_fields_and_statuses
```

Laravel does not include `make:service` by default, so the service classes were created under `app/Services`.

## Customer Routes

```text
/customer/cart
/customer/wishlist
/customer/checkout
/customer/checkout/success
```

## Business Rules

- Customers can add only approved products from active categories.
- Pending, rejected, inactive, and out-of-stock products are blocked.
- Product stock is checked when adding to cart and checked again inside checkout.
- Checkout uses `DB::transaction()` and row locks before reducing stock.
- Stock cannot become negative.
- Cart rows are scoped through the authenticated customer's active cart.
- Wishlist duplicates are prevented with `firstOrCreate`.
- Currency is LKR only.
- Prices are displayed with `CurrencyService::formatLkr()`, for example `Rs. 1,500.00`.

## Delivery Scheduling

Checkout requires `scheduled_delivery_at`.

Backend validation enforces:

```text
scheduled_delivery_at >= placed_at + 30 minutes
```

Validation message:

```text
Delivery time must be at least 30 minutes after placing the order.
```

## Payment Methods

The checkout creates placeholder payment records for:

```text
cash_on_delivery
card
bank_transfer
wallet
```

All payment records start as `pending`.

## Checkout Totals

Checkout calculates:

- subtotal
- coupon discount
- delivery charge
- service charge
- grand total

Orders are grouped by vendor because the `orders` table belongs to a single vendor.

## Testing Steps

1. Run migrations:

```bash
php artisan migrate
```

2. Ensure default categories exist:

```bash
php artisan db:seed
```

3. Log in as a customer and visit `/customer/products`.

4. Add an approved product to cart.

5. Update quantity, remove item, and clear cart from `/customer/cart`.

6. Add a product to wishlist and move it to cart from `/customer/wishlist`.

7. Visit `/customer/checkout`.

8. Try scheduling delivery less than 30 minutes from now; validation should fail.

9. Schedule a valid future time and place the order.

10. Confirm order items, payment placeholder, delivery record, and reduced stock in the database.
