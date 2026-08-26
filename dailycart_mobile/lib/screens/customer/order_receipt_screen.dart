import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../providers/order_provider.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/loading_widget.dart';

class OrderReceiptScreen extends ConsumerStatefulWidget {
  const OrderReceiptScreen({required this.orderId, super.key});

  final int orderId;

  @override
  ConsumerState<OrderReceiptScreen> createState() =>
      _OrderReceiptScreenState();
}

class _OrderReceiptScreenState extends ConsumerState<OrderReceiptScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(
      () => ref.read(orderProvider).getOrderDetails(widget.orderId),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(orderProvider);
    final order = state.selectedOrder;
    return Scaffold(
      appBar: const CustomAppBar(title: 'Order Receipt'),
      body: state.isLoading && order == null
          ? const LoadingWidget(message: 'Preparing receipt...')
          : order == null
              ? const Center(child: Text('Receipt is unavailable.'))
              : ListView(
                  padding: const EdgeInsets.all(20),
                  children: [
                    DailyCartCard(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Center(
                            child: Text(
                              'DAILYCART',
                              style: Theme.of(context)
                                  .textTheme
                                  .headlineSmall
                                  ?.copyWith(fontWeight: FontWeight.w900),
                            ),
                          ),
                          const Divider(height: 28),
                          _row('Receipt', order.orderNumber),
                          _row('Date', order.orderDate.toLocal().toString()),
                          _row('Status', order.status.replaceAll('_', ' ')),
                          _row('Payment', order.paymentMethod.replaceAll('_', ' ')),
                          _row('Payment status', order.paymentStatus),
                          const Divider(height: 28),
                          for (final item in order.items)
                            Padding(
                              padding: const EdgeInsets.only(bottom: 10),
                              child: Row(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Expanded(
                                    child: Text(
                                      '${item.quantity} × ${item.productName}',
                                    ),
                                  ),
                                  Text(CurrencyFormatter.lkr(item.subtotal)),
                                ],
                              ),
                            ),
                          const Divider(height: 28),
                          _money('Subtotal', order.subtotal),
                          _money('Discount', -order.discount),
                          _money('Delivery', order.deliveryCharge),
                          _money('Service charge', order.serviceCharge),
                          const Divider(height: 22),
                          _money('Total', order.grandTotal, emphasized: true),
                          const Divider(height: 28),
                          Text('Deliver to\n${order.deliveryAddress}'),
                        ],
                      ),
                    ),
                  ],
                ),
    );
  }

  Widget _row(String label, String value) => Padding(
        padding: const EdgeInsets.only(bottom: 8),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            SizedBox(width: 112, child: Text(label)),
            Expanded(child: Text(value.isEmpty ? '-' : value)),
          ],
        ),
      );

  Widget _money(String label, double amount, {bool emphasized = false}) =>
      Padding(
        padding: const EdgeInsets.only(bottom: 8),
        child: DefaultTextStyle.merge(
          style: TextStyle(
            fontWeight: emphasized ? FontWeight.w900 : FontWeight.normal,
            fontSize: emphasized ? 17 : null,
          ),
          child: Row(
            children: [
              Expanded(child: Text(label)),
              Text(CurrencyFormatter.lkr(amount)),
            ],
          ),
        ),
      );
}
