<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_payout_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('bank_name');
            $table->string('account_name');
            $table->string('account_number');
            $table->string('branch')->nullable();
            $table->string('status')->default('requested');
            $table->text('admin_note')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->index(['vendor_id', 'status']);
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->text('vendor_note')->nullable()->after('admin_note');
            $table->timestamp('vendor_responded_at')->nullable()->after('vendor_note');
        });
    }

    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropColumn(['vendor_note', 'vendor_responded_at']);
        });
        Schema::dropIfExists('vendor_payout_requests');
    }
};
