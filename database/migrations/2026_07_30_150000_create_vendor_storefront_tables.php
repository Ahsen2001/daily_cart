<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vendor_profiles')) {
            Schema::create('vendor_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('logo_path')->nullable();
                $table->string('cover_image_path')->nullable();
                $table->json('opening_hours')->nullable();
                $table->string('delivery_estimate', 100)->nullable();
                $table->decimal('minimum_order', 12, 2)->nullable();
                $table->string('contact_phone', 60)->nullable();
                $table->string('contact_email')->nullable();
                $table->boolean('is_featured')->default(false)->index();
                $table->boolean('is_enabled')->default(true)->index();
                $table->string('profile_status', 20)->default('pending')->index();
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('store_banners')) {
            Schema::create('store_banners', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_profile_id')->constrained()->cascadeOnDelete();
                $table->string('title')->nullable();
                $table->string('image_path');
                $table->string('link_url')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->timestamps();
                $table->index(['vendor_profile_id', 'is_active']);
            });
        }

        if (! Schema::hasTable('store_followers')) {
            Schema::create('store_followers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_profile_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['vendor_profile_id', 'customer_id']);
            });
        }

        DB::table('vendors')->orderBy('id')->each(function (object $vendor): void {
            if (DB::table('vendor_profiles')->where('vendor_id', $vendor->id)->exists()) {
                return;
            }

            $base = Str::slug($vendor->store_name ?: 'store');

            DB::table('vendor_profiles')->insert([
                'vendor_id' => $vendor->id,
                'slug' => $base.'-'.$vendor->id,
                'delivery_estimate' => '30-60 minutes',
                'minimum_order' => null,
                'contact_phone' => $vendor->phone,
                'profile_status' => $vendor->status === 'approved' ? 'approved' : 'pending',
                'is_enabled' => $vendor->status === 'approved',
                'approved_at' => $vendor->status === 'approved' ? ($vendor->approved_at ?: now()) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_followers');
        Schema::dropIfExists('store_banners');
        Schema::dropIfExists('vendor_profiles');
    }
};
