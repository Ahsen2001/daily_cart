<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rider_locations', function (Blueprint $table) {
            $table->foreignId('delivery_id')
                ->nullable()
                ->after('rider_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rider_locations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('delivery_id');
        });
    }
};
