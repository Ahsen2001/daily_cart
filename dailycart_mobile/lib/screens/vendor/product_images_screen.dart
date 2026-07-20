import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
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
      appBar: const CustomAppBar(title: 'Product Images'),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          if (product != null && product.gallery.isNotEmpty) ...[
            Text(
              'Current images',
              style: Theme.of(context)
                  .textTheme
                  .titleMedium
                  ?.copyWith(fontWeight: FontWeight.w900),
            ),
            const SizedBox(height: 10),
            Wrap(
              spacing: 10,
              runSpacing: 10,
              children: [
                for (final image in product.gallery)
                  Stack(
                    children: [
                      ClipRRect(
                        borderRadius: BorderRadius.circular(14),
                        child: CachedNetworkImage(
                          imageUrl: image.url,
                          width: 96,
                          height: 96,
                          fit: BoxFit.cover,
                        ),
                      ),
                      Positioned(
                        right: 0,
                        child: IconButton.filled(
                          onPressed: () => ref
                              .read(vendorProductProvider)
                              .deleteImage(widget.productId, image.id),
                          icon: const Icon(Icons.close, size: 18),
                        ),
                      ),
                    ],
                  ),
              ],
            ),
            const SizedBox(height: 18),
          ],
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
    if (ok) {
      setState(() => _imagePaths = const []);
    }
  }
}
