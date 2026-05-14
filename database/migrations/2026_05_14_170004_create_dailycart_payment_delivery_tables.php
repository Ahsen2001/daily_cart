<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('payment_method', ['cash_on_delivery', 'card', 'bank_transfer', 'wallet']);
            $table->string('transaction_id')->nullable()->unique();
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('LKR');
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded', 'partially_refunded'])
                ->default('pending')
                ->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('rider_id')->nullable()->constrained()->nullOnDelete();
            $table->text('pickup_address');
            $table->text('delivery_address');
            $table->timestamp('scheduled_at')->index();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->enum('status', [
                'pending',
                'assigned',
                'picked_up',
                'on_the_way',
                'delivered',
                'failed',
                'cancelled',
            ])->default('pending')->index();
            $table->timestamps();

            $table->index(['rider_id', 'status']);
        });

        Schema::create('rider_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rider_id')->constrained()->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->timestamp('recorded_at')->index();

            $table->index(['rider_id', 'recorded_at']);
        });

        Schema::create('delivery_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained()->cascadeOnDelete();
            $table->string('proof_image')->nullable();
            $table->string('customer_signature')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->text('reason');
            $table->enum('status', ['requested', 'approved', 'rejected', 'processed', 'failed'])
                ->default('requested')
                ->index();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('delivery_proofs');
        Schema::dropIfExists('rider_locations');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('payments');
    }
};
