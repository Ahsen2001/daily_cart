import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/vendor_provider.dart';
import '../../routes/app_routes.dart';
import '../../theme/app_colors.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/vendor_dashboard_card.dart';

class VendorDashboardScreen extends ConsumerStatefulWidget {
  const VendorDashboardScreen({super.key});

  @override
  ConsumerState<VendorDashboardScreen> createState() =>
      _VendorDashboardScreenState();
}

class _VendorDashboardScreenState extends ConsumerState<VendorDashboardScreen> {
  int _selectedIndex = 0;

  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(vendorProvider).getVendorDashboard());
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(vendorProvider);
    final dashboard = state.dashboard;

    return Scaffold(
      appBar: const CustomAppBar(title: 'Vendor Dashboard'),
      drawer: const AppDrawer(roleName: 'Vendor'),
      body: state.isLoading && dashboard == null
          ? const LoadingWidget(message: 'Loading vendor dashboard...')
          : dashboard == null
              ? const Center(child: Text('Vendor dashboard not found.'))
              : dashboard.isApproved
                  ? RefreshIndicator(
                      onRefresh: () =>
                          ref.read(vendorProvider).getVendorDashboard(),
                      child: GridView.count(
                        padding: const EdgeInsets.all(20),
                        crossAxisCount: 2,
                        crossAxisSpacing: 14,
                        mainAxisSpacing: 14,
                        childAspectRatio: 1.04,
                        children: [
                          VendorDashboardCard(
                            title: 'Total Products',
                            value: '${dashboard.totalProducts}',
                            icon: Icons.inventory_2_outlined,
                            onTap: () => context.push(AppRoutes.vendorProducts),
                          ),
                          VendorDashboardCard(
                            title: 'Pending Products',
                            value: '${dashboard.pendingProducts}',
                            icon: Icons.pending_actions_outlined,
                            color: AppColors.accentOrange,
                          ),
                          VendorDashboardCard(
                            title: 'Approved Products',
                            value: '${dashboard.approvedProducts}',
                            icon: Icons.verified_outlined,
                          ),
                          VendorDashboardCard(
                            title: 'Total Orders',
                            value: '${dashboard.totalOrders}',
                            icon: Icons.receipt_long_outlined,
                            onTap: () => context.push(AppRoutes.vendorOrders),
                          ),
                          VendorDashboardCard(
                            title: 'Pending Orders',
                            value: '${dashboard.pendingOrders}',
                            icon: Icons.schedule_rounded,
                            color: AppColors.accentOrange,
                          ),
                          VendorDashboardCard(
                            title: 'Completed Orders',
                            value: '${dashboard.completedOrders}',
                            icon: Icons.done_all_rounded,
                          ),
                          VendorDashboardCard(
                            title: "Today's Sales",
                            value: CurrencyFormatter.lkr(dashboard.todaySales),
                            icon: Icons.point_of_sale_rounded,
                          ),
                          VendorDashboardCard(
                            title: 'Total Earnings',
                            value:
                                CurrencyFormatter.lkr(dashboard.totalEarnings),
                            icon: Icons.account_balance_wallet_outlined,
                            onTap: () => context.push(AppRoutes.vendorEarnings),
                          ),
                          VendorDashboardCard(
                            title: 'Low Stock Products',
                            value: '${dashboard.lowStockProducts}',
                            icon: Icons.warning_amber_rounded,
                            color: AppColors.danger,
                            onTap: () => context.push(AppRoutes.vendorProducts),
                          ),
                        ],
                      ),
                    )
                  : _PendingApproval(status: dashboard.approvalStatus),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _selectedIndex,
        onDestinationSelected: (index) {
          setState(() => _selectedIndex = index);
          if (index == 1) {
            context.push(AppRoutes.vendorProducts);
          }
          if (index == 2) {
            context.push(AppRoutes.vendorOrders);
          }
          if (index == 3) {
            context.push(AppRoutes.vendorEarnings);
          }
          if (index == 4) {
            context.push(AppRoutes.vendorProfile);
          }
        },
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.dashboard_outlined),
            selectedIcon: Icon(Icons.dashboard_rounded),
            label: 'Home',
          ),
          NavigationDestination(
            icon: Icon(Icons.inventory_2_outlined),
            selectedIcon: Icon(Icons.inventory_2_rounded),
            label: 'Products',
          ),
          NavigationDestination(
            icon: Icon(Icons.receipt_long_outlined),
            selectedIcon: Icon(Icons.receipt_long_rounded),
            label: 'Orders',
          ),
          NavigationDestination(
            icon: Icon(Icons.payments_outlined),
            selectedIcon: Icon(Icons.payments_rounded),
            label: 'Earnings',
          ),
          NavigationDestination(
            icon: Icon(Icons.storefront_outlined),
            selectedIcon: Icon(Icons.storefront_rounded),
            label: 'Profile',
          ),
        ],
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
              'Vendor account is waiting for admin approval.',
              textAlign: TextAlign.center,
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    fontWeight: FontWeight.w900,
                  ),
            ),
            const SizedBox(height: 8),
            Text('Current status: ${status.replaceAll('_', ' ')}'),
          ],
        ),
      ),
    );
  }
}
