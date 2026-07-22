import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../models/product_model.dart';
import '../../providers/cart_provider.dart';
import '../../providers/category_provider.dart';
import '../../providers/coupon_provider.dart';
import '../../providers/loyalty_provider.dart';
import '../../providers/notification_provider.dart';
import '../../providers/product_provider.dart';
import '../../providers/promotion_provider.dart';
import '../../providers/wishlist_provider.dart';
import '../../routes/app_routes.dart';
import '../../theme/app_colors.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/category_card.dart';
import '../../widgets/coupon_card.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/empty_products_widget.dart';
import '../../widgets/error_widget.dart';
import '../../widgets/loyalty_balance_card.dart';
import '../../widgets/product_card.dart';
import '../../widgets/promotion_banner.dart';
import '../../widgets/search_bar_widget.dart';

class CustomerHomeScreen extends ConsumerStatefulWidget {
  const CustomerHomeScreen({super.key});

  @override
  ConsumerState<CustomerHomeScreen> createState() => _CustomerHomeScreenState();
}

class _CustomerHomeScreenState extends ConsumerState<CustomerHomeScreen> {
  int _selectedIndex = 0;

  @override
  void initState() {
    super.initState();
    Future.microtask(_loadHomeData);
  }

  Future<void> _loadHomeData() async {
    // The PHP development server is single-worker on Windows. Stage the
    // catalog before account extras so queued requests cannot time each other
    // out. Production servers also benefit from the smaller startup burst.
    await ref.read(categoryProvider).getCategories();
    await ref.read(productProvider).loadHomeProducts();
    await Future.wait([
      ref.read(loyaltyProvider).getBalance(),
      ref.read(couponProvider).getAvailableCoupons(),
      ref.read(promotionProvider).getPromotions(),
    ]);
  }

  @override
  Widget build(BuildContext context) {
    final categories = ref.watch(categoryProvider);
    final products = ref.watch(productProvider);
    final notifications = ref.watch(notificationProvider);
    final loyalty = ref.watch(loyaltyProvider);
    final coupons = ref.watch(couponProvider);
    final promotions = ref.watch(promotionProvider);

    return Scaffold(
      appBar: CustomAppBar(
        title: 'DailyCart',
        actions: [
          IconButton(
            tooltip: 'Cart',
            onPressed: () => context.push(AppRoutes.cart),
            icon: const Icon(Icons.shopping_cart_outlined),
          ),
          Padding(
            padding: const EdgeInsets.only(right: 8),
            child: Badge(
              isLabelVisible: notifications.unreadCount > 0,
              label: Text('${notifications.unreadCount}'),
              backgroundColor: AppColors.accentOrange,
              child: IconButton(
                tooltip: 'Notifications',
                onPressed: () => context.push(AppRoutes.notifications),
                icon: const Icon(Icons.notifications_none_rounded),
              ),
            ),
          ),
        ],
      ),
      drawer: const AppDrawer(roleName: 'Customer'),
      body: RefreshIndicator(
        onRefresh: _loadHomeData,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),
          children: [
            _WelcomeBanner(),
            const SizedBox(height: 18),
            SearchBarWidget(
              hintText: 'Search products, brands, SKU, barcode',
              readOnly: true,
              onTap: () => context.push(AppRoutes.search),
            ),
            const SizedBox(height: 18),
            _DashboardActions(),
            const SizedBox(height: 24),
            _SectionHeader(
              title: 'Categories',
              onViewAll: () => context.push(AppRoutes.categories),
            ),
            const SizedBox(height: 12),
            SizedBox(
              height: 176,
              child: categories.isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : ListView.separated(
                      scrollDirection: Axis.horizontal,
                      itemBuilder: (context, index) {
                        final category = categories.categories[index];
                        return CategoryCard(
                          category: category,
                          onTap: () => context.push(
                            '${AppRoutes.products}?categoryId=${category.id}&categoryName=${Uri.encodeComponent(category.name)}',
                          ),
                        );
                      },
                      separatorBuilder: (context, index) =>
                          const SizedBox(width: 12),
                      itemCount: categories.categories.length,
                    ),
            ),
            const SizedBox(height: 24),
            _ProductSection(
              title: 'Latest Products',
              products: products.newArrivals,
              isLoading: products.isLoading,
            ),
            if (categories.errorMessage != null ||
                products.errorMessage != null) ...[
              const SizedBox(height: 18),
              DailyCartErrorWidget(
                title: 'Unable to refresh the catalog',
                message:
                    products.errorMessage ??
                    categories.errorMessage ??
                    'Please try again.',
                onRetry: () {
                  ref.read(categoryProvider).getCategories();
                  ref.read(productProvider).loadHomeProducts();
                },
              ),
            ],
            const SizedBox(height: 18),
            LoyaltyBalanceCard(
              points: loyalty.loyaltyBalance,
              onHistory: () => context.push(AppRoutes.loyaltyHistory),
              onRedeem: loyalty.loyaltyBalance > 0
                  ? () => _showPlaceholder(
                      context,
                      'Redeem points during checkout placeholder.',
                    )
                  : null,
            ),
            if (promotions.promotions.isNotEmpty) ...[
              const SizedBox(height: 18),
              PromotionBanner(
                promotion: promotions.promotions.first,
                onTap: () => context.push(
                  '${AppRoutes.promotionDetails}/${promotions.promotions.first.id}',
                ),
              ),
            ],
            if (coupons.coupons.isNotEmpty) ...[
              const SizedBox(height: 24),
              _SectionHeader(
                title: 'Available Coupons',
                onViewAll: () => context.push(AppRoutes.availableCoupons),
              ),
              const SizedBox(height: 12),
              SizedBox(
                height: 230,
                child: ListView.separated(
                  scrollDirection: Axis.horizontal,
                  itemBuilder: (context, index) {
                    final coupon = coupons.coupons[index];
                    return SizedBox(
                      width: 300,
                      child: CouponCard(
                        coupon: coupon,
                        onCopy: () => _showPlaceholder(
                          context,
                          '${coupon.code} is ready to use at checkout.',
                        ),
                        onApply: () => context.push(AppRoutes.checkout),
                      ),
                    );
                  },
                  separatorBuilder: (context, index) =>
                      const SizedBox(width: 12),
                  itemCount: coupons.coupons.take(5).length,
                ),
              ),
            ],
            _AdvertisementBanner(),
            const SizedBox(height: 24),
            _ProductSection(
              title: 'Featured Products',
              products: products.featuredProducts,
              isLoading: products.isLoading,
            ),
            _ProductSection(
              title: 'Best Selling Products',
              products: products.bestSellingProducts,
              isLoading: products.isLoading,
            ),
            _ProductSection(
              title: 'Flash Deals',
              products: products.flashDeals,
              isLoading: products.isLoading,
              showBadge: true,
            ),
            _ProductSection(
              title: 'Recommended Products',
              products: products.recommendedProducts,
              isLoading: products.isLoading,
            ),
            _ProductSection(
              title: 'Recently Viewed Products',
              products: products.recentlyViewedProducts,
              isLoading: products.isLoading,
            ),
            _RecentlyPurchasedSection(
              products: products.recentlyViewedProducts,
              isLoading: products.isLoading,
            ),
          ],
        ),
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _selectedIndex,
        onDestinationSelected: (index) {
          setState(() => _selectedIndex = index);
          if (index == 1) {
            context.push(AppRoutes.categories);
          }
          if (index == 2) {
            context.push(AppRoutes.wishlist);
          }
          if (index == 3) {
            context.push(AppRoutes.myOrders);
          }
          if (index == 4) {
            context.push(AppRoutes.profile);
          }
        },
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.home_outlined),
            selectedIcon: Icon(Icons.home_rounded),
            label: 'Home',
          ),
          NavigationDestination(
            icon: Icon(Icons.category_outlined),
            selectedIcon: Icon(Icons.category_rounded),
            label: 'Shop',
          ),
          NavigationDestination(
            icon: Icon(Icons.favorite_border_rounded),
            selectedIcon: Icon(Icons.favorite_rounded),
            label: 'Wishlist',
          ),
          NavigationDestination(
            icon: Icon(Icons.receipt_long_outlined),
            selectedIcon: Icon(Icons.receipt_long_rounded),
            label: 'Orders',
          ),
          NavigationDestination(
            icon: Icon(Icons.person_outline_rounded),
            selectedIcon: Icon(Icons.person_rounded),
            label: 'Profile',
          ),
        ],
      ),
    );
  }
}

class _DashboardActions extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: _QuickActionButton(
            icon: Icons.support_agent_rounded,
            label: 'Support',
            onTap: () => context.push(AppRoutes.supportTickets),
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: _QuickActionButton(
            icon: Icons.confirmation_number_outlined,
            label: 'Coupons',
            onTap: () => context.push(AppRoutes.availableCoupons),
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: _QuickActionButton(
            icon: Icons.star_rate_rounded,
            label: 'Reviews',
            onTap: () => context.push(AppRoutes.myReviews),
          ),
        ),
      ],
    );
  }
}

class _QuickActionButton extends StatelessWidget {
  const _QuickActionButton({
    required this.icon,
    required this.label,
    required this.onTap,
  });

  final IconData icon;
  final String label;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(18),
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 14),
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: BorderRadius.circular(18),
          boxShadow: const [
            BoxShadow(
              color: AppColors.shadow,
              blurRadius: 18,
              offset: Offset(0, 8),
            ),
          ],
        ),
        child: Column(
          children: [
            Icon(icon, color: AppColors.darkGreen),
            const SizedBox(height: 6),
            Text(
              label,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(fontWeight: FontWeight.w800),
            ),
          ],
        ),
      ),
    );
  }
}

class _WelcomeBanner extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return DailyCartCard(
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Good day',
                  style: Theme.of(
                    context,
                  ).textTheme.bodyMedium?.copyWith(color: AppColors.mutedText),
                ),
                const SizedBox(height: 4),
                Text(
                  'Fresh groceries delivered fast',
                  style: Theme.of(
                    context,
                  ).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w900),
                ),
              ],
            ),
          ),
          Container(
            width: 64,
            height: 64,
            decoration: BoxDecoration(
              color: AppColors.primaryGreen.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(22),
            ),
            child: const Icon(
              Icons.local_grocery_store_rounded,
              color: AppColors.darkGreen,
              size: 34,
            ),
          ),
        ],
      ),
    );
  }
}

class _AdvertisementBanner extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: AppColors.darkGreen,
        borderRadius: BorderRadius.circular(24),
        boxShadow: const [
          BoxShadow(
            color: AppColors.shadow,
            blurRadius: 24,
            offset: Offset(0, 12),
          ),
        ],
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 5,
                  ),
                  decoration: BoxDecoration(
                    color: AppColors.accentOrange,
                    borderRadius: BorderRadius.circular(999),
                  ),
                  child: const Text(
                    'Daily Deal',
                    style: TextStyle(
                      color: AppColors.white,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                Text(
                  'Save more on fresh produce today',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    color: AppColors.white,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ],
            ),
          ),
          const Icon(Icons.eco_rounded, color: AppColors.white, size: 56),
        ],
      ),
    );
  }
}

class _RecentlyPurchasedSection extends ConsumerWidget {
  const _RecentlyPurchasedSection({
    required this.products,
    required this.isLoading,
  });

  final List<ProductModel> products;
  final bool isLoading;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    if (products.isEmpty) {
      return const SizedBox.shrink();
    }

    return Padding(
      padding: const EdgeInsets.only(bottom: 24),
      child: Column(
        children: [
          _SectionHeader(title: 'Recently Purchased Products'),
          const SizedBox(height: 12),
          SizedBox(
            height: 346,
            child: isLoading
                ? const Center(child: CircularProgressIndicator())
                : ListView.separated(
                    scrollDirection: Axis.horizontal,
                    itemBuilder: (context, index) {
                      final product = products[index];
                      return SizedBox(
                        width: 190,
                        child: Column(
                          children: [
                            Expanded(
                              child: ProductCard(
                                product: product,
                                onTap: () => context.push(
                                  '${AppRoutes.productDetails}/${product.id}',
                                ),
                                onAddToCart: () async {
                                  final ok = await ref
                                      .read(cartProvider)
                                      .addToCart(product: product, quantity: 1);
                                  if (!context.mounted) {
                                    return;
                                  }
                                  _showPlaceholder(
                                    context,
                                    ok
                                        ? '${product.name} added to cart.'
                                        : ref.read(cartProvider).errorMessage ??
                                              'Unable to add cart item.',
                                  );
                                },
                                onWishlist: () async {
                                  final ok = await ref
                                      .read(wishlistProvider)
                                      .addToWishlist(product);
                                  if (!context.mounted) {
                                    return;
                                  }
                                  _showPlaceholder(
                                    context,
                                    ok
                                        ? '${product.name} added to wishlist.'
                                        : ref
                                                  .read(wishlistProvider)
                                                  .errorMessage ??
                                              'Unable to add wishlist item.',
                                  );
                                },
                              ),
                            ),
                            const SizedBox(height: 8),
                            SizedBox(
                              width: double.infinity,
                              child: ElevatedButton.icon(
                                onPressed: () async {
                                  final ok = await ref
                                      .read(cartProvider)
                                      .addToCart(product: product, quantity: 1);
                                  if (!context.mounted) {
                                    return;
                                  }
                                  _showPlaceholder(
                                    context,
                                    ok
                                        ? '${product.name} added to cart.'
                                        : ref.read(cartProvider).errorMessage ??
                                              'Unable to reorder item.',
                                  );
                                },
                                icon: const Icon(Icons.refresh_rounded),
                                label: const Text('Reorder'),
                              ),
                            ),
                          ],
                        ),
                      );
                    },
                    separatorBuilder: (context, index) =>
                        const SizedBox(width: 12),
                    itemCount: products.length,
                  ),
          ),
        ],
      ),
    );
  }
}

class _ProductSection extends ConsumerWidget {
  const _ProductSection({
    required this.title,
    required this.products,
    required this.isLoading,
    this.showBadge = false,
  });

  final String title;
  final List<ProductModel> products;
  final bool isLoading;
  final bool showBadge;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    if (!isLoading && products.isEmpty) {
      return const SizedBox.shrink();
    }

    return Padding(
      padding: const EdgeInsets.only(bottom: 24),
      child: Column(
        children: [
          _SectionHeader(
            title: title,
            showBadge: showBadge,
            onViewAll: () => context.push(AppRoutes.products),
          ),
          const SizedBox(height: 12),
          SizedBox(
            height: 316,
            child: isLoading
                ? const Center(child: CircularProgressIndicator())
                : products.isEmpty
                ? const EmptyProductsWidget()
                : ListView.separated(
                    scrollDirection: Axis.horizontal,
                    itemBuilder: (context, index) {
                      final product = products[index];
                      return SizedBox(
                        width: 190,
                        child: ProductCard(
                          product: product,
                          onTap: () => context.push(
                            '${AppRoutes.productDetails}/${product.id}',
                          ),
                          onAddToCart: () async {
                            final ok = await ref
                                .read(cartProvider)
                                .addToCart(product: product, quantity: 1);
                            if (!context.mounted) {
                              return;
                            }
                            _showPlaceholder(
                              context,
                              ok
                                  ? '${product.name} added to cart.'
                                  : ref.read(cartProvider).errorMessage ??
                                        'Unable to add cart item.',
                            );
                          },
                          onWishlist: () async {
                            final ok = await ref
                                .read(wishlistProvider)
                                .addToWishlist(product);
                            if (!context.mounted) {
                              return;
                            }
                            _showPlaceholder(
                              context,
                              ok
                                  ? '${product.name} added to wishlist.'
                                  : ref.read(wishlistProvider).errorMessage ??
                                        'Unable to add wishlist item.',
                            );
                          },
                        ),
                      );
                    },
                    separatorBuilder: (context, index) =>
                        const SizedBox(width: 12),
                    itemCount: products.length,
                  ),
          ),
        ],
      ),
    );
  }
}

class _SectionHeader extends StatelessWidget {
  const _SectionHeader({
    required this.title,
    this.onViewAll,
    this.showBadge = false,
  });

  final String title;
  final VoidCallback? onViewAll;
  final bool showBadge;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Text(
          title,
          style: Theme.of(
            context,
          ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w900),
        ),
        if (showBadge) ...[
          const SizedBox(width: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
            decoration: BoxDecoration(
              color: AppColors.accentOrange,
              borderRadius: BorderRadius.circular(999),
            ),
            child: const Text(
              'Hot',
              style: TextStyle(color: AppColors.white, fontSize: 11),
            ),
          ),
        ],
        const Spacer(),
        if (onViewAll != null)
          TextButton(onPressed: onViewAll, child: const Text('View all')),
      ],
    );
  }
}

void _showPlaceholder(BuildContext context, String message) {
  ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
}
