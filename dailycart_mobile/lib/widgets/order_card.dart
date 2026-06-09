import 'package:flutter/material.dart';

import '../models/order_model.dart';
import '../theme/app_colors.dart';
import '../utils/currency_formatter.dart';
import 'custom_button.dart';
import 'dailycart_card.dart';

class OrderCard extends StatelessWidget {
  const OrderCard({
    required this.order,
    required this.onViewDetails,
    super.key,
  });

  final OrderModel order;
  final VoidCallback onViewDetails;

  @override
  Widget build(BuildContext context) {
    return DailyCartCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  order.orderNumber.isEmpty ? 'Order #${order.id}' : order.orderNumber,
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w900,
                      ),
                ),
              ),
              _StatusBadge(label: order.status),
            ],
          ),
          const SizedBox(height: 8),
          Text('Date: ${_date(order.orderDate)}'),
          const SizedBox(height: 6),
          Text('Payment: ${order.paymentStatus}'),
          const SizedBox(height: 6),
          Text('Delivery: ${order.status}'),
          const SizedBox(height: 8),
          Text(
            CurrencyFormatter.lkr(order.grandTotal),
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  color: AppColors.darkGreen,
                  fontWeight: FontWeight.w900,
                ),
          ),
          const SizedBox(height: 12),
          CustomButton(
            label: 'View Details',
            icon: Icons.receipt_long_outlined,
            onPressed: onViewDetails,
          ),
        ],
      ),
    );
  }

  String _date(DateTime value) {
    return '${value.year}-${value.month.toString().padLeft(2, '0')}-${value.day.toString().padLeft(2, '0')}';
  }
}

class _StatusBadge extends StatelessWidget {
  const _StatusBadge({required this.label});

  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: AppColors.accentOrange,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        label.replaceAll('_', ' '),
        style: const TextStyle(
          color: AppColors.white,
          fontSize: 11,
          fontWeight: FontWeight.w800,
        ),
      ),
    );
  }
}
