<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('status')->default('active')->index();
            $table->timestamps();

            $table->unique(['district_id', 'name']);
        });

        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->string('status')->default('active')->index();
            $table->timestamps();

            $table->unique(['city_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zones');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('districts');
    }
};
