<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ImportLegacyStorefronts extends Command
{
    protected $signature = 'dailycart:import-legacy-storefronts {--source=dailycart : Source MySQL database name}';

    protected $description = 'Import registered stores and catalog items from a legacy DailyCart schema.';

    public function handle(): int
    {
        $source = (string) $this->option('source');
        $connection = 'legacy_storefront_source';
        $config = config('database.connections.mysql');
        $config['database'] = $source;
        config(["database.connections.{$connection}" => $config]);
        DB::purge($connection);

        $legacy = DB::connection($connection);
        $schema = $legacy->getSchemaBuilder();

        if (! $schema->hasTable('vendor_profiles') || ! $schema->hasTable('users')) {
            $this->error("The {$source} database does not contain the legacy vendor profile schema.");

            return self::FAILURE;
        }

        $profiles = $legacy->table('vendor_profiles')->orderBy('id')->get();
        if ($profiles->isEmpty()) {
            $this->info('No legacy stores were found.');

            return self::SUCCESS;
        }

        $vendorRole = Role::findByName('Vendor', 'web');
        $importedStores = 0;
        $importedProducts = 0;

        DB::transaction(function () use ($legacy, $schema, $profiles, $vendorRole, &$importedStores, &$importedProducts): void {
            foreach ($profiles as $legacyProfile) {
                $legacyUser = $legacy->table('users')->where('id', $legacyProfile->user_id)->first();
                if (! $legacyUser) {
                    $this->warn("Skipped store {$legacyProfile->store_name}: its owner account is missing.");

                    continue;
                }

                $user = User::withTrashed()->where('email', $legacyUser->email)->first() ?? new User();
                $user->forceFill([
                    'name' => $legacyUser->name,
                    'email' => $legacyUser->email,
                    'phone' => $legacyUser->phone,
                    'password' => $legacyUser->password,
                    'status' => $legacyUser->status === 'inactive' ? 'inactive' : 'active',
                    'email_verified_at' => $legacyUser->email_verified_at,
                    'phone_verified_at' => $legacyUser->phone_verified_at,
                    'role_id' => $vendorRole->id,
                ]);
                $user->save();
                if ($user->trashed()) {
                    $user->restore();
                }
                $user->syncRoles([$vendorRole]);

                $approved = strtoupper((string) $legacyProfile->status) === 'APPROVED';
                $vendor = Vendor::withTrashed()->firstOrNew(['user_id' => $user->id]);
                $vendor->fill([
                    'store_name' => $legacyProfile->store_name,
                    'phone' => $legacyUser->phone ?? '',
                    'address' => 'Legacy store address pending update',
                    'city' => 'Pending',
                    'district' => 'Pending',
                    'commission_rate' => $legacyProfile->commission_rate ?? 0,
                    'status' => $approved ? 'approved' : 'pending',
                    'approved_at' => $approved ? now() : null,
                ]);
                $vendor->save();
                if ($vendor->trashed()) {
                    $vendor->restore();
                }

                $slug = $this->uniqueStoreSlug((string) ($legacyProfile->slug ?: $legacyProfile->store_name), $vendor->id);
                VendorProfile::updateOrCreate(
                    ['vendor_id' => $vendor->id],
                    [
                        'slug' => $slug,
                        'description' => $legacyProfile->description,
                        'logo_path' => $legacyProfile->logo,
                        'cover_image_path' => $legacyProfile->banner,
                        'delivery_estimate' => '30-60 minutes',
                        'contact_phone' => $legacyUser->phone,
                        'contact_email' => $legacyUser->email,
                        'is_featured' => (bool) $legacyProfile->is_featured,
                        'is_enabled' => $approved,
                        'profile_status' => $approved ? 'approved' : 'pending',
                        'approved_at' => $approved ? now() : null,
                    ],
                );

                $importedStores++;

                if (! $schema->hasTable('products')) {
                    continue;
                }

                $legacy->table('products')->where('vendor_id', $legacyProfile->id)->orderBy('id')->each(
                    function (object $legacyProduct) use ($legacy, $schema, $vendor, $user, &$importedProducts): void {
                        $category = $this->categoryForLegacyProduct($legacy, $schema, $legacyProduct);
                        $brand = $this->brandForLegacyProduct($legacy, $schema, $legacyProduct);
                        $imageUrl = $this->legacyProductImageUrl($legacy, $schema, $legacyProduct->id);

                        $productData = [
                            'vendor_id' => $vendor->id,
                            'category_id' => $category->id,
                            'brand_id' => $brand?->id,
                            'brand' => $brand?->name,
                            'name' => $legacyProduct->name,
                            'slug' => $this->uniqueProductSlug($legacyProduct->slug ?: $legacyProduct->name, $legacyProduct->sku),
                            'barcode' => $legacyProduct->barcode,
                            'description' => $legacyProduct->description,
                            'price' => $legacyProduct->price,
                            'discount_price' => $legacyProduct->discount_price,
                            'base_price' => $legacyProduct->price,
                            'sale_price' => $legacyProduct->discount_price,
                            'unit_type' => 'item',
                            'unit' => 'item',
                            'stock_quantity' => $legacyProduct->stock_quantity,
                            'is_featured' => (bool) $legacyProduct->is_featured,
                            'status' => in_array($legacyProduct->status, ['active', 'approved'], true) ? 'approved' : 'pending',
                            'created_by' => $user->id,
                            'deleted_at' => null,
                        ];

                        if ($imageUrl) {
                            $productData['image'] = $imageUrl;
                        }

                        Product::withTrashed()->updateOrCreate(
                            ['sku' => $legacyProduct->sku ?: null],
                            $productData,
                        );
                        $importedProducts++;
                    },
                );
            }
        });

        $this->info("Imported {$importedStores} store(s) and {$importedProducts} product(s).");

        return self::SUCCESS;
    }

    private function uniqueStoreSlug(string $value, int $vendorId): string
    {
        $base = Str::slug($value) ?: 'store';
        $slug = $base;

        if (VendorProfile::query()->where('slug', $slug)->where('vendor_id', '!=', $vendorId)->exists()) {
            $slug = "{$base}-{$vendorId}";
        }

        return $slug;
    }

    private function uniqueProductSlug(string $value, ?string $sku): string
    {
        $base = Str::slug($value) ?: 'product';
        $slug = $base;
        $existing = Product::withTrashed()->where('slug', $slug)->first();

        if ($existing && $existing->sku !== $sku) {
            $slug = "{$base}-".Str::lower(Str::random(6));
        }

        return $slug;
    }

    private function categoryForLegacyProduct($legacy, $schema, object $product): Category
    {
        $legacyCategory = $schema->hasTable('categories')
            ? $legacy->table('categories')->where('id', $product->category_id)->first()
            : null;
        $slug = Str::slug($legacyCategory?->slug ?: $legacyCategory?->name ?: 'uncategorized');

        return Category::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $legacyCategory?->name ?: 'Uncategorized',
                'description' => $legacyCategory?->description,
                'status' => 'active',
            ],
        );
    }

    private function brandForLegacyProduct($legacy, $schema, object $product): ?Brand
    {
        if (! $product->brand_id || ! $schema->hasTable('brands')) {
            return null;
        }

        $legacyBrand = $legacy->table('brands')->where('id', $product->brand_id)->first();
        if (! $legacyBrand) {
            return null;
        }

        return Brand::firstOrCreate(
            ['slug' => Str::slug($legacyBrand->slug ?: $legacyBrand->name)],
            ['name' => $legacyBrand->name],
        );
    }

    private function legacyProductImageUrl($legacy, $schema, int $productId): ?string
    {
        if (! $schema->hasTable('product_images') || ! $schema->hasColumn('product_images', 'url')) {
            return null;
        }

        $url = $legacy->table('product_images')
            ->where('product_id', $productId)
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->value('url');

        return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }
}
