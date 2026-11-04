<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            if (! Schema::hasColumn('deliveries', 'failed_reason_category')) {
                $table->string('failed_reason_category', 60)->nullable()->after('failed_reason')->index();
            }
        });

        if (! Schema::hasTable('delivery_recovery_cases')) {
            Schema::create('delivery_recovery_cases', function (Blueprint $table) {
                $table->id();
                $table->foreignId('delivery_id')->constrained()->restrictOnDelete();
                $table->foreignId('order_id')->constrained()->restrictOnDelete();
                $table->unsignedInteger('failure_sequence');
                $table->string('reason_category', 60)->index();
                $table->text('reason_details');
                $table->string('status', 40)->default('open')->index();
                $table->string('resolution', 60)->nullable();
                $table->string('refund_decision', 30)->default('not_requested');
                $table->foreignId('refund_id')->nullable()->constrained()->restrictOnDelete();
                $table->foreignId('failed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('assigned_admin_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('escalation_due_at')->index();
                $table->timestamp('escalated_at')->nullable();
                $table->timestamp('return_started_at')->nullable();
                $table->timestamp('return_completed_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('resolution_notes')->nullable();
                $table->timestamps();

                $table->unique(['delivery_id', 'failure_sequence'], 'delivery_recovery_failure_unique');
            });
        }

        if (! Schema::hasTable('delivery_recovery_events')) {
            Schema::create('delivery_recovery_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('delivery_recovery_case_id')->constrained()->restrictOnDelete();
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('event_type', 60)->index();
                $table->string('from_status', 40)->nullable();
                $table->string('to_status', 40)->nullable();
                $table->text('note')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive. Recovery and audit records must be retained.
    }
};
