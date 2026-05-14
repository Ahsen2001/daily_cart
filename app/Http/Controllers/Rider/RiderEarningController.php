<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Http\Requests\EarningFilterRequest;
use App\Services\RiderEarningService;
use Illuminate\View\View;

class RiderEarningController extends Controller
{
    public function index(EarningFilterRequest $request, RiderEarningService $earnings): View
    {
        return view('rider.earnings.index', [
            'summary' => $earnings->summary($request->user()->rider),
        ]);
    }
}
