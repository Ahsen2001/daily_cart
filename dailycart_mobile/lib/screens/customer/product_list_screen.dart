import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/product_provider.dart';
import '../../routes/app_routes.dart';
import '../../theme/app_colors.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_products_widget.dart';
import '../../widgets/product_card.dart';

class ProductListScreen extends ConsumerStatefulWidget {
  const ProductListScreen({
    this.categoryId,
    this.categoryName,
    super.key,
  });

  final int? categoryId;
  final String? categoryName;

  @override
  ConsumerState<ProductListScreen> createState() => _ProductListScreenState();
}

class _ProductListScreenState extends ConsumerState<ProductListScreen> {
  bool _isGrid = true;
  RangeValues _priceRange = const RangeValues(0, 5000);
  double? _rating;
  bool _availableOnly = true;
  String _brand = '';
  String _sort = 'latest';

  @override
  void initState() {
    super.initState();
    Future.microtask(_loadProducts);
  }

  Future<void> _loadProducts() {
    return ref.read(productProvider).getProducts(
          categoryId: widget.categoryId,
          minPrice: _priceRange.start,
          maxPrice: _priceRange.end,
          rating: _rating,
          available: _availableOnly,
          brand: _brand,
          sort: _sort,
        );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(productProvider);
    final title = widget.categoryName ?? 'Products';

    return Scaffold(
      appBar: CustomAppBar(
        title: title,
        actions: [
          IconButton(
            tooltip: _isGrid ? 'List view' : 'Grid view',
            onPressed: () => setState(() => _isGrid = !_isGrid),
            icon: Icon(_isGrid ? Icons.view_list_rounded : Icons.grid_view_rounded),
          ),
          IconButton(
            tooltip: 'Filters',
            onPressed: _openFilters,
            icon: const Icon(Icons.tune_rounded),
          ),
        ],
      ),
      body: Column(
        children: [
          _SortBar(
            selectedSort: _sort,
            onChanged: (value) {
              setState(() => _sort = value);
              _loadProducts();
            },
          ),
          Expanded(
            child: state.isLoading
                ? const Center(child: CircularProgressIndicator())
                : state.products.isEmpty
                    ? const EmptyProductsWidget()
                    : _isGrid
                        ? GridView.builder(
                            padding: const EdgeInsets.all(20),
                            gridDelegate:
                                const SliverGridDelegateWithFixedCrossAxisCount(
                              crossAxisCount: 2,
                              mainAxisSpacing: 14,
                              crossAxisSpacing: 14,
                              childAspectRatio: 0.58,
                            ),
                            itemCount: state.products.length,
                            itemBuilder: (context, index) {
                              final product = state.products[index];
                              return ProductGridCard(
                                product: product,
                                onTap: () => context.push(
                                  '${AppRoutes.productDetails}/${product.id}',
                                ),
                                onAddToCart: () => _showPlaceholder(
                                  '${product.name} added to cart placeholder',
                                ),
                                onWishlist: () => _showPlaceholder(
                                  '${product.name} wishlist placeholder',
                                ),
                              );
                            },
                          )
                        : ListView.separated(
                            padding: const EdgeInsets.all(20),
                            itemBuilder: (context, index) {
                              final product = state.products[index];
                              return ProductListCard(
                                product: product,
                                onTap: () => context.push(
                                  '${AppRoutes.productDetails}/${product.id}',
                                ),
                                onAddToCart: () => _showPlaceholder(
                                  '${product.name} added to cart placeholder',
                                ),
                                onWishlist: () => _showPlaceholder(
                                  '${product.name} wishlist placeholder',
                                ),
                              );
                            },
                            separatorBuilder: (context, index) =>
                                const SizedBox(height: 14),
                            itemCount: state.products.length,
                          ),
          ),
        ],
      ),
    );
  }

  void _openFilters() {
    final brandController = TextEditingController(text: _brand);

    showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.lightBackground,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setSheetState) {
            return Padding(
              padding: EdgeInsets.only(
                left: 20,
                right: 20,
                top: 20,
                bottom: MediaQuery.viewInsetsOf(context).bottom + 20,
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Filters',
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(
                          fontWeight: FontWeight.w900,
                        ),
                  ),
                  const SizedBox(height: 18),
                  Text('Price range: Rs. ${_priceRange.start.round()} - Rs. ${_priceRange.end.round()}'),
                  RangeSlider(
                    values: _priceRange,
                    min: 0,
                    max: 10000,
                    divisions: 20,
                    activeColor: AppColors.primaryGreen,
                    onChanged: (value) {
                      setSheetState(() => _priceRange = value);
                    },
                  ),
                  const SizedBox(height: 10),
                  DropdownButtonFormField<double?>(
                    value: _rating,
                    decoration: const InputDecoration(labelText: 'Rating'),
                    items: const [
                      DropdownMenuItem<double?>(
                        value: null,
                        child: Text('Any rating'),
                      ),
                      DropdownMenuItem<double?>(
                        value: 3.0,
                        child: Text('3 stars and up'),
                      ),
                      DropdownMenuItem<double?>(
                        value: 4.0,
                        child: Text('4 stars and up'),
                      ),
                      DropdownMenuItem<double?>(
                        value: 4.5,
                        child: Text('4.5 stars and up'),
                      ),
                    ],
                    onChanged: (value) => setSheetState(() => _rating = value),
                  ),
                  const SizedBox(height: 12),
                  SwitchListTile(
                    contentPadding: EdgeInsets.zero,
                    value: _availableOnly,
                    title: const Text('Available only'),
                    activeThumbColor: AppColors.primaryGreen,
                    onChanged: (value) {
                      setSheetState(() => _availableOnly = value);
                    },
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: brandController,
                    decoration: const InputDecoration(
                      labelText: 'Brand',
                      prefixIcon: Icon(Icons.sell_outlined),
                    ),
                  ),
                  const SizedBox(height: 18),
                  FilledButton(
                    onPressed: () {
                      setState(() => _brand = brandController.text.trim());
                      Navigator.of(context).pop();
                      _loadProducts();
                    },
                    child: const Text('Apply Filters'),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  void _showPlaceholder(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }
}

class _SortBar extends StatelessWidget {
  const _SortBar({
    required this.selectedSort,
    required this.onChanged,
  });

  final String selectedSort;
  final ValueChanged<String> onChanged;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 6, 20, 12),
      child: DropdownButtonFormField<String>(
        value: selectedSort,
        decoration: const InputDecoration(
          labelText: 'Sort by',
          prefixIcon: Icon(Icons.sort_rounded),
        ),
        items: const [
          DropdownMenuItem(value: 'latest', child: Text('Latest')),
          DropdownMenuItem(value: 'price_low_high', child: Text('Price Low to High')),
          DropdownMenuItem(value: 'price_high_low', child: Text('Price High to Low')),
          DropdownMenuItem(value: 'highest_rated', child: Text('Highest Rated')),
          DropdownMenuItem(value: 'most_sold', child: Text('Most Sold')),
        ],
        onChanged: (value) {
          if (value != null) {
            onChanged(value);
          }
        },
      ),
    );
  }
}
