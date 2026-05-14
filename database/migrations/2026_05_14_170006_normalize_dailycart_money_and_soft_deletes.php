<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $moneyColumns = [
            'vendors' => ['commission_rate' => [5, 2]],
            'products' => ['base_price' => [10, 2], 'sale_price' => [10, 2]],
            'product_variants' => ['price' => [10, 2]],
            'cart_items' => ['unit_price' => [10, 2]],
            'coupons' => ['value' => [10, 2], 'minimum_order_amount' => [10, 2], 'max_discount_amount' => [10, 2]],
            'orders' => [
                'subtotal' => [10, 2],
                'discount_amount' => [10, 2],
                'delivery_fee' => [10, 2],
                'tax_amount' => [10, 2],
                'total_amount' => [10, 2],
            ],
            'order_items' => ['unit_price' => [10, 2], 'total_price' => [10, 2]],
            'payments' => ['amount' => [10, 2]],
            'wallet_transactions' => ['amount' => [10, 2]],
            'refunds' => ['amount' => [10, 2]],
            'subscriptions' => ['price' => [10, 2]],
        ];

        foreach ($moneyColumns as $tableName => $columns) {
            Schema::table($tableName, function (Blueprint $table) use ($columns) {
                foreach ($columns as $column => [$precision, $scale]) {
                    $definition = $table->decimal($column, $precision, $scale);

                    if (in_array($column, ['sale_price', 'max_discount_amount'], true)) {
                        $definition->nullable();
                    }

                    if (in_array($column, ['commission_rate', 'minimum_order_amount', 'discount_amount', 'delivery_fee', 'tax_amount'], true)) {
                        $definition->default(0);
                    }

                    $definition->change();
                }
            });
        }

        foreach ($this->softDeleteTables() as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->softDeleteTables() as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        $moneyColumns = [
            'products' => ['base_price', 'sale_price'],
            'product_variants' => ['price'],
            'cart_items' => ['unit_price'],
            'coupons' => ['value', 'minimum_order_amount', 'max_discount_amount'],
            'orders' => ['subtotal', 'discount_amount', 'delivery_fee', 'tax_amount', 'total_amount'],
            'order_items' => ['unit_price', 'total_price'],
            'payments' => ['amount'],
            'wallet_transactions' => ['amount'],
            'refunds' => ['amount'],
            'subscriptions' => ['price'],
        ];

        foreach ($moneyColumns as $tableName => $columns) {
            Schema::table($tableName, function (Blueprint $table) use ($columns) {
                foreach ($columns as $column) {
                    $definition = $table->decimal($column, 12, 2);

                    if (in_array($column, ['sale_price', 'max_discount_amount'], true)) {
                        $definition->nullable();
                    }

                    if (in_array($column, ['minimum_order_amount', 'discount_amount', 'delivery_fee', 'tax_amount'], true)) {
                        $definition->default(0);
                    }

                    $definition->change();
                }
            });
        }
    }

    /**
     * @return array<int, string>
     */
    private function softDeleteTables(): array
    {
        return [
            'users',
            'customers',
            'vendors',
            'riders',
            'admins',
            'categories',
            'products',
            'product_images',
            'product_variants',
            'carts',
            'coupons',
            'orders',
            'payments',
            'deliveries',
            'reviews',
            'support_tickets',
            'refunds',
            'advertisements',
            'subscriptions',
        ];
    }
};
