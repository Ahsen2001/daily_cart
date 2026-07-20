<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE deliveries MODIFY status ENUM('pending','assigned','accepted','picked_up','on_the_way','delivered','failed','cancelled') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('deliveries')->where('status', 'accepted')->update(['status' => 'assigned']);
            DB::statement("ALTER TABLE deliveries MODIFY status ENUM('pending','assigned','picked_up','on_the_way','delivered','failed','cancelled') NOT NULL DEFAULT 'pending'");
        }
    }
};
