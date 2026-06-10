import 'package:flutter/material.dart';

import '../theme/app_colors.dart';

class DeliveryStatusBadge extends StatelessWidget {
  const DeliveryStatusBadge({
    required this.status,
    super.key,
  });

  final String status;

  @override
  Widget build(BuildContext context) {
    final normalized = status.toLowerCase();
    final color = switch (normalized) {
      'delivered' => AppColors.primaryGreen,
      'failed' || 'cancelled' => AppColors.danger,
      'assigned' || 'picked_up' || 'on_the_way' => AppColors.accentOrange,
      _ => AppColors.mutedText,
    };
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.14),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        normalized.replaceAll('_', ' ').toUpperCase(),
        style: TextStyle(
          color: color,
          fontSize: 11,
          fontWeight: FontWeight.w900,
        ),
      ),
    );
  }
}
