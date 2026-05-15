<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\PromotionService;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(PromotionService $promotions): View
    {
        return view('customer.promotions.index', [
            'promotions' => $promotions->active(),
        ]);
    }
}
