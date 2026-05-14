# DailyCart Eloquent Model Guide

The full model code lives in `app/Models`. Each model now uses explicit `$fillable` fields, money casts, relationship methods, and SoftDeletes where the table supports `deleted_at`.

## Artisan Commands

```bash
php artisan make:model Role
php artisan make:model Customer
php artisan make:model Vendor
php artisan make:model Rider
php artisan make:model Admin
php artisan make:model Category
php artisan make:model Product
php artisan make:model ProductImage
php artisan make:model ProductVariant
php artisan make:model Inventory
php artisan make:model Cart
php artisan make:model CartItem
php artisan make:model Wishlist
php artisan make:model Coupon
php artisan make:model Order
php artisan make:model OrderItem
php artisan make:model Payment
php artisan make:model Delivery
php artisan make:model RiderLocation
php artisan make:model DeliveryProof
php artisan make:model Review
php artisan make:model Notification
php artisan make:model SupportTicket
php artisan make:model WalletTransaction
php artisan make:model Refund
php artisan make:model LoyaltyPoint
php artisan make:model Advertisement
php artisan make:model ActivityLog
php artisan make:model Subscription
```

`User` already exists in Laravel. `Role` extends Spatie Permission's role model so the project can support both `User belongsTo Role` for a primary role and Spatie's many-to-many role assignments.

## Implemented Model Files

```text
app/Models/Role.php
app/Models/User.php
app/Models/Customer.php
app/Models/Vendor.php
app/Models/Rider.php
app/Models/Admin.php
app/Models/Category.php
app/Models/Product.php
app/Models/ProductImage.php
app/Models/ProductVariant.php
app/Models/Inventory.php
app/Models/Cart.php
app/Models/CartItem.php
app/Models/Wishlist.php
app/Models/Coupon.php
app/Models/Order.php
app/Models/OrderItem.php
app/Models/Payment.php
app/Models/Delivery.php
app/Models/RiderLocation.php
app/Models/DeliveryProof.php
app/Models/Review.php
app/Models/Notification.php
app/Models/SupportTicket.php
app/Models/WalletTransaction.php
app/Models/Refund.php
app/Models/LoyaltyPoint.php
app/Models/Advertisement.php
app/Models/ActivityLog.php
app/Models/Subscription.php
```

## Relationship Examples

```php
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Vendor;

$customer = Customer::with(['user.role', 'cart.items.product', 'orders.payment'])->first();

$activeCartItems = $customer->cart?->cartItems ?? collect();

$orders = $customer->orders()
    ->with(['vendor', 'items.product', 'payment', 'delivery.rider'])
    ->latest()
    ->get();

$vendorProducts = Vendor::with(['products.category', 'coupons'])->find($vendorId);

$product = Product::with(['vendor', 'category', 'images', 'variants', 'inventory', 'reviews.customer'])
    ->where('slug', $slug)
    ->firstOrFail();

$order = Order::with(['customer.user', 'vendor', 'items', 'payment', 'delivery.rider', 'refund'])
    ->where('order_number', $orderNumber)
    ->firstOrFail();
```

## LKR Money Usage

Money fields are cast as `decimal:2` in the models. Store only LKR amounts in the database.

```php
$order->total_amount; // "1500.00"
```

For display:

```php
\App\Services\CurrencyService::formatLkr($order->total_amount);
```

Output:

```text
Rs. 1,500.00
```

## Delivery Scheduling Usage

`Order` casts `placed_at` and `scheduled_delivery_at` as datetimes.

```php
$schedule = app(\App\Services\DeliveryScheduleService::class);

$request->validate([
    'scheduled_delivery_at' => [
        'required',
        'date',
        $schedule->validationRule(now()),
    ],
]);
```
