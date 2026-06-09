import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../models/vendor_product_model.dart';
import '../../providers/vendor_product_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/product_image_picker.dart';

class AddProductScreen extends ConsumerStatefulWidget {
  const AddProductScreen({super.key});

  @override
  ConsumerState<AddProductScreen> createState() => _AddProductScreenState();
}

class _AddProductScreenState extends ConsumerState<AddProductScreen> {
  final _formKey = GlobalKey<FormState>();
  final _categoryController = TextEditingController();
  final _nameController = TextEditingController();
  final _brandController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _priceController = TextEditingController();
  final _discountController = TextEditingController();
  final _unitController = TextEditingController();
  final _weightController = TextEditingController();
  final _skuController = TextEditingController();
  final _barcodeController = TextEditingController();
  final _stockController = TextEditingController();
  final _expiryController = TextEditingController();
  List<String> _imagePaths = const [];

  @override
  void dispose() {
    for (final controller in [
      _categoryController,
      _nameController,
      _brandController,
      _descriptionController,
      _priceController,
      _discountController,
      _unitController,
      _weightController,
      _skuController,
      _barcodeController,
      _stockController,
      _expiryController,
    ]) {
      controller.dispose();
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(vendorProductProvider);

    return Scaffold(
      appBar: const CustomAppBar(title: 'Add Product'),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          DailyCartCard(
            child: Form(
              key: _formKey,
              child: _ProductFormFields(
                categoryController: _categoryController,
                nameController: _nameController,
                brandController: _brandController,
                descriptionController: _descriptionController,
                priceController: _priceController,
                discountController: _discountController,
                unitController: _unitController,
                weightController: _weightController,
                skuController: _skuController,
                barcodeController: _barcodeController,
                stockController: _stockController,
                expiryController: _expiryController,
              ),
            ),
          ),
          const SizedBox(height: 16),
          DailyCartCard(
            child: ProductImagePicker(
              imagePaths: _imagePaths,
              onChanged: (paths) => setState(() => _imagePaths = paths),
            ),
          ),
          const SizedBox(height: 20),
          CustomButton(
            label: 'Submit for Approval',
            isLoading: state.isLoading,
            onPressed: _save,
          ),
        ],
      ),
    );
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }
    final product = _productFromFields(id: 0);
    final ok = await ref.read(vendorProductProvider).addProduct(product);
    final created = ref.read(vendorProductProvider).selectedProduct;
    if (ok && created != null && _imagePaths.isNotEmpty) {
      await ref.read(vendorProductProvider).uploadProductImages(
            productId: created.id,
            imagePaths: _imagePaths,
          );
    }
    if (!mounted) {
      return;
    }
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          ok
              ? 'Product submitted for admin approval.'
              : ref.read(vendorProductProvider).errorMessage ??
                  'Unable to add product.',
        ),
      ),
    );
    if (ok) {
      context.pop();
    }
  }

  VendorProductModel _productFromFields({required int id}) {
    return VendorProductModel(
      id: id,
      categoryId: int.tryParse(_categoryController.text.trim()) ?? 0,
      name: _nameController.text.trim(),
      brand: _brandController.text.trim(),
      description: _descriptionController.text.trim(),
      price: double.tryParse(_priceController.text.trim()) ?? 0,
      discountPrice: double.tryParse(_discountController.text.trim()),
      unitType: _unitController.text.trim(),
      weight: _weightController.text.trim(),
      sku: _skuController.text.trim(),
      barcode: _barcodeController.text.trim(),
      stockQuantity: int.tryParse(_stockController.text.trim()) ?? 0,
      expiryDate: DateTime.tryParse(_expiryController.text.trim()),
    );
  }
}

class _ProductFormFields extends StatelessWidget {
  const _ProductFormFields({
    required this.categoryController,
    required this.nameController,
    required this.brandController,
    required this.descriptionController,
    required this.priceController,
    required this.discountController,
    required this.unitController,
    required this.weightController,
    required this.skuController,
    required this.barcodeController,
    required this.stockController,
    required this.expiryController,
  });

  final TextEditingController categoryController;
  final TextEditingController nameController;
  final TextEditingController brandController;
  final TextEditingController descriptionController;
  final TextEditingController priceController;
  final TextEditingController discountController;
  final TextEditingController unitController;
  final TextEditingController weightController;
  final TextEditingController skuController;
  final TextEditingController barcodeController;
  final TextEditingController stockController;
  final TextEditingController expiryController;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        CustomTextField(
          label: 'Category ID',
          controller: categoryController,
          icon: Icons.category_outlined,
          keyboardType: TextInputType.number,
          validator: _required,
        ),
        const SizedBox(height: 12),
        CustomTextField(
          label: 'Product Name',
          controller: nameController,
          icon: Icons.inventory_2_outlined,
          validator: _required,
        ),
        const SizedBox(height: 12),
        CustomTextField(
          label: 'Brand',
          controller: brandController,
          icon: Icons.sell_outlined,
        ),
        const SizedBox(height: 12),
        TextFormField(
          controller: descriptionController,
          maxLines: 4,
          decoration: const InputDecoration(
            labelText: 'Description',
            alignLabelWithHint: true,
            prefixIcon: Icon(Icons.description_outlined),
          ),
        ),
        const SizedBox(height: 12),
        CustomTextField(
          label: 'Price',
          controller: priceController,
          icon: Icons.payments_outlined,
          keyboardType: TextInputType.number,
          validator: _required,
        ),
        const SizedBox(height: 12),
        CustomTextField(
          label: 'Discount Price',
          controller: discountController,
          icon: Icons.local_offer_outlined,
          keyboardType: TextInputType.number,
        ),
        const SizedBox(height: 12),
        CustomTextField(
          label: 'Unit Type',
          controller: unitController,
          icon: Icons.straighten_outlined,
        ),
        const SizedBox(height: 12),
        CustomTextField(
          label: 'Weight',
          controller: weightController,
          icon: Icons.scale_outlined,
        ),
        const SizedBox(height: 12),
        CustomTextField(
          label: 'SKU',
          controller: skuController,
          icon: Icons.qr_code_2_rounded,
        ),
        const SizedBox(height: 12),
        CustomTextField(
          label: 'Barcode',
          controller: barcodeController,
          icon: Icons.document_scanner_outlined,
        ),
        const SizedBox(height: 12),
        CustomTextField(
          label: 'Stock Quantity',
          controller: stockController,
          icon: Icons.inventory_outlined,
          keyboardType: TextInputType.number,
          validator: _required,
        ),
        const SizedBox(height: 12),
        CustomTextField(
          label: 'Expiry Date YYYY-MM-DD',
          controller: expiryController,
          icon: Icons.event_outlined,
        ),
      ],
    );
  }

  static String? _required(String? value) {
    return value == null || value.trim().isEmpty ? 'This field is required.' : null;
  }
}
