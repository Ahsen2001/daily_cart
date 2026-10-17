import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../models/store_model.dart';
import '../../providers/cart_provider.dart';
import '../../providers/store_provider.dart';
import '../../providers/wishlist_provider.dart';
import '../../routes/app_routes.dart';
import '../../theme/app_colors.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/error_widget.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/product_card.dart';
import '../../widgets/promotion_card.dart';

class StoreDetailsScreen extends ConsumerStatefulWidget {
  const StoreDetailsScreen({required this.slug, super.key});
  final String slug;

  @override
  ConsumerState<StoreDetailsScreen> createState() => _StoreDetailsScreenState();
}

class _StoreDetailsScreenState extends ConsumerState<StoreDetailsScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(storeProvider).getStore(widget.slug));
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(storeProvider);
    final store = state.selectedStore;
    return Scaffold(
      appBar: CustomAppBar(title: store?.name ?? 'Store Details'),
      body: state.isLoading && store == null
          ? const LoadingWidget(message: 'Loading store...')
          : store == null
          ? DailyCartErrorWidget(
              title: 'Unable to load store',
              message: state.errorMessage ?? 'This store is unavailable.',
              onRetry: () => ref.read(storeProvider).getStore(widget.slug),
            )
          : RefreshIndicator(
              onRefresh: () => ref.read(storeProvider).getStore(widget.slug),
              child: ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.only(bottom: 28),
                children: [
                  _StoreHero(store: store),
                  Padding(
                    padding: const EdgeInsets.fromLTRB(20, 20, 20, 0),
                    child: _StoreSummary(
                      store: store,
                      onFollow: () async {
                        final ok = await ref.read(storeProvider).toggleFollow();
                        if (!context.mounted) return;
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(
                              ok
                                  ? (store.isFollowing
                                        ? 'Store unfollowed.'
                                        : 'Store followed.')
                                  : (ref.read(storeProvider).errorMessage ??
                                        'Unable to update store.'),
                            ),
                          ),
                        );
                      },
                    ),
                  ),
                  if (store.offers.isNotEmpty) _Offers(store: store),
                  if (store.categories.isNotEmpty) _Categories(store: store),
                  if (store.products.isNotEmpty) _Products(store: store),
                  if (store.reviews.isNotEmpty) _Reviews(store: store),
                ],
              ),
            ),
    );
  }
}

class _StoreHero extends StatelessWidget {
  const _StoreHero({required this.store});
  final StoreModel store;

  @override
  Widget build(BuildContext context) {
    final image = store.banners.isNotEmpty
        ? store.banners.first.image
        : store.coverImage;
    return SizedBox(
      height: 210,
      child: image.isEmpty
          ? const ColoredBox(
              color: AppColors.lightBackground,
              child: Icon(
                Icons.storefront_rounded,
                size: 72,
                color: AppColors.primaryGreen,
              ),
            )
          : CachedNetworkImage(
              imageUrl: image,
              fit: BoxFit.cover,
              memCacheWidth: 1080,
              errorWidget: (_, _, _) => const ColoredBox(
                color: AppColors.lightBackground,
                child: Icon(Icons.storefront_rounded, size: 72),
              ),
            ),
    );
  }
}

class _StoreSummary extends StatelessWidget {
  const _StoreSummary({required this.store, required this.onFollow});
  final StoreModel store;
  final VoidCallback onFollow;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(18),
              child: SizedBox(
                width: 68,
                height: 68,
                child: CachedNetworkImage(
                  imageUrl: store.logo,
                  fit: BoxFit.cover,
                  errorWidget: (_, _, _) =>
                      const Icon(Icons.storefront_rounded),
                ),
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    store.name,
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  Text(
                    '${store.followersCount} followers${store.rating > 0 ? '  •  ★ ${store.rating.toStringAsFixed(1)}' : ''}',
                    style: const TextStyle(color: AppColors.mutedText),
                  ),
                ],
              ),
            ),
            FilledButton.tonalIcon(
              onPressed: onFollow,
              icon: Icon(
                store.isFollowing ? Icons.check_rounded : Icons.add_rounded,
              ),
              label: Text(store.isFollowing ? 'Following' : 'Follow'),
            ),
          ],
        ),
        if (store.description.isNotEmpty) ...[
          const SizedBox(height: 16),
          Text(store.description),
        ],
        const SizedBox(height: 14),
        Wrap(
          spacing: 10,
          runSpacing: 10,
          children: [
            if (store.deliveryEstimate.isNotEmpty)
              _InfoChip(
                icon: Icons.schedule_rounded,
                text: store.deliveryEstimate,
              ),
            if (store.minimumOrder > 0)
              _InfoChip(
                icon: Icons.shopping_bag_outlined,
                text: 'Min ${CurrencyFormatter.lkr(store.minimumOrder)}',
              ),
            if (store.address.isNotEmpty)
              _InfoChip(icon: Icons.location_on_outlined, text: store.address),
            if (store.contactPhone.isNotEmpty)
              _InfoChip(icon: Icons.phone_outlined, text: store.contactPhone),
          ],
        ),
      ],
    );
  }
}

class _InfoChip extends StatelessWidget {
  const _InfoChip({required this.icon, required this.text});
  final IconData icon;
  final String text;
  @override
  Widget build(BuildContext context) =>
      Chip(avatar: Icon(icon, size: 18), label: Text(text));
}

class _Offers extends StatelessWidget {
  const _Offers({required this.store});
  final StoreModel store;
  @override
  Widget build(BuildContext context) => _Section(
    title: 'Store Offers',
    child: SizedBox(
      height: 270,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: store.offers.length,
        separatorBuilder: (_, _) => const SizedBox(width: 12),
        itemBuilder: (context, index) {
          final offer = store.offers[index];
          return SizedBox(
            width: 300,
            child: PromotionCard(
              promotion: offer,
              onTap: () =>
                  context.push('${AppRoutes.promotionDetails}/${offer.id}'),
            ),
          );
        },
      ),
    ),
  );
}

class _Categories extends StatelessWidget {
  const _Categories({required this.store});
  final StoreModel store;
  @override
  Widget build(BuildContext context) => _Section(
    title: 'Categories',
    child: Wrap(
      spacing: 8,
      runSpacing: 8,
      children: store.categories
          .map(
            (category) => ActionChip(
              label: Text(category.name),
              onPressed: () => context.push(
                '${AppRoutes.products}?categoryId=${category.id}&categoryName=${Uri.encodeComponent(category.name)}',
              ),
            ),
          )
          .toList(),
    ),
  );
}

class _Products extends ConsumerWidget {
  const _Products({required this.store});
  final StoreModel store;
  @override
  Widget build(BuildContext context, WidgetRef ref) => _Section(
    title: 'Products',
    child: SizedBox(
      height: 365,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: store.products.length,
        separatorBuilder: (_, _) => const SizedBox(width: 12),
        itemBuilder: (context, index) {
          final product = store.products[index];
          return SizedBox(
            width: 205,
            child: ProductCard(
              product: product,
              onTap: () =>
                  context.push('${AppRoutes.productDetails}/${product.id}'),
              onAddToCart: () async {
                final ok = await ref
                    .read(cartProvider)
                    .addToCart(product: product, quantity: 1);
                if (!context.mounted) return;
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(
                      ok
                          ? '${product.name} added to cart.'
                          : (ref.read(cartProvider).errorMessage ??
                                'Unable to add product.'),
                    ),
                  ),
                );
              },
              onWishlist: () async {
                final ok = await ref
                    .read(wishlistProvider)
                    .addToWishlist(product);
                if (!context.mounted) return;
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(
                      ok
                          ? '${product.name} added to wishlist.'
                          : (ref.read(wishlistProvider).errorMessage ??
                                'Unable to update wishlist.'),
                    ),
                  ),
                );
              },
            ),
          );
        },
      ),
    ),
  );
}

class _Reviews extends StatelessWidget {
  const _Reviews({required this.store});
  final StoreModel store;
  @override
  Widget build(BuildContext context) => _Section(
    title: 'Customer Reviews',
    child: Column(
      children: store.reviews
          .map(
            (review) => ListTile(
              contentPadding: EdgeInsets.zero,
              leading: const CircleAvatar(
                child: Icon(Icons.person_outline_rounded),
              ),
              title: Text(review.customerName),
              subtitle: Text(
                review.comment.isEmpty ? 'Rated this store' : review.comment,
              ),
              trailing: Text('★ ${review.rating.toStringAsFixed(1)}'),
            ),
          )
          .toList(),
    ),
  );
}

class _Section extends StatelessWidget {
  const _Section({required this.title, required this.child});
  final String title;
  final Widget child;
  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.fromLTRB(20, 26, 20, 0),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: Theme.of(
            context,
          ).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w900),
        ),
        const SizedBox(height: 12),
        child,
      ],
    ),
  );
}
