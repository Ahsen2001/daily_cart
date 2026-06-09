import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../providers/vendor_earning_provider.dart';
import '../../routes/app_routes.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/vendor_earning_card.dart';

class VendorEarningsScreen extends ConsumerStatefulWidget {
  const VendorEarningsScreen({super.key});

  @override
  ConsumerState<VendorEarningsScreen> createState() =>
      _VendorEarningsScreenState();
}

class _VendorEarningsScreenState extends ConsumerState<VendorEarningsScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(
      () => ref.read(vendorEarningProvider).getVendorEarnings(),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(vendorEarningProvider);
    final earnings = state.earnings;
    return Scaffold(
      appBar: CustomAppBar(
        title: 'Vendor Earnings',
        actions: [
          IconButton(
            tooltip: 'Details',
            onPressed: () => context.push(AppRoutes.vendorEarningDetails),
            icon: const Icon(Icons.list_alt_rounded),
          ),
        ],
      ),
      body: state.isLoading && earnings == null
          ? const LoadingWidget(message: 'Loading earnings...')
          : earnings == null
              ? const Center(child: Text('Earnings not found.'))
              : ListView(
                  padding: const EdgeInsets.all(20),
                  children: [
                    VendorEarningCard(
                      title: 'Total Earnings',
                      amount: earnings.totalEarnings,
                    ),
                    const SizedBox(height: 12),
                    VendorEarningCard(
                      title: "Today's Earnings",
                      amount: earnings.todayEarnings,
                      icon: Icons.today_outlined,
                    ),
                    const SizedBox(height: 12),
                    VendorEarningCard(
                      title: 'Weekly Earnings',
                      amount: earnings.weeklyEarnings,
                      icon: Icons.date_range_outlined,
                    ),
                    const SizedBox(height: 12),
                    VendorEarningCard(
                      title: 'Monthly Earnings',
                      amount: earnings.monthlyEarnings,
                      icon: Icons.calendar_month_outlined,
                    ),
                    const SizedBox(height: 12),
                    VendorEarningCard(
                      title: 'Platform Commission',
                      amount: earnings.platformCommission,
                      icon: Icons.percent_rounded,
                    ),
                    const SizedBox(height: 12),
                    VendorEarningCard(
                      title: 'Pending Payout',
                      amount: earnings.pendingPayout,
                      icon: Icons.pending_actions_outlined,
                    ),
                    const SizedBox(height: 12),
                    VendorEarningCard(
                      title: 'Completed Payout',
                      amount: earnings.completedPayout,
                      icon: Icons.done_all_rounded,
                    ),
                    const SizedBox(height: 12),
                    VendorEarningCard(
                      title: 'Refunded Amount',
                      amount: earnings.refundedAmount,
                      icon: Icons.undo_rounded,
                    ),
                    if (earnings.transactions.isNotEmpty) ...[
                      const SizedBox(height: 22),
                      Text(
                        'Recent Transactions',
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                              fontWeight: FontWeight.w900,
                            ),
                      ),
                      const SizedBox(height: 10),
                      for (final item in earnings.transactions)
                        ListTile(
                          contentPadding: EdgeInsets.zero,
                          title: Text(item.title),
                          subtitle: Text(
                            DateFormat('MMM d, yyyy').format(item.createdAt),
                          ),
                          trailing: Text(CurrencyFormatter.lkr(item.amount)),
                        ),
                    ],
                  ],
                ),
    );
  }
}
