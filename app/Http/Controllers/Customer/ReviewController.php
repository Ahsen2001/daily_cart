<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function create(Order $order, Product $product): View
    {
        $this->authorize('createForOrderProduct', [Review::class, $order, $product]);

        return view('customer.reviews.create', compact('order', 'product'));
    }

    public function store(StoreReviewRequest $request, Order $order, Product $product, ReviewService $reviews): RedirectResponse
    {
        $reviews->create(
            $request->user(),
            $order,
            $product,
            (int) $request->rating,
            $request->comment,
            $request->file('image')
        );

        return redirect()->route('customer.orders.show', $order)->with('status', 'Review submitted.');
    }
}
