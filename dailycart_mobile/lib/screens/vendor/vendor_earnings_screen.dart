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
import '../../widgets/vendor_bottom_navigation.dart';
import '../../widgets/error_widget.dart';

class VendorEarningsScreen extends ConsumerStatefulWidget {
  const VendorEarningsScreen({super.key});

  @override
  ConsumerState<VendorEarningsScreen> createState() =>
      _VendorEarningsScreenState();
}

class _VendorEarningsScreenState extends ConsumerState<VendorEarningsScreen> {
  DateTime? _from;
  DateTime? _to;
  @override
  void initState() {
    super.initState();
    Future.microtask(_load);
  }

  Future<void> _load() =>
      ref.read(vendorEarningProvider).getVendorEarnings(from: _from, to: _to);

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
          ? DailyCartErrorWidget(
              title: 'Earnings unavailable',
              message: state.errorMessage ?? 'Earnings not found.',
              onRetry: _load,
            )
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(20),
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: () => _pickDate(true),
                          icon: const Icon(Icons.calendar_today_outlined),
                          label: Text(
                            _from == null
                                ? 'From'
                                : DateFormat('MMM d, yyyy').format(_from!),
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: () => _pickDate(false),
                          icon: const Icon(Icons.event_outlined),
                          label: Text(
                            _to == null
                                ? 'To'
                                : DateFormat('MMM d, yyyy').format(_to!),
                          ),
                        ),
                      ),
                    ],
                  ),
                  if (_from != null || _to != null)
                    Align(
                      alignment: Alignment.centerRight,
                      child: TextButton(
                        onPressed: () {
                          setState(() {
                            _from = null;
                            _to = null;
                          });
                          _load();
                        },
                        child: const Text('Clear dates'),
                      ),
                    ),
                  const SizedBox(height: 8),
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
            ),
      bottomNavigationBar: const VendorBottomNavigation(currentIndex: 3),
    );
  }

  Future<void> _pickDate(bool from) async {
    final selected = await showDatePicker(
      context: context,
      initialDate: (from ? _from : _to) ?? DateTime.now(),
      firstDate: DateTime(2020),
      lastDate: DateTime.now(),
    );
    if (selected == null) return;
    setState(() {
      if (from) {
        _from = selected;
        if (_to != null && _to!.isBefore(selected)) _to = selected;
      } else {
        _to = selected;
        if (_from != null && _from!.isAfter(selected)) _from = selected;
      }
    });
    await _load();
  }
}
