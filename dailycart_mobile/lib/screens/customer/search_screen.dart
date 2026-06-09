import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/cart_provider.dart';
import '../../providers/search_provider.dart';
import '../../providers/wishlist_provider.dart';
import '../../routes/app_routes.dart';
import '../../theme/app_colors.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_products_widget.dart';
import '../../widgets/product_card.dart';
import '../../widgets/search_bar_widget.dart';

class SearchScreen extends ConsumerStatefulWidget {
  const SearchScreen({super.key});

  @override
  ConsumerState<SearchScreen> createState() => _SearchScreenState();
}

class _SearchScreenState extends ConsumerState<SearchScreen> {
  final _controller = TextEditingController();

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(searchProvider);

    return Scaffold(
      appBar: const CustomAppBar(title: 'Search'),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          SearchBarWidget(
            controller: _controller,
            hintText: 'Search by name, brand, category, SKU, barcode',
            onSubmitted: (value) {
              ref.read(searchProvider).searchProducts(value);
            },
          ),
          const SizedBox(height: 18),
          if (_controller.text.isEmpty && state.searchResults.isEmpty) ...[
            _SearchChips(
              title: 'Recent Searches',
              items: state.recentSearches,
              onTap: _runSearch,
            ),
            const SizedBox(height: 18),
            _SearchChips(
              title: 'Popular Searches',
              items: state.popularSearches,
              onTap: _runSearch,
            ),
            const SizedBox(height: 18),
            _VoiceSearchPlaceholder(onTap: () {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Voice search placeholder')),
              );
            }),
          ],
          if (state.isLoading)
            const Padding(
              padding: EdgeInsets.only(top: 48),
              child: Center(child: CircularProgressIndicator()),
            )
          else if (_controller.text.isNotEmpty && state.searchResults.isEmpty)
            const Padding(
              padding: EdgeInsets.only(top: 48),
              child: EmptyProductsWidget(),
            )
          else if (state.searchResults.isNotEmpty)
            ListView.separated(
              padding: const EdgeInsets.only(top: 8),
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemBuilder: (context, index) {
                final product = state.searchResults[index];
                return ProductListCard(
                  product: product,
                  onTap: () => context.push(
                    '${AppRoutes.productDetails}/${product.id}',
                  ),
                  onAddToCart: () async {
                    final ok = await ref.read(cartProvider).addToCart(
                          product: product,
                          quantity: 1,
                        );
                    _showPlaceholder(
                      ok
                          ? '${product.name} added to cart.'
                          : ref.read(cartProvider).errorMessage ??
                              'Unable to add cart item.',
                    );
                  },
                  onWishlist: () async {
                    final ok =
                        await ref.read(wishlistProvider).addToWishlist(product);
                    _showPlaceholder(
                      ok
                          ? '${product.name} added to wishlist.'
                          : ref.read(wishlistProvider).errorMessage ??
                              'Unable to add wishlist item.',
                    );
                  },
                );
              },
              separatorBuilder: (context, index) => const SizedBox(height: 14),
              itemCount: state.searchResults.length,
            ),
        ],
      ),
    );
  }

  void _runSearch(String query) {
    setState(() => _controller.text = query);
    ref.read(searchProvider).searchProducts(query);
  }

  void _showPlaceholder(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }
}

class _SearchChips extends StatelessWidget {
  const _SearchChips({
    required this.title,
    required this.items,
    required this.onTap,
  });

  final String title;
  final List<String> items;
  final ValueChanged<String> onTap;

  @override
  Widget build(BuildContext context) {
    if (items.isEmpty) {
      return const SizedBox.shrink();
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: Theme.of(context).textTheme.titleMedium?.copyWith(
                fontWeight: FontWeight.w900,
              ),
        ),
        const SizedBox(height: 10),
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: [
            for (final item in items)
              ActionChip(
                label: Text(item),
                onPressed: () => onTap(item),
                backgroundColor: AppColors.white,
                side: const BorderSide(color: AppColors.border),
              ),
          ],
        ),
      ],
    );
  }
}

class _VoiceSearchPlaceholder extends StatelessWidget {
  const _VoiceSearchPlaceholder({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(22),
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: BorderRadius.circular(22),
          boxShadow: const [
            BoxShadow(
              color: AppColors.shadow,
              blurRadius: 20,
              offset: Offset(0, 10),
            ),
          ],
        ),
        child: const Row(
          children: [
            Icon(Icons.mic_none_rounded, color: AppColors.accentOrange),
            SizedBox(width: 12),
            Expanded(child: Text('Voice search placeholder')),
          ],
        ),
      ),
    );
  }
}
