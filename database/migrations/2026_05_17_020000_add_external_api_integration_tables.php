<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('email')->index();
            $table->string('code_hash');
            $table->enum('purpose', ['email_verification', 'login'])->index();
            $table->timestamp('expires_at')->index();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamps();

            $table->index(['email', 'purpose', 'expires_at']);
        });

        Schema::create('api_integration_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->index();
            $table->string('action')->index();
            $table->string('status')->default('pending')->index();
            $table->string('reference')->nullable()->index();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_gateway_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('gateway')->index();
            $table->string('gateway_order_id')->nullable()->index();
            $table->string('gateway_payment_id')->nullable()->index();
            $table->string('status')->default('pending')->index();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('LKR');
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['gateway', 'gateway_order_id', 'payment_id'], 'gateway_order_payment_unique');
        });

        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('postal_code');
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
                $table->string('formatted_address')->nullable()->after('longitude');
            }
        });

        Schema::table('vendors', function (Blueprint $table) {
            if (! Schema::hasColumn('vendors', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('district');
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
                $table->string('formatted_address')->nullable()->after('longitude');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'delivery_latitude')) {
                $table->decimal('delivery_latitude', 10, 7)->nullable()->after('delivery_address');
                $table->decimal('delivery_longitude', 10, 7)->nullable()->after('delivery_latitude');
                $table->unsignedInteger('delivery_distance_meters')->nullable()->after('delivery_longitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'delivery_latitude')) {
                $table->dropColumn(['delivery_latitude', 'delivery_longitude', 'delivery_distance_meters']);
            }
        });

        Schema::table('vendors', function (Blueprint $table) {
            if (Schema::hasColumn('vendors', 'latitude')) {
                $table->dropColumn(['latitude', 'longitude', 'formatted_address']);
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'latitude')) {
                $table->dropColumn(['latitude', 'longitude', 'formatted_address']);
            }
        });

        Schema::dropIfExists('payment_gateway_transactions');
        Schema::dropIfExists('api_integration_logs');
        Schema::dropIfExists('email_otps');
    }
};
