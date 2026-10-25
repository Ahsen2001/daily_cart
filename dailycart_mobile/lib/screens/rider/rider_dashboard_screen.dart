import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/rider_provider.dart';
import '../../providers/notification_provider.dart';
import '../../routes/app_routes.dart';
import '../../theme/app_colors.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/rider_dashboard_card.dart';
import '../../widgets/rider_bottom_navigation.dart';
import '../../widgets/error_widget.dart';

class RiderDashboardScreen extends ConsumerStatefulWidget {
  const RiderDashboardScreen({super.key});

  @override
  ConsumerState<RiderDashboardScreen> createState() =>
      _RiderDashboardScreenState();
}

class _RiderDashboardScreenState extends ConsumerState<RiderDashboardScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() async {
      await Future.wait([
        ref.read(riderProvider).getRiderDashboard(),
        ref.read(riderProvider).getRiderProfile(),
        ref.read(notificationProvider).getNotifications(),
      ]);
    });
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(riderProvider);
    final notifications = ref.watch(notificationProvider);
    final dashboard = state.dashboard;
    return Scaffold(
      appBar: CustomAppBar(
        title: 'Rider Dashboard',
        actions: [
          IconButton(
            tooltip: 'Notifications',
            onPressed: () => context.push(AppRoutes.riderNotifications),
            icon: Badge(
              isLabelVisible: notifications.unreadCount > 0,
              label: Text('${notifications.unreadCount}'),
              child: const Icon(Icons.notifications_none_rounded),
            ),
          ),
          const SizedBox(width: 8),
        ],
      ),
      drawer: const AppDrawer(roleName: 'Rider'),
      body: state.isLoading && dashboard == null
          ? const LoadingWidget(message: 'Loading rider dashboard...')
          : dashboard == null
          ? DailyCartErrorWidget(
              title: 'Dashboard unavailable',
              message: state.errorMessage ?? 'Rider dashboard not found.',
              onRetry: () => ref.read(riderProvider).getRiderDashboard(),
            )
          : dashboard.isApproved
          ? RefreshIndicator(
              onRefresh: () => ref.read(riderProvider).getRiderDashboard(),
              child: GridView.count(
                padding: const EdgeInsets.all(20),
                crossAxisCount: 2,
                crossAxisSpacing: 14,
                mainAxisSpacing: 14,
                childAspectRatio: 1.04,
                children: [
                  RiderDashboardCard(
                    title: '${dashboard.ratingCount} customer ratings',
                    value: '${dashboard.averageRating.toStringAsFixed(1)} / 5',
                    icon: Icons.star_rounded,
                    color: Colors.amber.shade700,
                    onTap: () => context.push(AppRoutes.riderRatings),
                  ),
                  RiderDashboardCard(
                    title: 'Availability',
                    value: dashboard.availabilityStatus
                        .replaceAll('_', ' ')
                        .toUpperCase(),
                    icon: dashboard.availabilityStatus == 'available'
                        ? Icons.toggle_on
                        : Icons.toggle_off,
                    color: dashboard.availabilityStatus == 'available'
                        ? AppColors.primaryGreen
                        : AppColors.mutedText,
                    onTap: () =>
                        _toggleAvailability(dashboard.availabilityStatus),
                  ),
                  RiderDashboardCard(
                    title: "Today's Deliveries",
                    value: '${dashboard.todayDeliveries}',
                    icon: Icons.today_outlined,
                  ),
                  RiderDashboardCard(
                    title: 'Assigned Deliveries',
                    value: '${dashboard.assignedDeliveries}',
                    icon: Icons.assignment_outlined,
                    color: AppColors.accentOrange,
                    onTap: () => context.push(AppRoutes.assignedDeliveries),
                  ),
                  RiderDashboardCard(
                    title: 'Completed Deliveries',
                    value: '${dashboard.completedDeliveries}',
                    icon: Icons.done_all_rounded,
                  ),
                  RiderDashboardCard(
                    title: 'Failed Deliveries',
                    value: '${dashboard.failedDeliveries}',
                    icon: Icons.error_outline_rounded,
                    color: AppColors.danger,
                  ),
                  RiderDashboardCard(
                    title: "Today's Earnings",
                    value: CurrencyFormatter.lkr(dashboard.todayEarnings),
                    icon: Icons.payments_outlined,
                  ),
                  RiderDashboardCard(
                    title: 'Weekly Earnings',
                    value: CurrencyFormatter.lkr(dashboard.weeklyEarnings),
                    icon: Icons.date_range_outlined,
                    onTap: () => context.push(AppRoutes.riderEarnings),
                  ),
                  RiderDashboardCard(
                    title: 'Monthly Earnings',
                    value: CurrencyFormatter.lkr(dashboard.monthlyEarnings),
                    icon: Icons.calendar_month_outlined,
                    onTap: () => context.push(AppRoutes.riderEarnings),
                  ),
                ],
              ),
            )
          : _PendingApproval(status: dashboard.approvalStatus),
      bottomNavigationBar: const RiderBottomNavigation(currentIndex: 0),
    );
  }

  Future<void> _toggleAvailability(String current) async {
    final next = current == 'available' ? 'unavailable' : 'available';
    final ok = await ref.read(riderProvider).updateAvailability(next);
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          ok
              ? 'Availability changed to $next.'
              : ref.read(riderProvider).errorMessage ??
                    'Unable to change availability.',
        ),
      ),
    );
  }
}

class _PendingApproval extends StatelessWidget {
  const _PendingApproval({required this.status});

  final String status;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(28),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(
              Icons.hourglass_top_rounded,
              color: AppColors.accentOrange,
              size: 58,
            ),
            const SizedBox(height: 16),
            Text(
              'Rider account is waiting for admin approval.',
              textAlign: TextAlign.center,
              style: Theme.of(
                context,
              ).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w900),
            ),
            const SizedBox(height: 8),
            Text('Current status: ${status.replaceAll('_', ' ')}'),
          ],
        ),
      ),
    );
  }
}
