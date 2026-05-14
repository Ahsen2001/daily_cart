<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'wallet_balance')) {
                $table->decimal('wallet_balance', 10, 2)->default(0)->after('status');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->default(0)->after('transaction_id');
            }
            if (! Schema::hasColumn('payments', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('subtotal');
            }
            if (! Schema::hasColumn('payments', 'delivery_fee')) {
                $table->decimal('delivery_fee', 10, 2)->default(0)->after('discount_amount');
            }
            if (! Schema::hasColumn('payments', 'service_charge')) {
                $table->decimal('service_charge', 10, 2)->default(0)->after('delivery_fee');
            }
            if (! Schema::hasColumn('payments', 'grand_total')) {
                $table->decimal('grand_total', 10, 2)->default(0)->after('service_charge');
            }
            if (! Schema::hasColumn('payments', 'transaction_reference')) {
                $table->string('transaction_reference')->nullable()->index()->after('transaction_id');
            }
        });

        DB::statement('
            UPDATE payments
            INNER JOIN orders ON orders.id = payments.order_id
            SET payments.subtotal = orders.subtotal,
                payments.discount_amount = orders.discount_amount,
                payments.delivery_fee = orders.delivery_fee,
                payments.service_charge = orders.service_charge,
                payments.grand_total = orders.total_amount
        ');

        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('wallet_transactions', 'transaction_type')) {
                $table->enum('transaction_type', ['top_up', 'payment', 'refund', 'cashback', 'adjustment'])
                    ->default('adjustment')
                    ->after('user_id')
                    ->index();
            }
            if (! Schema::hasColumn('wallet_transactions', 'balance_after')) {
                $table->decimal('balance_after', 10, 2)->default(0)->after('amount');
            }
            if (! Schema::hasColumn('wallet_transactions', 'reference')) {
                $table->string('reference')->nullable()->after('currency')->index();
            }
        });

        Schema::table('refunds', function (Blueprint $table) {
            if (! Schema::hasColumn('refunds', 'admin_note')) {
                $table->text('admin_note')->nullable()->after('reason');
            }
            if (! Schema::hasColumn('refunds', 'refund_method')) {
                $table->enum('refund_method', ['wallet'])->default('wallet')->after('amount');
            }
            if (! Schema::hasColumn('refunds', 'requested_at')) {
                $table->timestamp('requested_at')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            if (Schema::hasColumn('refunds', 'requested_at')) {
                $table->dropColumn('requested_at');
            }
            if (Schema::hasColumn('refunds', 'refund_method')) {
                $table->dropColumn('refund_method');
            }
            if (Schema::hasColumn('refunds', 'admin_note')) {
                $table->dropColumn('admin_note');
            }
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('wallet_transactions', 'reference')) {
                $table->dropColumn('reference');
            }
            if (Schema::hasColumn('wallet_transactions', 'balance_after')) {
                $table->dropColumn('balance_after');
            }
            if (Schema::hasColumn('wallet_transactions', 'transaction_type')) {
                $table->dropColumn('transaction_type');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            foreach (['transaction_reference', 'grand_total', 'service_charge', 'delivery_fee', 'discount_amount', 'subtotal'] as $column) {
                if (Schema::hasColumn('payments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'wallet_balance')) {
                $table->dropColumn('wallet_balance');
            }
        });
    }
};
