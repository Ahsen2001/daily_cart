<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Services\NotificationService;
use App\Services\RiderRatingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RiderApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $riders = Rider::query()
            ->with('user')
            ->withCount([
                'deliveries as active_deliveries_count' => fn ($query) => $query->whereIn('status', ['assigned', 'accepted', 'picked_up', 'on_the_way']),
                'deliveries as completed_deliveries_count' => fn ($query) => $query->where('status', 'delivered'),
            ])
            ->when($request->filled('search'), fn ($query) => $query->whereHas('user', fn ($user) => $user->where('name', 'like', '%'.$request->search.'%')->orWhere('email', 'like', '%'.$request->search.'%')->orWhere('phone', 'like', '%'.$request->search.'%')))
            ->when($request->filled('verification_status'), fn ($query) => $query->where('verification_status', $request->verification_status))
            ->when($request->filled('availability_status'), fn ($query) => $query->where('availability_status', $request->availability_status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.riders.index', compact('riders'));
    }

    public function show(Rider $rider, RiderRatingService $ratings): View
    {
        return view('admin.riders.show', [
            'rider' => $rider->load([
                'user',
                'deliveries' => fn ($query) => $query->with(['order.customer.user', 'order.vendor'])->latest('scheduled_at')->limit(20),
                'ratings' => fn ($query) => $query->with(['order', 'customer.user'])->latest()->limit(10),
            ]),
            'ratingStatistics' => $ratings->statistics($rider),
        ]);
    }

    public function approve(Rider $rider, NotificationService $notifications): RedirectResponse
    {
        $this->ensureVerificationCredentials($rider);

        $rider->update([
            'verification_status' => 'verified',
            'availability_status' => 'available',
        ]);
        $rider->user()->update(['status' => 'active']);
        $notifications->sendOnce(
            $rider->user,
            'Rider account approved',
            'Your DailyCart rider account has been verified. You can now sign in and accept delivery assignments.',
            'rider_account_approved',
            ['database', 'mail', 'push'],
            ['rider_id' => $rider->id, 'status' => 'verified'],
            '/rider-profile',
            'rider',
            'rider-approved-'.$rider->id,
        );

        return back()->with('status', 'Rider approved successfully.');
    }

    public function reject(Rider $rider, NotificationService $notifications): RedirectResponse
    {
        $rider->update([
            'verification_status' => 'rejected',
            'availability_status' => 'unavailable',
        ]);
        $rider->user()->update(['status' => 'suspended']);
        $notifications->sendOnce($rider->user, 'Rider registration rejected', 'Your DailyCart rider registration was rejected. Contact support for details.', 'rider_account_rejected', ['database', 'mail', 'push'], ['rider_id' => $rider->id, 'status' => 'rejected'], '/rider-profile', 'rider', 'rider-rejected-'.$rider->id);

        return back()->with('status', 'Rider rejected.');
    }

    public function updateStatus(Request $request, Rider $rider, NotificationService $notifications): RedirectResponse
    {
        $validated = $request->validate(['verification_status' => ['required', 'in:pending,verified,rejected,suspended']]);
        $status = $validated['verification_status'];

        if ($status === 'verified') {
            $this->ensureVerificationCredentials($rider);
        }

        $rider->update([
            'verification_status' => $status,
            'availability_status' => $status === 'verified' ? $rider->availability_status : 'unavailable',
        ]);
        $rider->user()->update(['status' => $status === 'verified' ? 'active' : 'suspended']);
        $notifications->sendOnce($rider->user, 'Rider account status updated', 'Your rider account status is now '.str($status)->replace('_', ' ')->title().'.', 'rider_account_status', ['database', 'mail', 'push'], ['rider_id' => $rider->id, 'status' => $status], '/rider-profile', 'rider', 'rider-status-'.$rider->id.'-'.$status.'-'.now()->format('YmdHi'));

        return back()->with('status', 'Rider account status updated.');
    }

    public function updateCredentials(Request $request, Rider $rider): RedirectResponse
    {
        $validated = $request->validate([
            'license_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Rider::class, 'license_number')
                    ->ignore($rider->id)
                    ->withoutTrashed(),
            ],
        ]);

        $rider->update($validated);

        return back()->with('status', 'Rider verification credentials updated.');
    }

    public function updateAvailability(Request $request, Rider $rider): RedirectResponse
    {
        $validated = $request->validate(['availability_status' => ['required', 'in:available,unavailable']]);
        abort_unless($rider->verification_status === 'verified', 422, 'Only verified riders can be made available.');
        abort_if($rider->deliveries()->whereIn('status', ['assigned', 'accepted', 'picked_up', 'on_the_way'])->exists(), 422, 'An active rider cannot be made unavailable by management.');
        $rider->update(['availability_status' => $validated['availability_status']]);

        return back()->with('status', 'Rider availability updated.');
    }

    private function ensureVerificationCredentials(Rider $rider): void
    {
        if (blank($rider->license_number)) {
            throw ValidationException::withMessages([
                'license_number' => 'Add the rider driving licence number before verifying this account.',
            ]);
        }
    }
}
