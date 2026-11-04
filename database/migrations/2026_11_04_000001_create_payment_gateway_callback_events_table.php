<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_gateway_callback_events')) {
            return;
        }

        Schema::create('payment_gateway_callback_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_gateway_transaction_id')->nullable();
            $table->foreignId('payment_id')->nullable();
            $table->string('gateway', 32)->index();
            $table->char('event_key', 64)->unique();
            $table->string('gateway_order_id')->nullable()->index();
            $table->string('gateway_payment_id')->nullable()->index();
            $table->string('merchant_id')->nullable();
            $table->string('status_code', 16)->nullable();
            $table->string('normalized_status', 32)->nullable()->index();
            $table->decimal('amount', 12, 2)->nullable();
            $table->char('currency', 3)->nullable();
            $table->boolean('signature_valid')->default(false)->index();
            $table->boolean('identity_valid')->default(false)->index();
            $table->string('processing_result', 40)->index();
            $table->text('rejection_reason')->nullable();
            $table->json('payload');
            $table->timestamp('received_at')->index();
            $table->timestamps();

            $table->index(
                ['gateway', 'gateway_payment_id', 'status_code'],
                'gateway_callback_payment_status_index'
            );
            $table->foreign('payment_gateway_transaction_id', 'pg_callback_transaction_fk')
                ->references('id')->on('payment_gateway_transactions')->restrictOnDelete();
            $table->foreign('payment_id', 'pg_callback_payment_fk')
                ->references('id')->on('payments')->restrictOnDelete();
        });
    }

    /** Immutable payment evidence is intentionally never dropped by rollback. */
    public function down(): void {}
};
