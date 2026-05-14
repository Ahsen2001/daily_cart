# DailyCart Migration Guide

This project already contains executable DailyCart migrations in `database/migrations`. Laravel's default `users` migration and Spatie Permission's generated `roles` migration are reused instead of duplicating authentication and RBAC tables.

## Artisan Commands

Use these commands when creating the migration files manually in a fresh Laravel project:

```bash
php artisan make:migration create_roles_table
php artisan make:migration create_users_table
php artisan make:migration create_customers_table
php artisan make:migration create_vendors_table
php artisan make:migration create_riders_table
php artisan make:migration create_admins_table
php artisan make:migration create_categories_table
php artisan make:migration create_products_table
php artisan make:migration create_product_images_table
php artisan make:migration create_product_variants_table
php artisan make:migration create_inventory_table
php artisan make:migration create_carts_table
php artisan make:migration create_cart_items_table
php artisan make:migration create_wishlists_table
php artisan make:migration create_coupons_table
php artisan make:migration create_orders_table
php artisan make:migration create_order_items_table
php artisan make:migration create_payments_table
php artisan make:migration create_deliveries_table
php artisan make:migration create_rider_locations_table
php artisan make:migration create_delivery_proofs_table
php artisan make:migration create_reviews_table
php artisan make:migration create_notifications_table
php artisan make:migration create_support_tickets_table
php artisan make:migration create_wallet_transactions_table
php artisan make:migration create_refunds_table
php artisan make:migration create_loyalty_points_table
php artisan make:migration create_advertisements_table
php artisan make:migration create_activity_logs_table
php artisan make:migration create_subscriptions_table
```

In this project, the same schema is implemented through these active migration files:

```text
0001_01_01_000000_create_users_table.php
2026_05_14_162547_create_permission_tables.php
2026_05_14_170000_add_dailycart_fields_to_users_table.php
2026_05_14_170001_create_dailycart_profile_tables.php
2026_05_14_170002_create_dailycart_catalog_tables.php
2026_05_14_170003_create_dailycart_cart_order_tables.php
2026_05_14_170004_create_dailycart_payment_delivery_tables.php
2026_05_14_170005_create_dailycart_engagement_tables.php
2026_05_14_170006_normalize_dailycart_money_and_soft_deletes.php
```

## Table Order And Fields

### 1. roles

Provided by Spatie Permission:

```php
Schema::create('roles', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('name');
    $table->string('guard_name');
    $table->timestamps();
    $table->unique(['name', 'guard_name']);
});
```

### 2. users

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name')->index();
    $table->string('email')->unique();
    $table->string('phone')->nullable()->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->enum('status', ['active', 'inactive', 'suspended', 'pending'])->default('active')->index();
    $table->rememberToken();
    $table->timestamps();
    $table->softDeletes();
});
```

### 3-6. user profile tables

```php
Schema::create('customers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
    $table->string('first_name')->index();
    $table->string('last_name')->nullable();
    $table->string('phone');
    $table->string('address_line_1');
    $table->string('address_line_2')->nullable();
    $table->string('city')->index();
    $table->string('district')->index();
    $table->string('postal_code')->nullable();
    $table->enum('status', ['active', 'inactive', 'blocked'])->default('active')->index();
    $table->timestamps();
    $table->softDeletes();
});

Schema::create('vendors', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
    $table->string('store_name')->index();
    $table->string('business_registration_no')->nullable()->unique();
    $table->string('phone');
    $table->text('address');
    $table->string('city')->index();
    $table->string('district')->index();
    $table->decimal('commission_rate', 5, 2)->default(0);
    $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])->default('pending')->index();
    $table->timestamp('approved_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
});

Schema::create('riders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
    $table->enum('vehicle_type', ['bicycle', 'motorbike', 'three_wheeler', 'van']);
    $table->string('vehicle_number')->nullable();
    $table->string('license_number')->nullable()->unique();
    $table->enum('availability_status', ['available', 'unavailable', 'delivering'])->default('unavailable')->index();
    $table->enum('verification_status', ['pending', 'verified', 'rejected', 'suspended'])->default('pending')->index();
    $table->timestamps();
    $table->softDeletes();
});

Schema::create('admins', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
    $table->string('department')->nullable();
    $table->enum('access_level', ['admin', 'super_admin'])->default('admin')->index();
    $table->enum('status', ['active', 'inactive'])->default('active')->index();
    $table->timestamps();
    $table->softDeletes();
});
```

### 7-11. catalog tables

```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
    $table->string('name')->index();
    $table->string('slug')->unique();
    $table->string('image')->nullable();
    $table->enum('status', ['active', 'inactive'])->default('active')->index();
    $table->timestamps();
    $table->softDeletes();
});

Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('vendor_id')->constrained()->restrictOnDelete();
    $table->foreignId('category_id')->constrained()->restrictOnDelete();
    $table->string('name')->index();
    $table->string('slug');
    $table->text('description')->nullable();
    $table->decimal('base_price', 10, 2);
    $table->decimal('sale_price', 10, 2)->nullable();
    $table->string('unit')->default('item');
    $table->enum('status', ['draft', 'pending', 'active', 'inactive', 'rejected', 'out_of_stock'])->default('draft')->index();
    $table->boolean('is_featured')->default(false)->index();
    $table->timestamps();
    $table->softDeletes();
    $table->unique(['vendor_id', 'slug']);
    $table->index(['category_id', 'status']);
});

Schema::create('product_images', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->string('image_path');
    $table->string('alt_text')->nullable();
    $table->unsignedInteger('sort_order')->default(0);
    $table->boolean('is_primary')->default(false)->index();
    $table->timestamps();
    $table->softDeletes();
});

Schema::create('product_variants', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('sku')->nullable()->unique();
    $table->decimal('price', 10, 2);
    $table->enum('status', ['active', 'inactive'])->default('active')->index();
    $table->timestamps();
    $table->softDeletes();
});

Schema::create('inventory', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
    $table->unsignedInteger('quantity')->default(0);
    $table->unsignedInteger('low_stock_threshold')->default(5);
    $table->timestamps();
    $table->unique(['product_id', 'product_variant_id']);
    $table->index(['product_id', 'quantity']);
});
```

### 12-17. cart, coupon, and order tables

```php
Schema::create('carts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
    $table->enum('status', ['active', 'converted', 'abandoned'])->default('active')->index();
    $table->timestamps();
    $table->softDeletes();
});

Schema::create('cart_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
    $table->unsignedInteger('quantity');
    $table->decimal('unit_price', 10, 2);
    $table->timestamps();
    $table->index(['cart_id', 'product_id']);
});

Schema::create('wishlists', function (Blueprint $table) {
    $table->id();
    $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->timestamps();
    $table->unique(['customer_id', 'product_id']);
});

Schema::create('coupons', function (Blueprint $table) {
    $table->id();
    $table->string('code')->unique();
    $table->enum('type', ['fixed', 'percentage']);
    $table->decimal('value', 10, 2);
    $table->decimal('minimum_order_amount', 10, 2)->default(0);
    $table->decimal('max_discount_amount', 10, 2)->nullable();
    $table->unsignedInteger('usage_limit')->nullable();
    $table->unsignedInteger('used_count')->default(0);
    $table->timestamp('starts_at')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->enum('status', ['active', 'inactive', 'expired'])->default('active')->index();
    $table->timestamps();
    $table->softDeletes();
});

Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->string('order_number')->unique();
    $table->foreignId('customer_id')->constrained()->restrictOnDelete();
    $table->foreignId('vendor_id')->constrained()->restrictOnDelete();
    $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
    $table->decimal('subtotal', 10, 2);
    $table->decimal('discount_amount', 10, 2)->default(0);
    $table->decimal('delivery_fee', 10, 2)->default(0);
    $table->decimal('tax_amount', 10, 2)->default(0);
    $table->decimal('total_amount', 10, 2);
    $table->char('currency', 3)->default('LKR');
    $table->text('delivery_address');
    $table->enum('order_status', ['pending', 'confirmed', 'preparing', 'ready_for_pickup', 'assigned_to_rider', 'picked_up', 'out_for_delivery', 'delivered', 'cancelled', 'rejected', 'refunded'])->default('pending')->index();
    $table->enum('payment_status', ['pending', 'paid', 'failed', 'cancelled', 'refunded', 'partially_refunded'])->default('pending')->index();
    $table->dateTime('placed_at')->index();
    $table->dateTime('scheduled_delivery_at')->index();
    $table->timestamps();
    $table->softDeletes();
    $table->index(['customer_id', 'order_status']);
    $table->index(['vendor_id', 'order_status']);
});

Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->cascadeOnDelete();
    $table->foreignId('product_id')->constrained()->restrictOnDelete();
    $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
    $table->foreignId('vendor_id')->constrained()->restrictOnDelete();
    $table->string('product_name');
    $table->unsignedInteger('quantity');
    $table->decimal('unit_price', 10, 2);
    $table->decimal('total_price', 10, 2);
    $table->timestamps();
    $table->index(['order_id', 'product_id']);
    $table->index('vendor_id');
});
```

### 18-21 and 26. payment and delivery tables

```php
Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
    $table->enum('payment_method', ['cash_on_delivery', 'card', 'bank_transfer', 'wallet']);
    $table->string('transaction_id')->nullable()->unique();
    $table->decimal('amount', 10, 2);
    $table->char('currency', 3)->default('LKR');
    $table->enum('status', ['pending', 'paid', 'failed', 'refunded', 'partially_refunded'])->default('pending')->index();
    $table->timestamp('paid_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
});

Schema::create('deliveries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
    $table->foreignId('rider_id')->nullable()->constrained()->nullOnDelete();
    $table->text('pickup_address');
    $table->text('delivery_address');
    $table->dateTime('scheduled_at')->index();
    $table->timestamp('picked_up_at')->nullable();
    $table->timestamp('delivered_at')->nullable();
    $table->enum('status', ['pending', 'assigned', 'picked_up', 'on_the_way', 'delivered', 'failed', 'cancelled'])->default('pending')->index();
    $table->timestamps();
    $table->softDeletes();
    $table->index(['rider_id', 'status']);
});

Schema::create('rider_locations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('rider_id')->constrained()->cascadeOnDelete();
    $table->decimal('latitude', 10, 7);
    $table->decimal('longitude', 10, 7);
    $table->dateTime('recorded_at')->index();
    $table->index(['rider_id', 'recorded_at']);
});

Schema::create('delivery_proofs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('delivery_id')->constrained()->cascadeOnDelete();
    $table->string('proof_image')->nullable();
    $table->string('customer_signature')->nullable();
    $table->text('note')->nullable();
    $table->dateTime('submitted_at');
    $table->timestamps();
});

Schema::create('refunds', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->cascadeOnDelete();
    $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
    $table->decimal('amount', 10, 2);
    $table->text('reason');
    $table->enum('status', ['requested', 'approved', 'rejected', 'processed', 'failed'])->default('requested')->index();
    $table->timestamp('processed_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
});
```

### 22-30. engagement, finance, marketing, and audit tables

```php
Schema::create('reviews', function (Blueprint $table) {
    $table->id();
    $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
    $table->foreignId('product_id')->nullable()->constrained()->cascadeOnDelete();
    $table->foreignId('vendor_id')->nullable()->constrained()->cascadeOnDelete();
    $table->foreignId('order_id')->constrained()->cascadeOnDelete();
    $table->unsignedTinyInteger('rating');
    $table->text('comment')->nullable();
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
    $table->timestamps();
    $table->softDeletes();
});

Schema::create('notifications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('title');
    $table->text('message');
    $table->string('type')->index();
    $table->timestamp('read_at')->nullable()->index();
    $table->timestamps();
});

Schema::create('support_tickets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
    $table->string('subject')->index();
    $table->text('message');
    $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->index();
    $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open')->index();
    $table->timestamps();
    $table->softDeletes();
});

Schema::create('wallet_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->enum('type', ['credit', 'debit'])->index();
    $table->enum('source', ['refund', 'order_payment', 'admin_adjustment', 'loyalty_redeem']);
    $table->decimal('amount', 10, 2);
    $table->char('currency', 3)->default('LKR');
    $table->string('description')->nullable();
    $table->timestamps();
    $table->index(['user_id', 'created_at']);
});

Schema::create('loyalty_points', function (Blueprint $table) {
    $table->id();
    $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
    $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
    $table->integer('points');
    $table->enum('type', ['earned', 'redeemed', 'expired', 'adjusted'])->index();
    $table->timestamp('expires_at')->nullable()->index();
    $table->timestamps();
});

Schema::create('advertisements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
    $table->string('title')->index();
    $table->string('image_path');
    $table->string('target_url')->nullable();
    $table->enum('placement', ['home_banner', 'category_banner', 'sidebar', 'product_page'])->index();
    $table->dateTime('starts_at')->index();
    $table->dateTime('ends_at')->index();
    $table->enum('status', ['pending', 'active', 'paused', 'expired', 'rejected'])->default('pending')->index();
    $table->timestamps();
    $table->softDeletes();
});

Schema::create('activity_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->string('action')->index();
    $table->string('module')->index();
    $table->text('description')->nullable();
    $table->string('ip_address', 45)->nullable();
    $table->text('user_agent')->nullable();
    $table->timestamps();
    $table->index(['user_id', 'created_at']);
});

Schema::create('subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
    $table->string('plan_name');
    $table->decimal('price', 10, 2);
    $table->char('currency', 3)->default('LKR');
    $table->dateTime('starts_at');
    $table->dateTime('ends_at')->index();
    $table->enum('status', ['active', 'expired', 'cancelled', 'pending'])->default('pending')->index();
    $table->timestamps();
    $table->softDeletes();
});
```

## Important Fields

- `currency` is always `LKR`; the app should not offer currency selection.
- Money columns use `decimal(10, 2)` to avoid floating-point rounding errors.
- `orders.scheduled_delivery_at` stores the customer-selected delivery time.
- `deliveries.status` tracks rider flow from assignment to delivered, failed, or cancelled.
- Status columns are indexed because dashboards filter by status frequently.
- Searchable fields such as product name, category, vendor, order status, and user ownership are indexed.

## Delivery Scheduling Validation

The 30-minute rule must be enforced in Laravel validation/service logic:

```php
$placedAt = now();

$request->validate([
    'scheduled_delivery_at' => [
        'required',
        'date',
        'after_or_equal:'.$placedAt->copy()->addMinutes(30)->toDateTimeString(),
    ],
]);
```

## Run Migrations

```bash
php artisan migrate
```

For a fresh local development rebuild:

```bash
php artisan migrate:fresh --seed
```

## Rollback Commands

Rollback the latest batch:

```bash
php artisan migrate:rollback
```

Rollback all migrations:

```bash
php artisan migrate:reset
```
