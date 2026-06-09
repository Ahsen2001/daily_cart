import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../providers/vendor_product_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/product_image_picker.dart';

class ProductImagesScreen extends ConsumerStatefulWidget {
  const ProductImagesScreen({
    required this.productId,
    super.key,
  });

  final int productId;

  @override
  ConsumerState<ProductImagesScreen> createState() => _ProductImagesScreenState();
}

class _ProductImagesScreenState extends ConsumerState<ProductImagesScreen> {
  List<String> _imagePaths = const [];

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(vendorProductProvider);
    return Scaffold(
      appBar: const CustomAppBar(title: 'Product Images'),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          DailyCartCard(
            child: ProductImagePicker(
              imagePaths: _imagePaths,
              onChanged: (paths) => setState(() => _imagePaths = paths),
            ),
          ),
          const SizedBox(height: 20),
          CustomButton(
            label: 'Upload Images',
            isLoading: state.isLoading,
            onPressed: _imagePaths.isEmpty ? null : _upload,
          ),
        ],
      ),
    );
  }

  Future<void> _upload() async {
    final ok = await ref.read(vendorProductProvider).uploadProductImages(
          productId: widget.productId,
          imagePaths: _imagePaths,
        );
    if (!mounted) {
      return;
    }
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(ok ? 'Images uploaded.' : 'Unable to upload images.')),
    );
  }
}
