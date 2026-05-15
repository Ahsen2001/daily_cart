<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'is_subscription_eligible')) {
                $table->boolean('is_subscription_eligible')->default(false)->after('is_featured')->index();
            }
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('subscriptions', 'product_id')) {
                $table->foreignId('product_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('subscriptions', 'frequency')) {
                $table->enum('frequency', ['daily', 'weekly', 'monthly'])->default('weekly')->after('vendor_id')->index();
                $table->unsignedInteger('quantity')->default(1)->after('frequency');
                $table->decimal('unit_price', 10, 2)->default(0)->after('quantity');
                $table->decimal('total_amount', 10, 2)->default(0)->after('unit_price');
                $table->text('delivery_address')->nullable()->after('total_amount');
                $table->time('preferred_delivery_time')->nullable()->after('delivery_address');
                $table->date('start_date')->nullable()->after('preferred_delivery_time')->index();
                $table->date('end_date')->nullable()->after('start_date')->index();
                $table->date('next_delivery_date')->nullable()->after('end_date')->index();
                $table->string('payment_method')->default('cash_on_delivery')->after('next_delivery_date');
                $table->text('notes')->nullable()->after('payment_method');
                $table->timestamp('last_generated_at')->nullable()->after('notes');
                $table->text('failed_reason')->nullable()->after('last_generated_at');
            }

            $table->index(['customer_id', 'status']);
            $table->index(['vendor_id', 'status', 'next_delivery_date']);
            $table->index(['product_id', 'status']);
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'subscription_id')) {
                $table->foreignId('subscription_id')->nullable()->after('coupon_id')->constrained()->nullOnDelete();
                $table->index(['subscription_id', 'order_status']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'subscription_id')) {
                $table->dropIndex(['subscription_id', 'order_status']);
                $table->dropConstrainedForeignId('subscription_id');
            }
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('subscriptions', 'frequency')) {
                $table->dropIndex(['customer_id', 'status']);
                $table->dropIndex(['vendor_id', 'status', 'next_delivery_date']);
                $table->dropIndex(['product_id', 'status']);
                $table->dropColumn([
                    'frequency',
                    'quantity',
                    'unit_price',
                    'total_amount',
                    'delivery_address',
                    'preferred_delivery_time',
                    'start_date',
                    'end_date',
                    'next_delivery_date',
                    'payment_method',
                    'notes',
                    'last_generated_at',
                    'failed_reason',
                ]);
            }

            if (Schema::hasColumn('subscriptions', 'product_id')) {
                $table->dropConstrainedForeignId('product_id');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'is_subscription_eligible')) {
                $table->dropColumn('is_subscription_eligible');
            }
        });
    }
};
