import 'package:flutter/material.dart';

import '../theme/app_colors.dart';
import '../utils/currency_formatter.dart';

class PriceRowWidget extends StatelessWidget {
  const PriceRowWidget({
    required this.label,
    required this.amount,
    this.isTotal = false,
    this.isDiscount = false,
    super.key,
  });

  final String label;
  final double amount;
  final bool isTotal;
  final bool isDiscount;

  @override
  Widget build(BuildContext context) {
    final color = isDiscount
        ? AppColors.accentOrange
        : isTotal
            ? AppColors.darkGreen
            : AppColors.textColor;

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        children: [
          Expanded(
            child: Text(
              label,
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                    color: isTotal ? AppColors.textColor : AppColors.mutedText,
                    fontWeight: isTotal ? FontWeight.w900 : FontWeight.w500,
                  ),
            ),
          ),
          Text(
            isDiscount
                ? '- ${CurrencyFormatter.lkr(amount)}'
                : CurrencyFormatter.lkr(amount),
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  color: color,
                  fontWeight: isTotal ? FontWeight.w900 : FontWeight.w700,
                ),
          ),
        ],
      ),
    );
  }
}
