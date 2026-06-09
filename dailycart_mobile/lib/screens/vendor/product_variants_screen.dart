import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../providers/vendor_product_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/loading_widget.dart';

class ProductVariantsScreen extends ConsumerStatefulWidget {
  const ProductVariantsScreen({
    required this.productId,
    super.key,
  });

  final int productId;

  @override
  ConsumerState<ProductVariantsScreen> createState() =>
      _ProductVariantsScreenState();
}

class _ProductVariantsScreenState extends ConsumerState<ProductVariantsScreen> {
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
      appBar: const CustomAppBar(title: 'Product Variants'),
      body: state.isLoading && product == null
          ? const LoadingWidget(message: 'Loading variants...')
          : ListView(
              padding: const EdgeInsets.all(20),
              children: [
                if (product == null || product.variants.isEmpty)
                  const DailyCartCard(
                    child: Text('No variants yet. Variant creation can be connected to the Laravel endpoint when available.'),
                  )
                else
                  for (final variant in product.variants)
                    Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: DailyCartCard(
                        child: ListTile(
                          contentPadding: EdgeInsets.zero,
                          title: Text('${variant.name}: ${variant.value}'),
                          subtitle: Text('Stock: ${variant.stockQuantity}'),
                          trailing: Text(
                            variant.price == null
                                ? '-'
                                : variant.price!.toStringAsFixed(2),
                          ),
                        ),
                      ),
                    ),
              ],
            ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Add variant placeholder.')),
          );
        },
        icon: const Icon(Icons.add_rounded),
        label: const Text('Variant'),
      ),
    );
  }
}
