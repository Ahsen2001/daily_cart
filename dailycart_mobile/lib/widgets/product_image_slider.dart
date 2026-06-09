import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';

import '../theme/app_colors.dart';

class ProductImageSlider extends StatefulWidget {
  const ProductImageSlider({
    required this.imageUrls,
    super.key,
  });

  final List<String> imageUrls;

  @override
  State<ProductImageSlider> createState() => _ProductImageSliderState();
}

class _ProductImageSliderState extends State<ProductImageSlider> {
  final _controller = PageController();
  int _index = 0;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        AspectRatio(
          aspectRatio: 1.12,
          child: Container(
            decoration: BoxDecoration(
              color: AppColors.white,
              borderRadius: BorderRadius.circular(28),
              boxShadow: const [
                BoxShadow(
                  color: AppColors.shadow,
                  blurRadius: 24,
                  offset: Offset(0, 12),
                ),
              ],
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(28),
              child: PageView.builder(
                controller: _controller,
                itemCount: widget.imageUrls.isEmpty ? 1 : widget.imageUrls.length,
                onPageChanged: (value) => setState(() => _index = value),
                itemBuilder: (context, index) {
                  if (widget.imageUrls.isEmpty) {
                    return const Center(
                      child: Icon(
                        Icons.shopping_basket_rounded,
                        color: AppColors.primaryGreen,
                        size: 82,
                      ),
                    );
                  }

                  return CachedNetworkImage(
                    imageUrl: widget.imageUrls[index],
                    fit: BoxFit.cover,
                  );
                },
              ),
            ),
          ),
        ),
        if (widget.imageUrls.length > 1) ...[
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              for (var i = 0; i < widget.imageUrls.length; i++)
                AnimatedContainer(
                  duration: const Duration(milliseconds: 180),
                  width: i == _index ? 22 : 8,
                  height: 8,
                  margin: const EdgeInsets.symmetric(horizontal: 3),
                  decoration: BoxDecoration(
                    color: i == _index
                        ? AppColors.primaryGreen
                        : AppColors.border,
                    borderRadius: BorderRadius.circular(99),
                  ),
                ),
            ],
          ),
        ],
      ],
    );
  }
}
