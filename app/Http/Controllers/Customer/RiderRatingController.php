<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRiderRatingRequest;
use App\Models\Order;
use App\Models\RiderRating;
use App\Services\RiderRatingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RiderRatingController extends Controller
{
    public function edit(Order $order): View
    {
        $this->authorize('rateOrder', [RiderRating::class, $order]);
        $order->load(['delivery.rider.user', 'riderRating']);

        return view('customer.rider-ratings.edit', [
            'order' => $order,
            'riderRating' => $order->riderRating,
        ]);
    }

    public function store(StoreRiderRatingRequest $request, Order $order, RiderRatingService $ratings): RedirectResponse
    {
        $ratings->submit(
            $request->user(),
            $order,
            (int) $request->validated('rating'),
            $request->validated('comment'),
            $request->validated('tags', []),
        );

        return redirect()->route('customer.orders.show', $order)
            ->with('status', 'Your rider rating has been saved.');
    }
}
