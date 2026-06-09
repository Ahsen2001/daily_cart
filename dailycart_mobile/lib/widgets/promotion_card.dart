import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';

import '../models/promotion_model.dart';
import '../theme/app_colors.dart';
import 'dailycart_card.dart';

class PromotionCard extends StatelessWidget {
  const PromotionCard({
    required this.promotion,
    required this.onTap,
    super.key,
  });

  final PromotionModel promotion;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(22),
      onTap: onTap,
      child: DailyCartCard(
        padding: EdgeInsets.zero,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            ClipRRect(
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(22),
              ),
              child: promotion.image.isEmpty
                  ? Container(
                      height: 150,
                      color: AppColors.primaryGreen.withValues(alpha: 0.12),
                      child: const Center(
                        child: Icon(
                          Icons.local_offer_rounded,
                          color: AppColors.darkGreen,
                          size: 42,
                        ),
                      ),
                    )
                  : CachedNetworkImage(
                      imageUrl: promotion.image,
                      height: 150,
                      width: double.infinity,
                      fit: BoxFit.cover,
                    ),
            ),
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    promotion.type.replaceAll('_', ' ').toUpperCase(),
                    style: const TextStyle(
                      color: AppColors.accentOrange,
                      fontSize: 11,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    promotion.title,
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          fontWeight: FontWeight.w900,
                        ),
                  ),
                  if (promotion.discountText.isNotEmpty)
                    Text(promotion.discountText),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
