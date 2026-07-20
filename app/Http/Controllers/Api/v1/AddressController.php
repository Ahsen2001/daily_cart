<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'addresses' => $request->user()->customer->addresses()
                ->orderByDesc('is_default')
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules());
        $customer = $request->user()->customer;

        $address = DB::transaction(function () use ($customer, $validated) {
            if (($validated['is_default'] ?? false) || ! $customer->addresses()->exists()) {
                $customer->addresses()->update(['is_default' => false]);
                $validated['is_default'] = true;
            }

            return $customer->addresses()->create($validated);
        });

        return response()->json([
            'message' => 'Address created successfully.',
            'address' => $address,
        ], 201);
    }

    public function update(Request $request, Address $address): JsonResponse
    {
        $this->ensureOwned($request, $address);
        $validated = $request->validate($this->rules());

        DB::transaction(function () use ($request, $address, $validated) {
            if ($validated['is_default'] ?? false) {
                $request->user()->customer->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
            }
            $address->update($validated);
        });

        return response()->json([
            'message' => 'Address updated successfully.',
            'address' => $address->refresh(),
        ]);
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        $this->ensureOwned($request, $address);
        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $request->user()->customer->addresses()->latest()->first()?->update(['is_default' => true]);
        }

        return response()->json(['message' => 'Address deleted successfully.']);
    }

    public function makeDefault(Request $request, Address $address): JsonResponse
    {
        $this->ensureOwned($request, $address);

        DB::transaction(function () use ($request, $address) {
            $request->user()->customer->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return response()->json([
            'message' => 'Default address updated.',
            'address' => $address->refresh(),
        ]);
    }

    private function rules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:30'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'formatted_address' => ['nullable', 'string', 'max:500'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    private function ensureOwned(Request $request, Address $address): void
    {
        abort_unless($address->customer_id === $request->user()->customer?->id, 403);
    }
}
