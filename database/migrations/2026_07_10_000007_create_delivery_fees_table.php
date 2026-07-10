<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_fees', function (Blueprint $table) {
            $table->id();
            $table->string('district')->unique();
            $table->decimal('base_fee', 10, 2);
            $table->decimal('per_km_fee', 10, 2);
            $table->decimal('minimum_order', 10, 2)->default(0.00);
            $table->decimal('free_delivery_limit', 10, 2)->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_fees');
    }
};
