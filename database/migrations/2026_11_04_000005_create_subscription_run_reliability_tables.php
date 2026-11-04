<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('subscription_runs')) {
            Schema::create('subscription_runs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('subscription_id')->constrained()->restrictOnDelete();
                $table->date('scheduled_date');
                $table->string('run_key', 64)->unique();
                $table->string('status', 30)->default('queued')->index();
                $table->unsignedSmallInteger('attempt_count')->default(0);
                $table->unsignedSmallInteger('max_attempts')->default(3);
                $table->timestamp('next_retry_at')->nullable()->index();
                $table->timestamp('first_dispatched_at')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('escalated_at')->nullable();
                $table->text('failure_reason')->nullable();
                $table->string('last_error_class')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['subscription_id', 'scheduled_date'], 'subscription_scheduled_run_unique');
            });
        }

        if (! Schema::hasTable('subscription_run_attempts')) {
            Schema::create('subscription_run_attempts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('subscription_run_id')->constrained()->restrictOnDelete();
                $table->unsignedSmallInteger('attempt_number');
                $table->string('status', 20);
                $table->string('error_class')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('started_at');
                $table->timestamp('completed_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->unique(['subscription_run_id', 'attempt_number'], 'subscription_run_attempt_unique');
            });
        }

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'subscription_run_id')) {
                $table->foreignId('subscription_run_id')->nullable()->after('subscription_id')
                    ->unique()->constrained('subscription_runs')->restrictOnDelete();
            }
        });
    }

    public function down(): void
    {
        // Intentionally non-destructive: recurring-order run history is an audit record.
    }
};
