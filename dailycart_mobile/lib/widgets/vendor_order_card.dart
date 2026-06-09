import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../models/vendor_order_model.dart';
import '../theme/app_colors.dart';
import '../utils/currency_formatter.dart';
import 'dailycart_card.dart';
import 'stock_status_badge.dart';

class VendorOrderCard extends StatelessWidget {
  const VendorOrderCard({
    required this.order,
    required this.onTap,
    super.key,
  });

  final VendorOrderModel order;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(22),
      onTap: onTap,
      child: DailyCartCard(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    order.orderNumber.isEmpty
                        ? 'Order #${order.id}'
                        : order.orderNumber,
                    style: const TextStyle(fontWeight: FontWeight.w900),
                  ),
                ),
                StockStatusBadge(status: order.status),
              ],
            ),
            const SizedBox(height: 8),
            Text(order.customerName.isEmpty ? 'Customer' : order.customerName),
            const SizedBox(height: 6),
            Row(
              children: [
                Text(
                  DateFormat('MMM d, yyyy').format(order.createdAt),
                  style: const TextStyle(color: AppColors.mutedText),
                ),
                const Spacer(),
                Text(
                  CurrencyFormatter.lkr(order.totalAmount),
                  style: const TextStyle(fontWeight: FontWeight.w900),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
