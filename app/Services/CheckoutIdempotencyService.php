<?php

namespace App\Services;

use App\Exceptions\CheckoutIdempotencyConflictException;
use App\Models\CheckoutIdempotencyRecord;
use App\Models\Customer;
use App\Models\Order;
use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CheckoutIdempotencyService
{
    /**
     * @param  Closure(): array<int, Order>  $createOrders
     * @return array{orders: array<int, Order>, replayed: bool}
     */
    public function execute(Customer $customer, string $key, array $request, Closure $createOrders): array
    {
        $snapshot = $this->normalizedSnapshot($request);
        $fingerprint = hash('sha256', json_encode($snapshot, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));

        return DB::transaction(function () use ($customer, $key, $snapshot, $fingerprint, $createOrders): array {
            $record = CheckoutIdempotencyRecord::query()->firstOrCreate(
                [
                    'customer_id' => $customer->id,
                    'idempotency_key' => $key,
                ],
                [
                    'request_fingerprint' => $fingerprint,
                    'request_snapshot' => $snapshot,
                    'status' => 'processing',
                ],
            );

            $record = CheckoutIdempotencyRecord::query()->lockForUpdate()->findOrFail($record->id);

            if (! hash_equals($record->request_fingerprint, $fingerprint)) {
                throw new CheckoutIdempotencyConflictException(
                    'This checkout key was already used with different order details. Refresh checkout and try again.'
                );
            }

            if ($record->status === 'completed') {
                $ordersById = Order::query()
                    ->where('customer_id', $customer->id)
                    ->whereIn('id', $record->order_ids ?? [])
                    ->get()
                    ->keyBy('id');
                $orders = collect($record->order_ids ?? [])
                    ->map(fn ($id) => $ordersById->get((int) $id))
                    ->filter()
                    ->values()
                    ->all();

                if (count($orders) !== count($record->order_ids ?? [])) {
                    throw new CheckoutIdempotencyConflictException(
                        'The original checkout result is unavailable. Please contact support before trying again.'
                    );
                }

                return ['orders' => $orders, 'replayed' => true];
            }

            $orders = $createOrders();

            $record->update([
                'status' => 'completed',
                'order_ids' => collect($orders)->pluck('id')->map(fn ($id) => (int) $id)->all(),
                'completed_at' => now(),
            ]);

            return ['orders' => $orders, 'replayed' => false];
        }, 3);
    }

    private function normalizedSnapshot(array $request): array
    {
        return [
            'coupon_code' => isset($request['coupon_code'])
                ? mb_strtoupper(trim((string) $request['coupon_code']))
                : null,
            'delivery_address' => trim((string) ($request['delivery_address'] ?? '')),
            'delivery_distance_meters' => isset($request['delivery_distance_meters'])
                ? (int) $request['delivery_distance_meters']
                : null,
            'delivery_district' => isset($request['delivery_district'])
                ? trim((string) $request['delivery_district'])
                : null,
            'delivery_latitude' => isset($request['delivery_latitude'])
                ? round((float) $request['delivery_latitude'], 7)
                : null,
            'delivery_longitude' => isset($request['delivery_longitude'])
                ? round((float) $request['delivery_longitude'], 7)
                : null,
            'loyalty_points' => (int) ($request['loyalty_points'] ?? 0),
            'payment_method' => (string) ($request['payment_method'] ?? ''),
            'scheduled_delivery_at' => isset($request['scheduled_delivery_at'])
                ? Carbon::parse($request['scheduled_delivery_at'])->utc()->toIso8601String()
                : null,
        ];
    }
}
