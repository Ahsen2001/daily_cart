<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('phone');
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->string('district');
            $table->string('postal_code')->nullable();
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active')->index();
            $table->timestamps();

            $table->index(['city', 'district']);
        });

        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('store_name');
            $table->string('business_registration_no')->nullable()->unique();
            $table->string('phone');
            $table->text('address');
            $table->string('city');
            $table->string('district');
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])->default('pending')->index();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['city', 'district']);
        });

        Schema::create('riders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('vehicle_type', ['bicycle', 'motorbike', 'three_wheeler', 'van']);
            $table->string('vehicle_number')->nullable();
            $table->string('license_number')->nullable()->unique();
            $table->enum('availability_status', ['available', 'unavailable', 'delivering'])
                ->default('unavailable')
                ->index();
            $table->enum('verification_status', ['pending', 'verified', 'rejected', 'suspended'])
                ->default('pending')
                ->index();
            $table->timestamps();
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('department')->nullable();
            $table->enum('access_level', ['admin', 'super_admin'])->default('admin')->index();
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
        Schema::dropIfExists('riders');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('customers');
    }
};
