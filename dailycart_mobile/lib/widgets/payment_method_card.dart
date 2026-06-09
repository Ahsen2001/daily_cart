import 'package:flutter/material.dart';

import '../models/payment_method_model.dart';
import '../theme/app_colors.dart';
import 'dailycart_card.dart';

class PaymentMethodCard extends StatelessWidget {
  const PaymentMethodCard({
    required this.method,
    required this.isSelected,
    required this.onTap,
    super.key,
  });

  final PaymentMethodModel method;
  final bool isSelected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(22),
      onTap: onTap,
      child: DailyCartCard(
        child: Row(
          children: [
            Container(
              width: 48,
              height: 48,
              decoration: BoxDecoration(
                color: AppColors.lightBackground,
                borderRadius: BorderRadius.circular(16),
              ),
              child: Icon(_iconFor(method.type), color: AppColors.darkGreen),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    method.type.title,
                    style: Theme.of(context).textTheme.titleSmall?.copyWith(
                          fontWeight: FontWeight.w900,
                        ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    method.type.subtitle,
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: AppColors.mutedText,
                        ),
                  ),
                ],
              ),
            ),
            Icon(
              isSelected
                  ? Icons.radio_button_checked_rounded
                  : Icons.radio_button_off_rounded,
              color: AppColors.primaryGreen,
            ),
          ],
        ),
      ),
    );
  }

  IconData _iconFor(PaymentMethodType type) {
    return switch (type) {
      PaymentMethodType.cashOnDelivery => Icons.payments_outlined,
      PaymentMethodType.payHere => Icons.credit_card_rounded,
      PaymentMethodType.bankTransfer => Icons.account_balance_rounded,
      PaymentMethodType.wallet => Icons.account_balance_wallet_outlined,
    };
  }
}
