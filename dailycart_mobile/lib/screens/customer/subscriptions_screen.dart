import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../models/subscription_model.dart';
import '../../providers/customer_extended_provider.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/error_widget.dart';
import '../../widgets/loading_widget.dart';

class SubscriptionsScreen extends ConsumerStatefulWidget {
  const SubscriptionsScreen({super.key});

  @override
  ConsumerState<SubscriptionsScreen> createState() =>
      _SubscriptionsScreenState();
}

class _SubscriptionsScreenState extends ConsumerState<SubscriptionsScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(
      () => ref.read(customerExtendedProvider).loadSubscriptions(),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(customerExtendedProvider);
    return Scaffold(
      appBar: const CustomAppBar(title: 'Subscriptions'),
      body: state.isLoading && state.subscriptions.isEmpty
          ? const LoadingWidget(message: 'Loading subscriptions...')
          : state.errorMessage != null && state.subscriptions.isEmpty
              ? DailyCartErrorWidget(
                  title: 'Unable to load subscriptions',
                  message: state.errorMessage!,
                  onRetry: () =>
                      ref.read(customerExtendedProvider).loadSubscriptions(),
                )
              : RefreshIndicator(
                  onRefresh: () =>
                      ref.read(customerExtendedProvider).loadSubscriptions(),
                  child: ListView(
                    padding: const EdgeInsets.all(20),
                    children: [
                      if (state.subscriptions.isEmpty)
                        const Padding(
                          padding: EdgeInsets.all(30),
                          child: Center(
                            child: Text(
                              'No subscriptions. Open an eligible product to subscribe.',
                            ),
                          ),
                        ),
                      for (final subscription in state.subscriptions)
                        _SubscriptionCard(subscription: subscription),
                    ],
                  ),
                ),
    );
  }
}

class _SubscriptionCard extends ConsumerWidget {
  const _SubscriptionCard({required this.subscription});

  final SubscriptionModel subscription;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: DailyCartCard(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              subscription.productName,
              style: Theme.of(context)
                  .textTheme
                  .titleMedium
                  ?.copyWith(fontWeight: FontWeight.w900),
            ),
            Text(
              '${subscription.quantity} × ${subscription.frequency} • ${CurrencyFormatter.lkr(subscription.totalAmount)}',
            ),
            Text(
              'Next: ${subscription.nextDeliveryDate?.toLocal().toString().split(' ').first ?? '-'}',
            ),
            Text('Status: ${subscription.status.toUpperCase()}'),
            const SizedBox(height: 8),
            Wrap(
              spacing: 8,
              children: [
                if (subscription.isActive)
                  TextButton(
                    onPressed: () => ref
                        .read(customerExtendedProvider)
                        .changeSubscriptionStatus(subscription.id, 'pause'),
                    child: const Text('Pause'),
                  ),
                if (subscription.isPaused)
                  TextButton(
                    onPressed: () => ref
                        .read(customerExtendedProvider)
                        .changeSubscriptionStatus(subscription.id, 'resume'),
                    child: const Text('Resume'),
                  ),
                if (subscription.isActive || subscription.isPaused)
                  TextButton(
                    onPressed: () => ref
                        .read(customerExtendedProvider)
                        .changeSubscriptionStatus(subscription.id, 'cancel'),
                    child: const Text('Cancel'),
                  ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
