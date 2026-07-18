<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->string('district')->nullable()->after('name')->index();
            $table->string('province')->nullable()->after('district')->index();
            $table->decimal('latitude', 10, 7)->nullable()->after('province');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->decimal('radius_km', 8, 2)->nullable()->after('longitude');
            $table->unsignedInteger('estimated_delivery_minutes')->nullable()->after('radius_km');
        });

        Schema::create('delivery_pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->enum('scope', ['zone', 'district', 'province', 'default'])->default('default')->index();
            $table->string('district')->nullable()->index();
            $table->string('province')->nullable()->index();
            $table->decimal('base_fee', 10, 2)->default(0);
            $table->decimal('per_km_fee', 10, 2)->default(0);
            $table->decimal('minimum_order', 10, 2)->default(0);
            $table->decimal('free_delivery_threshold', 10, 2)->nullable();
            $table->decimal('maximum_distance_km', 8, 2)->nullable();
            $table->unsignedInteger('estimated_delivery_minutes')->nullable();
            $table->unsignedInteger('priority')->default(100)->index();
            $table->date('starts_on')->nullable()->index();
            $table->date('ends_on')->nullable()->index();
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_pricing_rules');
        Schema::table('zones', function (Blueprint $table) {
            $table->dropColumn(['district', 'province', 'latitude', 'longitude', 'radius_km', 'estimated_delivery_minutes']);
        });
    }
};
