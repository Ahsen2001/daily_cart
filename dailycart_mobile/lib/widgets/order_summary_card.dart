import 'package:flutter/material.dart';

import '../models/order_model.dart';
import '../theme/app_colors.dart';
import 'dailycart_card.dart';
import 'price_row_widget.dart';

class OrderSummaryCard extends StatelessWidget {
  const OrderSummaryCard({
    required this.order,
    super.key,
  });

  final OrderModel order;

  @override
  Widget build(BuildContext context) {
    return DailyCartCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Summary',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.w900,
                ),
          ),
          const SizedBox(height: 8),
          PriceRowWidget(label: 'Subtotal', amount: order.subtotal),
          PriceRowWidget(
            label: 'Discount',
            amount: order.discount,
            isDiscount: order.discount > 0,
          ),
          PriceRowWidget(label: 'Delivery Charge', amount: order.deliveryCharge),
          PriceRowWidget(label: 'Service Charge', amount: order.serviceCharge),
          const Divider(color: AppColors.border),
          PriceRowWidget(
            label: 'Grand Total',
            amount: order.grandTotal,
            isTotal: true,
          ),
        ],
      ),
    );
  }
}
