import 'package:flutter/material.dart';

import '../theme/app_colors.dart';
import '../utils/currency_formatter.dart';

class VendorEarningCard extends StatelessWidget {
  const VendorEarningCard({
    required this.title,
    required this.amount,
    this.icon = Icons.account_balance_wallet_outlined,
    super.key,
  });

  final String title;
  final double amount;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(22),
        boxShadow: const [
          BoxShadow(
            color: AppColors.shadow,
            blurRadius: 22,
            offset: Offset(0, 10),
          ),
        ],
      ),
      child: Row(
        children: [
          CircleAvatar(
            backgroundColor: AppColors.primaryGreen.withValues(alpha: 0.14),
            child: Icon(icon, color: AppColors.darkGreen),
          ),
          const SizedBox(width: 12),
          Expanded(child: Text(title)),
          Text(
            CurrencyFormatter.lkr(amount),
            style: const TextStyle(fontWeight: FontWeight.w900),
          ),
        ],
      ),
    );
  }
}
