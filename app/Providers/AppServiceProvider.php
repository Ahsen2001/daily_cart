<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\Subscription;
use App\Policies\SubscriptionPolicy;
use App\Policies\WalletPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Customer::class, WalletPolicy::class);
        Gate::policy(Subscription::class, SubscriptionPolicy::class);
        Gate::define('view-admin-reports', fn ($user) => $user->isAdminUser());
        Gate::define('view-vendor-reports', fn ($user) => $user->hasPrimaryRole('Vendor') && $user->vendor?->status === 'approved');
        Gate::define('view-rider-reports', fn ($user) => $user->hasPrimaryRole('Rider') && $user->rider?->verification_status === 'approved');
        Gate::define('view-admin-analytics', fn ($user) => $user->isAdminUser());
    }
}
