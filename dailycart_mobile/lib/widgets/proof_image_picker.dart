import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../theme/app_colors.dart';

class ProofImagePicker extends StatelessWidget {
  const ProofImagePicker({
    required this.imagePath,
    required this.onChanged,
    super.key,
  });

  final String? imagePath;
  final ValueChanged<String> onChanged;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        OutlinedButton.icon(
          onPressed: () async {
            final image = await ImagePicker().pickImage(
              source: ImageSource.camera,
              imageQuality: 80,
              maxWidth: 1200,
            );
            if (image != null) {
              onChanged(image.path);
            }
          },
          icon: const Icon(Icons.camera_alt_outlined),
          label: const Text('Capture delivery proof'),
        ),
        if (imagePath != null && imagePath!.isNotEmpty) ...[
          const SizedBox(height: 10),
          Container(
            width: double.infinity,
            height: 120,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              color: AppColors.lightBackground,
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: AppColors.border),
            ),
            child: const Icon(Icons.image_rounded, size: 42),
          ),
        ],
      ],
    );
  }
}
