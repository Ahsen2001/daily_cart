<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('delivery_current_locations')) {
            Schema::create('delivery_current_locations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('delivery_id')->unique()->constrained()->restrictOnDelete();
                $table->foreignId('rider_id')->constrained()->restrictOnDelete();
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->decimal('accuracy_meters', 8, 2)->nullable();
                $table->decimal('heading', 6, 2)->nullable();
                $table->decimal('speed', 8, 2)->nullable();
                $table->timestamp('recorded_at')->index();
                $table->timestamp('received_at');
                $table->unsignedBigInteger('sequence_number')->nullable();
                $table->timestamps();

                $table->index(['rider_id', 'recorded_at']);
            });
        }

        Schema::table('rider_locations', function (Blueprint $table) {
            if (! Schema::hasColumn('rider_locations', 'point_uuid')) {
                $table->uuid('point_uuid')->nullable()->unique()->after('delivery_id');
            }
            if (! Schema::hasColumn('rider_locations', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('recorded_at')->index();
            }
            if (! Schema::hasColumn('rider_locations', 'sequence_number')) {
                $table->unsignedBigInteger('sequence_number')->nullable()->after('received_at');
            }
            if (! Schema::hasColumn('rider_locations', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    /**
     * Intentionally non-destructive. Tracking history and dispute evidence are
     * retained even when an application rollback is requested.
     */
    public function down(): void {}
};
