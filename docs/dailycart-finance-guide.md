# DailyCart Payment, Wallet, Refund, and Finance Management

## Artisan Commands Used

```bash
php artisan make:migration add_finance_management_fields
php artisan make:controller Customer/PaymentController
php artisan make:controller Customer/WalletController
php artisan make:controller Customer/RefundController
php artisan make:controller Admin/RefundController
php artisan make:controller Admin/AdminFinanceController
php artisan make:controller Vendor/VendorEarningController
php artisan make:controller Vendor/VendorRefundController
php artisan make:controller Rider/RiderEarningController
php artisan make:request SimulatePaymentRequest
php artisan make:request WalletTopUpRequest
php artisan make:request StoreRefundRequest
php artisan make:request ProcessRefundRequest
php artisan make:request FinanceFilterRequest
php artisan make:request EarningFilterRequest
php artisan make:policy PaymentPolicy --model=Payment
php artisan make:policy RefundPolicy --model=Refund
php artisan make:policy WalletPolicy
php artisan make:notification PaymentSuccessNotification
php artisan make:notification PaymentFailedNotification
php artisan make:notification RefundRequestedNotification
php artisan make:notification RefundApprovedNotification
php artisan make:notification RefundRejectedNotification
```

## Implemented Features

- Payment records are created during checkout and limited to one payment per order.
- Payment stores method, status, subtotal, discount, delivery fee, service charge, grand total, amount, and references.
- Cash on Delivery remains `pending` until the rider completes delivery.
- Card and bank transfer payments can be simulated as success or failed from the customer payment page.
- Wallet payments are processed during checkout and fail if the customer has insufficient balance.
- Wallet top-up placeholder adds LKR balance using a transactional ledger record.
- Wallet transactions store direction, transaction type, balance after, reference, and description.
- Customer refund requests are supported for delivered and paid orders.
- Admin refund approval updates refund, payment, order, and wallet records inside a transaction.
- Vendor earnings exclude refunded orders and deduct vendor commission.
- Rider earnings count delivered deliveries only.
- Admin finance dashboard reports revenue, delivery charges, service charges, vendor payouts, rider payouts, refunds, COD pending payments, and paid order count.

## Status Values

Payment statuses:

- `pending`
- `paid`
- `failed`
- `refunded`

Wallet transaction types:

- `top_up`
- `payment`
- `refund`
- `cashback`
- `adjustment`

Refund statuses:

- `requested`
- `approved`
- `rejected`
- `processed`
- `failed`

## Money Rules

All money fields use `decimal(10,2)` and the app displays LKR using:

```php
\App\Services\CurrencyService::formatLkr($amount)
```

Example:

```text
Rs. 1,500.00
```

## Testing Steps

1. Run `php artisan migrate`.
2. Log in as a customer and top up the wallet from Customer > Wallet.
3. Place an order using Wallet payment with enough balance; confirm wallet balance decreases and payment becomes `paid`.
4. Place an order using Card or Bank Transfer; open the payment page and simulate success or failure.
5. Place a Cash on Delivery order; confirm payment stays `pending` until rider marks delivery as delivered.
6. For a delivered paid order, request a refund as the customer.
7. Log in as Admin and approve the refund; confirm order status becomes `refunded`, payment status becomes `refunded`, and customer wallet balance increases.
8. Check Vendor Earnings to confirm refunded orders are excluded.
9. Check Rider Earnings to confirm only delivered orders count.
10. Check Admin Finance to confirm finance totals respond to date filters.
