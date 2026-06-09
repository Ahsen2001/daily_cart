import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../providers/vendor_order_provider.dart';
import '../../theme/app_colors.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/stock_status_badge.dart';

class VendorOrderDetailsScreen extends ConsumerStatefulWidget {
  const VendorOrderDetailsScreen({
    required this.orderId,
    super.key,
  });

  final int orderId;

  @override
  ConsumerState<VendorOrderDetailsScreen> createState() =>
      _VendorOrderDetailsScreenState();
}

class _VendorOrderDetailsScreenState
    extends ConsumerState<VendorOrderDetailsScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(
      () => ref.read(vendorOrderProvider).getVendorOrderDetails(widget.orderId),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(vendorOrderProvider);
    final order = state.selectedOrder;
    return Scaffold(
      appBar: const CustomAppBar(title: 'Order Details'),
      body: state.isLoading && order == null
          ? const LoadingWidget(message: 'Loading order...')
          : order == null
              ? const Center(child: Text('Order not found.'))
              : ListView(
                  padding: const EdgeInsets.all(20),
                  children: [
                    DailyCartCard(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Expanded(
                                child: Text(
                                  order.orderNumber.isEmpty
                                      ? 'Order #${order.id}'
                                      : order.orderNumber,
                                  style: Theme.of(context)
                                      .textTheme
                                      .titleLarge
                                      ?.copyWith(fontWeight: FontWeight.w900),
                                ),
                              ),
                              StockStatusBadge(status: order.status),
                            ],
                          ),
                          const SizedBox(height: 12),
                          _Info('Customer', order.customerName),
                          _Info('Phone', order.customerPhone),
                          _Info('Delivery', order.deliveryAddress),
                          _Info(
                            'Scheduled',
                            order.scheduledDeliveryTime
                                    ?.toString()
                                    .split('.')
                                    .first ??
                                '-',
                          ),
                          _Info('Payment', order.paymentStatus),
                          _Info('Total', CurrencyFormatter.lkr(order.totalAmount)),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),
                    DailyCartCard(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Items',
                            style: Theme.of(context)
                                .textTheme
                                .titleMedium
                                ?.copyWith(fontWeight: FontWeight.w900),
                          ),
                          const SizedBox(height: 10),
                          for (final item in order.items) ...[
                            Row(
                              children: [
                                Expanded(
                                  child: Text(
                                    '${item.productName} x ${item.quantity}',
                                  ),
                                ),
                                Text(CurrencyFormatter.lkr(item.subtotal)),
                              ],
                            ),
                            const Divider(color: AppColors.border),
                          ],
                        ],
                      ),
                    ),
                    const SizedBox(height: 18),
                    if (order.canConfirm)
                      CustomButton(
                        label: 'Confirm Order',
                        icon: Icons.check_rounded,
                        onPressed: () => _confirm(order.id),
                      ),
                    if (order.canPack) ...[
                      const SizedBox(height: 10),
                      CustomButton(
                        label: 'Mark as Packed',
                        icon: Icons.inventory_2_outlined,
                        onPressed: () => _pack(order.id),
                      ),
                    ],
                    if (order.canCancel) ...[
                      const SizedBox(height: 10),
                      CustomButton(
                        label: 'Cancel or Reject Order',
                        icon: Icons.cancel_outlined,
                        variant: CustomButtonVariant.secondary,
                        onPressed: () => _cancel(order.id),
                      ),
                    ],
                  ],
                ),
    );
  }

  Future<void> _confirm(int orderId) async {
    final ok = await ref.read(vendorOrderProvider).confirmOrder(orderId);
    _show(ok ? 'Order confirmed.' : 'Unable to confirm order.');
  }

  Future<void> _pack(int orderId) async {
    final ok = await ref.read(vendorOrderProvider).markOrderPacked(orderId);
    _show(ok ? 'Order marked as packed.' : 'Unable to update order.');
  }

  Future<void> _cancel(int orderId) async {
    final controller = TextEditingController();
    final reason = await showDialog<String>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Cancel order'),
        content: TextField(
          controller: controller,
          decoration: const InputDecoration(labelText: 'Reason'),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Close'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.of(context).pop(controller.text.trim()),
            child: const Text('Submit'),
          ),
        ],
      ),
    );
    controller.dispose();
    if (reason == null || reason.isEmpty) {
      return;
    }
    final ok = await ref.read(vendorOrderProvider).cancelOrder(orderId, reason);
    _show(ok ? 'Order cancelled.' : 'Unable to cancel order.');
  }

  void _show(String message) {
    if (!mounted) {
      return;
    }
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }
}

class _Info extends StatelessWidget {
  const _Info(this.label, this.value);

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 96,
            child: Text(label, style: const TextStyle(color: AppColors.mutedText)),
          ),
          Expanded(child: Text(value.isEmpty ? '-' : value)),
        ],
      ),
    );
  }
}
