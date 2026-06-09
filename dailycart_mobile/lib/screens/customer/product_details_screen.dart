import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../models/product_model.dart';
import '../../providers/product_provider.dart';
import '../../routes/app_routes.dart';
import '../../theme/app_colors.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/empty_products_widget.dart';
import '../../widgets/product_card.dart';
import '../../widgets/product_image_slider.dart';
import '../../widgets/rating_widget.dart';

class ProductDetailsScreen extends ConsumerStatefulWidget {
  const ProductDetailsScreen({
    required this.productId,
    super.key,
  });

  final int productId;

  @override
  ConsumerState<ProductDetailsScreen> createState() =>
      _ProductDetailsScreenState();
}

class _ProductDetailsScreenState extends ConsumerState<ProductDetailsScreen> {
  int _quantity = 1;
  ProductVariantModel? _selectedVariant;

  @override
  void initState() {
    super.initState();
    Future.microtask(() async {
      await ref.read(productProvider).getProductDetails(widget.productId);
      final product = ref.read(productProvider).selectedProduct;
      if (product != null) {
        ref.read(productProvider).addRecentlyViewed(product);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(productProvider);
    final product = state.selectedProduct;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Product Details'),
        actions: [
          IconButton(
            tooltip: 'Share Product',
            onPressed: () => _showPlaceholder('Share product placeholder'),
            icon: const Icon(Icons.share_outlined),
          ),
        ],
      ),
      body: state.isLoading
          ? const Center(child: CircularProgressIndicator())
          : product == null
              ? const EmptyProductsWidget()
              : ListView(
                  padding: const EdgeInsets.all(20),
                  children: [
                    ProductImageSlider(imageUrls: product.imageUrls),
                    const SizedBox(height: 22),
                    _ProductInfo(product: product),
                    const SizedBox(height: 16),
                    _StockAndVendor(product: product),
                    const SizedBox(height: 16),
                    _QuantitySelector(
                      quantity: _quantity,
                      onChanged: (value) => setState(() => _quantity = value),
                    ),
                    if (product.variants.isNotEmpty) ...[
                      const SizedBox(height: 16),
                      _VariantSelector(
                        variants: product.variants,
                        selectedVariant: _selectedVariant,
                        onChanged: (variant) {
                          setState(() => _selectedVariant = variant);
                        },
                      ),
                    ],
                    const SizedBox(height: 20),
                    Row(
                      children: [
                        Expanded(
                          child: CustomButton(
                            label: 'Add to Cart',
                            icon: Icons.add_shopping_cart_rounded,
                            onPressed: () => _showPlaceholder(
                              '${product.name} added to cart placeholder',
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: CustomButton(
                            label: 'Buy Now',
                            icon: Icons.flash_on_rounded,
                            onPressed: () => _showPlaceholder(
                              'Buy Now placeholder',
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),
                    CustomButton(
                      label: 'Add to Wishlist',
                      icon: Icons.favorite_border_rounded,
                      variant: CustomButtonVariant.secondary,
                      onPressed: () => _showPlaceholder(
                        '${product.name} wishlist placeholder',
                      ),
                    ),
                    const SizedBox(height: 24),
                    _ReviewsSection(reviews: product.reviews),
                    const SizedBox(height: 24),
                    _SimilarProducts(products: product.similarProducts),
                  ],
                ),
    );
  }

  void _showPlaceholder(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }
}

class _ProductInfo extends StatelessWidget {
  const _ProductInfo({required this.product});

  final ProductModel product;

  @override
  Widget build(BuildContext context) {
    return DailyCartCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            product.name,
            style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                  fontWeight: FontWeight.w900,
                ),
          ),
          const SizedBox(height: 8),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              _InfoChip(label: product.brand.isEmpty ? 'DailyCart' : product.brand),
              _InfoChip(
                label: product.categoryName.isEmpty
                    ? 'Grocery'
                    : product.categoryName,
              ),
              RatingWidget(rating: product.rating),
            ],
          ),
          const SizedBox(height: 14),
          Wrap(
            spacing: 10,
            crossAxisAlignment: WrapCrossAlignment.center,
            children: [
              Text(
                CurrencyFormatter.lkr(product.displayPrice),
                style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      color: AppColors.darkGreen,
                      fontWeight: FontWeight.w900,
                    ),
              ),
              if (product.hasDiscount)
                Text(
                  CurrencyFormatter.lkr(product.price),
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                        color: AppColors.mutedText,
                        decoration: TextDecoration.lineThrough,
                      ),
                ),
            ],
          ),
          const SizedBox(height: 14),
          Text(
            product.description.isEmpty
                ? 'Fresh product from a verified DailyCart vendor.'
                : product.description,
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  color: AppColors.mutedText,
                  height: 1.5,
                ),
          ),
        ],
      ),
    );
  }
}

class _InfoChip extends StatelessWidget {
  const _InfoChip({required this.label});

  final String label;

  @override
  Widget build(BuildContext context) {
    return Chip(
      label: Text(label),
      backgroundColor: AppColors.lightBackground,
      side: const BorderSide(color: AppColors.border),
    );
  }
}

class _StockAndVendor extends StatelessWidget {
  const _StockAndVendor({required this.product});

  final ProductModel product;

  @override
  Widget build(BuildContext context) {
    return DailyCartCard(
      child: Column(
        children: [
          Row(
            children: [
              const Icon(Icons.inventory_2_outlined, color: AppColors.darkGreen),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  product.isAvailable
                      ? 'In stock: ${product.stockQuantity}'
                      : 'Out of stock',
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              const Icon(Icons.storefront_outlined, color: AppColors.darkGreen),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  product.vendorName.isEmpty
                      ? 'Verified DailyCart vendor'
                      : product.vendorName,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _QuantitySelector extends StatelessWidget {
  const _QuantitySelector({
    required this.quantity,
    required this.onChanged,
  });

  final int quantity;
  final ValueChanged<int> onChanged;

  @override
  Widget build(BuildContext context) {
    return DailyCartCard(
      child: Row(
        children: [
          Text(
            'Quantity',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.w800,
                ),
          ),
          const Spacer(),
          IconButton.filledTonal(
            onPressed: quantity == 1 ? null : () => onChanged(quantity - 1),
            icon: const Icon(Icons.remove_rounded),
          ),
          SizedBox(
            width: 42,
            child: Text(
              '$quantity',
              textAlign: TextAlign.center,
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.w900,
                  ),
            ),
          ),
          IconButton.filled(
            onPressed: () => onChanged(quantity + 1),
            icon: const Icon(Icons.add_rounded),
          ),
        ],
      ),
    );
  }
}

class _VariantSelector extends StatelessWidget {
  const _VariantSelector({
    required this.variants,
    required this.selectedVariant,
    required this.onChanged,
  });

  final List<ProductVariantModel> variants;
  final ProductVariantModel? selectedVariant;
  final ValueChanged<ProductVariantModel> onChanged;

  @override
  Widget build(BuildContext context) {
    return DailyCartCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Product Variants',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.w800,
                ),
          ),
          const SizedBox(height: 12),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              for (final variant in variants)
                ChoiceChip(
                  label: Text(
                    variant.value.isEmpty ? variant.name : variant.value,
                  ),
                  selected: selectedVariant?.id == variant.id,
                  onSelected: (_) => onChanged(variant),
                ),
            ],
          ),
        ],
      ),
    );
  }
}

class _ReviewsSection extends StatelessWidget {
  const _ReviewsSection({required this.reviews});

  final List<ReviewModel> reviews;

  @override
  Widget build(BuildContext context) {
    return DailyCartCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Customer Reviews',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.w900,
                ),
          ),
          const SizedBox(height: 12),
          if (reviews.isEmpty)
            Text(
              'No reviews yet.',
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                    color: AppColors.mutedText,
                  ),
            )
          else
            for (final review in reviews.take(3)) ...[
              Row(
                children: [
                  Expanded(child: Text(review.userName)),
                  RatingWidget(rating: review.rating),
                ],
              ),
              const SizedBox(height: 6),
              Text(review.comment),
              const Divider(color: AppColors.border),
            ],
        ],
      ),
    );
  }
}

class _SimilarProducts extends StatelessWidget {
  const _SimilarProducts({required this.products});

  final List<ProductModel> products;

  @override
  Widget build(BuildContext context) {
    if (products.isEmpty) {
      return const SizedBox.shrink();
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Similar Products',
          style: Theme.of(context).textTheme.titleMedium?.copyWith(
                fontWeight: FontWeight.w900,
              ),
        ),
        const SizedBox(height: 12),
        SizedBox(
          height: 316,
          child: ListView.separated(
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
                  onAddToCart: () {},
                  onWishlist: () {},
                ),
              );
            },
            separatorBuilder: (context, index) => const SizedBox(width: 12),
            itemCount: products.length,
          ),
        ),
      ],
    );
  }
}
