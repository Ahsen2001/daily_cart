import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../theme/app_colors.dart';

class ProductImagePicker extends StatelessWidget {
  const ProductImagePicker({
    required this.imagePaths,
    required this.onChanged,
    this.allowMultiple = true,
    super.key,
  });

  final List<String> imagePaths;
  final ValueChanged<List<String>> onChanged;
  final bool allowMultiple;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        OutlinedButton.icon(
          onPressed: () async {
            final picker = ImagePicker();
            if (allowMultiple) {
              final images = await picker.pickMultiImage(imageQuality: 80);
              if (images.isNotEmpty) {
                onChanged([...imagePaths, ...images.map((item) => item.path)]);
              }
            } else {
              final image = await picker.pickImage(
                source: ImageSource.gallery,
                imageQuality: 80,
              );
              if (image != null) {
                onChanged([image.path]);
              }
            }
          },
          icon: const Icon(Icons.add_photo_alternate_outlined),
          label: const Text('Pick product images'),
        ),
        if (imagePaths.isNotEmpty) ...[
          const SizedBox(height: 10),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              for (final path in imagePaths)
                Container(
                  width: 74,
                  height: 74,
                  alignment: Alignment.center,
                  decoration: BoxDecoration(
                    color: AppColors.lightBackground,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: AppColors.border),
                  ),
                  child: const Icon(Icons.image_rounded),
                ),
            ],
          ),
        ],
      ],
    );
  }
}
