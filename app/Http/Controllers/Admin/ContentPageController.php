<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentPageController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.index', [
            'pages' => $this->pages(),
        ]);
    }

    public function edit(string $page): View
    {
        abort_unless(array_key_exists($page, $this->pages()), 404);

        return view('admin.pages.edit', [
            'page' => $page,
            'pageLabel' => $this->pages()[$page],
            'content' => Setting::values($this->defaults($page)),
        ]);
    }

    public function update(Request $request, string $page): RedirectResponse
    {
        abort_unless(array_key_exists($page, $this->pages()), 404);

        $validated = $request->validate([
            "page_{$page}_title" => ['required', 'string', 'max:120'],
            "page_{$page}_subtitle" => ['nullable', 'string', 'max:255'],
            "page_{$page}_body" => ['required', 'string'],
            "page_{$page}_email" => ['nullable', 'email', 'max:255'],
            "page_{$page}_phone" => ['nullable', 'string', 'max:60'],
            "page_{$page}_address" => ['nullable', 'string', 'max:255'],
            "page_{$page}_cta_label" => ['nullable', 'string', 'max:80'],
            "page_{$page}_cta_url" => ['nullable', 'string', 'max:255'],
        ]);

        Setting::putMany($validated);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', "{$this->pages()[$page]} page updated successfully.");
    }

    private function pages(): array
    {
        return [
            'about' => 'About',
            'contact' => 'Contact',
            'offers' => 'Offers',
        ];
    }

    private function defaults(string $page): array
    {
        $defaults = [
            'about' => [
                'title' => 'About DailyCart',
                'subtitle' => 'Daily essentials, delivered smart.',
                'body' => "DailyCart is a smart online shopping and delivery platform for groceries, vegetables, fruits, household items, bakery goods, pharmacy products, and daily essentials.\n\nWe connect customers, vendors, riders, and admins in one reliable local delivery workflow built for Sri Lanka and LKR payments.",
                'email' => 'uahsens1@gmail.com',
                'phone' => '+94 75 460 3008',
                'address' => 'Batticaloa, Sri Lanka',
                'cta_label' => 'Browse Categories',
                'cta_url' => '/categories',
            ],
            'contact' => [
                'title' => 'Contact DailyCart',
                'subtitle' => 'Need help with an order, vendor account, rider request, or platform question?',
                'body' => "Reach the DailyCart team for customer support, vendor onboarding, rider coordination, payment help, and general platform questions.\n\nOur team will review your message and guide you to the right support path.",
                'email' => 'uahsens1@gmail.com',
                'phone' => '+94 75 460 3008',
                'address' => 'Batticaloa, Sri Lanka',
                'cta_label' => 'Start Shopping',
                'cta_url' => '/register',
            ],
            'offers' => [
                'title' => 'DailyCart Offers',
                'subtitle' => 'Fresh deals and savings on daily essentials.',
                'body' => "Explore active DailyCart offers, promotions, and savings from approved vendors.\n\nAll offers are subject to availability, product approval, stock, schedule, and offer validity.",
                'email' => 'uahsens1@gmail.com',
                'phone' => '+94 75 460 3008',
                'address' => 'Batticaloa, Sri Lanka',
                'cta_label' => 'View Products',
                'cta_url' => '/products',
            ],
        ];

        return collect($defaults[$page])
            ->mapWithKeys(fn ($value, $field) => ["page_{$page}_{$field}" => $value])
            ->toArray();
    }
}
