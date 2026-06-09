import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';

import '../models/category_model.dart';
import '../theme/app_colors.dart';

class CategoryCard extends StatelessWidget {
  const CategoryCard({
    required this.category,
    required this.onTap,
    super.key,
  });

  final CategoryModel category;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(22),
      onTap: onTap,
      child: Container(
        width: 132,
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: BorderRadius.circular(22),
          boxShadow: const [
            BoxShadow(
              color: AppColors.shadow,
              blurRadius: 20,
              offset: Offset(0, 10),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              height: 68,
              decoration: BoxDecoration(
                color: AppColors.lightBackground,
                borderRadius: BorderRadius.circular(18),
              ),
              child: category.image.isEmpty
                  ? const Center(
                      child: Icon(
                        Icons.category_rounded,
                        color: AppColors.primaryGreen,
                        size: 34,
                      ),
                    )
                  : ClipRRect(
                      borderRadius: BorderRadius.circular(18),
                      child: CachedNetworkImage(
                        imageUrl: category.image,
                        fit: BoxFit.cover,
                        width: double.infinity,
                      ),
                    ),
            ),
            const SizedBox(height: 12),
            Text(
              category.name,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style: Theme.of(context).textTheme.titleSmall?.copyWith(
                    fontWeight: FontWeight.w800,
                  ),
            ),
            const SizedBox(height: 4),
            Text(
              '${category.productCount} products',
              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: AppColors.mutedText,
                  ),
            ),
          ],
        ),
      ),
    );
  }
}
