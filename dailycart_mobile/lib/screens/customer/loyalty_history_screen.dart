import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../providers/loyalty_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state_widget.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/loyalty_history_card.dart';

class LoyaltyHistoryScreen extends ConsumerStatefulWidget {
  const LoyaltyHistoryScreen({super.key});

  @override
  ConsumerState<LoyaltyHistoryScreen> createState() =>
      _LoyaltyHistoryScreenState();
}

class _LoyaltyHistoryScreenState extends ConsumerState<LoyaltyHistoryScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(loyaltyProvider).getHistory());
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(loyaltyProvider);
    return Scaffold(
      appBar: const CustomAppBar(title: 'Loyalty History'),
      body: state.isLoading && state.loyaltyHistory.isEmpty
          ? const LoadingWidget(message: 'Loading loyalty history...')
          : state.loyaltyHistory.isEmpty
              ? const EmptyStateWidget(
                  title: 'No point history',
                  message: 'Earned, redeemed, reversed, and adjusted points appear here.',
                  icon: Icons.history_rounded,
                )
              : ListView.separated(
                  padding: const EdgeInsets.all(20),
                  itemBuilder: (context, index) =>
                      LoyaltyHistoryCard(item: state.loyaltyHistory[index]),
                  separatorBuilder: (context, index) =>
                      const SizedBox(height: 14),
                  itemCount: state.loyaltyHistory.length,
                ),
    );
  }
}
