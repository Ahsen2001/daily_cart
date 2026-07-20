import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../models/order_model.dart';
import '../../providers/order_provider.dart';
import '../../providers/customer_extended_provider.dart';
import '../../routes/app_routes.dart';
import '../../theme/app_colors.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/order_summary_card.dart';

class OrderDetailsScreen extends ConsumerStatefulWidget {
  const OrderDetailsScreen({
    required this.orderId,
    super.key,
  });

  final int orderId;

  @override
  ConsumerState<OrderDetailsScreen> createState() => _OrderDetailsScreenState();
}

class _OrderDetailsScreenState extends ConsumerState<OrderDetailsScreen> {
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
      appBar: const CustomAppBar(title: 'Order Details'),
      body: state.isLoading && order == null
          ? const LoadingWidget(message: 'Loading order...')
          : order == null
              ? const Center(child: Text('Order not found.'))
              : ListView(
                  padding: const EdgeInsets.all(20),
                  children: [
                    _OrderInfoCard(order: order),
                    const SizedBox(height: 14),
                    _OrderItemsCard(order: order),
                    const SizedBox(height: 14),
                    OrderSummaryCard(order: order),
                    const SizedBox(height: 18),
                    CustomButton(
                      label: 'Track Order',
                      icon: Icons.route_outlined,
                      onPressed: () => context.push(
                        '${AppRoutes.orderTracking}/${order.id}',
                      ),
                    ),
                    const SizedBox(height: 10),
                    CustomButton(
                      label: 'Download Invoice Placeholder',
                      icon: Icons.download_rounded,
                      variant: CustomButtonVariant.secondary,
                      onPressed: () => _show('Invoice download placeholder.'),
                    ),
                    if (order.isPending) ...[
                      const SizedBox(height: 10),
                      CustomButton(
                        label: 'Cancel Order',
                        icon: Icons.cancel_outlined,
                        variant: CustomButtonVariant.secondary,
                        onPressed: _cancelOrder,
                      ),
                    ],
                    if (order.isCompleted &&
                        order.paymentStatus.toLowerCase() == 'paid') ...[
                      const SizedBox(height: 10),
                      CustomButton(
                        label: 'Request Refund',
                        icon: Icons.currency_exchange,
                        variant: CustomButtonVariant.secondary,
                        onPressed: () => _requestRefund(order),
                      ),
                    ],
                  ],
                ),
    );
  }

  Future<void> _cancelOrder() async {
    final ok = await ref.read(orderProvider).cancelOrder(widget.orderId);
    if (mounted) {
      _show(ok ? 'Order cancelled.' : 'Unable to cancel order.');
    }
  }

  Future<void> _requestRefund(OrderModel order) async {
    final amount = TextEditingController(
      text: order.grandTotal.toStringAsFixed(2),
    );
    final reason = TextEditingController();
    final submit = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Request refund'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: amount,
              keyboardType:
                  const TextInputType.numberWithOptions(decimal: true),
              decoration: const InputDecoration(labelText: 'Refund amount'),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: reason,
              maxLines: 3,
              decoration: const InputDecoration(labelText: 'Reason'),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Submit'),
          ),
        ],
      ),
    );
    final refundAmount = double.tryParse(amount.text);
    final refundReason = reason.text.trim();
    amount.dispose();
    reason.dispose();
    if (submit != true || refundAmount == null || refundReason.isEmpty) return;

    final ok = await ref.read(customerExtendedProvider).requestRefund(
          orderId: order.id,
          amount: refundAmount,
          reason: refundReason,
        );
    if (mounted) {
      _show(
        ok
            ? 'Refund request submitted.'
            : ref.read(customerExtendedProvider).errorMessage ??
                'Unable to request refund.',
      );
    }
  }

  void _show(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }
}

class _OrderInfoCard extends StatelessWidget {
  const _OrderInfoCard({required this.order});

  final OrderModel order;

  @override
  Widget build(BuildContext context) {
    return DailyCartCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            order.orderNumber.isEmpty ? 'Order #${order.id}' : order.orderNumber,
            style: Theme.of(context).textTheme.titleLarge?.copyWith(
                  fontWeight: FontWeight.w900,
                ),
          ),
          const SizedBox(height: 12),
          _InfoRow(label: 'Order Date', value: order.orderDate.toString()),
          _InfoRow(label: 'Delivery Address', value: order.deliveryAddress),
          _InfoRow(
            label: 'Scheduled Delivery',
            value: order.scheduledDeliveryTime?.toString() ?? 'Not scheduled',
          ),
          _InfoRow(label: 'Payment Method', value: order.paymentMethod),
          _InfoRow(label: 'Payment Status', value: order.paymentStatus),
          _InfoRow(label: 'Order Status', value: order.status),
        ],
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 130,
            child: Text(
              label,
              style: const TextStyle(color: AppColors.mutedText),
            ),
          ),
          Expanded(child: Text(value.isEmpty ? '-' : value)),
        ],
      ),
    );
  }
}

class _OrderItemsCard extends StatelessWidget {
  const _OrderItemsCard({required this.order});

  final OrderModel order;

  @override
  Widget build(BuildContext context) {
    return DailyCartCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Items',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.w900,
                ),
          ),
          const SizedBox(height: 12),
          for (final item in order.items) ...[
            Row(
              children: [
                Container(
                  width: 58,
                  height: 58,
                  decoration: BoxDecoration(
                    color: AppColors.lightBackground,
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: item.image.isEmpty
                      ? const Icon(Icons.shopping_basket_outlined)
                      : ClipRRect(
                          borderRadius: BorderRadius.circular(16),
                          child: CachedNetworkImage(
                            imageUrl: item.image,
                            fit: BoxFit.cover,
                          ),
                        ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(item.productName),
                      Text('Qty: ${item.quantity}'),
                      if (order.isCompleted)
                        TextButton(
                          onPressed: () => context.push(
                            '${AppRoutes.addReview}/${order.id}/${item.productId}',
                          ),
                          child: const Text('Review'),
                        ),
                    ],
                  ),
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(CurrencyFormatter.lkr(item.price)),
                    Text(CurrencyFormatter.lkr(item.subtotal)),
                  ],
                ),
              ],
            ),
            const Divider(color: AppColors.border),
          ],
        ],
      ),
    );
  }
}
