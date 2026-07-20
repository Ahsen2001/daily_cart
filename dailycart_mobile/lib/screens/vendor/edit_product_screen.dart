import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../models/vendor_product_model.dart';
import '../../providers/vendor_product_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/loading_widget.dart';

class EditProductScreen extends ConsumerStatefulWidget {
  const EditProductScreen({
    required this.productId,
    super.key,
  });

  final int productId;

  @override
  ConsumerState<EditProductScreen> createState() => _EditProductScreenState();
}

class _EditProductScreenState extends ConsumerState<EditProductScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _priceController = TextEditingController();
  final _stockController = TextEditingController();
  final _descriptionController = TextEditingController();
  bool _filled = false;
  bool _isSubscriptionEligible = false;

  @override
  void initState() {
    super.initState();
    Future.microtask(
      () => ref.read(vendorProductProvider).getProductDetails(widget.productId),
    );
  }

  @override
  void dispose() {
    _nameController.dispose();
    _priceController.dispose();
    _stockController.dispose();
    _descriptionController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(vendorProductProvider);
    final product = state.selectedProduct;
    if (product != null && !_filled) {
      _filled = true;
      _nameController.text = product.name;
      _priceController.text = product.price.toStringAsFixed(2);
      _stockController.text = '${product.stockQuantity}';
      _descriptionController.text = product.description;
      _isSubscriptionEligible = product.isSubscriptionEligible;
    }

    return Scaffold(
      appBar: const CustomAppBar(title: 'Edit Product'),
      body: state.isLoading && product == null
          ? const LoadingWidget(message: 'Loading product...')
          : product == null
              ? const Center(child: Text('Product not found.'))
              : ListView(
                  padding: const EdgeInsets.all(20),
                  children: [
                    DailyCartCard(
                      child: Form(
                        key: _formKey,
                        child: Column(
                          children: [
                            CustomTextField(
                              label: 'Product Name',
                              controller: _nameController,
                              icon: Icons.inventory_2_outlined,
                              validator: _required,
                            ),
                            const SizedBox(height: 12),
                            CustomTextField(
                              label: 'Price',
                              controller: _priceController,
                              icon: Icons.payments_outlined,
                              keyboardType: TextInputType.number,
                              validator: _required,
                            ),
                            const SizedBox(height: 12),
                            CustomTextField(
                              label: 'Stock Quantity',
                              controller: _stockController,
                              icon: Icons.inventory_outlined,
                              keyboardType: TextInputType.number,
                              validator: _required,
                            ),
                            const SizedBox(height: 12),
                            TextFormField(
                              controller: _descriptionController,
                              maxLines: 4,
                              decoration: const InputDecoration(
                                labelText: 'Description',
                                alignLabelWithHint: true,
                                prefixIcon: Icon(Icons.description_outlined),
                              ),
                            ),
                            SwitchListTile(
                              contentPadding: EdgeInsets.zero,
                              title: const Text('Allow subscriptions'),
                              value: _isSubscriptionEligible,
                              onChanged: (value) => setState(
                                () => _isSubscriptionEligible = value,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 20),
                    CustomButton(
                      label: 'Save Changes',
                      isLoading: state.isLoading,
                      onPressed: () => _save(product),
                    ),
                  ],
                ),
    );
  }

  Future<void> _save(VendorProductModel product) async {
    if (!_formKey.currentState!.validate()) {
      return;
    }
    final updated = VendorProductModel(
      id: product.id,
      categoryId: product.categoryId,
      categoryName: product.categoryName,
      name: _nameController.text.trim(),
      brand: product.brand,
      description: _descriptionController.text.trim(),
      price: double.tryParse(_priceController.text.trim()) ?? product.price,
      discountPrice: product.discountPrice,
      unitType: product.unitType,
      weight: product.weight,
      sku: product.sku,
      barcode: product.barcode,
      stockQuantity:
          int.tryParse(_stockController.text.trim()) ?? product.stockQuantity,
      expiryDate: product.expiryDate,
      status: product.status,
      image: product.image,
      images: product.images,
      variants: product.variants,
      gallery: product.gallery,
      isSubscriptionEligible: _isSubscriptionEligible,
      lowStockThreshold: product.lowStockThreshold,
      rejectionReason: product.rejectionReason,
    );
    final ok = await ref.read(vendorProductProvider).updateProduct(updated);
    if (!mounted) {
      return;
    }
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(ok ? 'Product updated.' : 'Unable to update product.')),
    );
    if (ok) {
      context.pop();
    }
  }

  static String? _required(String? value) {
    return value == null || value.trim().isEmpty ? 'This field is required.' : null;
  }
}
