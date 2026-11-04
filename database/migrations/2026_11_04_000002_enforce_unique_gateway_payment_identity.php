<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_gateway_transactions', function (Blueprint $table) {
            $table->unique(
                ['gateway', 'gateway_payment_id'],
                'gateway_payment_identity_unique'
            );
        });
    }

    /** Payment identity protection is intentionally retained on rollback. */
    public function down(): void {}
};
