import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/vendor_product_provider.dart';
import '../../routes/app_routes.dart';
import '../../theme/app_colors.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/stock_status_badge.dart';

class VendorProductDetailsScreen extends ConsumerStatefulWidget {
  const VendorProductDetailsScreen({
    required this.productId,
    super.key,
  });

  final int productId;

  @override
  ConsumerState<VendorProductDetailsScreen> createState() =>
      _VendorProductDetailsScreenState();
}

class _VendorProductDetailsScreenState
    extends ConsumerState<VendorProductDetailsScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(
      () => ref.read(vendorProductProvider).getProductDetails(widget.productId),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(vendorProductProvider);
    final product = state.selectedProduct;

    return Scaffold(
      appBar: CustomAppBar(
        title: 'Product Details',
        actions: [
          IconButton(
            tooltip: 'Edit',
            onPressed: () => context.push(
              '${AppRoutes.vendorEditProduct}/${widget.productId}',
            ),
            icon: const Icon(Icons.edit_outlined),
          ),
        ],
      ),
      body: state.isLoading && product == null
          ? const LoadingWidget(message: 'Loading product...')
          : product == null
              ? const Center(child: Text('Product not found.'))
              : ListView(
                  padding: const EdgeInsets.all(20),
                  children: [
                    ClipRRect(
                      borderRadius: BorderRadius.circular(24),
                      child: product.image.isEmpty
                          ? Container(
                              height: 210,
                              color: AppColors.lightBackground,
                              child: const Icon(Icons.inventory_2_outlined),
                            )
                          : CachedNetworkImage(
                              imageUrl: product.image,
                              height: 210,
                              fit: BoxFit.cover,
                            ),
                    ),
                    const SizedBox(height: 16),
                    DailyCartCard(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Expanded(
                                child: Text(
                                  product.name,
                                  style: Theme.of(context)
                                      .textTheme
                                      .titleLarge
                                      ?.copyWith(fontWeight: FontWeight.w900),
                                ),
                              ),
                              StockStatusBadge(
                                status: product.status,
                                stockQuantity: product.stockQuantity,
                              ),
                            ],
                          ),
                          const SizedBox(height: 10),
                          _Info('Category', product.categoryName),
                          _Info('Brand', product.brand),
                          _Info('Price', CurrencyFormatter.lkr(product.price)),
                          _Info(
                            'Discount Price',
                            product.discountPrice == null
                                ? '-'
                                : CurrencyFormatter.lkr(product.discountPrice!),
                          ),
                          _Info('SKU', product.sku),
                          _Info('Barcode', product.barcode),
                          _Info('Stock', '${product.stockQuantity}'),
                          _Info(
                            'Expiry',
                            product.expiryDate?.toString().split(' ').first ??
                                '-',
                          ),
                          const SizedBox(height: 10),
                          Text(product.description),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),
                    Wrap(
                      spacing: 10,
                      runSpacing: 10,
                      children: [
                        OutlinedButton.icon(
                          onPressed: () => context.push(
                            '${AppRoutes.vendorProductImages}/${product.id}',
                          ),
                          icon: const Icon(Icons.image_outlined),
                          label: const Text('Images'),
                        ),
                        OutlinedButton.icon(
                          onPressed: () => context.push(
                            '${AppRoutes.vendorProductVariants}/${product.id}',
                          ),
                          icon: const Icon(Icons.tune_rounded),
                          label: const Text('Variants'),
                        ),
                        OutlinedButton.icon(
                          onPressed: () => context.push(
                            '${AppRoutes.vendorInventory}/${product.id}',
                          ),
                          icon: const Icon(Icons.inventory_outlined),
                          label: const Text('Inventory'),
                        ),
                      ],
                    ),
                  ],
                ),
    );
  }
}

class _Info extends StatelessWidget {
  const _Info(this.label, this.value);

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          SizedBox(
            width: 110,
            child: Text(
              label,
              style: const TextStyle(color: AppColors.mutedText),
            ),
          ),
          Expanded(child: Text(value.isEmpty ? '-' : value)),
        ],
      ),
    );
  }
}
