import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

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
  DateTime? _from;
  DateTime? _to;

  @override
  void initState() {
    super.initState();
    Future.microtask(_load);
  }

  Future<bool> _load() =>
      ref.read(riderProvider).getRiderReport(from: _from, to: _to);

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
              onRetry: _load,
            )
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(20),
                children: [
                  DailyCartCard(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Report period',
                          style: Theme.of(context).textTheme.titleMedium
                              ?.copyWith(fontWeight: FontWeight.w900),
                        ),
                        const SizedBox(height: 10),
                        Row(
                          children: [
                            Expanded(
                              child: OutlinedButton.icon(
                                onPressed: () => _pick(true),
                                icon: const Icon(Icons.calendar_today_outlined),
                                label: Text(
                                  _from == null
                                      ? 'From'
                                      : DateFormat(
                                          'MMM d, yyyy',
                                        ).format(_from!),
                                ),
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: OutlinedButton.icon(
                                onPressed: () => _pick(false),
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
                      ],
                    ),
                  ),
                  const SizedBox(height: 14),
                  DailyCartCard(
                    child: Column(
                      children: [
                        for (final entry in _summaryEntries(report))
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
                  if (_map(report?['earnings_policy']).isNotEmpty) ...[
                    const SizedBox(height: 14),
                    DailyCartCard(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Earnings policy',
                            style: Theme.of(context).textTheme.titleMedium
                                ?.copyWith(fontWeight: FontWeight.w900),
                          ),
                          const SizedBox(height: 6),
                          _policyRow(
                            'Base per delivery',
                            _map(
                              report?['earnings_policy'],
                            )['base_per_delivery'],
                          ),
                          _policyRow(
                            'Per km bonus',
                            _map(report?['earnings_policy'])['per_km_bonus'],
                          ),
                          _policyRow(
                            'Peak bonus',
                            _map(report?['earnings_policy'])['peak_bonus'],
                          ),
                        ],
                      ),
                    ),
                  ],
                  if (_rows(report?['deliveries']).isNotEmpty) ...[
                    const SizedBox(height: 20),
                    Text(
                      'Delivery history',
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    const SizedBox(height: 8),
                    for (final delivery in _rows(report?['deliveries']))
                      Card(
                        child: ListTile(
                          title: Text(
                            (delivery['order_number'] ??
                                    'Delivery #${delivery['id']}')
                                .toString(),
                            style: const TextStyle(fontWeight: FontWeight.w800),
                          ),
                          subtitle: Text(
                            '${delivery['customer_name'] ?? 'Customer'} · ${(delivery['status'] ?? '').toString().replaceAll('_', ' ')}',
                          ),
                          trailing: Text(
                            CurrencyFormatter.lkr(
                              _number(delivery['rider_payout']),
                            ),
                          ),
                        ),
                      ),
                  ],
                ],
              ),
            ),
    );
  }

  double _number(Object? value) {
    if (value is num) return value.toDouble();
    return double.tryParse(value?.toString() ?? '') ?? 0;
  }

  List<MapEntry<String, dynamic>> _summaryEntries(
    Map<String, dynamic>? report,
  ) {
    if (report == null) return const [];
    const keys = [
      'total_deliveries',
      'completed_deliveries',
      'failed_deliveries',
      'active_deliveries',
      'completion_rate',
      'total_earnings',
    ];
    return keys
        .where(report.containsKey)
        .map((key) => MapEntry(key, report[key]))
        .toList();
  }

  Map<String, dynamic> _map(Object? value) => value is Map
      ? value.map((key, item) => MapEntry(key.toString(), item))
      : const {};

  List<Map<String, dynamic>> _rows(Object? value) => value is List
      ? value
            .whereType<Map>()
            .map(
              (row) => row.map((key, item) => MapEntry(key.toString(), item)),
            )
            .toList()
      : const [];

  Widget _policyRow(String label, Object? amount) => ListTile(
    dense: true,
    contentPadding: EdgeInsets.zero,
    title: Text(label),
    trailing: Text(CurrencyFormatter.lkr(_number(amount))),
  );

  Future<void> _pick(bool isFrom) async {
    final picked = await showDatePicker(
      context: context,
      initialDate: (isFrom ? _from : _to) ?? DateTime.now(),
      firstDate: DateTime(2020),
      lastDate: DateTime.now(),
    );
    if (picked == null) return;
    setState(() {
      if (isFrom) {
        _from = picked;
        if (_to != null && _to!.isBefore(picked)) _to = picked;
      } else {
        _to = picked;
        if (_from != null && _from!.isAfter(picked)) _from = picked;
      }
    });
    await _load();
  }
}
