<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $categories = DB::table('categories')->pluck('slug', 'id');
        $usedSlugs = [];

        DB::table('products')->orderBy('id')->select(['id', 'vendor_id', 'category_id', 'name', 'slug', 'sku'])->each(function (object $product) use ($categories, &$usedSlugs): void {
            $base = Str::limit(Str::slug($product->name ?: $product->slug) ?: 'product', 240, '');
            $slug = $base;
            $sequence = 2;

            while (isset($usedSlugs[$slug])) {
                $suffix = '-'.$sequence++;
                $slug = Str::limit($base, 255 - strlen($suffix), '').$suffix;
            }

            $usedSlugs[$slug] = true;
            $updates = $product->slug === $slug ? [] : ['slug' => $slug];

            if (blank($product->sku)) {
                $source = Str::upper((string) ($categories[$product->category_id] ?? 'GEN'));
                $code = preg_replace('/[^A-Z0-9]/', '', $source) ?: 'GEN';
                $code = Str::padRight(Str::substr($code, 0, 3), 3, 'X');
                $updates['sku'] = sprintf('%s-V%03d-%06d', $code, $product->vendor_id, $product->id);
            }

            if ($updates !== []) {
                DB::table('products')->where('id', $product->id)->update($updates);
            }
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_vendor_id_slug_unique');
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_slug_unique');
            $table->unique(['vendor_id', 'slug']);
        });
    }
};
