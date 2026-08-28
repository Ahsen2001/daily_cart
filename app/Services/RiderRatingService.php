<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Rider;
use App\Models\RiderRating;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RiderRatingService
{
    public function __construct(private readonly NotificationService $notifications) {}

    /** @param array<int, string> $tags */
    public function submit(User $user, Order $order, int $rating, ?string $comment = null, array $tags = []): RiderRating
    {
        $customer = $user->customer;
        $delivery = $order->delivery()->with('rider.user')->first();

        if (! $customer || $order->customer_id !== $customer->id) {
            throw ValidationException::withMessages(['order' => 'You can rate only your own delivery.']);
        }

        if ($order->order_status !== 'delivered' || $delivery?->status !== 'delivered') {
            throw ValidationException::withMessages(['order' => 'The rider can be rated only after the delivery is completed.']);
        }

        if (! $delivery->rider_id) {
            throw ValidationException::withMessages(['order' => 'No rider was assigned to this delivery.']);
        }

        $riderRating = DB::transaction(function () use ($customer, $order, $delivery, $rating, $comment, $tags) {
            $existing = RiderRating::query()->where('delivery_id', $delivery->id)->first();
            $attributes = [
                'rating' => $rating,
                'comment' => filled($comment) ? trim($comment) : null,
                'tags' => array_values(array_unique($tags)),
            ];

            if ($existing) {
                abort_unless($existing->customer_id === $customer->id, 403);
                $existing->update($attributes);

                return $existing->refresh();
            }

            return RiderRating::query()->create($attributes + [
                'order_id' => $order->id,
                'delivery_id' => $delivery->id,
                'customer_id' => $customer->id,
                'rider_id' => $delivery->rider_id,
                'status' => 'visible',
            ]);
        });

        if ($delivery->rider?->user) {
            $this->notifications->send(
                $delivery->rider->user,
                'New delivery rating',
                'A customer rated delivery '.$order->order_number.' '.$rating.' out of 5.',
                'rider_rating_received',
                ['database', 'push'],
                ['order_id' => $order->id, 'rating_id' => $riderRating->id],
                '/rider/ratings',
                'rider',
            );
        }

        return $riderRating;
    }

    public function report(RiderRating $rating, Rider $rider, string $reason): RiderRating
    {
        abort_unless($rating->rider_id === $rider->id, 403);

        $rating->update([
            'status' => 'reported',
            'report_reason' => trim($reason),
            'reported_at' => now(),
        ]);

        return $rating->refresh();
    }

    public function moderate(RiderRating $rating, User $moderator, string $status): RiderRating
    {
        abort_unless($moderator->isAdminUser(), 403);

        $rating->update([
            'status' => $status,
            'moderated_by' => $moderator->id,
            'moderated_at' => now(),
        ]);

        return $rating->refresh();
    }

    /** @return array{average: float, count: int, reported: int, distribution: array<int, int>} */
    public function statistics(?Rider $rider = null): array
    {
        $base = RiderRating::query()->when($rider, fn ($query) => $query->where('rider_id', $rider->id));
        $visible = (clone $base)->where('status', 'visible');
        $distribution = [];

        foreach (range(5, 1) as $score) {
            $distribution[$score] = (clone $visible)->where('rating', $score)->count();
        }

        return [
            'average' => round((float) (clone $visible)->avg('rating'), 2),
            'count' => (clone $visible)->count(),
            'reported' => (clone $base)->where('status', 'reported')->count(),
            'distribution' => $distribution,
        ];
    }
}
