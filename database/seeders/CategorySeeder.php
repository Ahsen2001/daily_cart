<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        collect([
            'Grocery',
            'Vegetables',
            'Fruits',
            'Household',
            'Powder Products',
            'Beverages',
            'Frozen Food',
            'Bakery',
            'Pharmacy',
            'Baby Care',
            'Personal Care',
            'Pet Supplies',
            'Office Essentials',
        ])->each(function (string $name) {
            Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $name.' products for DailyCart customers.',
                    'status' => 'active',
                ]
            );
        });
    }
}
