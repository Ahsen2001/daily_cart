import 'package:flutter/material.dart';

import '../theme/app_colors.dart';

class RatingWidget extends StatelessWidget {
  const RatingWidget({
    required this.rating,
    this.showValue = true,
    super.key,
  });

  final double rating;
  final bool showValue;

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        const Icon(Icons.star_rounded, color: AppColors.accentOrange, size: 18),
        const SizedBox(width: 4),
        if (showValue)
          Text(
            rating.toStringAsFixed(1),
            style: Theme.of(context).textTheme.bodySmall?.copyWith(
                  fontWeight: FontWeight.w700,
                ),
          ),
      ],
    );
  }
}
