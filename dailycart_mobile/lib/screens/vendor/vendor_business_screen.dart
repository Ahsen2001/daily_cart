import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/vendor_business_provider.dart';
import '../../routes/app_routes.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/error_widget.dart';
import '../../widgets/loading_widget.dart';

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
      VendorBusinessSection.scheduledOrders =>
        provider.loadScheduledOrders(),
      VendorBusinessSection.reports => provider.loadReport(),
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
      body: state.isLoading && !_hasData(state)
          ? LoadingWidget(message: 'Loading ${_title.toLowerCase()}...')
          : state.errorMessage != null && !_hasData(state)
              ? DailyCartErrorWidget(
                  title: 'Unable to load $_title',
                  message: state.errorMessage!,
                  onRetry: _load,
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  child: _body(state),
                ),
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
          const Center(child: Padding(
            padding: EdgeInsets.all(30),
            child: Text('No refund requests.'),
          )),
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
          const Center(child: Padding(
            padding: EdgeInsets.all(30),
            child: Text('No coupons.'),
          )),
        for (final coupon in state.coupons)
          ListTile(
            contentPadding: EdgeInsets.zero,
            leading: const CircleAvatar(child: Icon(Icons.confirmation_number)),
            title: Text('${coupon.code} — ${coupon.title}'),
            subtitle: Text(
              '${coupon.discountValue} ${coupon.discountType} • ${coupon.status}',
            ),
            trailing: IconButton(
              onPressed: () =>
                  ref.read(vendorBusinessProvider).deleteCoupon(coupon.id),
              icon: const Icon(Icons.delete_outline),
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
          const Center(child: Padding(
            padding: EdgeInsets.all(30),
            child: Text('No promotions.'),
          )),
        for (final promotion in state.promotions)
          ListTile(
            contentPadding: EdgeInsets.zero,
            leading: const CircleAvatar(child: Icon(Icons.local_offer)),
            title: Text(promotion.title),
            subtitle: Text(
              '${promotion.productName} • ${promotion.discountValue}% • ${promotion.status}',
            ),
            trailing: IconButton(
              onPressed: () => ref
                  .read(vendorBusinessProvider)
                  .deletePromotion(promotion.id),
              icon: const Icon(Icons.delete_outline),
            ),
          ),
      ],
    );
  }

  Widget _subscriptions(VendorBusinessProvider state) {
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        if (state.subscriptions.isEmpty)
          const Center(child: Padding(
            padding: EdgeInsets.all(30),
            child: Text('No active subscriptions.'),
          )),
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
          const Center(child: Padding(
            padding: EdgeInsets.all(30),
            child: Text('No scheduled orders.'),
          )),
        for (final order in state.scheduledOrders)
          ListTile(
            contentPadding: EdgeInsets.zero,
            onTap: () => context.push(
              '${AppRoutes.vendorOrderDetails}/${order.id}',
            ),
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
            children: [
              for (final entry in report?.summary.entries ?? const [])
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  title: Text(entry.key.replaceAll('_', ' ')),
                  trailing: Text(entry.value.toString()),
                ),
            ],
          ),
        ),
        const SizedBox(height: 18),
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

  Future<void> _createCoupon() async {
    final code = TextEditingController();
    final title = TextEditingController();
    final discount = TextEditingController();
    final minimum = TextEditingController(text: '0');
    final ok = await _formDialog(
      title: 'Create coupon',
      fields: [
        _field(code, 'Code'),
        _field(title, 'Title'),
        _field(discount, 'Discount percentage', number: true),
        _field(minimum, 'Minimum order', number: true),
      ],
    );
    if (ok) {
      final starts = DateTime.now();
      await ref.read(vendorBusinessProvider).saveCoupon({
        'code': code.text.trim().toUpperCase(),
        'title': title.text.trim(),
        'discount_type': 'percentage',
        'discount_value': double.tryParse(discount.text) ?? 0,
        'minimum_order_amount': double.tryParse(minimum.text) ?? 0,
        'starts_at': starts.toIso8601String(),
        'expires_at': starts.add(const Duration(days: 30)).toIso8601String(),
        'status': 'active',
      });
    }
    for (final controller in [code, title, discount, minimum]) {
      controller.dispose();
    }
  }

  Future<void> _createPromotion() async {
    final title = TextEditingController();
    final productId = TextEditingController();
    final discount = TextEditingController();
    final ok = await _formDialog(
      title: 'Create promotion',
      fields: [
        _field(title, 'Title'),
        _field(productId, 'Product ID', number: true),
        _field(discount, 'Discount percentage', number: true),
      ],
    );
    if (ok) {
      final starts = DateTime.now();
      await ref.read(vendorBusinessProvider).savePromotion({
        'title': title.text.trim(),
        'promotion_type': 'featured_offer',
        'target_id': int.tryParse(productId.text) ?? 0,
        'discount_type': 'percentage',
        'discount_value': double.tryParse(discount.text) ?? 0,
        'starts_at': starts.toIso8601String(),
        'ends_at': starts.add(const Duration(days: 30)).toIso8601String(),
        'status': 'active',
      });
    }
    for (final controller in [title, productId, discount]) {
      controller.dispose();
    }
  }

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
}
