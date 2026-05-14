<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminReviewRequest;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminReviewController extends Controller
{
    public function index(Request $request, ReviewService $reviews): View
    {
        $reviewList = Review::query()
            ->with(['customer.user', 'product.vendor', 'order'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews.index', [
            'reviews' => $reviewList,
            'analytics' => $reviews->analytics(),
        ]);
    }

    public function hide(AdminReviewRequest $request, Review $review, ReviewService $reviews): RedirectResponse
    {
        $this->authorize('moderate', $review);
        $reviews->hide($review);

        return back()->with('status', 'Review hidden.');
    }

    public function destroy(Review $review, ReviewService $reviews): RedirectResponse
    {
        $this->authorize('moderate', $review);
        $reviews->delete($review);

        return back()->with('status', 'Review deleted.');
    }
}
