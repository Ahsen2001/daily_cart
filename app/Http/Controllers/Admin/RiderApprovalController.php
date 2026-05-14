<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiderApprovalController extends Controller
{
    public function index(): View
    {
        return view('admin.riders.index', [
            'riders' => Rider::with('user')->latest()->paginate(15),
        ]);
    }

    public function approve(Rider $rider): RedirectResponse
    {
        $rider->update([
            'verification_status' => 'verified',
            'availability_status' => 'available',
        ]);

        $rider->user()->update(['status' => 'active']);

        return back()->with('status', 'Rider approved successfully.');
    }

    public function reject(Request $request, Rider $rider): RedirectResponse
    {
        $rider->update([
            'verification_status' => 'rejected',
            'availability_status' => 'unavailable',
        ]);

        $rider->user()->update(['status' => 'suspended']);

        return back()->with('status', 'Rider rejected.');
    }
}
