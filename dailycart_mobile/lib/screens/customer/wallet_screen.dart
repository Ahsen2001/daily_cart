import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../providers/customer_extended_provider.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/error_widget.dart';
import '../../widgets/loading_widget.dart';

class WalletScreen extends ConsumerStatefulWidget {
  const WalletScreen({super.key});

  @override
  ConsumerState<WalletScreen> createState() => _WalletScreenState();
}

class _WalletScreenState extends ConsumerState<WalletScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(customerExtendedProvider).loadWallet());
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(customerExtendedProvider);
    final wallet = state.wallet;
    return Scaffold(
      appBar: const CustomAppBar(title: 'Wallet'),
      body: state.isLoading && wallet == null
          ? const LoadingWidget(message: 'Loading wallet...')
          : state.errorMessage != null && wallet == null
              ? DailyCartErrorWidget(
                  title: 'Unable to load wallet',
                  message: state.errorMessage!,
                  onRetry: () =>
                      ref.read(customerExtendedProvider).loadWallet(),
                )
              : RefreshIndicator(
                  onRefresh: () =>
                      ref.read(customerExtendedProvider).loadWallet(),
                  child: ListView(
                    padding: const EdgeInsets.all(20),
                    children: [
                      DailyCartCard(
                        child: Column(
                          children: [
                            const Text('Available balance'),
                            const SizedBox(height: 8),
                            Text(
                              CurrencyFormatter.lkr(wallet?.balance ?? 0),
                              style: Theme.of(context)
                                  .textTheme
                                  .headlineMedium
                                  ?.copyWith(fontWeight: FontWeight.w900),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 18),
                      Text(
                        'Transactions',
                        style: Theme.of(context)
                            .textTheme
                            .titleMedium
                            ?.copyWith(fontWeight: FontWeight.w900),
                      ),
                      const SizedBox(height: 8),
                      if (wallet?.transactions.isEmpty ?? true)
                        const Padding(
                          padding: EdgeInsets.all(24),
                          child: Center(child: Text('No wallet transactions.')),
                        )
                      else
                        for (final transaction in wallet!.transactions)
                          ListTile(
                            contentPadding: EdgeInsets.zero,
                            leading: CircleAvatar(
                              child: Icon(
                                transaction.type == 'credit'
                                    ? Icons.south_west
                                    : Icons.north_east,
                              ),
                            ),
                            title: Text(transaction.description),
                            subtitle: Text(
                              transaction.createdAt?.toLocal().toString() ?? '',
                            ),
                            trailing: Text(
                              '${transaction.type == 'credit' ? '+' : '-'}${CurrencyFormatter.lkr(transaction.amount)}',
                            ),
                          ),
                    ],
                  ),
                ),
    );
  }
}
