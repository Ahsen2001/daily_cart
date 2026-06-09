import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../providers/wishlist_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_wishlist_widget.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/wishlist_item_card.dart';

class WishlistScreen extends ConsumerStatefulWidget {
  const WishlistScreen({super.key});

  @override
  ConsumerState<WishlistScreen> createState() => _WishlistScreenState();
}

class _WishlistScreenState extends ConsumerState<WishlistScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(wishlistProvider).getWishlist());
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(wishlistProvider);

    return Scaffold(
      appBar: const CustomAppBar(title: 'Wishlist'),
      body: state.isLoading && state.wishlistItems.isEmpty
          ? const LoadingWidget(message: 'Loading wishlist...')
          : state.wishlistItems.isEmpty
              ? const EmptyWishlistWidget()
              : RefreshIndicator(
                  onRefresh: () => ref.read(wishlistProvider).getWishlist(),
                  child: ListView.separated(
                    padding: const EdgeInsets.all(20),
                    itemBuilder: (context, index) {
                      final item = state.wishlistItems[index];
                      return WishlistItemCard(
                        item: item,
                        onMoveToCart: () async {
                          final ok = await ref
                              .read(wishlistProvider)
                              .moveToCart(item);
                          _showProviderMessage(ok);
                        },
                        onRemove: () async {
                          final ok = await ref
                              .read(wishlistProvider)
                              .removeFromWishlist(item.productId);
                          _showProviderMessage(ok);
                        },
                      );
                    },
                    separatorBuilder: (context, index) =>
                        const SizedBox(height: 14),
                    itemCount: state.wishlistItems.length,
                  ),
                ),
    );
  }

  void _showProviderMessage(bool ok) {
    final message = ok
        ? 'Wishlist updated.'
        : ref.read(wishlistProvider).errorMessage ?? 'Unable to update wishlist.';
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }
}
