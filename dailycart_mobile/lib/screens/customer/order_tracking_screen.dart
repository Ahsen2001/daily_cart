import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';

import '../../providers/order_provider.dart';
import '../../theme/app_colors.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/order_status_timeline.dart';

class OrderTrackingScreen extends ConsumerStatefulWidget {
  const OrderTrackingScreen({required this.orderId, super.key});

  final int orderId;

  @override
  ConsumerState<OrderTrackingScreen> createState() =>
      _OrderTrackingScreenState();
}

class _OrderTrackingScreenState extends ConsumerState<OrderTrackingScreen> {
  Timer? _refreshTimer;

  @override
  void initState() {
    super.initState();
    Future.microtask(() async {
      await ref.read(orderProvider).getOrderDetails(widget.orderId);
      if (!mounted) return;
      _refreshTimer = Timer.periodic(const Duration(seconds: 15), (_) {
        ref.read(orderProvider).getOrderDetails(widget.orderId);
      });
    });
  }

  @override
  void dispose() {
    _refreshTimer?.cancel();
    super.dispose();
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
                        style: Theme.of(context).textTheme.titleMedium
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
                        style: Theme.of(context).textTheme.titleMedium
                            ?.copyWith(fontWeight: FontWeight.w900),
                      ),
                      const SizedBox(height: 10),
                      Text(
                        'Rider: ${order.riderName.isEmpty ? '-' : order.riderName}',
                      ),
                      const SizedBox(height: 6),
                      Text(
                        'Phone: ${order.riderPhone.isEmpty ? '-' : order.riderPhone}',
                      ),
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
                if (order.deliveryLatitude != null &&
                    order.deliveryLongitude != null)
                  ClipRRect(
                    borderRadius: BorderRadius.circular(24),
                    child: SizedBox(
                      height: 300,
                      child: GoogleMap(
                        key: ValueKey(
                          '${order.riderLatitude}:${order.riderLongitude}',
                        ),
                        initialCameraPosition: CameraPosition(
                          target: LatLng(
                            order.riderLatitude ?? order.deliveryLatitude!,
                            order.riderLongitude ?? order.deliveryLongitude!,
                          ),
                          zoom: 13.5,
                        ),
                        markers: {
                          Marker(
                            markerId: const MarkerId('destination'),
                            position: LatLng(
                              order.deliveryLatitude!,
                              order.deliveryLongitude!,
                            ),
                            infoWindow: const InfoWindow(
                              title: 'Delivery address',
                            ),
                          ),
                          if (order.riderLatitude != null &&
                              order.riderLongitude != null)
                            Marker(
                              markerId: const MarkerId('rider'),
                              position: LatLng(
                                order.riderLatitude!,
                                order.riderLongitude!,
                              ),
                              icon: BitmapDescriptor.defaultMarkerWithHue(
                                BitmapDescriptor.hueGreen,
                              ),
                              infoWindow: const InfoWindow(
                                title: 'Rider live location',
                              ),
                            ),
                        },
                        zoomControlsEnabled: false,
                        mapToolbarEnabled: false,
                      ),
                    ),
                  )
                else
                  const DailyCartCard(
                    child: Row(
                      children: [
                        Icon(
                          Icons.location_off_outlined,
                          color: AppColors.primaryGreen,
                        ),
                        SizedBox(width: 12),
                        Expanded(
                          child: Text(
                            'Live map becomes available after a delivery location is assigned.',
                          ),
                        ),
                      ],
                    ),
                  ),
                const SizedBox(height: 8),
                Text(
                  'Tracking refreshes automatically every 15 seconds.',
                  textAlign: TextAlign.center,
                  style: Theme.of(context).textTheme.bodySmall,
                ),
              ],
            ),
    );
  }
}
