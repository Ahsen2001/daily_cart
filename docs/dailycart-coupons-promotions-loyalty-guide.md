# DailyCart Coupons, Promotions, Advertisements, and Loyalty Points

## Artisan Commands Used

```bash
php artisan make:migration add_promotions_coupons_loyalty_fields
php artisan make:model CouponRedemption
php artisan make:model Promotion
php artisan make:model LoyaltySetting
php artisan make:controller Customer/CouponController
php artisan make:controller Customer/PromotionController
php artisan make:controller Customer/AdvertisementController
php artisan make:controller Customer/LoyaltyPointController
php artisan make:controller Vendor/VendorCouponController
php artisan make:controller Vendor/VendorPromotionController
php artisan make:controller Admin/AdminCouponController
php artisan make:controller Admin/AdminPromotionController
php artisan make:controller Admin/AdvertisementController
php artisan make:controller Admin/AdminLoyaltySettingController
php artisan make:request StoreCouponRequest
php artisan make:request StorePromotionRequest
php artisan make:request StoreAdvertisementRequest
php artisan make:request UpdateLoyaltySettingRequest
php artisan make:policy CouponPolicy --model=Coupon
php artisan make:policy PromotionPolicy --model=Promotion
php artisan make:policy AdvertisementPolicy --model=Advertisement
php artisan make:policy LoyaltyPointPolicy
```

## Implemented Features

- Customer pages for active coupons, promotions, advertisements, and loyalty history.
- Vendor coupon management scoped to the authenticated vendor.
- Vendor promotion management scoped to the authenticated vendor.
- Admin coupon, promotion, advertisement, and loyalty setting management.
- Coupon validation supports active dates, minimum order, usage limit, per-customer limit, vendor scoping, percentage maximum discount, free delivery, and redemption recording.
- Checkout revalidates coupons and loyalty points inside the order creation transaction.
- Loyalty points can be redeemed during checkout and are distributed across vendor-split orders without making totals negative.
- Loyalty points are earned when an order is delivered.
- Loyalty earned points are reversed when an order is refunded or cancelled.
- Advertisement and promotion records include basic performance counters.

## Status Values

Coupon statuses:

- `active`
- `inactive`
- `expired`

Promotion statuses:

- `active`
- `inactive`
- `expired`

Loyalty transaction types:

- `earned`
- `redeemed`
- `reversed`
- `adjusted`
- `expired`

## Testing Steps

1. Run `php artisan migrate`.
2. Create a global coupon from Admin > Coupons.
3. Create a vendor coupon from Vendor > Coupons and confirm it applies only to that vendor’s order group.
4. Apply a coupon during checkout and confirm the LKR discount appears before the final total.
5. Set loyalty rates from Admin > Loyalty Settings.
6. Complete a delivered order and confirm points are earned.
7. Redeem loyalty points during checkout and confirm the discount cannot exceed the order total.
8. Refund or cancel an order with earned points and confirm reversal records are added.
9. Create active/inactive/expired promotions and advertisements and confirm customers see only active records.
