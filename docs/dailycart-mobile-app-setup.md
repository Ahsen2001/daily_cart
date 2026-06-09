# DailyCart Mobile App Setup

The Flutter source lives in `dailycart_mobile`.

## Current status

- Flutter app shell is scaffolded.
- Brand theme is implemented with Poppins, green/orange colors, rounded controls, and soft shadows.
- App routing is wired with splash, login, home, products, cart, checkout, PayHere WebView, and map screens.
- Laravel API client is configured through `.env`.
- Firebase Messaging and local notification bootstrap code is in place.
- Google Maps screen is in place.

## Required local install

Flutter is not currently available on this machine's PATH. Install Flutter latest stable, then run:

```bash
cd dailycart_mobile
flutter create --project-name dailycart_mobile --org com.dailycart --platforms=android,ios .
flutter pub get
dart run flutter_launcher_icons
flutter run
```

## Android

For an Android emulator, keep:

```env
API_BASE_URL=http://10.0.2.2:8000/api
```

Start Laravel from the repository root:

```bash
php artisan serve
```

Then run the mobile app:

```bash
cd dailycart_mobile
flutter run
```

## iOS

iOS requires macOS and Xcode. For the iOS simulator, use your Mac host IP or localhost depending on where the Laravel server is running:

```env
API_BASE_URL=http://127.0.0.1:8000/api
```

## Next backend step

The Laravel app currently has web routes, but no dedicated mobile API routes or token auth. Add Laravel Sanctum or another bearer-token API auth layer before connecting real login, cart, checkout, and notifications.

The Flutter app expects these auth endpoints under `https://dailycart.lk/api`:

- `POST /login`
- `POST /register`
- `POST /logout`
- `POST /forgot-password`
- `POST /otp/verify`
- `GET /user`

The testing API base URL is configured as:

```env
TESTING_API_BASE_URL=https://your-laravel-cloud-url.laravel.cloud/api
```

## UI foundation

The app entrypoint now uses:

- `lib/main.dart`
- `lib/app.dart`
- `lib/routes/app_router.dart`
- `lib/theme/light_theme.dart`
- `lib/theme/app_colors.dart`
- `lib/theme/app_text_styles.dart`

Reusable UI is in `lib/widgets`, including buttons, text fields, loading, empty, error, app bar, app drawer, cards, and logo widgets.

## Authentication testing checklist

1. Test customer login with valid credentials and confirm redirect to `/customer-home`.
2. Test vendor login while pending approval and confirm the pending approval message appears.
3. Test rider login while pending approval and confirm the pending approval message appears.
4. Test invalid password and confirm an error message appears without storing a token.
5. Test logout from the drawer and confirm secure token/user data is cleared.
6. Test app restart after login and confirm the splash screen restores the stored token and redirects by role.
7. Test registration for customer, vendor, and rider.
8. Test forgot password with a valid email and an invalid email.
9. Test OTP verification with an invalid code and a valid 6-digit code.

## Customer shopping testing checklist

1. Open `/customer-home` and confirm categories, featured products, best sellers, new arrivals, flash deals, recommended products, recently viewed products, and advertisement banners render.
2. Pull to refresh on customer home and confirm categories/products reload.
3. Open `/categories` and confirm all category cards show image placeholders, names, and product counts.
4. Tap a category and confirm `/products` opens with that category filter.
5. Toggle grid/list view on `/products`.
6. Test sorting: latest, price low to high, price high to low, highest rated, and most sold.
7. Test filters: category, price range, rating, availability, and brand.
8. Open a product details page and confirm image slider, price, discount price, rating, description, stock, vendor, variants, quantity selector, similar products, and reviews.
9. Test Add to Cart, Buy Now, Add to Wishlist, and Share placeholder actions.
10. Open `/search`, search by product name, brand, category, SKU, and barcode terms.
11. Confirm recent searches and popular searches display.
12. Confirm inactive, pending, and rejected products are not shown in customer product lists.

## Wishlist, cart, coupon, and checkout preparation testing checklist

1. Add an approved active product to wishlist and confirm it appears on `/wishlist`.
2. Try adding the same product to wishlist twice and confirm duplicate prevention.
3. Remove a wishlist item and confirm the empty wishlist message appears when no items remain.
4. Move a wishlist item to cart and confirm it appears on `/cart`.
5. Add an approved active product to cart from product details.
6. Try adding an out-of-stock product and confirm the app blocks it.
7. Increase and decrease cart quantity and confirm stock validation.
8. Remove a cart item and confirm totals update.
9. Clear the cart and confirm the empty cart message appears.
10. Apply a valid coupon and confirm the discount updates in LKR.
11. Apply an invalid or expired coupon and confirm an error message appears.
12. Remove an applied coupon and confirm discount resets.
13. Open `/checkout-preparation`, review cart items, confirm delivery address, and confirm totals.
14. Confirm no payment flow starts from checkout preparation; payment belongs to Step 6.

## Checkout, delivery, and PayHere testing checklist

1. Open `/checkout` with a non-empty cart and confirm cart items and LKR totals display.
2. Open `/addresses`, add a new address, then confirm it appears in the address list.
3. Edit an address and confirm the updated fields save.
4. Delete an address and confirm it disappears.
5. Set a default address and confirm the default badge appears.
6. Select an address and confirm checkout shows the selected address.
7. Open `/delivery-schedule` and select a delivery time at least 30 minutes from now.
8. Try selecting a delivery time before 30 minutes and confirm the error: `Delivery time must be at least 30 minutes after placing the order.`
9. Open `/payment-method` and select Cash on Delivery.
10. Place a Cash on Delivery order and confirm `/order-success` appears.
11. Select PayHere Card Payment and place the order.
12. Confirm `/payhere-webview` opens the Laravel PayHere payment URL.
13. Return from a URL containing `/payment/success` and confirm the app checks payment status.
14. Confirm paid orders show `/payment-success`.
15. Confirm cancel or failed URLs show `/payment-failed`.
16. Select Bank Transfer or Wallet and confirm placeholder messages appear.
17. Confirm all payment/order amounts are displayed in LKR only.
