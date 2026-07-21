import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../providers/rider_provider.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/error_widget.dart';
import '../../widgets/loading_widget.dart';

class RiderReportScreen extends ConsumerStatefulWidget {
  const RiderReportScreen({super.key});

  @override
  ConsumerState<RiderReportScreen> createState() => _RiderReportScreenState();
}

class _RiderReportScreenState extends ConsumerState<RiderReportScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(riderProvider).getRiderReport());
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(riderProvider);
    final report = state.report;
    return Scaffold(
      appBar: const CustomAppBar(title: 'Rider Report'),
      body: state.isLoading && report == null
          ? const LoadingWidget(message: 'Loading report...')
          : state.errorMessage != null && report == null
          ? DailyCartErrorWidget(
              title: 'Unable to load report',
              message: state.errorMessage!,
              onRetry: () => ref.read(riderProvider).getRiderReport(),
            )
          : RefreshIndicator(
              onRefresh: () => ref.read(riderProvider).getRiderReport(),
              child: ListView(
                padding: const EdgeInsets.all(20),
                children: [
                  DailyCartCard(
                    child: Column(
                      children: [
                        for (final entry
                            in report?.entries.toList() ??
                                <MapEntry<String, dynamic>>[])
                          ListTile(
                            contentPadding: EdgeInsets.zero,
                            title: Text(entry.key.replaceAll('_', ' ')),
                            trailing: Text(
                              entry.key.contains('earnings')
                                  ? CurrencyFormatter.lkr(_number(entry.value))
                                  : entry.value.toString(),
                            ),
                          ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
    );
  }

  double _number(Object? value) {
    if (value is num) return value.toDouble();
    return double.tryParse(value?.toString() ?? '') ?? 0;
  }
}
