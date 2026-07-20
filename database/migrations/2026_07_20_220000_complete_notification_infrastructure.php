<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('device_tokens', function (Blueprint $table) {
            $table->string('device_id')->nullable()->after('user_id');
            $table->string('app_role', 20)->default('customer')->after('device_id');
            $table->string('app_version', 40)->nullable()->after('platform');
            $table->timestamp('refreshed_at')->nullable()->after('app_version');
            $table->timestamp('last_used_at')->nullable()->after('refreshed_at');
            $table->timestamp('revoked_at')->nullable()->after('last_used_at');
            $table->index(['user_id', 'app_role', 'revoked_at'], 'device_tokens_delivery_index');
            $table->index(['user_id', 'device_id', 'app_role'], 'device_tokens_device_index');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->string('app_role', 20)->nullable()->after('type');
            $table->json('data')->nullable()->after('app_role');
            $table->string('deep_link', 2048)->nullable()->after('data');
            $table->index(['user_id', 'app_role', 'created_at'], 'notifications_role_index');
        });

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('app_role', 20);
            $table->boolean('push_enabled')->default(true);
            $table->boolean('order_updates')->default(true);
            $table->boolean('delivery_updates')->default(true);
            $table->boolean('wallet_updates')->default(true);
            $table->boolean('support_updates')->default(true);
            $table->boolean('promotions')->default(true);
            $table->timestamps();
            $table->unique(['user_id', 'app_role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_role_index');
            $table->dropColumn(['app_role', 'data', 'deep_link']);
        });

        Schema::table('device_tokens', function (Blueprint $table) {
            $table->dropIndex('device_tokens_delivery_index');
            $table->dropIndex('device_tokens_device_index');
            $table->dropColumn([
                'device_id',
                'app_role',
                'app_version',
                'refreshed_at',
                'last_used_at',
                'revoked_at',
            ]);
        });
    }
};
