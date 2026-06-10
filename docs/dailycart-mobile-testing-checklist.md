# DailyCart Mobile Testing Checklist

Project: DailyCart Mobile App  
Platforms: Android and iOS  
Backend: Laravel REST API with Sanctum  
Rules: English only, LKR only, PayHere payments, Firebase notifications, Google Maps, delivery schedule minimum order time plus 30 minutes.

## Authentication Tests

1. Customer login with valid credentials redirects to `/customer-home`.
2. Vendor login redirects approved vendors to `/vendor-dashboard`.
3. Rider login redirects approved riders to `/rider-dashboard`.
4. Pending vendor and rider accounts show the admin approval message.
5. Invalid login shows an error and does not store a token.
6. Logout clears secure storage and redirects to login.
7. Restart the app after login and confirm token persistence.
8. Confirm customer, vendor, and rider role-based redirects.

## Customer Tests

1. View products, categories, featured products, best sellers, flash deals, and promotions.
2. Search by product name, brand, category, SKU, and barcode.
3. Add and remove wishlist items.
4. Add products to cart, update quantities, and remove cart items.
5. Apply valid and invalid coupons.
6. Select delivery address and delivery schedule at least 30 minutes after order time.
7. Create Cash on Delivery order.
8. Complete PayHere WebView payment and verify payment success/failure handling.
9. View orders and track order status.
10. Submit review only for delivered orders.
11. Create support ticket and reply.
12. View loyalty point balance and history.

## Vendor Tests

1. Add, edit, and delete vendor-owned products.
2. Upload product image and multiple images.
3. View product approval status: pending, approved, rejected, inactive, out of stock.
4. View vendor-owned orders only.
5. Confirm pending order.
6. Mark confirmed order as packed.
7. Cancel eligible order with reason.
8. View vendor earnings and LKR totals.
9. View customer reviews for own products only.
10. Edit vendor profile.

## Rider Tests

1. View assigned deliveries only.
2. Open delivery details.
3. Mark assigned delivery as picked up.
4. Mark picked up delivery as on the way.
5. Mark on the way delivery as delivered with proof image.
6. Mark eligible delivery as failed with reason.
7. Update rider location from map screen.
8. View rider earnings and delivery counts.
9. Edit rider profile.

## Regression Tests

1. All money appears as `Rs. 1,500.00`.
2. All app text is English.
3. Unauthorized users cannot open protected API data.
4. Offline or network errors show readable messages.
5. Firebase notifications open the correct order/delivery context where supported.
