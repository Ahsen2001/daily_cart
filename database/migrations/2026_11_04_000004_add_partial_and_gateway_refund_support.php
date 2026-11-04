<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'refunded_amount')) {
                $table->decimal('refunded_amount', 10, 2)->default(0)->after('amount');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY status ENUM('pending','paid','failed','partially_refunded','refunded') NOT NULL DEFAULT 'pending'");
            DB::statement("ALTER TABLE orders MODIFY payment_status ENUM('pending','paid','failed','cancelled','partially_refunded','refunded') NOT NULL DEFAULT 'pending'");
            DB::statement("ALTER TABLE refunds MODIFY refund_method ENUM('wallet','payhere_gateway') NOT NULL DEFAULT 'wallet'");
        }

        Schema::table('refunds', function (Blueprint $table) {
            if (! Schema::hasColumn('refunds', 'refund_type')) {
                $table->string('refund_type', 20)->default('full')->after('refund_method');
            }
            if (! Schema::hasColumn('refunds', 'gateway_refund_id')) {
                $table->string('gateway_refund_id')->nullable()->after('refund_type')->index();
            }
            if (! Schema::hasColumn('refunds', 'processed_by')) {
                $table->foreignId('processed_by')->nullable()->after('processed_at')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('refunds', 'failure_reason')) {
                $table->text('failure_reason')->nullable()->after('processed_by');
            }
        });

        if (! Schema::hasTable('refund_attempt_events')) {
            Schema::create('refund_attempt_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('refund_id')->constrained()->restrictOnDelete();
                $table->foreignId('payment_id')->constrained()->restrictOnDelete();
                $table->foreignId('payment_gateway_transaction_id')->nullable()->constrained()->nullOnDelete();
                $table->uuid('attempt_uuid');
                $table->string('event_type', 30);
                $table->string('gateway', 30);
                $table->decimal('amount', 10, 2);
                $table->char('currency', 3);
                $table->string('gateway_refund_id')->nullable();
                $table->unsignedSmallInteger('http_status')->nullable();
                $table->string('error_code')->nullable();
                $table->text('message')->nullable();
                $table->json('request_payload')->nullable();
                $table->json('response_payload')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->unique(['attempt_uuid', 'event_type'], 'refund_attempt_event_unique');
                $table->index(['refund_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive: financial audit records must be retained.
    }
};
