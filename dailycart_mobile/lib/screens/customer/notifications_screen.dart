import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../config/app_identity.dart';
import '../../models/notification_model.dart';
import '../../providers/notification_provider.dart';
import '../../routes/app_routes.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state_widget.dart';
import '../../widgets/error_widget.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/notification_card.dart';

enum _NotificationView { all, unread, action }

class NotificationsScreen extends ConsumerStatefulWidget {
  const NotificationsScreen({super.key});

  @override
  ConsumerState<NotificationsScreen> createState() =>
      _NotificationsScreenState();
}

class _NotificationsScreenState extends ConsumerState<NotificationsScreen> {
  final _searchController = TextEditingController();
  _NotificationView _view = _NotificationView.all;
  NotificationType? _category;

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

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
          : state.notifications.isEmpty && state.errorMessage != null
          ? DailyCartErrorWidget(
              title: 'Notifications unavailable',
              message: state.errorMessage!,
              onRetry: () =>
                  ref.read(notificationProvider).getNotifications(),
            )
          : state.notifications.isEmpty
          ? const EmptyStateWidget(
              title: 'No notifications',
              message:
                  'Order, payment, promotion, and system alerts appear here.',
              icon: Icons.notifications_none_rounded,
            )
          : _buildActionCenter(context, state),
    );
  }

  Widget _buildActionCenter(BuildContext context, NotificationProvider state) {
    final query = _searchController.text.trim().toLowerCase();
    final filtered = state.notifications.where((item) {
      final viewMatches = switch (_view) {
        _NotificationView.all => true,
        _NotificationView.unread => !item.isRead,
        _NotificationView.action => item.requiresAction,
      };
      final categoryMatches = _category == null || item.type == _category;
      final searchMatches = query.isEmpty ||
          item.title.toLowerCase().contains(query) ||
          item.message.toLowerCase().contains(query) ||
          item.categoryLabel.toLowerCase().contains(query);
      return viewMatches && categoryMatches && searchMatches;
    }).toList(growable: false);

    return RefreshIndicator(
      onRefresh: () => ref.read(notificationProvider).getNotifications(),
      child: LayoutBuilder(
        builder: (context, constraints) {
          final horizontal = constraints.maxWidth >= 700 ? 48.0 : 16.0;
          return ListView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: EdgeInsets.fromLTRB(horizontal, 20, horizontal, 32),
            children: [
              Center(
                child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 900),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      _ActionHeader(
                        unread: state.unreadCount,
                        action: state.notifications
                            .where((item) => item.requiresAction)
                            .length,
                        selected: _view,
                        onSelected: (value) => setState(() => _view = value),
                      ),
                      const SizedBox(height: 16),
                      TextField(
                        controller: _searchController,
                        onChanged: (_) => setState(() {}),
                        decoration: const InputDecoration(
                          prefixIcon: Icon(Icons.search_rounded),
                          hintText: 'Search notifications',
                          labelText: 'Search',
                        ),
                      ),
                      const SizedBox(height: 12),
                      SingleChildScrollView(
                        scrollDirection: Axis.horizontal,
                        child: Row(
                          children: [
                            ChoiceChip(
                              label: const Text('All categories'),
                              selected: _category == null,
                              onSelected: (_) =>
                                  setState(() => _category = null),
                            ),
                            const SizedBox(width: 8),
                            for (final type in NotificationType.values) ...[
                              ChoiceChip(
                                label: Text(_categoryName(type)),
                                selected: _category == type,
                                onSelected: (_) =>
                                    setState(() => _category = type),
                              ),
                              const SizedBox(width: 8),
                            ],
                          ],
                        ),
                      ),
                      if (state.errorMessage != null) ...[
                        const SizedBox(height: 12),
                        MaterialBanner(
                          content: Text(state.isUsingCachedData
                              ? 'You are viewing saved notifications. ${state.errorMessage}'
                              : state.errorMessage!),
                          leading: const Icon(Icons.cloud_off_rounded),
                          actions: [
                            TextButton(
                              onPressed: () => ref
                                  .read(notificationProvider)
                                  .getNotifications(),
                              child: const Text('Retry'),
                            ),
                          ],
                        ),
                      ],
                      const SizedBox(height: 18),
                      if (filtered.isEmpty)
                        const EmptyStateWidget(
                          title: 'Nothing matches',
                          message: 'Try another filter or search term.',
                          icon: Icons.filter_alt_off_outlined,
                        )
                      else
                        ..._groupedCards(filtered),
                    ],
                  ),
                ),
              ),
            ],
          );
        },
      ),
    );
  }

  List<Widget> _groupedCards(List<NotificationModel> items) {
    final widgets = <Widget>[];
    String? previousGroup;
    for (final notification in items) {
      final group = _dateGroup(notification.createdAt);
      if (group != previousGroup) {
        if (widgets.isNotEmpty) widgets.add(const SizedBox(height: 20));
        widgets.add(Padding(
          padding: const EdgeInsets.only(bottom: 10),
          child: Text(
            group,
            style: Theme.of(context)
                .textTheme
                .titleMedium
                ?.copyWith(fontWeight: FontWeight.w900),
          ),
        ));
        previousGroup = group;
      }
      widgets.add(NotificationCard(
        notification: notification,
        onOpen: () => _openNotification(notification),
        onMarkRead: () =>
            ref.read(notificationProvider).markAsRead(notification.id),
        onDelete: () =>
            ref.read(notificationProvider).deleteNotification(notification.id),
      ));
      widgets.add(const SizedBox(height: 12));
    }
    return widgets;
  }

  Future<void> _openNotification(NotificationModel notification) async {
    if (!notification.isRead) {
      await ref.read(notificationProvider).markAsRead(notification.id);
    }
    if (!mounted) return;
    final route = _safeRoute(notification);
    if (route != null) context.push(route);
  }

  String? _safeRoute(NotificationModel notification) {
    if (notification.orderId != null) {
      return _orderRoute(notification.orderId!);
    }
    final deliveryId = notification.data['delivery_id'];
    if (AppIdentity.isRider && deliveryId != null) {
      return '${AppRoutes.deliveryDetails}/$deliveryId';
    }
    if (notification.type == NotificationType.support) {
      return AppIdentity.isVendor
          ? AppRoutes.vendorSupportTickets
          : AppIdentity.isRider
              ? AppRoutes.riderSupportTickets
              : AppRoutes.supportTickets;
    }
    if (notification.type == NotificationType.payment) {
      return AppIdentity.isVendor
          ? AppRoutes.vendorEarnings
          : AppIdentity.isRider
              ? AppRoutes.riderEarnings
              : AppRoutes.wallet;
    }
    final deepLink = notification.deepLink;
    return deepLink != null && deepLink.startsWith('/') ? deepLink : null;
  }

  String _dateGroup(DateTime timestamp) {
    final now = DateTime.now();
    final local = timestamp.toLocal();
    final date = DateTime(local.year, local.month, local.day);
    final today = DateTime(now.year, now.month, now.day);
    if (date == today) return 'Today';
    if (date == today.subtract(const Duration(days: 1))) return 'Yesterday';
    return 'Earlier';
  }

  String _categoryName(NotificationType type) => switch (type) {
        NotificationType.order => 'Orders',
        NotificationType.delivery => 'Delivery',
        NotificationType.payment => 'Payments',
        NotificationType.support => 'Support',
        NotificationType.account => 'Account',
        NotificationType.review => 'Reviews',
        NotificationType.inventory => 'Inventory',
        NotificationType.promotion => 'Offers',
        NotificationType.system => 'Other',
      };

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

class _ActionHeader extends StatelessWidget {
  const _ActionHeader({
    required this.unread,
    required this.action,
    required this.selected,
    required this.onSelected,
  });

  final int unread;
  final int action;
  final _NotificationView selected;
  final ValueChanged<_NotificationView> onSelected;

  @override
  Widget build(BuildContext context) {
    return Semantics(
      container: true,
      label: '${AppIdentity.flavor.displayName} notification action center',
      child: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            colors: [Color(0xFF0B5D2A), Color(0xFF15803D)],
          ),
          borderRadius: BorderRadius.circular(24),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Your action center',
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    color: Colors.white,
                    fontWeight: FontWeight.w900,
                  ),
            ),
            const SizedBox(height: 4),
            const Text(
              'Updates, decisions, and order activity in one place.',
              style: TextStyle(color: Color(0xFFDDF7E5)),
            ),
            const SizedBox(height: 18),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                _HeaderFilter(
                  label: 'All',
                  selected: selected == _NotificationView.all,
                  onTap: () => onSelected(_NotificationView.all),
                ),
                _HeaderFilter(
                  label: 'Unread $unread',
                  selected: selected == _NotificationView.unread,
                  onTap: () => onSelected(_NotificationView.unread),
                ),
                _HeaderFilter(
                  label: 'Action $action',
                  selected: selected == _NotificationView.action,
                  onTap: () => onSelected(_NotificationView.action),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _HeaderFilter extends StatelessWidget {
  const _HeaderFilter({
    required this.label,
    required this.selected,
    required this.onTap,
  });

  final String label;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Semantics(
      button: true,
      selected: selected,
      child: ChoiceChip(
        label: Text(label),
        selected: selected,
        onSelected: (_) => onTap(),
        backgroundColor: Colors.white.withValues(alpha: 0.12),
        selectedColor: Colors.white,
        labelStyle: TextStyle(
          color: selected ? const Color(0xFF0B5D2A) : Colors.white,
          fontWeight: FontWeight.w800,
        ),
        side: BorderSide(color: Colors.white.withValues(alpha: 0.35)),
      ),
    );
  }
}
