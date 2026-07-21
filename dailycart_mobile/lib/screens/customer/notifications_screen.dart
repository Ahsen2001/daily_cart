import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../config/app_identity.dart';
import '../../models/notification_model.dart';
import '../../providers/notification_provider.dart';
import '../../routes/app_routes.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state_widget.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/notification_card.dart';

class NotificationsScreen extends ConsumerStatefulWidget {
  const NotificationsScreen({super.key});

  @override
  ConsumerState<NotificationsScreen> createState() =>
      _NotificationsScreenState();
}

class _NotificationsScreenState extends ConsumerState<NotificationsScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(notificationProvider).getNotifications());
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(notificationProvider);

    return Scaffold(
      appBar: CustomAppBar(
        title: 'Notifications (${state.unreadCount})',
        actions: [
          IconButton(
            tooltip: 'Notification preferences',
            onPressed: () => _showPreferences(context, state),
            icon: const Icon(Icons.tune_rounded),
          ),
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
              message:
                  'Order, payment, promotion, and system alerts appear here.',
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
                      if (!notification.isRead) {
                        ref
                            .read(notificationProvider)
                            .markAsRead(notification.id);
                      }
                      final deepLink = notification.deepLink;
                      if (deepLink != null && deepLink.startsWith('/')) {
                        context.push(deepLink);
                      } else if (notification.orderId != null) {
                        context.push(_orderRoute(notification.orderId!));
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

  String _orderRoute(int orderId) {
    if (AppIdentity.isVendor) {
      return '${AppRoutes.vendorOrderDetails}/$orderId';
    }
    if (AppIdentity.isRider) {
      return AppRoutes.assignedDeliveries;
    }
    return '${AppRoutes.orderDetails}/$orderId';
  }

  Future<void> _showPreferences(
    BuildContext context,
    NotificationProvider state,
  ) async {
    var value = state.preferences;
    final saved = await showModalBottomSheet<NotificationPreferences>(
      context: context,
      isScrollControlled: true,
      builder: (context) => StatefulBuilder(
        builder: (context, setSheetState) {
          Widget toggle(
            String title,
            bool selected,
            NotificationPreferences Function(bool) update,
          ) {
            return SwitchListTile.adaptive(
              title: Text(title),
              value: selected,
              onChanged: (enabled) => setSheetState(() {
                value = update(enabled);
              }),
            );
          }

          return SafeArea(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    'Notification preferences',
                    style: Theme.of(context).textTheme.titleLarge,
                  ),
                  toggle(
                    'Push notifications',
                    value.pushEnabled,
                    (enabled) => value.copyWith(pushEnabled: enabled),
                  ),
                  toggle(
                    'Order updates',
                    value.orderUpdates,
                    (enabled) => value.copyWith(orderUpdates: enabled),
                  ),
                  toggle(
                    'Delivery updates',
                    value.deliveryUpdates,
                    (enabled) => value.copyWith(deliveryUpdates: enabled),
                  ),
                  toggle(
                    'Wallet and payment updates',
                    value.walletUpdates,
                    (enabled) => value.copyWith(walletUpdates: enabled),
                  ),
                  toggle(
                    'Support updates',
                    value.supportUpdates,
                    (enabled) => value.copyWith(supportUpdates: enabled),
                  ),
                  toggle(
                    'Public promotions',
                    value.promotions,
                    (enabled) => value.copyWith(promotions: enabled),
                  ),
                  const SizedBox(height: 8),
                  FilledButton(
                    onPressed: () => Navigator.of(context).pop(value),
                    child: const Text('Save preferences'),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );

    if (saved != null) {
      await ref.read(notificationProvider).updatePreferences(saved);
    }
  }
}
