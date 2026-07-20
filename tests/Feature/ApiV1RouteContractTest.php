<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiV1RouteContractTest extends TestCase
{
    public function test_api_v1_route_surface_matches_the_frozen_contract(): void
    {
        $contractPath = base_path('docs/api/v1/route-contract.json');
        $contract = json_decode(
            file_get_contents($contractPath),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $expected = collect($contract['endpoints'])
            ->map(fn (array $endpoint): string => $endpoint['method'].' '.ltrim($endpoint['path'], '/'))
            ->sort()
            ->values()
            ->all();

        $actual = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route): bool => str_starts_with($route->uri(), 'api/v1/'))
            ->map(function ($route): string {
                $method = collect($route->methods())
                    ->first(fn (string $method): bool => $method !== 'HEAD');

                return $method.' '.$route->uri();
            })
            ->sort()
            ->values()
            ->all();

        $this->assertSame(
            $expected,
            $actual,
            'The Laravel v1 route surface changed. Update the API contract and version deliberately.',
        );

        foreach ($contract['endpoints'] as $endpoint) {
            $uri = ltrim($endpoint['path'], '/');
            $route = collect(Route::getRoutes()->getRoutes())->first(
                fn ($route): bool => $route->uri() === $uri
                    && in_array($endpoint['method'], $route->methods(), true),
            );
            $middleware = $route->gatherMiddleware();

            if ($endpoint['access'] === 'public') {
                $this->assertNotContains('auth:sanctum', $middleware, $uri);

                continue;
            }

            $this->assertContains('auth:sanctum', $middleware, $uri);
            $this->assertContains('ability:'.$endpoint['ability'], $middleware, $uri);

            if (in_array($endpoint['access'], ['customer', 'approved_rider', 'approved_vendor'], true)) {
                $this->assertContains('verified', $middleware, $uri);
                $this->assertContains('phone.verified', $middleware, $uri);
            }

            if ($endpoint['access'] === 'approved_rider') {
                $this->assertContains('role:Rider', $middleware, $uri);
                $this->assertContains('rider.approved', $middleware, $uri);
            }

            if ($endpoint['access'] === 'approved_vendor') {
                $this->assertContains('role:Vendor', $middleware, $uri);
                $this->assertContains('vendor.approved', $middleware, $uri);
            }
        }
    }
}
