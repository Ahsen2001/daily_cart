<?php

namespace App\Http\Controllers;

use App\Services\RoleRedirector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function redirect(Request $request, RoleRedirector $redirector): RedirectResponse
    {
        return redirect()->route($redirector->dashboardRouteName($request->user()));
    }

    public function superAdmin(): View
    {
        return view('dashboards.super-admin');
    }

    public function admin(): View
    {
        return view('dashboards.admin');
    }

    public function vendor(): View
    {
        return view('dashboards.vendor');
    }

    public function rider(): View
    {
        return view('dashboards.rider');
    }

    public function customer(): View
    {
        return view('dashboards.customer');
    }

    public function vendorPending(): View
    {
        return view('dashboards.vendor-pending');
    }

    public function riderPending(): View
    {
        return view('dashboards.rider-pending');
    }
}
