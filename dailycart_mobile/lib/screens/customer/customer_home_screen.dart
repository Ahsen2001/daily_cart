import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../models/product_model.dart';
import '../../providers/category_provider.dart';
import '../../providers/product_provider.dart';
import '../../routes/app_routes.dart';
import '../../theme/app_colors.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/category_card.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/empty_products_widget.dart';
import '../../widgets/product_card.dart';
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
    Future.microtask(() {
      ref.read(categoryProvider).getCategories();
      ref.read(productProvider).loadHomeProducts();
    });
  }

  @override
  Widget build(BuildContext context) {
    final categories = ref.watch(categoryProvider);
    final products = ref.watch(productProvider);

    return Scaffold(
      appBar: const CustomAppBar(title: 'DailyCart'),
      drawer: const AppDrawer(roleName: 'Customer'),
      body: RefreshIndicator(
        onRefresh: () async {
          await Future.wait([
            ref.read(categoryProvider).getCategories(),
            ref.read(productProvider).loadHomeProducts(),
          ]);
        },
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
              title: 'New Arrivals',
              products: products.newArrivals,
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
            _showPlaceholder(context, 'Wishlist placeholder');
          }
          if (index == 3) {
            _showPlaceholder(context, 'Orders placeholder');
          }
          if (index == 4) {
            _showPlaceholder(context, 'Profile placeholder');
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
            label: 'Categories',
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
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                        color: AppColors.mutedText,
                      ),
                ),
                const SizedBox(height: 4),
                Text(
                  'Fresh groceries delivered fast',
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.w900,
                      ),
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
          const Icon(
            Icons.eco_rounded,
            color: AppColors.white,
            size: 56,
          ),
        ],
      ),
    );
  }
}

class _ProductSection extends StatelessWidget {
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
  Widget build(BuildContext context) {
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
                              onAddToCart: () => _showPlaceholder(
                                context,
                                '${product.name} added to cart placeholder',
                              ),
                              onWishlist: () => _showPlaceholder(
                                context,
                                '${product.name} wishlist placeholder',
                              ),
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
          style: Theme.of(context).textTheme.titleMedium?.copyWith(
                fontWeight: FontWeight.w900,
              ),
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
          TextButton(
            onPressed: onViewAll,
            child: const Text('View all'),
          ),
      ],
    );
  }
}

void _showPlaceholder(BuildContext context, String message) {
  ScaffoldMessenger.of(context).showSnackBar(
    SnackBar(content: Text(message)),
  );
}
