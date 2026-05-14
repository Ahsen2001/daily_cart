# DailyCart Product And Category Management

This implementation uses Laravel Breeze, Blade, Tailwind CSS, MySQL, form requests, policies, and role middleware.

## Artisan Commands

```bash
php artisan make:controller Admin/CategoryController --resource
php artisan make:controller Admin/AdminProductController
php artisan make:controller Vendor/ProductController --resource
php artisan make:controller Vendor/ProductImageController
php artisan make:controller Vendor/ProductVariantController
php artisan make:controller Customer/ProductBrowseController

php artisan make:request CategoryRequest
php artisan make:request StoreProductRequest
php artisan make:request UpdateProductRequest
php artisan make:request StoreProductVariantRequest

php artisan make:policy ProductPolicy --model=Product
php artisan make:policy CategoryPolicy --model=Category

php artisan make:seeder CategorySeeder
php artisan make:migration add_product_category_management_fields
```

## Admin Workflow

- Create, edit, and deactivate categories.
- View all vendor products.
- Search/filter products by name, brand, SKU, status, and category.
- Approve or reject products.
- Toggle homepage featured products.
- Set product status manually.

Admin routes:

```text
/admin/categories
/admin/products
```

## Vendor Workflow

- Add own products only.
- Edit own products only.
- Upload one main image and multiple gallery images.
- Add variants such as `500g`, `1kg`, `2kg`, `5kg`, `Small`, `Medium`, and `Large`.
- Update stock quantity.
- Product changes are sent back to `pending` for admin approval.

Vendor routes:

```text
/vendor/products
/vendor/products/create
/vendor/products/{product}/edit
```

## Customer Visibility

Customers can view only products where:

```text
products.status = approved
categories.status = active
```

Customer routes:

```text
/customer/products
/customer/products/{product}
```

## Image Handling

- Images are validated as `jpg`, `jpeg`, `png`, or `webp`.
- Max image size is `2048 KB`.
- Files are stored on Laravel's `public` disk.
- Product images are deleted from storage when their database records are deleted.
- The public storage link is created with:

```bash
php artisan storage:link
```

## LKR Prices

Money is stored as `decimal(10,2)` and displayed with:

```php
\App\Services\CurrencyService::formatLkr($product->price);
```

Example:

```text
Rs. 1,500.00
```

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

3. Create or edit categories at `/admin/categories`.

4. Register and approve a vendor.

5. Log in as the approved vendor and create a product at `/vendor/products/create`.

6. Confirm the product appears as `pending`.

7. Log in as Super Admin or Admin and approve it at `/admin/products`.

8. Log in as a customer and confirm the product appears at `/customer/products`.

9. Reject or mark the product inactive and confirm it disappears from the customer product list.

10. Try editing another vendor's product as a vendor; the policy should return `403`.
