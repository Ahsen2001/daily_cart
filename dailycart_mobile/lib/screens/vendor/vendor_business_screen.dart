import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../providers/vendor_business_provider.dart';
import '../../models/vendor_business_model.dart';
import '../../routes/app_routes.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/error_widget.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/app_drawer.dart';

enum VendorBusinessSection {
  wallet,
  refunds,
  coupons,
  promotions,
  subscriptions,
  scheduledOrders,
  reports,
}

class VendorBusinessScreen extends ConsumerStatefulWidget {
  const VendorBusinessScreen({required this.section, super.key});

  final VendorBusinessSection section;

  @override
  ConsumerState<VendorBusinessScreen> createState() =>
      _VendorBusinessScreenState();
}

class _VendorBusinessScreenState extends ConsumerState<VendorBusinessScreen> {
  DateTime? _reportFrom;
  DateTime? _reportTo;
  String _reportStatus = 'all';
  @override
  void initState() {
    super.initState();
    Future.microtask(_load);
  }

  Future<bool> _load() {
    final provider = ref.read(vendorBusinessProvider);
    return switch (widget.section) {
      VendorBusinessSection.wallet => provider.loadWallet(),
      VendorBusinessSection.refunds => provider.loadRefunds(),
      VendorBusinessSection.coupons => provider.loadCoupons(),
      VendorBusinessSection.promotions => provider.loadPromotions(),
      VendorBusinessSection.subscriptions => provider.loadSubscriptions(),
      VendorBusinessSection.scheduledOrders => provider.loadScheduledOrders(),
      VendorBusinessSection.reports => provider.loadReport(
        from: _reportFrom,
        to: _reportTo,
        orderStatus: _reportStatus,
      ),
    };
  }

  String get _title => switch (widget.section) {
    VendorBusinessSection.wallet => 'Wallet & Payouts',
    VendorBusinessSection.refunds => 'Refund Handling',
    VendorBusinessSection.coupons => 'Coupons',
    VendorBusinessSection.promotions => 'Promotions',
    VendorBusinessSection.subscriptions => 'Subscriptions',
    VendorBusinessSection.scheduledOrders => 'Scheduled Orders',
    VendorBusinessSection.reports => 'Reports',
  };

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(vendorBusinessProvider);
    return Scaffold(
      appBar: CustomAppBar(title: _title),
      drawer: const AppDrawer(roleName: 'Vendor'),
      body: state.isLoading && !_hasData(state)
          ? LoadingWidget(message: 'Loading ${_title.toLowerCase()}...')
          : state.errorMessage != null && !_hasData(state)
          ? DailyCartErrorWidget(
              title: 'Unable to load $_title',
              message: state.errorMessage!,
              onRetry: _load,
            )
          : RefreshIndicator(onRefresh: _load, child: _body(state)),
      floatingActionButton: switch (widget.section) {
        VendorBusinessSection.wallet => FloatingActionButton.extended(
          onPressed: _requestPayout,
          icon: const Icon(Icons.payments_outlined),
          label: const Text('Request payout'),
        ),
        VendorBusinessSection.coupons => FloatingActionButton.extended(
          onPressed: _createCoupon,
          icon: const Icon(Icons.add),
          label: const Text('Coupon'),
        ),
        VendorBusinessSection.promotions => FloatingActionButton.extended(
          onPressed: _createPromotion,
          icon: const Icon(Icons.add),
          label: const Text('Promotion'),
        ),
        _ => null,
      },
    );
  }

  Widget _body(VendorBusinessProvider state) {
    return switch (widget.section) {
      VendorBusinessSection.wallet => _wallet(state),
      VendorBusinessSection.refunds => _refunds(state),
      VendorBusinessSection.coupons => _coupons(state),
      VendorBusinessSection.promotions => _promotions(state),
      VendorBusinessSection.subscriptions => _subscriptions(state),
      VendorBusinessSection.scheduledOrders => _scheduled(state),
      VendorBusinessSection.reports => _reports(state),
    };
  }

  Widget _wallet(VendorBusinessProvider state) {
    final wallet = state.wallet;
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        DailyCartCard(
          child: Column(
            children: [
              _value('Available', wallet?.availableBalance ?? 0),
              _value('Current balance', wallet?.balance ?? 0),
              _value('Pending earnings', wallet?.pendingBalance ?? 0),
              _value('Lifetime earnings', wallet?.totalEarned ?? 0),
              _value('Paid out', wallet?.totalWithdrawn ?? 0),
            ],
          ),
        ),
        const SizedBox(height: 18),
        const Text('Payout requests'),
        for (final payout in wallet?.payouts ?? const [])
          ListTile(
            contentPadding: EdgeInsets.zero,
            title: Text(CurrencyFormatter.lkr(payout.amount)),
            subtitle: Text('${payout.bankName} • ${payout.accountNumber}'),
            trailing: Text(payout.status.toUpperCase()),
          ),
      ],
    );
  }

  Widget _refunds(VendorBusinessProvider state) {
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        if (state.refunds.isEmpty)
          const Center(
            child: Padding(
              padding: EdgeInsets.all(30),
              child: Text('No refund requests.'),
            ),
          ),
        for (final refund in state.refunds)
          Padding(
            padding: const EdgeInsets.only(bottom: 12),
            child: DailyCartCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    refund.orderNumber,
                    style: const TextStyle(fontWeight: FontWeight.w900),
                  ),
                  Text('${refund.customerName} • ${refund.status}'),
                  Text(CurrencyFormatter.lkr(refund.amount)),
                  Text(refund.reason),
                  if (refund.vendorNote != null)
                    Text('Your response: ${refund.vendorNote}'),
                  TextButton(
                    onPressed: () => _respondToRefund(refund.id),
                    child: const Text('Respond for admin review'),
                  ),
                ],
              ),
            ),
          ),
      ],
    );
  }

  Widget _coupons(VendorBusinessProvider state) {
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        if (state.coupons.isEmpty)
          const Center(
            child: Padding(
              padding: EdgeInsets.all(30),
              child: Text('No coupons.'),
            ),
          ),
        for (final coupon in state.coupons)
          ListTile(
            contentPadding: EdgeInsets.zero,
            leading: const CircleAvatar(child: Icon(Icons.confirmation_number)),
            title: Text('${coupon.code} — ${coupon.title}'),
            subtitle: Text(
              '${coupon.discountValue} ${coupon.discountType} • ${coupon.status}',
            ),
            onTap: () => _createCoupon(coupon: coupon),
            trailing: PopupMenuButton<String>(
              onSelected: (value) => value == 'edit'
                  ? _createCoupon(coupon: coupon)
                  : _deleteCoupon(coupon),
              itemBuilder: (_) => const [
                PopupMenuItem(value: 'edit', child: Text('Edit')),
                PopupMenuItem(value: 'delete', child: Text('Delete')),
              ],
            ),
          ),
      ],
    );
  }

  Widget _promotions(VendorBusinessProvider state) {
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        if (state.promotions.isEmpty)
          const Center(
            child: Padding(
              padding: EdgeInsets.all(30),
              child: Text('No promotions.'),
            ),
          ),
        for (final promotion in state.promotions)
          ListTile(
            contentPadding: EdgeInsets.zero,
            leading: const CircleAvatar(child: Icon(Icons.local_offer)),
            title: Text(promotion.title),
            subtitle: Text(
              '${promotion.productName} • ${promotion.discountValue}% • ${promotion.status}',
            ),
            onTap: () => _createPromotion(promotion: promotion),
            trailing: PopupMenuButton<String>(
              onSelected: (value) => value == 'edit'
                  ? _createPromotion(promotion: promotion)
                  : _deletePromotion(promotion),
              itemBuilder: (_) => const [
                PopupMenuItem(value: 'edit', child: Text('Edit')),
                PopupMenuItem(value: 'delete', child: Text('Delete')),
              ],
            ),
          ),
      ],
    );
  }

  Widget _subscriptions(VendorBusinessProvider state) {
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        if (state.subscriptionStockRequirements.isNotEmpty) ...[
          Text(
            'Upcoming stock requirements',
            style: Theme.of(
              context,
            ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w900),
          ),
          const SizedBox(height: 8),
          DailyCartCard(
            child: Column(
              children: [
                for (final stock in state.subscriptionStockRequirements)
                  ListTile(
                    contentPadding: EdgeInsets.zero,
                    title: Text(stock.productName),
                    subtitle: stock.variantName.isEmpty
                        ? null
                        : Text(stock.variantName),
                    trailing: Text(
                      '${stock.requiredQuantity} needed',
                      style: const TextStyle(fontWeight: FontWeight.w800),
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(height: 18),
        ],
        if (state.subscriptions.isEmpty)
          const Center(
            child: Padding(
              padding: EdgeInsets.all(30),
              child: Text('No active subscriptions.'),
            ),
          ),
        for (final item in state.subscriptions)
          ListTile(
            contentPadding: EdgeInsets.zero,
            title: Text(item.productName),
            subtitle: Text(
              '${item.customerName} • ${item.quantity} ${item.frequency}',
            ),
            trailing: Text(item.status.toUpperCase()),
          ),
      ],
    );
  }

  Widget _scheduled(VendorBusinessProvider state) {
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        if (state.scheduledOrders.isEmpty)
          const Center(
            child: Padding(
              padding: EdgeInsets.all(30),
              child: Text('No scheduled orders.'),
            ),
          ),
        for (final order in state.scheduledOrders)
          ListTile(
            contentPadding: EdgeInsets.zero,
            onTap: () =>
                context.push('${AppRoutes.vendorOrderDetails}/${order.id}'),
            title: Text(order.orderNumber),
            subtitle: Text(
              order.scheduledDeliveryTime?.toLocal().toString() ?? '-',
            ),
            trailing: Text(order.status.toUpperCase()),
          ),
      ],
    );
  }

  Widget _reports(VendorBusinessProvider state) {
    final report = state.report;
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        DailyCartCard(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Report filters',
                style: Theme.of(
                  context,
                ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w900),
              ),
              const SizedBox(height: 10),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () => _pickReportDate(true),
                      icon: const Icon(Icons.calendar_today_outlined),
                      label: Text(
                        _reportFrom == null
                            ? 'From'
                            : DateFormat('MMM d, yyyy').format(_reportFrom!),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () => _pickReportDate(false),
                      icon: const Icon(Icons.event_outlined),
                      label: Text(
                        _reportTo == null
                            ? 'To'
                            : DateFormat('MMM d, yyyy').format(_reportTo!),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              DropdownButtonFormField<String>(
                initialValue: _reportStatus,
                decoration: const InputDecoration(labelText: 'Order status'),
                items: const [
                  DropdownMenuItem(value: 'all', child: Text('All statuses')),
                  DropdownMenuItem(value: 'pending', child: Text('Pending')),
                  DropdownMenuItem(
                    value: 'confirmed',
                    child: Text('Confirmed'),
                  ),
                  DropdownMenuItem(value: 'packed', child: Text('Packed')),
                  DropdownMenuItem(
                    value: 'delivered',
                    child: Text('Delivered'),
                  ),
                  DropdownMenuItem(
                    value: 'cancelled',
                    child: Text('Cancelled'),
                  ),
                ],
                onChanged: (value) {
                  setState(() => _reportStatus = value ?? 'all');
                  _load();
                },
              ),
              if (_reportFrom != null || _reportTo != null)
                Align(
                  alignment: Alignment.centerRight,
                  child: TextButton(
                    onPressed: () {
                      setState(() {
                        _reportFrom = null;
                        _reportTo = null;
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
              for (final entry
                  in report?.summary.entries.toList() ??
                      <MapEntry<String, dynamic>>[])
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  title: Text(entry.key.replaceAll('_', ' ')),
                  trailing: Text(entry.value.toString()),
                ),
            ],
          ),
        ),
        const SizedBox(height: 18),
        if (report?.charts.isNotEmpty == true) ...[
          Text(
            'Performance trend',
            style: Theme.of(
              context,
            ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w900),
          ),
          const SizedBox(height: 8),
          DailyCartCard(
            child: Column(
              children: report!.charts.entries
                  .map(
                    (entry) => ListTile(
                      contentPadding: EdgeInsets.zero,
                      title: Text(entry.key.replaceAll('_', ' ')),
                      subtitle: Text(_chartSummary(entry.value)),
                    ),
                  )
                  .toList(),
            ),
          ),
          const SizedBox(height: 18),
        ],
        const Text('Best selling'),
        for (final row in report?.bestSelling ?? const [])
          ListTile(
            contentPadding: EdgeInsets.zero,
            title: Text((row['product_name'] ?? '').toString()),
            subtitle: Text('Sold: ${row['sold_quantity'] ?? 0}'),
            trailing: Text(CurrencyFormatter.lkr(_number(row['revenue']))),
          ),
        const SizedBox(height: 18),
        const Text('Low stock'),
        for (final row in report?.lowStock ?? const [])
          ListTile(
            contentPadding: EdgeInsets.zero,
            title: Text((row['name'] ?? '').toString()),
            trailing: Text('${row['stock_quantity'] ?? 0} left'),
          ),
      ],
    );
  }

  Widget _value(String label, double amount) {
    return ListTile(
      contentPadding: EdgeInsets.zero,
      title: Text(label),
      trailing: Text(
        CurrencyFormatter.lkr(amount),
        style: const TextStyle(fontWeight: FontWeight.w900),
      ),
    );
  }

  bool _hasData(VendorBusinessProvider state) {
    return switch (widget.section) {
      VendorBusinessSection.wallet => state.wallet != null,
      VendorBusinessSection.refunds => state.refunds.isNotEmpty,
      VendorBusinessSection.coupons => state.coupons.isNotEmpty,
      VendorBusinessSection.promotions => state.promotions.isNotEmpty,
      VendorBusinessSection.subscriptions => state.subscriptions.isNotEmpty,
      VendorBusinessSection.scheduledOrders => state.scheduledOrders.isNotEmpty,
      VendorBusinessSection.reports => state.report != null,
    };
  }

  Future<void> _requestPayout() async {
    final amount = TextEditingController();
    final bank = TextEditingController();
    final accountName = TextEditingController();
    final accountNumber = TextEditingController();
    final ok = await _formDialog(
      title: 'Request payout',
      fields: [
        _field(amount, 'Amount', number: true),
        _field(bank, 'Bank name'),
        _field(accountName, 'Account name'),
        _field(accountNumber, 'Account number'),
      ],
    );
    if (ok) {
      await ref.read(vendorBusinessProvider).requestPayout({
        'amount': double.tryParse(amount.text) ?? 0,
        'bank_name': bank.text.trim(),
        'account_name': accountName.text.trim(),
        'account_number': accountNumber.text.trim(),
      });
    }
    for (final controller in [amount, bank, accountName, accountNumber]) {
      controller.dispose();
    }
  }

  Future<void> _respondToRefund(int id) async {
    final note = TextEditingController();
    final ok = await _formDialog(
      title: 'Refund response',
      fields: [_field(note, 'Response for administrator', lines: 4)],
    );
    if (ok && note.text.trim().isNotEmpty) {
      await ref
          .read(vendorBusinessProvider)
          .respondToRefund(id, note.text.trim());
    }
    note.dispose();
  }

  Future<void> _createCoupon({VendorCouponModel? coupon}) async {
    final code = TextEditingController(text: coupon?.code ?? '');
    final title = TextEditingController(text: coupon?.title ?? '');
    final discount = TextEditingController(
      text: coupon?.discountValue.toString() ?? '',
    );
    final minimum = TextEditingController(
      text: coupon?.minimumOrderAmount.toString() ?? '0',
    );
    final ok = await _formDialog(
      title: coupon == null ? 'Create coupon' : 'Edit coupon',
      fields: [
        _field(code, 'Code'),
        _field(title, 'Title'),
        _field(discount, 'Discount percentage', number: true),
        _field(minimum, 'Minimum order', number: true),
      ],
    );
    if (ok) {
      final starts = coupon?.startsAt ?? DateTime.now();
      await ref.read(vendorBusinessProvider).saveCoupon({
        'code': code.text.trim().toUpperCase(),
        'title': title.text.trim(),
        'discount_type': 'percentage',
        'discount_value': double.tryParse(discount.text) ?? 0,
        'minimum_order_amount': double.tryParse(minimum.text) ?? 0,
        'starts_at': starts.toIso8601String(),
        'expires_at':
            (coupon?.expiresAt ?? starts.add(const Duration(days: 30)))
                .toIso8601String(),
        'status': coupon?.status ?? 'active',
      }, id: coupon?.id);
    }
    for (final controller in [code, title, discount, minimum]) {
      controller.dispose();
    }
  }

  Future<void> _createPromotion({VendorPromotionModel? promotion}) async {
    final title = TextEditingController(text: promotion?.title ?? '');
    final productId = TextEditingController(
      text: promotion?.targetId == 0 ? '' : promotion?.targetId.toString(),
    );
    final discount = TextEditingController(
      text: promotion?.discountValue.toString() ?? '',
    );
    final ok = await _formDialog(
      title: promotion == null ? 'Create promotion' : 'Edit promotion',
      fields: [
        _field(title, 'Title'),
        _field(productId, 'Product ID', number: true),
        _field(discount, 'Discount percentage', number: true),
      ],
    );
    if (ok) {
      final starts = promotion?.startsAt ?? DateTime.now();
      await ref.read(vendorBusinessProvider).savePromotion({
        'title': title.text.trim(),
        'promotion_type': promotion?.promotionType ?? 'featured_offer',
        'target_id': int.tryParse(productId.text) ?? 0,
        'discount_type': promotion?.discountType ?? 'percentage',
        'discount_value': double.tryParse(discount.text) ?? 0,
        'starts_at': starts.toIso8601String(),
        'ends_at': (promotion?.endsAt ?? starts.add(const Duration(days: 30)))
            .toIso8601String(),
        'status': promotion?.status ?? 'active',
      }, id: promotion?.id);
    }
    for (final controller in [title, productId, discount]) {
      controller.dispose();
    }
  }

  Future<void> _deleteCoupon(VendorCouponModel coupon) async {
    if (!await _confirmDelete('coupon ${coupon.code}')) return;
    await ref.read(vendorBusinessProvider).deleteCoupon(coupon.id);
  }

  Future<void> _deletePromotion(VendorPromotionModel promotion) async {
    if (!await _confirmDelete('promotion ${promotion.title}')) return;
    await ref.read(vendorBusinessProvider).deletePromotion(promotion.id);
  }

  Future<bool> _confirmDelete(String label) async =>
      await showDialog<bool>(
        context: context,
        builder: (context) => AlertDialog(
          title: const Text('Confirm deletion'),
          content: Text('Delete $label?'),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context, false),
              child: const Text('Cancel'),
            ),
            FilledButton(
              onPressed: () => Navigator.pop(context, true),
              child: const Text('Delete'),
            ),
          ],
        ),
      ) ??
      false;

  Widget _field(
    TextEditingController controller,
    String label, {
    bool number = false,
    int lines = 1,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: TextField(
        controller: controller,
        maxLines: lines,
        keyboardType: number
            ? const TextInputType.numberWithOptions(decimal: true)
            : TextInputType.text,
        decoration: InputDecoration(labelText: label),
      ),
    );
  }

  Future<bool> _formDialog({
    required String title,
    required List<Widget> fields,
  }) async {
    return await showDialog<bool>(
          context: context,
          builder: (context) => AlertDialog(
            title: Text(title),
            content: SingleChildScrollView(
              child: Column(mainAxisSize: MainAxisSize.min, children: fields),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context, false),
                child: const Text('Cancel'),
              ),
              FilledButton(
                onPressed: () => Navigator.pop(context, true),
                child: const Text('Save'),
              ),
            ],
          ),
        ) ??
        false;
  }

  double _number(Object? value) {
    if (value is num) return value.toDouble();
    return double.tryParse(value?.toString() ?? '') ?? 0;
  }

  String _chartSummary(Object? value) {
    if (value is List) return '${value.length} data points';
    if (value is Map) return '${value.length} data points';
    return value?.toString() ?? '-';
  }

  Future<void> _pickReportDate(bool from) async {
    final selected = await showDatePicker(
      context: context,
      initialDate: (from ? _reportFrom : _reportTo) ?? DateTime.now(),
      firstDate: DateTime(2020),
      lastDate: DateTime.now(),
    );
    if (selected == null) return;
    setState(() {
      if (from) {
        _reportFrom = selected;
        if (_reportTo != null && _reportTo!.isBefore(selected)) {
          _reportTo = selected;
        }
      } else {
        _reportTo = selected;
        if (_reportFrom != null && _reportFrom!.isAfter(selected)) {
          _reportFrom = selected;
        }
      }
    });
    await _load();
  }
}
