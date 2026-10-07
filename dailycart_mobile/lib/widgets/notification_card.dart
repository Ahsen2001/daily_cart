import 'package:flutter/material.dart';

import '../models/notification_model.dart';
import '../theme/app_colors.dart';
import 'dailycart_card.dart';

class NotificationCard extends StatelessWidget {
  const NotificationCard({
    required this.notification,
    required this.onOpen,
    required this.onMarkRead,
    required this.onDelete,
    super.key,
  });

  final NotificationModel notification;
  final VoidCallback onOpen;
  final VoidCallback onMarkRead;
  final VoidCallback onDelete;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(22),
      onTap: onOpen,
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
                        Semantics(
                          label: 'Unread',
                          child: CircleAvatar(
                            radius: 5,
                            backgroundColor: AppColors.accentOrange,
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
                    runSpacing: 6,
                    children: [
                      _Tag(label: notification.categoryLabel, color: _color),
                      if (notification.requiresAction)
                        const _Tag(
                          label: 'Action needed',
                          color: AppColors.accentOrange,
                        ),
                    ],
                  ),
                  if (notification.timeline.isNotEmpty)
                    ExpansionTile(
                      tilePadding: EdgeInsets.zero,
                      childrenPadding: EdgeInsets.zero,
                      leading: const Icon(Icons.timeline_rounded),
                      title: const Text('Order activity'),
                      children: notification.timeline
                          .map((entry) => ListTile(
                                dense: true,
                                leading: const Icon(
                                  Icons.check_circle_rounded,
                                  color: AppColors.primaryGreen,
                                  size: 20,
                                ),
                                title: Text(
                                  entry.status.replaceAll('_', ' ').toUpperCase(),
                                ),
                                subtitle: entry.remarks.isEmpty
                                    ? null
                                    : Text(entry.remarks),
                              ))
                          .toList(growable: false),
                    ),
                  Wrap(
                    spacing: 8,
                    children: [
                      if (!notification.isRead)
                        TextButton(
                          onPressed: onMarkRead,
                          child: const Text('Mark read'),
                        ),
                      FilledButton.tonalIcon(
                        onPressed: onOpen,
                        icon: const Icon(Icons.open_in_new_rounded),
                        label: Text(
                          notification.requiresAction ? 'Take action' : 'View',
                        ),
                      ),
                      PopupMenuButton<String>(
                        tooltip: 'More actions',
                        onSelected: (value) {
                          if (value == 'delete') onDelete();
                        },
                        itemBuilder: (context) => const [
                          PopupMenuItem(
                            value: 'delete',
                            child: Text('Remove notification'),
                          ),
                        ],
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
      NotificationType.delivery => const Color(0xFF1677A8),
      NotificationType.payment => AppColors.darkGreen,
      NotificationType.support => const Color(0xFF6D4C9C),
      NotificationType.account => const Color(0xFF4B6074),
      NotificationType.review => const Color(0xFFF59E0B),
      NotificationType.inventory => const Color(0xFFB45309),
      NotificationType.promotion => AppColors.accentOrange,
      NotificationType.system => AppColors.mutedText,
    };
  }

  IconData get _icon {
    return switch (notification.type) {
      NotificationType.order => Icons.receipt_long_outlined,
      NotificationType.delivery => Icons.local_shipping_outlined,
      NotificationType.payment => Icons.payments_outlined,
      NotificationType.support => Icons.support_agent_rounded,
      NotificationType.account => Icons.verified_user_outlined,
      NotificationType.review => Icons.star_outline_rounded,
      NotificationType.inventory => Icons.inventory_2_outlined,
      NotificationType.promotion => Icons.local_offer_outlined,
      NotificationType.system => Icons.security_outlined,
    };
  }
}

class _Tag extends StatelessWidget {
  const _Tag({required this.label, required this.color});

  final String label;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        label,
        style: Theme.of(context).textTheme.labelSmall?.copyWith(
              color: color,
              fontWeight: FontWeight.w800,
            ),
      ),
    );
  }
}
