import 'package:flutter/material.dart';

import '../../core/constants/app_colors.dart';
import '../../core/utils/currency_formatter.dart';

class PriceText extends StatelessWidget {
  const PriceText(this.amount, {super.key});

  final num amount;

  @override
  Widget build(BuildContext context) {
    return Text(
      CurrencyFormatter.lkr(amount),
      style: Theme.of(context).textTheme.titleMedium?.copyWith(
            color: AppColors.darkGreen,
            fontWeight: FontWeight.w800,
          ),
    );
  }
}
