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
  StreamSubscription<Map<String, dynamic>>? _trackingSubscription;
  DateTime? _lastRealtimeUpdate;

  @override
  void initState() {
    super.initState();
    Future.microtask(_initializeTracking);
  }

  Future<void> _initializeTracking() async {
    await ref.read(orderProvider).getOrderDetails(widget.orderId);
    if (!mounted || ref.read(orderProvider).selectedOrder?.isTrackingTerminal == true) {
      return;
    }
    _trackingSubscription = ref
        .read(orderProvider)
        .watchTracking(widget.orderId)
        .listen((payload) {
          if (!mounted) return;
          _lastRealtimeUpdate = DateTime.now();
          ref.read(orderProvider).applyTrackingUpdate(widget.orderId, payload);
          if (ref.read(orderProvider).selectedOrder?.isTrackingTerminal == true) {
            _stopTrackingRefresh();
          }
        });
    _refreshTimer = Timer.periodic(const Duration(seconds: 20), (_) async {
      final lastRealtimeUpdate = _lastRealtimeUpdate;
      if (lastRealtimeUpdate != null &&
          DateTime.now().difference(lastRealtimeUpdate) <
              const Duration(seconds: 25)) {
        return;
      }
      await ref.read(orderProvider).getOrderDetails(widget.orderId);
      if (ref.read(orderProvider).selectedOrder?.isTrackingTerminal == true) {
        _stopTrackingRefresh();
      }
    });
  }

  void _stopTrackingRefresh() {
    _refreshTimer?.cancel();
    _refreshTimer = null;
    _trackingSubscription?.cancel();
    _trackingSubscription = null;
  }

  @override
  void dispose() {
    _stopTrackingRefresh();
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
                      OrderStatusTimeline(
                        currentStatus: order.status,
                        entries: order.timeline,
                      ),
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
                if (order.isTrackingTerminal)
                  DailyCartCard(
                    child: Row(
                      children: [
                        const Icon(
                          Icons.flag_circle_rounded,
                          color: AppColors.primaryGreen,
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Text(
                            'Final delivery state: ${order.deliveryStatus.isEmpty ? order.status : order.deliveryStatus}',
                            style: const TextStyle(fontWeight: FontWeight.w800),
                          ),
                        ),
                      ],
                    ),
                  )
                else ...[
                  DailyCartCard(
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Icon(
                          order.riderLocationIsOutdated
                              ? Icons.location_disabled_rounded
                              : order.trackingActive
                              ? Icons.my_location_rounded
                              : Icons.location_searching_rounded,
                          color: order.riderLocationIsOutdated
                              ? Colors.orange.shade800
                              : AppColors.primaryGreen,
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                order.riderLocationMessage,
                                style: TextStyle(
                                  fontWeight: FontWeight.w900,
                                  color: order.riderLocationIsOutdated
                                      ? Colors.orange.shade900
                                      : null,
                                ),
                              ),
                              if (order.riderLocationRecordedAt != null) ...[
                                const SizedBox(height: 4),
                                Text(
                                  'Last updated: ${_formatTime(order.riderLocationRecordedAt!)}',
                                  style: Theme.of(context).textTheme.bodySmall,
                                ),
                              ],
                            ],
                          ),
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
                              order.riderLongitude != null &&
                              order.trackingActive)
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
                ],
                const SizedBox(height: 8),
                Text(
                  'Live tracking updates automatically while this screen is open.',
                  textAlign: TextAlign.center,
                  style: Theme.of(context).textTheme.bodySmall,
                ),
              ],
            ),
    );
  }

  String _formatTime(DateTime value) {
    final local = value.toLocal();
    final hour = local.hour % 12 == 0 ? 12 : local.hour % 12;
    final minute = local.minute.toString().padLeft(2, '0');
    final suffix = local.hour >= 12 ? 'PM' : 'AM';
    return '${local.day}/${local.month}/${local.year} $hour:$minute $suffix';
  }
}
