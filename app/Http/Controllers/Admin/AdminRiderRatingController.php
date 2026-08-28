<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ModerateRiderRatingRequest;
use App\Models\Rider;
use App\Models\RiderRating;
use App\Services\RiderRatingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminRiderRatingController extends Controller
{
    public function index(Request $request, RiderRatingService $ratings): View
    {
        $ratingList = RiderRating::query()
            ->with(['order', 'customer.user', 'rider.user', 'moderator'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('rating'), fn ($query) => $query->where('rating', $request->integer('rating')))
            ->when($request->filled('rider_id'), fn ($query) => $query->where('rider_id', $request->integer('rider_id')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.rider-ratings.index', [
            'ratings' => $ratingList,
            'statistics' => $ratings->statistics(),
            'riders' => Rider::query()->with('user')->orderBy('id')->get(),
        ]);
    }

    public function moderate(ModerateRiderRatingRequest $request, RiderRating $riderRating, RiderRatingService $ratings): RedirectResponse
    {
        $ratings->moderate($riderRating, $request->user(), $request->validated('status'));

        return back()->with('status', 'Rider rating moderation status updated.');
    }
}
