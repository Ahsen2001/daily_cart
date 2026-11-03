<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Product;
use App\Models\Rider;
use App\Models\RiderRating;
use App\Models\Subscription;
use App\Observers\DeliveryObserver;
use App\Observers\OrderObserver;
use App\Observers\RiderObserver;
use App\Policies\ProductPolicy;
use App\Policies\RiderRatingPolicy;
use App\Policies\SubscriptionPolicy;
use App\Policies\WalletPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        RateLimiter::for('api-login', fn (Request $request) => Limit::perMinute(5)
            ->by(Str::lower((string) $request->input('email')).'|'.$request->ip()));
        RateLimiter::for('api-register', fn (Request $request) => Limit::perMinute(3)->by($request->ip()));
        RateLimiter::for('api-otp', fn (Request $request) => Limit::perMinute(3)
            ->by((string) ($request->user()?->id ?? $request->ip())));
        RateLimiter::for('delivery-location', fn (Request $request) => Limit::perMinute(90)
            ->by((string) ($request->user()?->id ?? $request->ip()).'|'.(string) $request->route('delivery')));

        Delivery::observe(DeliveryObserver::class);
        Order::observe(OrderObserver::class);
        Rider::observe(RiderObserver::class);

        Gate::policy(Customer::class, WalletPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(RiderRating::class, RiderRatingPolicy::class);
        Gate::policy(Subscription::class, SubscriptionPolicy::class);
        Gate::define('view-admin-reports', fn ($user) => $user->isAdminUser());
        Gate::define('view-vendor-reports', fn ($user) => $user->hasPrimaryRole('Vendor') && $user->vendor?->status === 'approved');
        Gate::define('view-rider-reports', fn ($user) => $user->hasPrimaryRole('Rider') && in_array($user->rider?->verification_status, ['verified', 'approved'], true));
        Gate::define('view-admin-analytics', fn ($user) => $user->isAdminUser());
    }
}
