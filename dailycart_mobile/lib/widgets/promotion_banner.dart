import 'package:flutter/material.dart';

import '../models/promotion_model.dart';
import '../theme/app_colors.dart';

class PromotionBanner extends StatelessWidget {
  const PromotionBanner({
    required this.promotion,
    required this.onTap,
    super.key,
  });

  final PromotionModel promotion;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(24),
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          color: AppColors.darkGreen,
          borderRadius: BorderRadius.circular(24),
          boxShadow: const [
            BoxShadow(
              color: AppColors.shadow,
              blurRadius: 24,
              offset: Offset(0, 12),
            ),
          ],
        ),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    promotion.type.replaceAll('_', ' ').toUpperCase(),
                    style: const TextStyle(
                      color: AppColors.accentOrange,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    promotion.title,
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          color: AppColors.white,
                          fontWeight: FontWeight.w900,
                        ),
                  ),
                  if (promotion.discountText.isNotEmpty)
                    Text(
                      promotion.discountText,
                      style: const TextStyle(color: AppColors.white),
                    ),
                ],
              ),
            ),
            const Icon(Icons.arrow_forward_rounded, color: AppColors.white),
          ],
        ),
      ),
    );
  }
}
