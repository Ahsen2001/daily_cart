<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Rider;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    public function index(Request $request): View
    {
        $deliveries = Delivery::query()
            ->with(['order.customer.user', 'order.vendor', 'rider.user'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('rider_id'), fn ($query) => $query->where('rider_id', $request->rider_id))
            ->when($request->filled('date'), fn ($query) => $query->whereDate('scheduled_at', $request->date))
            ->latest('scheduled_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.deliveries.index', [
            'deliveries' => $deliveries,
            'riders' => Rider::with('user')->where('verification_status', 'verified')->get(),
        ]);
    }
}
