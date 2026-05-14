# DailyCart Authentication And RBAC Guide

DailyCart uses Laravel Breeze for authentication, `users.role_id` for the user's primary role, and Spatie Permission for role assignment compatibility.

## Seeders

Run:

```bash
php artisan db:seed
```

Seeded roles:

```text
Super Admin
Admin
Vendor
Rider
Customer
```

Default Super Admin:

```text
Email: superadmin@dailycart.lk
Password: Password@123
```

## Middleware

Registered in `bootstrap/app.php`:

```php
'role' => \App\Http\Middleware\CheckRole::class
'vendor.approved' => \App\Http\Middleware\EnsureVendorApproved::class
'rider.approved' => \App\Http\Middleware\EnsureRiderApproved::class
```

## Protected Routes

```php
Route::middleware(['auth', 'verified', 'role:Super Admin'])->prefix('super-admin')->group(...);
Route::middleware(['auth', 'verified', 'role:Super Admin,Admin'])->prefix('admin')->group(...);
Route::middleware(['auth', 'verified', 'role:Vendor', 'vendor.approved'])->prefix('vendor')->group(...);
Route::middleware(['auth', 'verified', 'role:Rider', 'rider.approved'])->prefix('rider')->group(...);
Route::middleware(['auth', 'verified', 'role:Customer'])->prefix('customer')->group(...);
```

## Registration Flows

- `/register` creates a Customer user and Customer profile.
- `/vendor/register` creates a pending Vendor user and Vendor profile.
- `/rider/register` creates a pending Rider user and Rider profile.

Pending vendors and riders can log in, but they are sent to approval-pending screens until approved.

## Approval Flows

Admin and Super Admin users can approve or reject:

```text
/admin/vendors
/admin/riders
```

Approval changes:

- Vendor: `vendors.status = approved`, `users.status = active`
- Rider: `riders.verification_status = verified`, `riders.availability_status = available`, `users.status = active`

## Policies

Implemented policies:

```text
ProductPolicy
OrderPolicy
DeliveryPolicy
```

Security intent:

- Vendors can manage only their own products and orders.
- Riders can update only their own assigned deliveries.
- Customers can view only their own orders.
- Admin and Super Admin can manage all protected resources.

## Testing Steps

1. Run migrations and seeders:

```bash
php artisan migrate
php artisan db:seed
```

2. Log in as Super Admin:

```text
superadmin@dailycart.lk
Password@123
```

3. Visit `/dashboard`; it should redirect to `/super-admin/dashboard`.

4. Register a vendor at `/vendor/register`; it should redirect to `/vendor/pending`.

5. Log in as Super Admin and approve the vendor at `/admin/vendors`.

6. Log in as the approved vendor and visit `/dashboard`; it should redirect to `/vendor/dashboard`.

7. Register a rider at `/rider/register`; it should redirect to `/rider/pending`.

8. Approve the rider at `/admin/riders`, then verify the rider dashboard works.

9. Register a customer at `/register`; it should redirect to `/customer/dashboard`.

10. Try visiting another role dashboard while logged in as Customer, Vendor, or Rider; the app should return `403`.
