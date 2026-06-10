import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../models/delivery_model.dart';
import '../theme/app_colors.dart';
import '../utils/currency_formatter.dart';
import 'dailycart_card.dart';
import 'delivery_status_badge.dart';

class DeliveryCard extends StatelessWidget {
  const DeliveryCard({
    required this.delivery,
    required this.onTap,
    super.key,
  });

  final DeliveryModel delivery;
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
                    delivery.orderNumber.isEmpty
                        ? 'Delivery #${delivery.id}'
                        : delivery.orderNumber,
                    style: const TextStyle(fontWeight: FontWeight.w900),
                  ),
                ),
                DeliveryStatusBadge(status: delivery.status),
              ],
            ),
            const SizedBox(height: 8),
            Text(delivery.customerName.isEmpty ? 'Customer' : delivery.customerName),
            const SizedBox(height: 4),
            Text(
              delivery.deliveryAddress.isEmpty ? '-' : delivery.deliveryAddress,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(color: AppColors.mutedText),
            ),
            const SizedBox(height: 10),
            Row(
              children: [
                Icon(Icons.schedule_rounded, size: 16, color: AppColors.mutedText),
                const SizedBox(width: 4),
                Expanded(
                  child: Text(
                    delivery.scheduledDeliveryTime == null
                        ? 'Not scheduled'
                        : DateFormat('MMM d, h:mm a')
                            .format(delivery.scheduledDeliveryTime!),
                    style: Theme.of(context).textTheme.bodySmall,
                  ),
                ),
                Text(
                  CurrencyFormatter.lkr(delivery.totalAmount),
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
