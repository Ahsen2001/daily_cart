import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/customer_extended_provider.dart';
import '../../routes/app_routes.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/error_widget.dart';
import '../../widgets/loading_widget.dart';

class ScheduledOrdersScreen extends ConsumerStatefulWidget {
  const ScheduledOrdersScreen({super.key});

  @override
  ConsumerState<ScheduledOrdersScreen> createState() =>
      _ScheduledOrdersScreenState();
}

class _ScheduledOrdersScreenState extends ConsumerState<ScheduledOrdersScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(
      () => ref.read(customerExtendedProvider).loadScheduledOrders(),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(customerExtendedProvider);
    return Scaffold(
      appBar: const CustomAppBar(title: 'Scheduled Orders'),
      body: state.isLoading && state.scheduledOrders.isEmpty
          ? const LoadingWidget(message: 'Loading scheduled orders...')
          : state.errorMessage != null && state.scheduledOrders.isEmpty
              ? DailyCartErrorWidget(
                  title: 'Unable to load scheduled orders',
                  message: state.errorMessage!,
                  onRetry: () =>
                      ref.read(customerExtendedProvider).loadScheduledOrders(),
                )
              : RefreshIndicator(
                  onRefresh: () =>
                      ref.read(customerExtendedProvider).loadScheduledOrders(),
                  child: ListView(
                    padding: const EdgeInsets.all(20),
                    children: [
                      if (state.scheduledOrders.isEmpty)
                        const Padding(
                          padding: EdgeInsets.all(30),
                          child: Center(child: Text('No upcoming orders.')),
                        ),
                      for (final order in state.scheduledOrders)
                        Padding(
                          padding: const EdgeInsets.only(bottom: 12),
                          child: InkWell(
                            onTap: () => context.push(
                              '${AppRoutes.orderDetails}/${order.id}',
                            ),
                            child: DailyCartCard(
                              child: ListTile(
                                contentPadding: EdgeInsets.zero,
                                title: Text(order.orderNumber),
                                subtitle: Text(
                                  order.scheduledDeliveryTime
                                          ?.toLocal()
                                          .toString() ??
                                      'Not scheduled',
                                ),
                                trailing: order.isPending
                                    ? IconButton(
                                        tooltip: 'Cancel scheduled order',
                                        onPressed: () => ref
                                            .read(customerExtendedProvider)
                                            .cancelScheduledOrder(order.id),
                                        icon: const Icon(Icons.cancel_outlined),
                                      )
                                    : Text(order.status.toUpperCase()),
                              ),
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
    );
  }
}
