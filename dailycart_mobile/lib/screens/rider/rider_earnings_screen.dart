import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../providers/rider_earning_provider.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/earnings_card.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/error_widget.dart';
import '../../widgets/rider_bottom_navigation.dart';

class RiderEarningsScreen extends ConsumerStatefulWidget {
  const RiderEarningsScreen({super.key});

  @override
  ConsumerState<RiderEarningsScreen> createState() =>
      _RiderEarningsScreenState();
}

class _RiderEarningsScreenState extends ConsumerState<RiderEarningsScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(riderEarningProvider).getRiderEarnings());
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(riderEarningProvider);
    final earnings = state.earnings;
    return Scaffold(
      appBar: const CustomAppBar(title: 'Rider Earnings'),
      body: state.isLoading && earnings == null
          ? const LoadingWidget(message: 'Loading rider earnings...')
          : earnings == null
          ? DailyCartErrorWidget(
              title: 'Earnings unavailable',
              message: state.errorMessage ?? 'Earnings not found.',
              onRetry: () => ref.read(riderEarningProvider).getRiderEarnings(),
            )
          : RefreshIndicator(
              onRefresh: () =>
                  ref.read(riderEarningProvider).getRiderEarnings(),
              child: ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(20),
                children: [
                  EarningsCard(
                    title: 'Total Earnings',
                    amount: earnings.totalEarnings,
                  ),
                  const SizedBox(height: 12),
                  EarningsCard(
                    title: 'Daily Earnings',
                    amount: earnings.dailyEarnings,
                  ),
                  const SizedBox(height: 12),
                  EarningsCard(
                    title: 'Weekly Earnings',
                    amount: earnings.weeklyEarnings,
                  ),
                  const SizedBox(height: 12),
                  EarningsCard(
                    title: 'Monthly Earnings',
                    amount: earnings.monthlyEarnings,
                  ),
                  const SizedBox(height: 16),
                  DailyCartCard(
                    child: Row(
                      children: [
                        Expanded(
                          child: Text(
                            'Completed: ${earnings.completedDeliveryCount}',
                            style: const TextStyle(fontWeight: FontWeight.w900),
                          ),
                        ),
                        Text('Failed: ${earnings.failedDeliveryCount}'),
                      ],
                    ),
                  ),
                  if (earnings.history.isNotEmpty) ...[
                    const SizedBox(height: 22),
                    Text(
                      'Earnings History',
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    const SizedBox(height: 10),
                    for (final item in earnings.history)
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
      bottomNavigationBar: const RiderBottomNavigation(currentIndex: 2),
    );
  }
}
