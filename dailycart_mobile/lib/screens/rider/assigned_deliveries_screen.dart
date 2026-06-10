import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/rider_delivery_provider.dart';
import '../../routes/app_routes.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/delivery_card.dart';
import '../../widgets/empty_state_widget.dart';
import '../../widgets/loading_widget.dart';

class AssignedDeliveriesScreen extends ConsumerStatefulWidget {
  const AssignedDeliveriesScreen({super.key});

  @override
  ConsumerState<AssignedDeliveriesScreen> createState() =>
      _AssignedDeliveriesScreenState();
}

class _AssignedDeliveriesScreenState
    extends ConsumerState<AssignedDeliveriesScreen> {
  String _status = 'all';

  @override
  void initState() {
    super.initState();
    Future.microtask(_load);
  }

  Future<void> _load() {
    return ref.read(riderDeliveryProvider).getAssignedDeliveries(status: _status);
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(riderDeliveryProvider);
    return Scaffold(
      appBar: const CustomAppBar(title: 'Assigned Deliveries'),
      body: Column(
        children: [
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.fromLTRB(20, 12, 20, 8),
            child: SegmentedButton<String>(
              selected: {_status},
              segments: const [
                ButtonSegment(value: 'all', label: Text('All')),
                ButtonSegment(value: 'assigned', label: Text('Assigned')),
                ButtonSegment(value: 'picked_up', label: Text('Picked Up')),
                ButtonSegment(value: 'on_the_way', label: Text('On Way')),
                ButtonSegment(value: 'delivered', label: Text('Delivered')),
                ButtonSegment(value: 'failed', label: Text('Failed')),
              ],
              onSelectionChanged: (value) {
                setState(() => _status = value.first);
                _load();
              },
            ),
          ),
          Expanded(
            child: state.isLoading && state.deliveries.isEmpty
                ? const LoadingWidget(message: 'Loading deliveries...')
                : state.deliveries.isEmpty
                    ? const EmptyStateWidget(
                        title: 'No assigned deliveries',
                        message: 'Your assigned DailyCart deliveries appear here.',
                        icon: Icons.delivery_dining_rounded,
                      )
                    : RefreshIndicator(
                        onRefresh: _load,
                        child: ListView.separated(
                          padding: const EdgeInsets.all(20),
                          itemBuilder: (context, index) {
                            final delivery = state.deliveries[index];
                            return DeliveryCard(
                              delivery: delivery,
                              onTap: () => context.push(
                                '${AppRoutes.deliveryDetails}/${delivery.id}',
                              ),
                            );
                          },
                          separatorBuilder: (context, index) =>
                              const SizedBox(height: 14),
                          itemCount: state.deliveries.length,
                        ),
                      ),
          ),
        ],
      ),
    );
  }
}
