<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $legacyRules = DB::table('delivery_fees')->orderBy('id')->get();

        foreach ($legacyRules as $legacy) {
            $isDefault = in_array(mb_strtolower((string) $legacy->district), ['all districts', 'default', '*'], true);
            $exists = DB::table('delivery_pricing_rules')
                ->where('scope', $isDefault ? 'default' : 'district')
                ->when(! $isDefault, fn ($query) => $query->whereRaw('LOWER(district) = ?', [mb_strtolower($legacy->district)]))
                ->exists();

            if (! $exists) {
                DB::table('delivery_pricing_rules')->insert([
                    'scope' => $isDefault ? 'default' : 'district',
                    'district' => $isDefault ? null : $legacy->district,
                    'base_fee' => $legacy->base_fee,
                    'per_km_fee' => $legacy->per_km_fee,
                    'minimum_order' => $legacy->minimum_order,
                    'free_delivery_threshold' => $legacy->free_delivery_limit,
                    'priority' => 100,
                    'status' => $legacy->status === 'active' ? 'active' : 'inactive',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // The original table remains for historic compatibility, but is no longer an active pricing source.
        DB::table('delivery_fees')->where('status', 'active')->update(['status' => 'retired', 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('delivery_fees')->where('status', 'retired')->update(['status' => 'active', 'updated_at' => now()]);
    }
};
