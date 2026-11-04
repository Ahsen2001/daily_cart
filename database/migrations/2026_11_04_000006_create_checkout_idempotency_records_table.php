<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkout_idempotency_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->string('idempotency_key', 100);
            $table->char('request_fingerprint', 64);
            $table->json('request_snapshot');
            $table->string('status', 20)->default('processing');
            $table->json('order_ids')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['customer_id', 'idempotency_key'], 'checkout_idempotency_customer_key_unique');
            $table->index(['status', 'updated_at'], 'checkout_idempotency_status_updated_index');
        });
    }

    /**
     * Checkout idempotency records are an order audit and replay safeguard.
     * They must not be removed automatically by a rollback.
     */
    public function down(): void
    {
        // Intentionally non-destructive.
    }
};
