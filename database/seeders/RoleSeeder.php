<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = collect([
            'Super Admin',
            'Admin',
            'Vendor',
            'Rider',
            'Customer',
        ])->mapWithKeys(fn (string $role) => [$role => Role::findOrCreate($role, 'web')]);

        $permissionsByRole = [
            'Super Admin' => [
                'platform.statistics.view',
                'analytics.revenue.view',
                'analytics.orders.view',
                'analytics.customers.view',
                'analytics.vendors.view',
                'analytics.riders.view',
                'admins.manage',
                'roles.manage',
                'permissions.manage',
                'logs.activity.view',
                'logs.api.view',
                'logs.security.view',
                'settings.manage',
                'delivery.pricing.manage',
                'delivery.service_charges.manage',
                'delivery.promotions.manage',
                'delivery.rider_payouts.manage',
                'finance.commissions.manage',
                'delivery.analytics.view',
                'backup.manage',
                'reports.export',
                'advertisements.manage',
                'newsletter.manage',
            ],
            'Admin' => [
                'customers.manage',
                'vendors.approve',
                'riders.approve',
                'products.manage',
                'categories.manage',
                'brands.manage',
                'orders.manage',
                'refunds.manage',
                'deliveries.manage',
                'delivery.pricing.manage',
                'delivery.service_charges.manage',
                'delivery.promotions.manage',
                'delivery.analytics.view',
                'coupons.manage',
                'promotions.manage',
                'support.manage',
                'contact_messages.manage',
                'notifications.manage',
                'reports.view',
                'analytics.view',
            ],
            'Vendor' => [
                'vendor.dashboard.view',
                'vendor.store.manage',
                'vendor.products.manage',
                'vendor.inventory.manage',
                'vendor.orders.manage',
                'vendor.promotions.manage',
                'vendor.coupons.manage',
                'vendor.reports.view',
                'vendor.wallet.view',
                'vendor.analytics.view',
            ],
            'Customer' => [
                'customer.products.browse',
                'customer.cart.manage',
                'customer.checkout',
                'customer.orders.track',
                'customer.reviews.manage',
                'customer.loyalty.view',
                'customer.subscriptions.manage',
                'customer.support.manage',
                'customer.notifications.view',
            ],
            'Rider' => [
                'rider.dashboard.view',
                'rider.orders.assigned',
                'rider.navigation.view',
                'rider.delivery.timeline',
                'rider.proof.manage',
                'rider.history.view',
                'rider.earnings.view',
                'rider.notifications.view',
            ],
        ];

        foreach ($permissionsByRole as $roleName => $permissions) {
            foreach ($permissions as $permission) {
                Permission::findOrCreate($permission, 'web');
            }

            $roles[$roleName]->syncPermissions($permissions);
        }
    }
}
