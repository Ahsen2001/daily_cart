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
                          onLongPress: () => _deleteVariant(variant.id),
                        ),
                      ),
                    ),
              ],
            ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _addVariant,
        icon: const Icon(Icons.add_rounded),
        label: const Text('Variant'),
      ),
    );
  }

  Future<void> _addVariant() async {
    final name = TextEditingController();
    final price = TextEditingController();
    final stock = TextEditingController(text: '0');
    final submit = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Add variant'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: name,
              decoration: const InputDecoration(labelText: 'Name (e.g. 1kg)'),
            ),
            TextField(
              controller: price,
              keyboardType:
                  const TextInputType.numberWithOptions(decimal: true),
              decoration: const InputDecoration(labelText: 'Price'),
            ),
            TextField(
              controller: stock,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(labelText: 'Stock'),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Add'),
          ),
        ],
      ),
    );
    final variantName = name.text.trim();
    final variantPrice = double.tryParse(price.text);
    final quantity = int.tryParse(stock.text) ?? 0;
    name.dispose();
    price.dispose();
    stock.dispose();
    if (submit != true || variantName.isEmpty || variantPrice == null) return;
    final ok = await ref.read(vendorProductProvider).addVariant(
          productId: widget.productId,
          name: variantName,
          price: variantPrice,
          stockQuantity: quantity,
        );
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(ok ? 'Variant added.' : 'Unable to add variant.')),
      );
    }
  }

  Future<void> _deleteVariant(int variantId) async {
    final ok = await ref
        .read(vendorProductProvider)
        .deleteVariant(widget.productId, variantId);
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(ok ? 'Variant deleted.' : 'Unable to delete variant.'),
        ),
      );
    }
  }
}
