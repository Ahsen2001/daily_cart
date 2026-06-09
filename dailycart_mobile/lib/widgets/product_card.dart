import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';

import '../models/product_model.dart';
import '../theme/app_colors.dart';
import '../utils/currency_formatter.dart';
import 'rating_widget.dart';

class ProductCard extends StatelessWidget {
  const ProductCard({
    required this.product,
    required this.onTap,
    required this.onAddToCart,
    required this.onWishlist,
    super.key,
  });

  final ProductModel product;
  final VoidCallback onTap;
  final VoidCallback onAddToCart;
  final VoidCallback onWishlist;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(22),
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: BorderRadius.circular(22),
          boxShadow: const [
            BoxShadow(
              color: AppColors.shadow,
              blurRadius: 22,
              offset: Offset(0, 12),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Stack(
                children: [
                  Positioned.fill(
                    child: _ProductImage(imageUrl: product.image),
                  ),
                  Positioned(
                    top: 10,
                    right: 10,
                    child: _RoundIconButton(
                      icon: Icons.favorite_border_rounded,
                      onTap: onWishlist,
                    ),
                  ),
                  if (product.hasDiscount)
                    Positioned(
                      top: 10,
                      left: 10,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color: AppColors.accentOrange,
                          borderRadius: BorderRadius.circular(999),
                        ),
                        child: Text(
                          'Deal',
                          style:
                              Theme.of(context).textTheme.labelSmall?.copyWith(
                                    color: AppColors.white,
                                    fontWeight: FontWeight.w800,
                                  ),
                        ),
                      ),
                    ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(12, 10, 12, 12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    product.name,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: Theme.of(context).textTheme.titleSmall?.copyWith(
                          fontWeight: FontWeight.w800,
                        ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    product.vendorName,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: AppColors.mutedText,
                        ),
                  ),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      Expanded(child: _PriceBlock(product: product)),
                      RatingWidget(rating: product.rating),
                    ],
                  ),
                  const SizedBox(height: 10),
                  SizedBox(
                    width: double.infinity,
                    child: FilledButton.icon(
                      onPressed: onAddToCart,
                      icon: const Icon(Icons.add_shopping_cart_rounded, size: 18),
                      label: const Text('Add'),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class ProductGridCard extends ProductCard {
  const ProductGridCard({
    required super.product,
    required super.onTap,
    required super.onAddToCart,
    required super.onWishlist,
    super.key,
  });
}

class ProductListCard extends StatelessWidget {
  const ProductListCard({
    required this.product,
    required this.onTap,
    required this.onAddToCart,
    required this.onWishlist,
    super.key,
  });

  final ProductModel product;
  final VoidCallback onTap;
  final VoidCallback onAddToCart;
  final VoidCallback onWishlist;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(22),
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(12),
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
        child: Row(
          children: [
            SizedBox(
              width: 96,
              height: 96,
              child: _ProductImage(imageUrl: product.image),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    product.name,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: Theme.of(context).textTheme.titleSmall?.copyWith(
                          fontWeight: FontWeight.w800,
                        ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    product.vendorName,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: AppColors.mutedText,
                        ),
                  ),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      Expanded(child: _PriceBlock(product: product)),
                      RatingWidget(rating: product.rating),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Expanded(
                        child: FilledButton.icon(
                          onPressed: onAddToCart,
                          icon: const Icon(
                            Icons.add_shopping_cart_rounded,
                            size: 18,
                          ),
                          label: const Text('Add to Cart'),
                        ),
                      ),
                      const SizedBox(width: 8),
                      _RoundIconButton(
                        icon: Icons.favorite_border_rounded,
                        onTap: onWishlist,
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ProductImage extends StatelessWidget {
  const _ProductImage({required this.imageUrl});

  final String imageUrl;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.lightBackground,
        borderRadius: BorderRadius.circular(20),
      ),
      child: imageUrl.isEmpty
          ? const Icon(
              Icons.shopping_basket_rounded,
              color: AppColors.primaryGreen,
              size: 42,
            )
          : ClipRRect(
              borderRadius: BorderRadius.circular(20),
              child: CachedNetworkImage(
                imageUrl: imageUrl,
                fit: BoxFit.cover,
              ),
            ),
    );
  }
}

class _PriceBlock extends StatelessWidget {
  const _PriceBlock({required this.product});

  final ProductModel product;

  @override
  Widget build(BuildContext context) {
    return Wrap(
      crossAxisAlignment: WrapCrossAlignment.center,
      spacing: 6,
      runSpacing: 2,
      children: [
        Text(
          CurrencyFormatter.lkr(product.displayPrice),
          style: Theme.of(context).textTheme.titleSmall?.copyWith(
                color: AppColors.darkGreen,
                fontWeight: FontWeight.w900,
              ),
        ),
        if (product.hasDiscount)
          Text(
            CurrencyFormatter.lkr(product.price),
            style: Theme.of(context).textTheme.bodySmall?.copyWith(
                  color: AppColors.mutedText,
                  decoration: TextDecoration.lineThrough,
                ),
          ),
      ],
    );
  }
}

class _RoundIconButton extends StatelessWidget {
  const _RoundIconButton({
    required this.icon,
    required this.onTap,
  });

  final IconData icon;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: AppColors.white,
      shape: const CircleBorder(),
      child: InkWell(
        customBorder: const CircleBorder(),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(8),
          child: Icon(icon, size: 20, color: AppColors.darkGreen),
        ),
      ),
    );
  }
}
