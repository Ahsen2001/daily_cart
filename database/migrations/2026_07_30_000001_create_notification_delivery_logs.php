<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('event_id', 64)->nullable()->after('user_id');
            $table->foreignId('order_id')->nullable()->after('event_id')
                ->constrained()->nullOnDelete();
            $table->unique(['user_id', 'event_id'], 'notifications_user_event_unique');
        });

        Schema::create('notification_delivery_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->nullable()
                ->constrained('notifications')->nullOnDelete();
            $table->foreignId('order_id')->nullable()
                ->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event_id', 64);
            $table->string('channel', 20);
            $table->string('status', 20)->default('queued');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedTinyInteger('max_attempts')->default(3);
            $table->text('failure_reason')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['event_id', 'user_id', 'channel'],
                'notification_delivery_event_user_channel_unique',
            );
            $table->index(['user_id', 'channel', 'status'], 'notification_delivery_user_channel_status_index');
            $table->index(['order_id', 'created_at'], 'notification_delivery_order_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_delivery_logs');

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropUnique('notifications_user_event_unique');
            $table->dropConstrainedForeignId('order_id');
            $table->dropColumn('event_id');
        });
    }
};
