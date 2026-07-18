<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\Setting;
use App\Services\PromotionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(PromotionService $promotions): View
    {
        return view('welcome', [
            'todayOffers' => $promotions->storefront(6),
            'featuredProducts' => Cache::remember('storefront:featured-products', now()->addSeconds(30), fn () => Product::query()
                ->visibleToCustomers()
                ->with(['category', 'vendor', 'images'])
                ->withAvg(['reviews as visible_reviews_avg_rating' => fn ($query) => $query->where('status', 'visible')], 'rating')
                ->latest()
                ->limit(4)
                ->get()),
        ]);
    }

    public function product(Product $product, PromotionService $promotions): View
    {
        abort_unless(Product::visibleToCustomers()->whereKey($product->getKey())->exists(), 404);

        $product->load(['category', 'vendor', 'images', 'variants']);

        return view('pages.product', [
            'product' => $product,
            'pricing' => $promotions->pricingFor($product),
            'variantPricing' => $product->variants->mapWithKeys(
                fn ($variant) => [$variant->id => $promotions->pricingFor($product, $variant)]
            ),
        ]);
    }

    public function categories(): View
    {
        return view('pages.categories', [
            'categories' => Category::active()
                ->withCount(['products as available_products_count' => fn ($query) => $query->visibleToCustomers()])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function products(Request $request): View
    {
        $selectedCategory = $request->filled('category')
            ? Category::active()->where('slug', $request->category)->first()
            : null;

        $products = Product::query()
            ->visibleToCustomers()
            ->with(['category', 'vendor', 'images'])
            ->withAvg(['reviews as visible_reviews_avg_rating' => fn ($query) => $query->where('status', 'visible')], 'rating')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($inner) use ($request) {
                    $inner->where('name', 'like', '%'.$request->search.'%')
                        ->orWhere('brand', 'like', '%'.$request->search.'%');
                });
            })
            ->when($selectedCategory, fn ($query) => $query->where('category_id', $selectedCategory->id))
            ->when($request->filled('category') && ! $selectedCategory, fn ($query) => $query->whereRaw('1 = 0'))
            ->latest()
            ->paginate(16)
            ->withQueryString();

        return view('pages.products', [
            'categories' => Category::active()->orderBy('name')->get(),
            'products' => $products,
            'selectedCategory' => $selectedCategory,
        ]);
    }

    public function refundPolicy(): View
    {
        return view('pages.refund-policy');
    }

    public function about(): View
    {
        return $this->contentPage('about');
    }

    public function contact(): View
    {
        return $this->contentPage('contact');
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:60'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ContactMessage::create($validated + ['status' => 'pending']);

        return back()->with('contact_status', 'Your message has been sent.');
    }

    public function offers(PromotionService $promotions): View
    {
        return $this->contentPage('offers', [
            'promotions' => $promotions->storefront(6),
        ]);
    }

    public function privacyPolicy(): View
    {
        return view('pages.privacy-policy');
    }

    public function termsAndConditions(): View
    {
        return view('pages.terms-and-conditions');
    }

    private function contentPage(string $page, array $data = []): View
    {
        return view('pages.content', array_replace([
            'page' => $page,
            'content' => Setting::values($this->pageDefaults($page)),
        ], $data));
    }

    private function pageDefaults(string $page): array
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
