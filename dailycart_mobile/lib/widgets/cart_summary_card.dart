import 'package:flutter/material.dart';

import '../models/cart_summary_model.dart';
import '../theme/app_colors.dart';
import 'dailycart_card.dart';
import 'price_row_widget.dart';

class CartSummaryCard extends StatelessWidget {
  const CartSummaryCard({
    required this.summary,
    this.action,
    super.key,
  });

  final CartSummaryModel summary;
  final Widget? action;

  @override
  Widget build(BuildContext context) {
    return DailyCartCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Cart Summary',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.w900,
                ),
          ),
          const SizedBox(height: 10),
          PriceRowWidget(label: 'Subtotal', amount: summary.subtotal),
          PriceRowWidget(
            label: 'Discount',
            amount: summary.discount,
            isDiscount: summary.discount > 0,
          ),
          PriceRowWidget(
            label: 'Delivery Charge',
            amount: summary.deliveryCharge,
          ),
          PriceRowWidget(
            label: 'Service Charge',
            amount: summary.serviceCharge,
          ),
          const Divider(color: AppColors.border),
          PriceRowWidget(
            label: 'Grand Total',
            amount: summary.grandTotal,
            isTotal: true,
          ),
          if (action != null) ...[
            const SizedBox(height: 14),
            action!,
          ],
        ],
      ),
    );
  }
}
