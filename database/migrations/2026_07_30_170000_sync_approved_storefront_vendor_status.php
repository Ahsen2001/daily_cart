<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $vendorIds = DB::table('vendor_profiles')
            ->where('profile_status', 'approved')
            ->where('is_enabled', true)
            ->pluck('vendor_id');

        if ($vendorIds->isEmpty()) {
            return;
        }

        $now = now();
        DB::table('vendors')
            ->whereIn('id', $vendorIds)
            ->where('status', 'pending')
            ->update([
                'status' => 'approved',
                'approved_at' => $now,
                'updated_at' => $now,
            ]);

        DB::table('users')
            ->whereIn('id', DB::table('vendors')->whereIn('id', $vendorIds)->pluck('user_id'))
            ->where('status', 'pending')
            ->update([
                'status' => 'active',
                'updated_at' => $now,
            ]);
    }

    public function down(): void
    {
        // Approval is a business decision and must not be rolled back automatically.
    }
};
