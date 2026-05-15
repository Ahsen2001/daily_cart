<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\AdvertisementService;
use Illuminate\View\View;

class AdvertisementController extends Controller
{
    public function index(AdvertisementService $advertisements): View
    {
        return view('customer.advertisements.index', [
            'advertisements' => $advertisements->active(),
        ]);
    }
}
