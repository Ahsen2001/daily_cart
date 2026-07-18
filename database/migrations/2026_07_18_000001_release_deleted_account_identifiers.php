<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $deletedUserIds = DB::table('users')
            ->whereNotNull('deleted_at')
            ->pluck('id');

        foreach ($deletedUserIds as $userId) {
            DB::table('users')
                ->where('id', $userId)
                ->update([
                    'email' => 'deleted+'.$userId.'@deleted.dailycart.invalid',
                    'phone' => null,
                    'email_verified_at' => null,
                    'phone_verified_at' => null,
                    'remember_token' => null,
                ]);
        }

        DB::table('vendors')
            ->whereIn('user_id', $deletedUserIds)
            ->update([
                'business_registration_no' => null,
                'deleted_at' => now(),
            ]);

        DB::table('riders')
            ->whereIn('user_id', $deletedUserIds)
            ->update([
                'license_number' => null,
                'deleted_at' => now(),
            ]);

        DB::table('customers')
            ->whereIn('user_id', $deletedUserIds)
            ->update(['deleted_at' => now()]);
    }

    public function down(): void
    {
        // Deleted account identifiers are deliberately not restored.
    }
};
