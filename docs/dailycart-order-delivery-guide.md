# DailyCart Order Management and Delivery Workflow

## Artisan Commands Used

```bash
php artisan make:controller Customer/CustomerOrderController
php artisan make:controller Vendor/VendorOrderController
php artisan make:controller Admin/AdminOrderController
php artisan make:controller Admin/DeliveryController
php artisan make:controller Rider/RiderDeliveryController
php artisan make:service OrderStatusService
php artisan make:service DeliveryService
php artisan make:service RiderEarningService
php artisan make:request CancelOrderRequest
php artisan make:request AssignRiderRequest
php artisan make:request AdminOrderStatusRequest
php artisan make:request DeliveryProofRequest
php artisan make:request FailedDeliveryRequest
php artisan make:request RiderLocationRequest
php artisan make:policy OrderPolicy --model=Order
php artisan make:policy DeliveryPolicy --model=Delivery
php artisan make:notification OrderConfirmedNotification
php artisan make:notification OrderPackedNotification
php artisan make:notification RiderAssignedNotification
php artisan make:notification OutForDeliveryNotification
php artisan make:notification OrderDeliveredNotification
php artisan make:notification OrderCancelledNotification
php artisan make:migration add_order_delivery_workflow_fields
```

## Status Flow Rules

Order statuses:

- `pending`
- `confirmed`
- `packed`
- `assigned_to_rider`
- `out_for_delivery`
- `delivered`
- `cancelled`
- `refunded`

Delivery statuses:

- `pending`
- `assigned`
- `picked_up`
- `on_the_way`
- `delivered`
- `failed`
- `cancelled`

Critical transitions are handled in `app/Services/OrderStatusService.php` and `app/Services/DeliveryService.php` with `DB::transaction()` where order, delivery, rider, payment, and proof data must stay consistent.

## Role Rules

- Customers can view only their own orders and can cancel only `pending` orders.
- Vendors can view and manage only orders where `orders.vendor_id` matches their vendor profile.
- Vendors can confirm only `pending` orders and mark packed only `confirmed` orders.
- Admins can view all orders, filter orders, assign verified riders, and monitor deliveries.
- Admins can assign riders only when the order status is `packed`.
- Riders can view and update only deliveries assigned to their rider profile.
- Riders can mark picked up only `assigned` deliveries, on the way only `picked_up` deliveries, and delivered only `on_the_way` deliveries.

## Delivery Completion

When a rider marks a delivery as delivered:

- The delivery status becomes `delivered`.
- The order status becomes `delivered`.
- `delivered_at` is saved.
- Delivery proof photo and optional customer signature are saved in public storage.
- Cash on Delivery payments are marked `paid`.
- Rider availability is returned to `available`.
- The customer receives an order delivered notification record.

## LKR Display

Money is stored as `decimal(10,2)` in the database and displayed with:

```php
\App\Services\CurrencyService::formatLkr($amount)
```

Output example:

```text
Rs. 1,500.00
```

## Delivery Scheduling

Checkout stores `orders.scheduled_delivery_at` and creates a matching `deliveries.scheduled_at`.
The checkout validation rule remains: delivery time must be at least 30 minutes after order placement.

## Testing Checklist

1. Log in as a customer, place an order with a scheduled delivery time at least 30 minutes ahead, then confirm it appears under Customer Orders.
2. Try cancelling the customer order while it is `pending`; it should succeed.
3. Place another order and log in as the correct vendor.
4. Confirm the order, then mark it as packed.
5. Log in as admin and assign a verified rider to the packed order.
6. Log in as that rider and confirm only assigned deliveries are visible.
7. Mark the delivery as picked up, then on the way, then delivered with a proof image.
8. Confirm the order status is `delivered`, delivery status is `delivered`, and Cash on Delivery payment is `paid`.
9. Try accessing another customer's order, another vendor's order, or another rider's delivery; access should be denied.
