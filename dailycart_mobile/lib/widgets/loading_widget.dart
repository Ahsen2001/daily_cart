import 'package:flutter/material.dart';

import '../theme/app_colors.dart';

class LoadingWidget extends StatelessWidget {
  const LoadingWidget({
    this.message = 'Loading...',
    super.key,
  });

  final String message;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const CircularProgressIndicator(color: AppColors.primaryGreen),
          const SizedBox(height: 16),
          Text(
            message,
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  color: AppColors.mutedText,
                ),
          ),
        ],
      ),
    );
  }
}
