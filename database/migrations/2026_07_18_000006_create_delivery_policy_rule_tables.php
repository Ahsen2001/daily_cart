<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->enum('type', ['free_delivery', 'percentage_discount', 'vendor_sponsored']);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('minimum_order', 10, 2)->default(0);
            $table->date('starts_on')->nullable()->index();
            $table->date('ends_on')->nullable()->index();
            $table->unsignedInteger('priority')->default(100);
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->timestamps();
        });

        Schema::create('free_delivery_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('condition_type', ['subtotal', 'first_order', 'weekend', 'coupon', 'premium_membership']);
            $table->decimal('minimum_order', 10, 2)->default(0);
            $table->string('coupon_code')->nullable();
            $table->date('starts_on')->nullable()->index();
            $table->date('ends_on')->nullable()->index();
            $table->unsignedInteger('priority')->default(100);
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->timestamps();
        });

        Schema::create('delivery_holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('extra_charge', 10, 2)->default(0);
            $table->date('starts_on')->index();
            $table->date('ends_on')->index();
            $table->string('reason')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->timestamps();
        });

        Schema::create('rider_payment_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('base_pay', 10, 2)->default(0);
            $table->decimal('per_km_bonus', 10, 2)->default(0);
            $table->decimal('peak_hour_bonus', 10, 2)->default(0);
            $table->decimal('rain_bonus', 10, 2)->default(0);
            $table->decimal('holiday_bonus', 10, 2)->default(0);
            $table->decimal('night_bonus', 10, 2)->default(0);
            $table->unsignedTinyInteger('peak_start_hour')->nullable();
            $table->unsignedTinyInteger('peak_end_hour')->nullable();
            $table->unsignedTinyInteger('night_start_hour')->nullable();
            $table->unsignedTinyInteger('night_end_hour')->nullable();
            $table->date('starts_on')->nullable()->index();
            $table->date('ends_on')->nullable()->index();
            $table->unsignedInteger('priority')->default(100);
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rider_payment_rules');
        Schema::dropIfExists('delivery_holidays');
        Schema::dropIfExists('free_delivery_rules');
        Schema::dropIfExists('delivery_promotions');
    }
};
