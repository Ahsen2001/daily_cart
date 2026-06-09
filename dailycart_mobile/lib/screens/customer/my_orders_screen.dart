import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/order_provider.dart';
import '../../routes/app_routes.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state_widget.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/order_card.dart';

class MyOrdersScreen extends ConsumerStatefulWidget {
  const MyOrdersScreen({super.key});

  @override
  ConsumerState<MyOrdersScreen> createState() => _MyOrdersScreenState();
}

class _MyOrdersScreenState extends ConsumerState<MyOrdersScreen> {
  String _filter = 'all';

  @override
  void initState() {
    super.initState();
    Future.microtask(_loadOrders);
  }

  Future<void> _loadOrders() {
    return ref.read(orderProvider).getOrders(filter: _filter);
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(orderProvider);

    return Scaffold(
      appBar: const CustomAppBar(title: 'My Orders'),
      body: Column(
        children: [
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.fromLTRB(20, 12, 20, 8),
            child: SegmentedButton<String>(
              segments: const [
                ButtonSegment(value: 'all', label: Text('All')),
                ButtonSegment(value: 'active', label: Text('Active')),
                ButtonSegment(value: 'completed', label: Text('Completed')),
                ButtonSegment(value: 'cancelled', label: Text('Cancelled')),
                ButtonSegment(value: 'refunded', label: Text('Refunded')),
              ],
              selected: {_filter},
              onSelectionChanged: (value) {
                setState(() => _filter = value.first);
                _loadOrders();
              },
            ),
          ),
          Expanded(
            child: state.isLoading && state.orders.isEmpty
                ? const LoadingWidget(message: 'Loading orders...')
                : state.orders.isEmpty
                    ? const EmptyStateWidget(
                        title: 'No orders found',
                        message: 'Your DailyCart orders will appear here.',
                        icon: Icons.receipt_long_outlined,
                      )
                    : RefreshIndicator(
                        onRefresh: _loadOrders,
                        child: ListView.separated(
                          padding: const EdgeInsets.all(20),
                          itemBuilder: (context, index) {
                            final order = state.orders[index];
                            return OrderCard(
                              order: order,
                              onViewDetails: () => context.push(
                                '${AppRoutes.orderDetails}/${order.id}',
                              ),
                            );
                          },
                          separatorBuilder: (context, index) =>
                              const SizedBox(height: 14),
                          itemCount: state.orders.length,
                        ),
                      ),
          ),
        ],
      ),
    );
  }
}
