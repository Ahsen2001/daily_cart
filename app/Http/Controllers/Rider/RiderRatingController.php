<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRiderRatingRequest;
use App\Models\RiderRating;
use App\Services\RiderRatingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiderRatingController extends Controller
{
    public function index(Request $request, RiderRatingService $ratings): View
    {
        $rider = $request->user()->rider;
        $ratingList = $rider->ratings()
            ->with('order')
            ->whereIn('status', ['visible', 'reported'])
            ->latest()
            ->paginate(15);

        return view('rider.ratings.index', [
            'ratings' => $ratingList,
            'statistics' => $ratings->statistics($rider),
        ]);
    }

    public function report(ReportRiderRatingRequest $request, RiderRating $riderRating, RiderRatingService $ratings): RedirectResponse
    {
        $ratings->report($riderRating, $request->user()->rider, $request->validated('reason'));

        return back()->with('status', 'The rating was reported for administrator review.');
    }
}
