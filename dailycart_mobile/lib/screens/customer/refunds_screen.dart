import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../providers/customer_extended_provider.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/error_widget.dart';
import '../../widgets/loading_widget.dart';

class RefundsScreen extends ConsumerStatefulWidget {
  const RefundsScreen({super.key});

  @override
  ConsumerState<RefundsScreen> createState() => _RefundsScreenState();
}

class _RefundsScreenState extends ConsumerState<RefundsScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(customerExtendedProvider).loadRefunds());
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(customerExtendedProvider);
    return Scaffold(
      appBar: const CustomAppBar(title: 'Refunds'),
      body: state.isLoading && state.refunds.isEmpty
          ? const LoadingWidget(message: 'Loading refunds...')
          : state.errorMessage != null && state.refunds.isEmpty
              ? DailyCartErrorWidget(
                  title: 'Unable to load refunds',
                  message: state.errorMessage!,
                  onRetry: () =>
                      ref.read(customerExtendedProvider).loadRefunds(),
                )
              : RefreshIndicator(
                  onRefresh: () =>
                      ref.read(customerExtendedProvider).loadRefunds(),
                  child: ListView(
                    padding: const EdgeInsets.all(20),
                    children: [
                      if (state.refunds.isEmpty)
                        const Padding(
                          padding: EdgeInsets.all(30),
                          child: Center(child: Text('No refund requests.')),
                        ),
                      for (final refund in state.refunds)
                        Padding(
                          padding: const EdgeInsets.only(bottom: 12),
                          child: DailyCartCard(
                            child: ListTile(
                              contentPadding: EdgeInsets.zero,
                              title: Text(
                                refund.orderNumber.isEmpty
                                    ? 'Order #${refund.orderId}'
                                    : refund.orderNumber,
                              ),
                              subtitle: Text(refund.reason),
                              trailing: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                crossAxisAlignment: CrossAxisAlignment.end,
                                children: [
                                  Text(CurrencyFormatter.lkr(refund.amount)),
                                  Text(refund.status.toUpperCase()),
                                ],
                              ),
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
    );
  }
}
