<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            if (! Schema::hasColumn('coupons', 'title')) {
                $table->string('title')->nullable()->after('code');
            }
            if (! Schema::hasColumn('coupons', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (! Schema::hasColumn('coupons', 'discount_type')) {
                $table->enum('discount_type', ['fixed_amount', 'percentage', 'free_delivery'])->default('fixed_amount')->after('description')->index();
            }
            if (! Schema::hasColumn('coupons', 'discount_value')) {
                $table->decimal('discount_value', 10, 2)->default(0)->after('discount_type');
            }
            if (! Schema::hasColumn('coupons', 'maximum_discount_amount')) {
                $table->decimal('maximum_discount_amount', 10, 2)->nullable()->after('minimum_order_amount');
            }
            if (! Schema::hasColumn('coupons', 'per_customer_limit')) {
                $table->unsignedInteger('per_customer_limit')->nullable()->after('used_count');
            }
        });

        DB::statement("
            UPDATE coupons
            SET discount_type = CASE WHEN type = 'percentage' THEN 'percentage' ELSE 'fixed_amount' END,
                discount_value = value,
                maximum_discount_amount = max_discount_amount,
                title = COALESCE(title, code)
        ");

        if (! Schema::hasTable('coupon_redemptions')) {
            Schema::create('coupon_redemptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->decimal('discount_amount', 10, 2);
                $table->timestamps();

                $table->unique(['coupon_id', 'order_id']);
                $table->index(['coupon_id', 'customer_id']);
            });
        }

        if (! Schema::hasTable('promotions')) {
            Schema::create('promotions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->enum('promotion_type', ['flash_sale', 'seasonal_offer', 'featured_offer', 'clearance_sale'])->index();
                $table->enum('target_type', ['product', 'category', 'vendor', 'global'])->default('global')->index();
                $table->unsignedBigInteger('target_id')->nullable()->index();
                $table->enum('discount_type', ['fixed_amount', 'percentage']);
                $table->decimal('discount_value', 10, 2);
                $table->string('banner_image')->nullable();
                $table->dateTime('starts_at')->index();
                $table->dateTime('ends_at')->index();
                $table->enum('status', ['active', 'inactive', 'expired'])->default('active')->index();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->unsignedInteger('views_count')->default(0);
                $table->unsignedInteger('clicks_count')->default(0);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        Schema::table('advertisements', function (Blueprint $table) {
            if (! Schema::hasColumn('advertisements', 'link_type')) {
                $table->enum('link_type', ['product', 'category', 'vendor', 'url'])->default('url')->after('image_path')->index();
            }
            if (! Schema::hasColumn('advertisements', 'link_id')) {
                $table->unsignedBigInteger('link_id')->nullable()->after('link_type')->index();
            }
            if (! Schema::hasColumn('advertisements', 'position')) {
                $table->enum('position', ['homepage_slider', 'homepage_banner', 'category_banner', 'sidebar', 'product_page'])->default('homepage_banner')->after('link_id')->index();
            }
            if (! Schema::hasColumn('advertisements', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('advertisements', 'views_count')) {
                $table->unsignedInteger('views_count')->default(0)->after('created_by');
            }
            if (! Schema::hasColumn('advertisements', 'clicks_count')) {
                $table->unsignedInteger('clicks_count')->default(0)->after('views_count');
            }
        });

        DB::statement("
            UPDATE advertisements
            SET position = CASE
                WHEN placement = 'home_banner' THEN 'homepage_banner'
                WHEN placement = 'category_banner' THEN 'category_banner'
                WHEN placement = 'sidebar' THEN 'sidebar'
                ELSE 'product_page'
            END
        ");

        Schema::table('loyalty_points', function (Blueprint $table) {
            if (! Schema::hasColumn('loyalty_points', 'description')) {
                $table->string('description')->nullable()->after('type');
            }
            if (! Schema::hasColumn('loyalty_points', 'balance_after')) {
                $table->integer('balance_after')->default(0)->after('description');
            }
        });

        DB::statement("ALTER TABLE loyalty_points MODIFY type ENUM('earned', 'redeemed', 'reversed', 'adjusted', 'expired') NOT NULL");

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'loyalty_points_redeemed')) {
                $table->unsignedInteger('loyalty_points_redeemed')->default(0)->after('discount_amount');
            }
            if (! Schema::hasColumn('orders', 'loyalty_discount_amount')) {
                $table->decimal('loyalty_discount_amount', 10, 2)->default(0)->after('loyalty_points_redeemed');
            }
        });

        if (! Schema::hasTable('loyalty_settings')) {
            Schema::create('loyalty_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('spend_amount_per_point')->default(100);
                $table->decimal('redemption_value_per_point', 10, 2)->default(1);
                $table->enum('status', ['active', 'inactive'])->default('active')->index();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });

            DB::table('loyalty_settings')->insert([
                'spend_amount_per_point' => 100,
                'redemption_value_per_point' => 1,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_settings');
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('coupon_redemptions');
    }
};
