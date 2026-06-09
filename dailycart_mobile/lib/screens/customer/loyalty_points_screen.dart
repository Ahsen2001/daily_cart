import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/loyalty_provider.dart';
import '../../routes/app_routes.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/loyalty_balance_card.dart';

class LoyaltyPointsScreen extends ConsumerStatefulWidget {
  const LoyaltyPointsScreen({super.key});

  @override
  ConsumerState<LoyaltyPointsScreen> createState() =>
      _LoyaltyPointsScreenState();
}

class _LoyaltyPointsScreenState extends ConsumerState<LoyaltyPointsScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(loyaltyProvider).getBalance());
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(loyaltyProvider);
    return Scaffold(
      appBar: const CustomAppBar(title: 'Loyalty Points'),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          LoyaltyBalanceCard(
            points: state.loyaltyBalance,
            onHistory: () => context.push(AppRoutes.loyaltyHistory),
            onRedeem: state.loyaltyBalance > 0
                ? () {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content:
                            Text('Redeem points during checkout placeholder.'),
                      ),
                    );
                  }
                : null,
          ),
        ],
      ),
    );
  }
}
