<?php

namespace App\Services;

use App\Models\ApiIntegrationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class GoogleMapsService
{
    public function browserKey(): ?string
    {
        return config('services.google_maps.browser_key');
    }

    public function geocode(string $address): array
    {
        return $this->request('geocode', 'https://maps.googleapis.com/maps/api/geocode/json', [
            'address' => $address,
            'key' => config('services.google_maps.server_key'),
        ]);
    }

    public function reverseGeocode(float $latitude, float $longitude): array
    {
        return $this->request('reverse_geocode', 'https://maps.googleapis.com/maps/api/geocode/json', [
            'latlng' => $latitude.','.$longitude,
            'key' => config('services.google_maps.server_key'),
        ]);
    }

    public function distance(float $originLatitude, float $originLongitude, float $destinationLatitude, float $destinationLongitude): array
    {
        return $this->request('distance_matrix', 'https://maps.googleapis.com/maps/api/distancematrix/json', [
            'origins' => $originLatitude.','.$originLongitude,
            'destinations' => $destinationLatitude.','.$destinationLongitude,
            'units' => 'metric',
            'key' => config('services.google_maps.server_key'),
        ]);
    }

    private function request(string $action, string $url, array $query): array
    {
        if (blank(config('services.google_maps.server_key'))) {
            throw ValidationException::withMessages(['google_maps' => 'Google Maps server key is not configured.']);
        }

        $log = ApiIntegrationLog::create([
            'provider' => 'google_maps',
            'action' => $action,
            'status' => 'pending',
            'request_payload' => collect($query)->except('key')->all(),
        ]);

        try {
            $response = Http::timeout(10)->get($url, $query);
            $payload = $response->json() ?? [];
            $ok = $response->successful() && in_array($payload['status'] ?? null, ['OK', 'ZERO_RESULTS'], true);

            $log->update([
                'status' => $ok ? 'success' : 'failed',
                'response_payload' => $payload,
                'error_message' => $ok ? null : ($payload['error_message'] ?? 'Google Maps request failed.'),
            ]);

            if (! $ok) {
                throw ValidationException::withMessages(['google_maps' => $payload['error_message'] ?? 'Google Maps request failed.']);
            }

            return $payload;
        } catch (\Throwable $exception) {
            $log->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
