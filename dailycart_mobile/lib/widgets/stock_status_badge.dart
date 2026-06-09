import 'package:flutter/material.dart';

import '../theme/app_colors.dart';

class StockStatusBadge extends StatelessWidget {
  const StockStatusBadge({
    required this.status,
    this.stockQuantity,
    super.key,
  });

  final String status;
  final int? stockQuantity;

  @override
  Widget build(BuildContext context) {
    final normalized = status.toLowerCase();
    final color = switch (normalized) {
      'approved' => AppColors.primaryGreen,
      'pending' => AppColors.accentOrange,
      'rejected' || 'cancelled' || 'out_of_stock' => AppColors.danger,
      _ => AppColors.mutedText,
    };

    final label = stockQuantity != null && stockQuantity! <= 0
        ? 'OUT OF STOCK'
        : normalized.replaceAll('_', ' ').toUpperCase();

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.14),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: color,
          fontSize: 11,
          fontWeight: FontWeight.w900,
        ),
      ),
    );
  }
}
