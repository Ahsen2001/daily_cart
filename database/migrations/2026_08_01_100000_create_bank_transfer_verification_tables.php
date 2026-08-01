<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bank_transfer_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('reference_number')->unique();
            $table->string('bank_name');
            $table->string('account_holder');
            $table->string('account_number');
            $table->string('branch')->nullable();
            $table->decimal('expected_amount', 12, 2);
            $table->string('currency', 3)->default('LKR');
            $table->enum('status', ['pending_upload', 'slip_uploaded', 'pending_verification', 'verified', 'rejected', 'refunded', 'cancelled'])->default('pending_upload');
            $table->text('last_rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'submitted_at']);
        });

        Schema::create('payment_slips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_transfer_payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->timestamp('uploaded_at');
            $table->timestamps();
            $table->index(['bank_transfer_payment_id', 'uploaded_at']);
        });

        Schema::create('payment_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_transfer_payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_slip_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('action', ['slip_uploaded', 'approved', 'rejected', 'reupload_requested']);
            $table->string('status', 40);
            $table->text('reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('financial_verification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_transfer_payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40)->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['bank_transfer_payment_id', 'created_at'], 'fin_verify_transfer_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_verification_logs');
        Schema::dropIfExists('payment_verifications');
        Schema::dropIfExists('payment_slips');
        Schema::dropIfExists('bank_transfer_payments');
    }
};
