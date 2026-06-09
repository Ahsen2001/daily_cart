import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';

import '../models/wishlist_model.dart';
import '../theme/app_colors.dart';
import '../utils/currency_formatter.dart';
import 'custom_button.dart';
import 'dailycart_card.dart';

class WishlistItemCard extends StatelessWidget {
  const WishlistItemCard({
    required this.item,
    required this.onMoveToCart,
    required this.onRemove,
    super.key,
  });

  final WishlistModel item;
  final VoidCallback onMoveToCart;
  final VoidCallback onRemove;

  @override
  Widget build(BuildContext context) {
    final product = item.product;

    return DailyCartCard(
      padding: const EdgeInsets.all(12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 88,
            height: 88,
            decoration: BoxDecoration(
              color: AppColors.lightBackground,
              borderRadius: BorderRadius.circular(18),
            ),
            child: product.image.isEmpty
                ? const Icon(
                    Icons.favorite_border_rounded,
                    color: AppColors.primaryGreen,
                  )
                : ClipRRect(
                    borderRadius: BorderRadius.circular(18),
                    child: CachedNetworkImage(
                      imageUrl: product.image,
                      fit: BoxFit.cover,
                    ),
                  ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(
                      child: Text(
                        product.name,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: Theme.of(context).textTheme.titleSmall?.copyWith(
                              fontWeight: FontWeight.w900,
                            ),
                      ),
                    ),
                    IconButton(
                      tooltip: 'Remove',
                      onPressed: onRemove,
                      icon: const Icon(Icons.close_rounded),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Text(
                  CurrencyFormatter.lkr(product.displayPrice),
                  style: Theme.of(context).textTheme.titleSmall?.copyWith(
                        color: AppColors.darkGreen,
                        fontWeight: FontWeight.w900,
                      ),
                ),
                const SizedBox(height: 10),
                CustomButton(
                  label: 'Move to Cart',
                  icon: Icons.shopping_cart_checkout_rounded,
                  onPressed: product.isAvailable ? onMoveToCart : null,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
