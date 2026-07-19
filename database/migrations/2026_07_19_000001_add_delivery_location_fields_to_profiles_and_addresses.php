<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (! Schema::hasColumn('vendors', 'province')) {
                $table->string('province')->nullable()->after('city')->index();
            }
        });

        Schema::table('riders', function (Blueprint $table) {
            if (! Schema::hasColumn('riders', 'province')) {
                $table->string('province')->nullable()->after('city')->index();
            }
        });

        Schema::table('addresses', function (Blueprint $table) {
            if (! Schema::hasColumn('addresses', 'province')) {
                $table->string('province')->nullable()->after('district')->index();
            }

            if (! Schema::hasColumn('addresses', 'formatted_address')) {
                $table->string('formatted_address', 500)->nullable()->after('longitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            if (Schema::hasColumn('addresses', 'formatted_address')) {
                $table->dropColumn('formatted_address');
            }

            if (Schema::hasColumn('addresses', 'province')) {
                $table->dropIndex(['province']);
                $table->dropColumn('province');
            }
        });

        Schema::table('riders', function (Blueprint $table) {
            if (Schema::hasColumn('riders', 'province')) {
                $table->dropIndex(['province']);
                $table->dropColumn('province');
            }
        });

        Schema::table('vendors', function (Blueprint $table) {
            if (Schema::hasColumn('vendors', 'province')) {
                $table->dropIndex(['province']);
                $table->dropColumn('province');
            }
        });
    }
};
