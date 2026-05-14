<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'service_charge')) {
                $table->decimal('service_charge', 10, 2)->default(0)->after('delivery_fee');
            }
        });

        DB::table('orders')->where('order_status', 'preparing')->update(['order_status' => 'packed']);
        DB::table('orders')->where('order_status', 'ready_for_pickup')->update(['order_status' => 'packed']);
        DB::table('orders')->where('order_status', 'picked_up')->update(['order_status' => 'out_for_delivery']);
        DB::table('orders')->where('order_status', 'rejected')->update(['order_status' => 'cancelled']);
        DB::table('orders')->where('payment_status', 'cancelled')->update(['payment_status' => 'failed']);
        DB::table('orders')->where('payment_status', 'partially_refunded')->update(['payment_status' => 'refunded']);
        DB::table('payments')->where('status', 'partially_refunded')->update(['status' => 'refunded']);

        DB::statement("ALTER TABLE orders MODIFY order_status ENUM('pending', 'confirmed', 'packed', 'assigned_to_rider', 'out_for_delivery', 'delivered', 'cancelled', 'refunded') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE orders MODIFY payment_status ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE payments MODIFY status ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY status ENUM('pending', 'paid', 'failed', 'refunded', 'partially_refunded') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE orders MODIFY payment_status ENUM('pending', 'paid', 'failed', 'cancelled', 'refunded', 'partially_refunded') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE orders MODIFY order_status ENUM('pending', 'confirmed', 'preparing', 'ready_for_pickup', 'assigned_to_rider', 'picked_up', 'out_for_delivery', 'delivered', 'cancelled', 'rejected', 'refunded') NOT NULL DEFAULT 'pending'");

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'service_charge')) {
                $table->dropColumn('service_charge');
            }
        });
    }
};
