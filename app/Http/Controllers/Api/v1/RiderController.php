<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Services\DashboardService;
use App\Services\DeliveryService;
use App\Services\RiderEarningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RiderController extends Controller
{
    public function __construct(private readonly DeliveryService $deliveryService) {}

    public function index(Request $request): JsonResponse
    {
        $rider = $request->user()->rider;
        if (! $rider) {
            return response()->json(['message' => 'Rider profile not found.'], 404);
        }

        $deliveries = $rider->deliveries()
            ->with($this->relations())
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->latest('scheduled_at')
            ->paginate(15);

        return response()->json([
            'deliveries' => collect($deliveries->items())
                ->map(fn (Delivery $delivery) => $this->payload($delivery)),
            'pagination' => [
                'total' => $deliveries->total(),
                'count' => $deliveries->count(),
                'per_page' => $deliveries->perPage(),
                'current_page' => $deliveries->currentPage(),
                'total_pages' => $deliveries->lastPage(),
            ],
        ]);
    }

    public function show(Delivery $delivery, Request $request): JsonResponse
    {
        $rider = $request->user()->rider;
        if (! $rider || $delivery->rider_id !== $rider->id) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        return response()->json([
            'delivery' => $this->payload($delivery->load($this->relations())),
        ]);
    }

    public function dashboard(
        Request $request,
        DashboardService $dashboards,
        RiderEarningService $earnings
    ): JsonResponse {
        $rider = $request->user()->rider;
        $summary = $dashboards->riderOverview($rider);
        $earningSummary = $earnings->summary($rider);

        return response()->json([
            'dashboard' => [
                'today_deliveries' => $summary['todays_deliveries'],
                'assigned_deliveries' => $summary['assigned_deliveries'],
                'completed_deliveries' => $summary['completed_deliveries'],
                'failed_deliveries' => $summary['failed_deliveries'],
                'today_earnings' => $earningSummary['daily'],
                'weekly_earnings' => $earningSummary['weekly'],
                'monthly_earnings' => $earningSummary['monthly'],
                'approval_status' => $rider->verification_status,
                'availability_status' => $rider->availability_status,
            ],
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        return response()->json(['rider' => $this->profilePayload($request)]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $rider = $user->rider;
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($user->id)],
            'vehicle_type' => ['required', 'string', 'max:100'],
            'vehicle_number' => ['required', 'string', 'max:100'],
            'license_number' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);
        if ($validated['email'] !== $user->email) {
            $user->email_verified_at = null;
        }
        if ($validated['phone'] !== $user->phone) {
            $user->phone_verified_at = null;
        }
        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ])->save();
        $rider->update([
            'vehicle_type' => $validated['vehicle_type'],
            'vehicle_number' => $validated['vehicle_number'],
            'license_number' => $validated['license_number'],
            'address' => $validated['address'] ?? $rider->address,
            'formatted_address' => $validated['address'] ?? $rider->formatted_address,
            'city' => $validated['city'] ?? $rider->city,
            'district' => $validated['district'] ?? $rider->district,
            'province' => $validated['province'] ?? $rider->province,
            'latitude' => $validated['latitude'] ?? $rider->latitude,
            'longitude' => $validated['longitude'] ?? $rider->longitude,
        ]);

        return response()->json([
            'message' => 'Rider profile updated.',
            'rider' => $this->profilePayload($request),
        ]);
    }

    public function availability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'availability_status' => ['required', 'in:available,unavailable'],
        ]);
        $rider = $request->user()->rider;
        $active = $rider->deliveries()
            ->whereIn('status', ['assigned', 'accepted', 'picked_up', 'on_the_way'])
            ->exists();
        abort_if($active, 422, 'Availability cannot be changed during an active delivery.');
        $rider->update(['availability_status' => $validated['availability_status']]);

        return response()->json([
            'message' => 'Availability updated.',
            'rider' => $this->profilePayload($request),
        ]);
    }

    public function accept(Request $request, Delivery $delivery): JsonResponse
    {
        $this->ensureOwned($request, $delivery);

        return $this->deliveryResponse(
            $this->deliveryService->accept($delivery, $request->user()),
            'Delivery accepted.',
        );
    }

    public function updateStatus(Request $request, Delivery $delivery): JsonResponse
    {
        $rider = $request->user()->rider;
        if (! $rider || $delivery->rider_id !== $rider->id) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $request->validate([
            'status' => ['required', 'string', 'in:accepted,picked_up,on_the_way,delivered,failed'],
            'failed_reason' => ['required_if:status,failed', 'string'],
            'proof_image' => ['required_if:status,delivered', 'image'],
            'customer_signature' => ['nullable', 'image'],
            'note' => ['nullable', 'string'],
        ]);

        switch ($request->status) {
            case 'accepted':
                $this->deliveryService->accept($delivery, $request->user());
                break;
            case 'picked_up':
                $this->deliveryService->markPickedUp($delivery, $request->user());
                break;
            case 'on_the_way':
                $this->deliveryService->markOnTheWay($delivery, $request->user());
                break;
            case 'delivered':
                $this->deliveryService->markDelivered(
                    $delivery,
                    $request->file('proof_image'),
                    $request->file('customer_signature'),
                    $request->note,
                    $request->user(),
                );
                break;
            case 'failed':
                $this->deliveryService->markFailed($delivery, $request->failed_reason, $request->user());
                break;
        }

        return response()->json([
            'message' => 'Delivery status updated successfully.',
            'delivery' => $this->payload($delivery->refresh()->load($this->relations())),
        ]);
    }

    public function replaceProof(Request $request, Delivery $delivery): JsonResponse
    {
        $this->ensureOwned($request, $delivery);
        $request->validate([
            'proof_image' => ['required', 'image', 'max:5120'],
            'customer_signature' => ['nullable', 'image', 'max:5120'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        return $this->deliveryResponse(
            $this->deliveryService->replaceProof(
                $delivery,
                $request->file('proof_image'),
                $request->file('customer_signature'),
                $request->note,
            ),
            'Delivery proof replaced.',
        );
    }

    public function location(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'delivery_id' => ['required', 'integer', 'exists:deliveries,id'],
        ]);

        $rider = $request->user()->rider;
        if (! $rider) {
            return response()->json(['message' => 'Rider profile not found.'], 404);
        }

        $delivery = $rider->deliveries()->whereKey($request->delivery_id)->firstOrFail();
        abort_unless(
            in_array($delivery->status, ['accepted', 'picked_up', 'on_the_way'], true),
            422,
            'Location tracking is allowed only for an active accepted delivery.',
        );
        $location = $rider->locations()->create([
            'delivery_id' => $delivery->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'recorded_at' => now(),
        ]);

        return response()->json([
            'message' => 'Location logged successfully.',
            'location' => [
                'id' => $location->id,
                'delivery_id' => $location->delivery_id,
                'latitude' => (float) $location->latitude,
                'longitude' => (float) $location->longitude,
                'recorded_at' => $location->recorded_at,
            ],
        ]);
    }

    public function earnings(
        Request $request,
        RiderEarningService $earnings
    ): JsonResponse {
        $rider = $request->user()->rider;
        $summary = $earnings->summary($rider);
        $history = $rider->deliveries()
            ->with('order')
            ->where('status', 'delivered')
            ->latest('delivered_at')
            ->limit(30)
            ->get();

        return response()->json([
            'earnings' => [
                'daily_earnings' => $summary['daily'],
                'weekly_earnings' => $summary['weekly'],
                'monthly_earnings' => $summary['monthly'],
                'total_earnings' => $earnings->total($rider),
                'completed_delivery_count' => $rider->deliveries()->where('status', 'delivered')->count(),
                'failed_delivery_count' => $rider->deliveries()->where('status', 'failed')->count(),
                'history' => $history->map(fn (Delivery $delivery) => [
                    'title' => 'Delivery '.$delivery->order?->order_number,
                    'amount' => (float) ($delivery->rider_payout ?? 0),
                    'created_at' => $delivery->delivered_at,
                ]),
            ],
        ]);
    }

    public function reports(Request $request, RiderEarningService $earnings): JsonResponse
    {
        $rider = $request->user()->rider;
        $total = $rider->deliveries()->count();
        $completed = $rider->deliveries()->where('status', 'delivered')->count();

        return response()->json([
            'report' => [
                'total_deliveries' => $total,
                'completed_deliveries' => $completed,
                'failed_deliveries' => $rider->deliveries()->where('status', 'failed')->count(),
                'active_deliveries' => $rider->deliveries()
                    ->whereIn('status', ['assigned', 'accepted', 'picked_up', 'on_the_way'])
                    ->count(),
                'completion_rate' => $total > 0 ? round($completed / $total * 100, 2) : 0,
                'total_earnings' => $earnings->total($rider),
                'earnings' => $earnings->summary($rider),
            ],
        ]);
    }

    private function deliveryResponse(Delivery $delivery, string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'delivery' => $this->payload($delivery->load($this->relations())),
        ]);
    }

    private function payload(Delivery $delivery): array
    {
        $order = $delivery->order;
        $customer = $order?->customer?->user;
        $payment = $order?->payment;

        return [
            'id' => $delivery->id,
            'status' => $delivery->status,
            'pickup_address' => $delivery->pickup_address,
            'delivery_address' => $delivery->delivery_address,
            'scheduled_at' => $delivery->scheduled_at,
            'accepted_at' => $delivery->accepted_at,
            'picked_up_at' => $delivery->picked_up_at,
            'delivered_at' => $delivery->delivered_at,
            'failed_reason' => $delivery->failed_reason,
            'latitude' => $order?->delivery_latitude,
            'longitude' => $order?->delivery_longitude,
            'order' => $order ? [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $customer?->name,
                'customer_phone' => $customer?->phone,
                'delivery_address' => $order->delivery_address,
                'delivery_latitude' => $order->delivery_latitude,
                'delivery_longitude' => $order->delivery_longitude,
                'payment_method' => $payment?->payment_method,
                'payment_status' => $order->payment_status,
                'scheduled_delivery_at' => $order->scheduled_delivery_at,
                'total_amount' => (float) $order->total_amount,
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'total_price' => (float) $item->total_price,
                ]),
            ] : null,
            'proofs' => $delivery->proofs->map(fn ($proof) => [
                'id' => $proof->id,
                'proof_image' => $proof->proof_image_url
                    ? url($proof->proof_image_url)
                    : null,
                'customer_signature' => $proof->customer_signature
                    ? url('storage/'.$proof->customer_signature)
                    : null,
                'note' => $proof->note,
                'submitted_at' => $proof->submitted_at,
            ]),
        ];
    }

    private function profilePayload(Request $request): array
    {
        $user = $request->user()->refresh();
        $rider = $user->rider()->firstOrFail();

        return [
            'id' => $rider->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'vehicle_type' => $rider->vehicle_type,
            'vehicle_number' => $rider->vehicle_number,
            'license_number' => $rider->license_number,
            'address' => $rider->formatted_address ?: $rider->address,
            'city' => $rider->city,
            'district' => $rider->district,
            'province' => $rider->province,
            'latitude' => $rider->latitude,
            'longitude' => $rider->longitude,
            'availability_status' => $rider->availability_status,
            'approval_status' => $rider->verification_status,
            'profile_photo' => $user->profile_photo ? url('storage/'.$user->profile_photo) : null,
        ];
    }

    private function relations(): array
    {
        return [
            'order.customer.user',
            'order.vendor',
            'order.payment',
            'order.items.product',
            'proofs',
        ];
    }

    private function ensureOwned(Request $request, Delivery $delivery): void
    {
        abort_unless($delivery->rider_id === $request->user()->rider?->id, 403);
    }
}
