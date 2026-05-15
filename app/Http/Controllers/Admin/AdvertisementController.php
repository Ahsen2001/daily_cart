<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdvertisementRequest;
use App\Models\Advertisement;
use App\Models\Vendor;
use App\Services\AdvertisementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdvertisementController extends Controller
{
    public function index(): View
    {
        return view('admin.advertisements.index', [
            'advertisements' => Advertisement::with('vendor')->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.advertisements.create', ['advertisement' => new Advertisement, 'vendors' => Vendor::orderBy('store_name')->get()]);
    }

    public function store(StoreAdvertisementRequest $request, AdvertisementService $ads): RedirectResponse
    {
        $advertisement = $ads->create($request->validated(), $request->user(), $request->file('image'));

        return redirect()->route('admin.advertisements.edit', $advertisement)->with('status', 'Advertisement created.');
    }

    public function edit(Advertisement $advertisement): View
    {
        return view('admin.advertisements.edit', ['advertisement' => $advertisement, 'vendors' => Vendor::orderBy('store_name')->get()]);
    }

    public function update(StoreAdvertisementRequest $request, Advertisement $advertisement, AdvertisementService $ads): RedirectResponse
    {
        $ads->update($advertisement, $request->validated(), $request->file('image'));

        return back()->with('status', 'Advertisement updated.');
    }
}
