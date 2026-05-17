<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Services\GoogleMapsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoogleMapsController extends Controller
{
    public function geocode(Request $request, GoogleMapsService $maps): JsonResponse
    {
        $data = $request->validate([
            'address' => ['required', 'string', 'max:1000'],
        ]);

        return response()->json($maps->geocode($data['address']));
    }

    public function reverseGeocode(Request $request, GoogleMapsService $maps): JsonResponse
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        return response()->json($maps->reverseGeocode((float) $data['latitude'], (float) $data['longitude']));
    }

    public function distance(Request $request, GoogleMapsService $maps): JsonResponse
    {
        $data = $request->validate([
            'origin_latitude' => ['required', 'numeric', 'between:-90,90'],
            'origin_longitude' => ['required', 'numeric', 'between:-180,180'],
            'destination_latitude' => ['required', 'numeric', 'between:-90,90'],
            'destination_longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        return response()->json($maps->distance(
            (float) $data['origin_latitude'],
            (float) $data['origin_longitude'],
            (float) $data['destination_latitude'],
            (float) $data['destination_longitude'],
        ));
    }
}
