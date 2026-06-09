import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/vendor_order_provider.dart';
import '../../routes/app_routes.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state_widget.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/vendor_order_card.dart';

class VendorOrdersScreen extends ConsumerStatefulWidget {
  const VendorOrdersScreen({super.key});

  @override
  ConsumerState<VendorOrdersScreen> createState() => _VendorOrdersScreenState();
}

class _VendorOrdersScreenState extends ConsumerState<VendorOrdersScreen> {
  String _status = 'all';

  @override
  void initState() {
    super.initState();
    Future.microtask(_load);
  }

  Future<void> _load() {
    return ref.read(vendorOrderProvider).getVendorOrders(status: _status);
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(vendorOrderProvider);
    return Scaffold(
      appBar: const CustomAppBar(title: 'Vendor Orders'),
      body: Column(
        children: [
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.fromLTRB(20, 12, 20, 8),
            child: SegmentedButton<String>(
              segments: const [
                ButtonSegment(value: 'all', label: Text('All')),
                ButtonSegment(value: 'pending', label: Text('Pending')),
                ButtonSegment(value: 'confirmed', label: Text('Confirmed')),
                ButtonSegment(value: 'packed', label: Text('Packed')),
                ButtonSegment(value: 'delivered', label: Text('Delivered')),
                ButtonSegment(value: 'cancelled', label: Text('Cancelled')),
              ],
              selected: {_status},
              onSelectionChanged: (value) {
                setState(() => _status = value.first);
                _load();
              },
            ),
          ),
          Expanded(
            child: state.isLoading && state.orders.isEmpty
                ? const LoadingWidget(message: 'Loading vendor orders...')
                : state.orders.isEmpty
                    ? const EmptyStateWidget(
                        title: 'No orders',
                        message: 'Orders for your products will appear here.',
                        icon: Icons.receipt_long_outlined,
                      )
                    : RefreshIndicator(
                        onRefresh: _load,
                        child: ListView.separated(
                          padding: const EdgeInsets.all(20),
                          itemBuilder: (context, index) {
                            final order = state.orders[index];
                            return VendorOrderCard(
                              order: order,
                              onTap: () => context.push(
                                '${AppRoutes.vendorOrderDetails}/${order.id}',
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
