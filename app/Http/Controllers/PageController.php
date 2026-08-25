<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\VendorProfile;
use App\Services\ContentPageService;
use App\Services\PromotionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(
        private readonly ContentPageService $contentPages
    ) {}

    /**
     * Display the DailyCart homepage.
     */
    public function home(
        Request $request,
        PromotionService $promotions
    ): View {
        $todayOffers = $promotions->storefront(6);

        $promotions->recordImpressions(
            $todayOffers,
            $request
        );

        return view('welcome', [
            'todayOffers' => $todayOffers,

            'featuredProducts' => Cache::remember(
                'storefront:featured-products',
                now()->addSeconds(30),
                fn() => Product::query()
                    ->visibleToCustomers()
                    ->with([
                        'category',
                        'vendor.storeProfile',
                        'images',
                    ])
                    ->withAvg([
                        'reviews as visible_reviews_avg_rating' => fn($query) => $query
                            ->where('status', 'visible'),
                    ], 'rating')
                    ->latest()
                    ->limit(5)
                    ->get()
            ),

            'featuredStores' => VendorProfile::query()
                ->publiclyVisible()
                ->with('vendor')
                ->withCount('followers')
                ->where('is_featured', true)
                ->latest()
                ->limit(4)
                ->get(),

            'popularStores' => VendorProfile::query()
                ->publiclyVisible()
                ->with('vendor')
                ->withCount('followers')
                ->orderByDesc('followers_count')
                ->latest()
                ->limit(4)
                ->get(),
        ]);
    }

    /**
     * Display a single product.
     */
    public function product(
        Request $request,
        Product $product,
        PromotionService $promotions
    ): View {
        abort_unless(
            Product::visibleToCustomers()
                ->whereKey($product->getKey())
                ->exists(),
            404
        );

        $promotions->recordClick(
            $product,
            $request->integer('promotion')
        );

        $product->load([
            'category',
            'vendor.storeProfile',
            'images',
            'variants',
        ]);

        return view('pages.product', [
            'product' => $product,

            'pricing' => $promotions->pricingFor($product),

            'variantPricing' => $product->variants->mapWithKeys(
                fn($variant) => [
                    $variant->id => $promotions->pricingFor(
                        $product,
                        $variant
                    ),
                ]
            ),
        ]);
    }

    /**
     * Display active product categories.
     */
    public function categories(): View
    {
        return view('pages.categories', [
            'categories' => Category::active()
                ->withCount([
                    'products as available_products_count' => fn($query) => $query
                        ->visibleToCustomers(),
                ])
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Display the searchable product listing.
     */
    public function products(Request $request): View
    {
        $selectedCategory = $request->filled('category')
            ? Category::active()
            ->where('slug', $request->category)
            ->first()
            : null;

        $products = Product::query()
            ->visibleToCustomers()
            ->with([
                'category',
                'vendor.storeProfile',
                'images',
            ])
            ->withAvg([
                'reviews as visible_reviews_avg_rating' => fn($query) => $query
                    ->where('status', 'visible'),
            ], 'rating')
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $query->where(
                        function ($inner) use ($request) {
                            $inner
                                ->where(
                                    'name',
                                    'like',
                                    '%' . $request->search . '%'
                                )
                                ->orWhere(
                                    'brand',
                                    'like',
                                    '%' . $request->search . '%'
                                );
                        }
                    );
                }
            )
            ->when(
                $selectedCategory,
                fn($query) => $query->where(
                    'category_id',
                    $selectedCategory->id
                )
            )
            ->when(
                $request->filled('category')
                    && ! $selectedCategory,
                fn($query) => $query->whereRaw('1 = 0')
            )
            ->latest()
            ->paginate(16)
            ->withQueryString();

        return view('pages.products', [
            'categories' => Category::active()
                ->orderBy('name')
                ->get(),

            'products' => $products,

            'selectedCategory' => $selectedCategory,
        ]);
    }

    /**
     * Display the refund policy.
     */
    public function refundPolicy(): View
    {
        return view('pages.refund-policy');
    }

    /**
     * Display the editable About page.
     */
    public function about(): View
    {
        return $this->contentPage('about');
    }

    /**
     * Display the editable Contact page.
     */
    public function contact(): View
    {
        return $this->contentPage('contact');
    }

    /**
     * Store a public contact message.
     */
    public function submitContact(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:60',
            ],

            'subject' => [
                'required',
                'string',
                'max:255',
            ],

            'message' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        ContactMessage::create(
            $validated + [
                'status' => 'pending',
            ]
        );

        return back()->with(
            'contact_status',
            'Your message has been sent.'
        );
    }

    /**
     * Display the editable Offers page.
     */
    public function offers(
        Request $request,
        PromotionService $promotions
    ): View {
        $storefrontPromotions = $promotions->storefront(6);

        $promotions->recordImpressions(
            $storefrontPromotions,
            $request
        );

        return $this->contentPage('offers', [
            'promotions' => $storefrontPromotions,
        ]);
    }

    /**
     * Display the privacy policy.
     */
    public function privacyPolicy(): View
    {
        return view('pages.privacy-policy');
    }

    /**
     * Display the terms and conditions.
     */
    public function termsAndConditions(): View
    {
        return view('pages.terms-and-conditions');
    }

    /**
     * Display a shared editable content page.
     */
    private function contentPage(
        string $page,
        array $data = []
    ): View {
        abort_unless(
            $this->contentPages->exists($page),
            404
        );

        return view(
            'pages.content',
            array_replace([
                'page' => $page,
                'content' => $this->contentPages->content($page),
            ], $data)
        );
    }
}
