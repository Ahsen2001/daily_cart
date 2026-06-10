import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/rider_delivery_provider.dart';
import '../../routes/app_routes.dart';
import '../../theme/app_colors.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/delivery_action_button.dart';
import '../../widgets/delivery_status_badge.dart';
import '../../widgets/delivery_status_timeline.dart';
import '../../widgets/loading_widget.dart';

class DeliveryDetailsScreen extends ConsumerStatefulWidget {
  const DeliveryDetailsScreen({
    required this.deliveryId,
    super.key,
  });

  final int deliveryId;

  @override
  ConsumerState<DeliveryDetailsScreen> createState() =>
      _DeliveryDetailsScreenState();
}

class _DeliveryDetailsScreenState extends ConsumerState<DeliveryDetailsScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(
      () => ref.read(riderDeliveryProvider).getDeliveryDetails(widget.deliveryId),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(riderDeliveryProvider);
    final delivery = state.selectedDelivery;
    return Scaffold(
      appBar: const CustomAppBar(title: 'Delivery Details'),
      body: state.isLoading && delivery == null
          ? const LoadingWidget(message: 'Loading delivery...')
          : delivery == null
              ? const Center(child: Text('Delivery not found.'))
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
                                  delivery.orderNumber.isEmpty
                                      ? 'Delivery #${delivery.id}'
                                      : delivery.orderNumber,
                                  style: Theme.of(context)
                                      .textTheme
                                      .titleLarge
                                      ?.copyWith(fontWeight: FontWeight.w900),
                                ),
                              ),
                              DeliveryStatusBadge(status: delivery.status),
                            ],
                          ),
                          const SizedBox(height: 12),
                          _Info('Customer', delivery.customerName),
                          _Info('Phone', delivery.customerPhone),
                          _Info('Address', delivery.deliveryAddress),
                          _Info(
                            'Scheduled',
                            delivery.scheduledDeliveryTime
                                    ?.toString()
                                    .split('.')
                                    .first ??
                                '-',
                          ),
                          _Info('Payment Method', delivery.paymentMethod),
                          _Info('Payment Status', delivery.paymentStatus),
                          _Info('Total', CurrencyFormatter.lkr(delivery.totalAmount)),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),
                    DailyCartCard(
                      child: DeliveryStatusTimeline(
                        currentStatus: delivery.status,
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
                          for (final item in delivery.items) ...[
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
                    DeliveryActionButton(
                      label: 'Open Map',
                      icon: Icons.map_outlined,
                      onPressed: () => context.push(
                        '${AppRoutes.riderMap}/${delivery.id}',
                      ),
                      isSecondary: true,
                    ),
                    if (delivery.canMarkPickedUp) ...[
                      const SizedBox(height: 10),
                      DeliveryActionButton(
                        label: 'Mark Picked Up',
                        icon: Icons.inventory_2_outlined,
                        onPressed: () => _pickedUp(delivery.id),
                      ),
                    ],
                    if (delivery.canMarkOnTheWay) ...[
                      const SizedBox(height: 10),
                      DeliveryActionButton(
                        label: 'Mark On The Way',
                        icon: Icons.delivery_dining_rounded,
                        onPressed: () => _onTheWay(delivery.id),
                      ),
                    ],
                    if (delivery.canMarkDelivered) ...[
                      const SizedBox(height: 10),
                      DeliveryActionButton(
                        label: 'Mark Delivered',
                        icon: Icons.check_circle_outline_rounded,
                        onPressed: () => context.push(
                          '${AppRoutes.deliveryProof}/${delivery.id}',
                        ),
                      ),
                    ],
                    if (delivery.canMarkFailed) ...[
                      const SizedBox(height: 10),
                      DeliveryActionButton(
                        label: 'Mark Failed',
                        icon: Icons.error_outline_rounded,
                        isSecondary: true,
                        onPressed: () => _failed(delivery.id),
                      ),
                    ],
                  ],
                ),
    );
  }

  Future<void> _pickedUp(int id) async {
    final ok = await ref.read(riderDeliveryProvider).markPickedUp(id);
    _show(ok ? 'Delivery marked picked up.' : 'Unable to update delivery.');
  }

  Future<void> _onTheWay(int id) async {
    final ok = await ref.read(riderDeliveryProvider).markOnTheWay(id);
    _show(ok ? 'Delivery marked on the way.' : 'Unable to update delivery.');
  }

  Future<void> _failed(int id) async {
    final controller = TextEditingController();
    final reason = await showDialog<String>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Mark failed'),
        content: TextField(
          controller: controller,
          decoration: const InputDecoration(labelText: 'Failure reason'),
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
    if (reason == null || reason.isEmpty) return;
    final ok = await ref.read(riderDeliveryProvider).markFailed(id, reason);
    _show(ok ? 'Delivery marked failed.' : 'Unable to update delivery.');
  }

  void _show(String message) {
    if (!mounted) return;
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
            width: 126,
            child: Text(label, style: const TextStyle(color: AppColors.mutedText)),
          ),
          Expanded(child: Text(value.isEmpty ? '-' : value)),
        ],
      ),
    );
  }
}
