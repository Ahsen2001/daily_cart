import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/notification_provider.dart';
import '../../routes/app_routes.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state_widget.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/notification_card.dart';

class NotificationsScreen extends ConsumerStatefulWidget {
  const NotificationsScreen({super.key});

  @override
  ConsumerState<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends ConsumerState<NotificationsScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(
      () => ref.read(notificationProvider).getNotifications(),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(notificationProvider);

    return Scaffold(
      appBar: CustomAppBar(
        title: 'Notifications (${state.unreadCount})',
        actions: [
          IconButton(
            tooltip: 'Mark all as read',
            onPressed: state.notifications.isEmpty
                ? null
                : () => ref.read(notificationProvider).markAllAsRead(),
            icon: const Icon(Icons.done_all_rounded),
          ),
        ],
      ),
      body: state.isLoading && state.notifications.isEmpty
          ? const LoadingWidget(message: 'Loading notifications...')
          : state.notifications.isEmpty
              ? const EmptyStateWidget(
                  title: 'No notifications',
                  message: 'Order, payment, promotion, and system alerts appear here.',
                  icon: Icons.notifications_none_rounded,
                )
              : RefreshIndicator(
                  onRefresh: () =>
                      ref.read(notificationProvider).getNotifications(),
                  child: ListView.separated(
                    padding: const EdgeInsets.all(20),
                    itemBuilder: (context, index) {
                      final notification = state.notifications[index];
                      return NotificationCard(
                        notification: notification,
                        onTap: () {
                          if (notification.orderId != null) {
                            context.push(
                              '${AppRoutes.orderDetails}/${notification.orderId}',
                            );
                          }
                        },
                        onMarkRead: () => ref
                            .read(notificationProvider)
                            .markAsRead(notification.id),
                        onDelete: () => ref
                            .read(notificationProvider)
                            .deleteNotification(notification.id),
                      );
                    },
                    separatorBuilder: (context, index) =>
                        const SizedBox(height: 14),
                    itemCount: state.notifications.length,
                  ),
                ),
    );
  }
}
