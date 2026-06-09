import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../models/vendor_review_model.dart';
import '../theme/app_colors.dart';
import 'dailycart_card.dart';
import 'rating_widget.dart';

class VendorReviewCard extends StatelessWidget {
  const VendorReviewCard({
    required this.review,
    super.key,
  });

  final VendorReviewModel review;

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
                  review.productName,
                  style: const TextStyle(fontWeight: FontWeight.w900),
                ),
              ),
              RatingWidget(rating: review.rating),
            ],
          ),
          const SizedBox(height: 8),
          Text(review.comment),
          const SizedBox(height: 8),
          Row(
            children: [
              Text(
                review.customerName,
                style: const TextStyle(color: AppColors.mutedText),
              ),
              const Spacer(),
              Text(
                DateFormat('MMM d, yyyy').format(review.createdAt),
                style: const TextStyle(color: AppColors.mutedText),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
