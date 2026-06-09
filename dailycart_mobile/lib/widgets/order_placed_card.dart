import 'package:flutter/material.dart';

import '../models/checkout_response_model.dart';
import '../theme/app_colors.dart';
import '../utils/currency_formatter.dart';
import 'dailycart_card.dart';

class OrderPlacedCard extends StatelessWidget {
  const OrderPlacedCard({
    required this.order,
    required this.title,
    required this.message,
    super.key,
  });

  final OrderModel order;
  final String title;
  final String message;

  @override
  Widget build(BuildContext context) {
    return DailyCartCard(
      child: Column(
        children: [
          Container(
            width: 74,
            height: 74,
            decoration: BoxDecoration(
              color: AppColors.primaryGreen.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(24),
            ),
            child: const Icon(
              Icons.check_circle_rounded,
              color: AppColors.darkGreen,
              size: 42,
            ),
          ),
          const SizedBox(height: 18),
          Text(
            title,
            textAlign: TextAlign.center,
            style: Theme.of(context).textTheme.titleLarge?.copyWith(
                  fontWeight: FontWeight.w900,
                ),
          ),
          const SizedBox(height: 8),
          Text(
            message,
            textAlign: TextAlign.center,
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  color: AppColors.mutedText,
                  height: 1.45,
                ),
          ),
          const SizedBox(height: 16),
          Text('Order: ${order.orderNumber.isEmpty ? order.id : order.orderNumber}'),
          const SizedBox(height: 6),
          Text(CurrencyFormatter.lkr(order.grandTotal)),
        ],
      ),
    );
  }
}
