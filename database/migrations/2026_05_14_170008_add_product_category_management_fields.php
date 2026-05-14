<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'description')) {
                $table->text('description')->nullable()->after('slug');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'brand')) {
                $table->string('brand')->nullable()->after('slug')->index();
            }

            if (! Schema::hasColumn('products', 'price')) {
                $table->decimal('price', 10, 2)->default(0)->after('description');
            }

            if (! Schema::hasColumn('products', 'discount_price')) {
                $table->decimal('discount_price', 10, 2)->nullable()->after('price');
            }

            if (! Schema::hasColumn('products', 'unit_type')) {
                $table->string('unit_type')->default('item')->after('discount_price');
            }

            if (! Schema::hasColumn('products', 'weight')) {
                $table->string('weight')->nullable()->after('unit_type');
            }

            if (! Schema::hasColumn('products', 'sku')) {
                $table->string('sku')->nullable()->unique()->after('weight');
            }

            if (! Schema::hasColumn('products', 'barcode')) {
                $table->string('barcode')->nullable()->unique()->after('sku');
            }

            if (! Schema::hasColumn('products', 'stock_quantity')) {
                $table->unsignedInteger('stock_quantity')->default(0)->after('barcode')->index();
            }

            if (! Schema::hasColumn('products', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('stock_quantity');
            }

            if (! Schema::hasColumn('products', 'image')) {
                $table->string('image')->nullable()->after('expiry_date');
            }

            if (! Schema::hasColumn('products', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('image')->constrained('users')->nullOnDelete();
            }

            $table->index(['vendor_id', 'status', 'is_featured'], 'products_vendor_status_featured_index');
            $table->index(['name', 'brand'], 'products_name_brand_index');
        });

        DB::table('products')->update([
            'price' => DB::raw('base_price'),
            'discount_price' => DB::raw('sale_price'),
            'unit_type' => DB::raw('unit'),
        ]);

        DB::statement("ALTER TABLE products MODIFY status ENUM('pending', 'approved', 'rejected', 'inactive', 'out_of_stock') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE products MODIFY status ENUM('draft', 'pending', 'active', 'inactive', 'rejected', 'out_of_stock') NOT NULL DEFAULT 'draft'");

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_vendor_status_featured_index');
            $table->dropIndex('products_name_brand_index');

            if (Schema::hasColumn('products', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }

            $table->dropColumn([
                'brand',
                'price',
                'discount_price',
                'unit_type',
                'weight',
                'sku',
                'barcode',
                'stock_quantity',
                'expiry_date',
                'image',
            ]);
        });

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
