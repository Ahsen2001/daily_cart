import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../providers/vendor_earning_provider.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/loading_widget.dart';

class VendorEarningDetailsScreen extends ConsumerStatefulWidget {
  const VendorEarningDetailsScreen({super.key});

  @override
  ConsumerState<VendorEarningDetailsScreen> createState() =>
      _VendorEarningDetailsScreenState();
}

class _VendorEarningDetailsScreenState
    extends ConsumerState<VendorEarningDetailsScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(
      () => ref.read(vendorEarningProvider).getVendorEarningDetails(),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(vendorEarningProvider);
    final earnings = state.earnings;
    return Scaffold(
      appBar: const CustomAppBar(title: 'Earning Details'),
      body: state.isLoading && earnings == null
          ? const LoadingWidget(message: 'Loading earning details...')
          : earnings == null
              ? const Center(child: Text('Earning details not found.'))
              : ListView(
                  padding: const EdgeInsets.all(20),
                  children: [
                    for (final item in earnings.transactions)
                      Padding(
                        padding: const EdgeInsets.only(bottom: 12),
                        child: DailyCartCard(
                          child: ListTile(
                            contentPadding: EdgeInsets.zero,
                            title: Text(item.title),
                            subtitle: Text(
                              '${item.status} - ${DateFormat('MMM d, yyyy').format(item.createdAt)}',
                            ),
                            trailing: Text(
                              CurrencyFormatter.lkr(item.amount),
                              style:
                                  const TextStyle(fontWeight: FontWeight.w900),
                            ),
                          ),
                        ),
                      ),
                  ],
                ),
    );
  }
}
