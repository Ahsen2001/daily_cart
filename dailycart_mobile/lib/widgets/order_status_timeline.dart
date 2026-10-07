import 'package:flutter/material.dart';

import '../models/order_model.dart';
import '../theme/app_colors.dart';

class OrderStatusTimeline extends StatelessWidget {
  const OrderStatusTimeline({
    required this.currentStatus,
    this.entries = const [],
    super.key,
  });

  final String currentStatus;
  final List<OrderTimelineEntry> entries;

  static const _steps = [
    ('pending', 'Order Placed'),
    ('confirmed', 'Order Confirmed'),
    ('packed', 'Order Packed'),
    ('assigned_to_rider', 'Rider Assigned'),
    ('delivery_accepted', 'Delivery Accepted'),
    ('picked_up', 'Picked Up'),
    ('out_for_delivery', 'Out for Delivery'),
    ('delivered', 'Delivered'),
  ];

  @override
  Widget build(BuildContext context) {
    final currentIndex = _steps.indexWhere((step) => step.$1 == currentStatus);
    final safeIndex = currentIndex < 0 ? 0 : currentIndex;

    final byStatus = <String, OrderTimelineEntry>{
      for (final entry in entries) entry.status.toLowerCase(): entry,
    };

    return Semantics(
      container: true,
      label: 'Order activity timeline. Current status $currentStatus',
      child: Column(
      children: [
        for (var index = 0; index < _steps.length; index++)
          _TimelineRow(
            title: _steps[index].$2,
            isComplete: index <= safeIndex,
            isLast: index == _steps.length - 1,
            entry: byStatus[_steps[index].$1],
          ),
      ],
      ),
    );
  }
}

class _TimelineRow extends StatelessWidget {
  const _TimelineRow({
    required this.title,
    required this.isComplete,
    required this.isLast,
    this.entry,
  });

  final String title;
  final bool isComplete;
  final bool isLast;
  final OrderTimelineEntry? entry;

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Column(
          children: [
            AnimatedContainer(
              duration: const Duration(milliseconds: 220),
              width: 28,
              height: 28,
              decoration: BoxDecoration(
                color: isComplete ? AppColors.primaryGreen : AppColors.border,
                shape: BoxShape.circle,
              ),
              child: Icon(
                isComplete ? Icons.check_rounded : Icons.circle_outlined,
                color: isComplete ? AppColors.white : AppColors.mutedText,
                size: 18,
              ),
            ),
            if (!isLast)
              Container(
                width: 3,
                height: 34,
                color: isComplete ? AppColors.primaryGreen : AppColors.border,
              ),
          ],
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Padding(
            padding: const EdgeInsets.only(top: 3),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: Theme.of(context).textTheme.titleSmall?.copyWith(
                        fontWeight:
                            isComplete ? FontWeight.w900 : FontWeight.w500,
                      ),
                ),
                if (entry?.remarks.isNotEmpty == true)
                  Text(
                    entry!.remarks,
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: AppColors.mutedText,
                        ),
                  ),
                if (entry?.timestamp != null)
                  Text(
                    entry!.timestamp!.toLocal().toString().substring(0, 16),
                    style: Theme.of(context).textTheme.labelSmall?.copyWith(
                          color: AppColors.mutedText,
                        ),
                  ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}
