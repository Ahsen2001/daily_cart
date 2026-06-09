import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../models/review_model.dart';
import '../theme/app_colors.dart';
import 'dailycart_card.dart';

class ReviewCard extends StatelessWidget {
  const ReviewCard({
    required this.review,
    this.onEdit,
    this.onDelete,
    super.key,
  });

  final ReviewModel review;
  final VoidCallback? onEdit;
  final VoidCallback? onDelete;

  @override
  Widget build(BuildContext context) {
    return DailyCartCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  review.productName.isEmpty
                      ? review.customerName
                      : review.productName,
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w900,
                      ),
                ),
              ),
              Text(
                DateFormat('MMM d, yyyy').format(review.createdAt),
                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: AppColors.mutedText,
                    ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Row(
            children: List.generate(
              5,
              (index) => Icon(
                index < review.rating
                    ? Icons.star_rounded
                    : Icons.star_border_rounded,
                color: AppColors.accentOrange,
                size: 20,
              ),
            ),
          ),
          if (review.comment.isNotEmpty) ...[
            const SizedBox(height: 10),
            Text(review.comment),
          ],
          if (onEdit != null || onDelete != null) ...[
            const SizedBox(height: 12),
            Row(
              children: [
                if (onEdit != null)
                  TextButton.icon(
                    onPressed: onEdit,
                    icon: const Icon(Icons.edit_outlined),
                    label: const Text('Edit'),
                  ),
                if (onDelete != null)
                  TextButton.icon(
                    onPressed: onDelete,
                    icon: const Icon(Icons.delete_outline_rounded),
                    label: const Text('Delete'),
                  ),
              ],
            ),
          ],
        ],
      ),
    );
  }
}
