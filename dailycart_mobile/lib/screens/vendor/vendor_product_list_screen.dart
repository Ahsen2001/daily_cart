import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/vendor_product_provider.dart';
import '../../routes/app_routes.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state_widget.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/vendor_product_card.dart';
import '../../widgets/vendor_bottom_navigation.dart';
import '../../widgets/error_widget.dart';

class VendorProductListScreen extends ConsumerStatefulWidget {
  const VendorProductListScreen({super.key});

  @override
  ConsumerState<VendorProductListScreen> createState() =>
      _VendorProductListScreenState();
}

class _VendorProductListScreenState
    extends ConsumerState<VendorProductListScreen> {
  String _status = 'all';

  @override
  void initState() {
    super.initState();
    Future.microtask(_load);
  }

  Future<void> _load() {
    return ref.read(vendorProductProvider).getVendorProducts(status: _status);
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(vendorProductProvider);

    return Scaffold(
      appBar: CustomAppBar(
        title: 'Vendor Products',
        actions: [
          IconButton(
            tooltip: 'Add product',
            onPressed: () => context.push(AppRoutes.vendorAddProduct),
            icon: const Icon(Icons.add_rounded),
          ),
        ],
      ),
      body: Column(
        children: [
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.fromLTRB(20, 12, 20, 8),
            child: SegmentedButton<String>(
              segments: const [
                ButtonSegment(value: 'all', label: Text('All')),
                ButtonSegment(value: 'pending', label: Text('Pending')),
                ButtonSegment(value: 'approved', label: Text('Approved')),
                ButtonSegment(value: 'rejected', label: Text('Rejected')),
                ButtonSegment(value: 'inactive', label: Text('Inactive')),
              ],
              selected: {_status},
              onSelectionChanged: (value) {
                setState(() => _status = value.first);
                _load();
              },
            ),
          ),
          Expanded(
            child: state.isLoading && state.products.isEmpty
                ? const LoadingWidget(message: 'Loading products...')
                : state.errorMessage != null && state.products.isEmpty
                ? DailyCartErrorWidget(
                    title: 'Products unavailable',
                    message: state.errorMessage!,
                    onRetry: _load,
                  )
                : state.products.isEmpty
                ? const EmptyStateWidget(
                    title: 'No products',
                    message: 'Add products for admin approval.',
                    icon: Icons.inventory_2_outlined,
                  )
                : RefreshIndicator(
                    onRefresh: _load,
                    child: ListView.separated(
                      padding: const EdgeInsets.all(20),
                      itemBuilder: (context, index) {
                        final product = state.products[index];
                        return VendorProductCard(
                          product: product,
                          onTap: () => context.push(
                            '${AppRoutes.vendorProductDetails}/${product.id}',
                          ),
                          onEdit: () => context.push(
                            '${AppRoutes.vendorEditProduct}/${product.id}',
                          ),
                          onDelete: () =>
                              _deleteProduct(product.id, product.name),
                        );
                      },
                      separatorBuilder: (context, index) =>
                          const SizedBox(height: 14),
                      itemCount: state.products.length,
                    ),
                  ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push(AppRoutes.vendorAddProduct),
        icon: const Icon(Icons.add_rounded),
        label: const Text('Product'),
      ),
      bottomNavigationBar: const VendorBottomNavigation(currentIndex: 1),
    );
  }

  Future<void> _deleteProduct(int id, String name) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete product?'),
        content: Text('Delete "$name"? This action cannot be undone.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Delete'),
          ),
        ],
      ),
    );
    if (confirmed != true) return;
    final ok = await ref.read(vendorProductProvider).deleteProduct(id);
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          ok
              ? 'Product deleted.'
              : ref.read(vendorProductProvider).errorMessage ??
                    'Unable to delete product.',
        ),
      ),
    );
  }
}
