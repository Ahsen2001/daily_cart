<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class PageController extends Controller
{
    public function categories(): View
    {
        return view('pages.categories', [
            'categories' => Category::active()
                ->withCount(['products as available_products_count' => fn ($query) => $query->visibleToCustomers()])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function refundPolicy(): View
    {
        return view('pages.refund-policy');
    }

    public function privacyPolicy(): View
    {
        return view('pages.privacy-policy');
    }

    public function termsAndConditions(): View
    {
        return view('pages.terms-and-conditions');
    }
}
