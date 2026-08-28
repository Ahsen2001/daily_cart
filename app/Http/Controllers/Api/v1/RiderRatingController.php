<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRiderRatingRequest;
use App\Http\Requests\StoreRiderRatingRequest;
use App\Models\Order;
use App\Models\RiderRating;
use App\Services\RiderRatingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RiderRatingController extends Controller
{
    public function show(Request $request, Order $order): JsonResponse
    {
        abort_unless($request->user()->customer?->id === $order->customer_id, 403);
        $order->load(['delivery.rider.user', 'riderRating']);

        return response()->json([
            'can_rate' => $request->user()->can('rateOrder', [RiderRating::class, $order]),
            'rider' => $order->delivery?->rider ? [
                'id' => $order->delivery->rider->id,
                'name' => $order->delivery->rider->user?->name,
            ] : null,
            'rating' => $order->riderRating ? $this->payload($order->riderRating) : null,
        ]);
    }

    public function store(StoreRiderRatingRequest $request, Order $order, RiderRatingService $ratings): JsonResponse
    {
        $rating = $ratings->submit(
            $request->user(),
            $order,
            (int) $request->validated('rating'),
            $request->validated('comment'),
            $request->validated('tags', []),
        );

        return response()->json([
            'message' => 'Rider rating saved.',
            'rating' => $this->payload($rating),
        ], $rating->wasRecentlyCreated ? 201 : 200);
    }

    public function riderIndex(Request $request, RiderRatingService $ratings): JsonResponse
    {
        $rider = $request->user()->rider;
        abort_unless($rider, 404, 'Rider profile not found.');
        $ratingList = $rider->ratings()
            ->with('order')
            ->whereIn('status', ['visible', 'reported'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'statistics' => $ratings->statistics($rider),
            'ratings' => collect($ratingList->items())->map(fn (RiderRating $rating) => $this->payload($rating, true)),
            'pagination' => [
                'total' => $ratingList->total(),
                'count' => $ratingList->count(),
                'per_page' => $ratingList->perPage(),
                'current_page' => $ratingList->currentPage(),
                'total_pages' => $ratingList->lastPage(),
            ],
        ]);
    }

    public function report(ReportRiderRatingRequest $request, RiderRating $riderRating, RiderRatingService $ratings): JsonResponse
    {
        $rating = $ratings->report($riderRating, $request->user()->rider, $request->validated('reason'));

        return response()->json([
            'message' => 'Rating reported for administrator review.',
            'rating' => $this->payload($rating, true),
        ]);
    }

    private function payload(RiderRating $rating, bool $includeReportReason = false): array
    {
        return [
            'id' => $rating->id,
            'order_id' => $rating->order_id,
            'order_number' => $rating->order?->order_number,
            'rider_id' => $rating->rider_id,
            'rating' => $rating->rating,
            'tags' => $rating->tags ?? [],
            'comment' => $rating->comment,
            'status' => $rating->status,
            'report_reason' => $includeReportReason ? $rating->report_reason : null,
            'created_at' => $rating->created_at,
            'updated_at' => $rating->updated_at,
        ];
    }
}
