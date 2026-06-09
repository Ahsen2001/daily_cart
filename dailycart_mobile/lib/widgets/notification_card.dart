import 'package:flutter/material.dart';

import '../models/notification_model.dart';
import '../theme/app_colors.dart';
import 'dailycart_card.dart';

class NotificationCard extends StatelessWidget {
  const NotificationCard({
    required this.notification,
    required this.onTap,
    required this.onMarkRead,
    required this.onDelete,
    super.key,
  });

  final NotificationModel notification;
  final VoidCallback onTap;
  final VoidCallback onMarkRead;
  final VoidCallback onDelete;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(22),
      onTap: onTap,
      child: DailyCartCard(
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 46,
              height: 46,
              decoration: BoxDecoration(
                color: _color.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Icon(_icon, color: _color),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          notification.title,
                          style:
                              Theme.of(context).textTheme.titleSmall?.copyWith(
                                    fontWeight: FontWeight.w900,
                                  ),
                        ),
                      ),
                      if (!notification.isRead)
                        Container(
                          width: 10,
                          height: 10,
                          decoration: const BoxDecoration(
                            color: AppColors.accentOrange,
                            shape: BoxShape.circle,
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 5),
                  Text(
                    notification.message,
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: AppColors.mutedText,
                          height: 1.35,
                        ),
                  ),
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 8,
                    children: [
                      TextButton(
                        onPressed: onMarkRead,
                        child: const Text('Mark read'),
                      ),
                      TextButton(
                        onPressed: onDelete,
                        child: const Text('Delete'),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Color get _color {
    return switch (notification.type) {
      NotificationType.order => AppColors.primaryGreen,
      NotificationType.payment => AppColors.darkGreen,
      NotificationType.promotion => AppColors.accentOrange,
      NotificationType.system => AppColors.mutedText,
    };
  }

  IconData get _icon {
    return switch (notification.type) {
      NotificationType.order => Icons.receipt_long_outlined,
      NotificationType.payment => Icons.payments_outlined,
      NotificationType.promotion => Icons.local_offer_outlined,
      NotificationType.system => Icons.security_outlined,
    };
  }
}
