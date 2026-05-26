<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
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
