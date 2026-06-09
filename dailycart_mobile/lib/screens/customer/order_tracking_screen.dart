import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../providers/order_provider.dart';
import '../../theme/app_colors.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/order_status_timeline.dart';

class OrderTrackingScreen extends ConsumerStatefulWidget {
  const OrderTrackingScreen({
    required this.orderId,
    super.key,
  });

  final int orderId;

  @override
  ConsumerState<OrderTrackingScreen> createState() =>
      _OrderTrackingScreenState();
}

class _OrderTrackingScreenState extends ConsumerState<OrderTrackingScreen> {
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
      appBar: const CustomAppBar(title: 'Track Order'),
      body: state.isLoading && order == null
          ? const LoadingWidget(message: 'Loading tracking...')
          : order == null
              ? const Center(child: Text('Tracking not found.'))
              : ListView(
                  padding: const EdgeInsets.all(20),
                  children: [
                    DailyCartCard(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Current Status',
                            style: Theme.of(context)
                                .textTheme
                                .titleMedium
                                ?.copyWith(fontWeight: FontWeight.w900),
                          ),
                          const SizedBox(height: 8),
                          Text(order.status.replaceAll('_', ' ')),
                          const SizedBox(height: 16),
                          OrderStatusTimeline(currentStatus: order.status),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),
                    DailyCartCard(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Delivery Details',
                            style: Theme.of(context)
                                .textTheme
                                .titleMedium
                                ?.copyWith(fontWeight: FontWeight.w900),
                          ),
                          const SizedBox(height: 10),
                          Text('Rider: ${order.riderName.isEmpty ? '-' : order.riderName}'),
                          const SizedBox(height: 6),
                          Text('Phone: ${order.riderPhone.isEmpty ? '-' : order.riderPhone}'),
                          const SizedBox(height: 6),
                          Text(
                            'Scheduled: ${order.scheduledDeliveryTime?.toString() ?? '-'}',
                          ),
                          const SizedBox(height: 6),
                          Text(
                            'Estimated: ${order.estimatedDeliveryTime?.toString() ?? '-'}',
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),
                    DailyCartCard(
                      child: Row(
                        children: [
                          Container(
                            width: 54,
                            height: 54,
                            decoration: BoxDecoration(
                              color: AppColors.lightBackground,
                              borderRadius: BorderRadius.circular(18),
                            ),
                            child: const Icon(
                              Icons.map_outlined,
                              color: AppColors.primaryGreen,
                            ),
                          ),
                          const SizedBox(width: 12),
                          const Expanded(
                            child: Text('Google Maps live tracking placeholder'),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
    );
  }
}
