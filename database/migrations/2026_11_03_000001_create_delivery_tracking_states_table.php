<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('delivery_tracking_states')) {
            Schema::create('delivery_tracking_states', function (Blueprint $table) {
                $table->id();
                $table->foreignId('delivery_id')->unique()->constrained()->restrictOnDelete();
                $table->foreignId('rider_id')->constrained()->restrictOnDelete();
                $table->string('status', 24)->default('inactive')->index();
                $table->string('tracking_token_hash', 64)->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->decimal('accuracy', 8, 2)->nullable();
                $table->decimal('altitude', 10, 2)->nullable();
                $table->decimal('speed', 8, 2)->nullable();
                $table->decimal('heading', 6, 2)->nullable();
                $table->timestamp('recorded_at')->nullable()->index();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('stopped_at')->nullable();
                $table->string('stop_reason', 80)->nullable();
                $table->timestamps();

                $table->index(['rider_id', 'status']);
            });
        }

        Schema::table('rider_locations', function (Blueprint $table) {
            if (! Schema::hasColumn('rider_locations', 'accuracy')) {
                $table->decimal('accuracy', 8, 2)->nullable()->after('longitude');
            }
            if (! Schema::hasColumn('rider_locations', 'altitude')) {
                $table->decimal('altitude', 10, 2)->nullable()->after('accuracy');
            }
            if (! Schema::hasColumn('rider_locations', 'speed')) {
                $table->decimal('speed', 8, 2)->nullable()->after('altitude');
            }
            if (! Schema::hasColumn('rider_locations', 'heading')) {
                $table->decimal('heading', 6, 2)->nullable()->after('speed');
            }
        });
    }

    /**
     * Intentionally non-destructive. DailyCart tracking records must never be
     * dropped by an application rollback.
     */
    public function down(): void {}
};
