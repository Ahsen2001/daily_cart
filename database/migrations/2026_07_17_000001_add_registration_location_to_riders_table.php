<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->text('address')->nullable()->after('license_number');
            $table->string('city')->nullable()->after('address');
            $table->string('district')->nullable()->after('city');
            $table->decimal('latitude', 10, 7)->nullable()->after('district');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('formatted_address', 500)->nullable()->after('longitude');

            $table->index(['city', 'district']);
        });
    }

    public function down(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->dropIndex(['city', 'district']);
            $table->dropColumn([
                'address',
                'city',
                'district',
                'latitude',
                'longitude',
                'formatted_address',
            ]);
        });
    }
};
