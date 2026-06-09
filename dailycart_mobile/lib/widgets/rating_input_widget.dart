import 'package:flutter/material.dart';

import '../theme/app_colors.dart';

class RatingInputWidget extends StatelessWidget {
  const RatingInputWidget({
    required this.rating,
    required this.onChanged,
    super.key,
  });

  final int rating;
  final ValueChanged<int> onChanged;

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: List.generate(5, (index) {
        final value = index + 1;
        return IconButton(
          tooltip: '$value star',
          onPressed: () => onChanged(value),
          icon: Icon(
            value <= rating ? Icons.star_rounded : Icons.star_border_rounded,
            color: AppColors.accentOrange,
            size: 34,
          ),
        );
      }),
    );
  }
}
