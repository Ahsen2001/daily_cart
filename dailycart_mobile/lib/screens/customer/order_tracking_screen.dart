import 'dart:async';

import 'package:connectivity_plus/connectivity_plus.dart';
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
  StreamSubscription<List<ConnectivityResult>>? _connectivitySubscription;
  DateTime? _lastRealtimeUpdate;
  bool _isOffline = false;
  bool _isReconnecting = false;
  bool _refreshFailed = false;
  bool _pollInProgress = false;

  @override
  void initState() {
    super.initState();
    Future.microtask(_initializeTracking);
  }

  Future<void> _initializeTracking() async {
    final connectivity = Connectivity();
    final initial = await connectivity.checkConnectivity();
    if (!mounted) return;
    _isOffline = initial.contains(ConnectivityResult.none);
    _connectivitySubscription = connectivity.onConnectivityChanged.listen(
      _handleConnectivityChanged,
    );

    if (!_isOffline) {
      await _refreshTracking();
    } else if (mounted) {
      setState(() {});
    }

    if (!mounted ||
        ref.read(orderProvider).selectedOrder?.isTrackingTerminal == true) {
      return;
    }

    if (!_isOffline) _startLiveUpdates();
    _startPolling();
  }

  void _startLiveUpdates() {
    _trackingSubscription?.cancel();
    _trackingSubscription = ref
        .read(orderProvider)
        .watchTracking(widget.orderId)
        .listen((payload) {
          if (!mounted) return;
          _lastRealtimeUpdate = DateTime.now();
          setState(() {
            _isReconnecting = false;
            _refreshFailed = false;
          });
          ref.read(orderProvider).applyTrackingUpdate(widget.orderId, payload);
          if (ref.read(orderProvider).selectedOrder?.isTrackingTerminal ==
              true) {
            _stopTrackingRefresh();
          }
        });
  }

  void _startPolling() {
    _refreshTimer?.cancel();
    _refreshTimer = Timer.periodic(const Duration(seconds: 20), (_) async {
      if (_isOffline || _pollInProgress) return;
      final lastRealtimeUpdate = _lastRealtimeUpdate;
      if (lastRealtimeUpdate != null &&
          DateTime.now().difference(lastRealtimeUpdate) <
              const Duration(seconds: 25)) {
        return;
      }
      await _refreshTracking();
    });
  }

  Future<bool> _refreshTracking() async {
    if (_isOffline || _pollInProgress) return false;
    _pollInProgress = true;
    await ref.read(orderProvider).getOrderDetails(widget.orderId);
    _pollInProgress = false;
    if (!mounted) return false;

    final succeeded = ref.read(orderProvider).errorMessage == null;
    setState(() {
      _isReconnecting = false;
      _refreshFailed = !succeeded;
    });

    if (ref.read(orderProvider).selectedOrder?.isTrackingTerminal == true) {
      _stopTrackingRefresh();
    }
    return succeeded;
  }

  Future<void> _handleConnectivityChanged(
    List<ConnectivityResult> results,
  ) async {
    if (!mounted) return;
    final offline = results.contains(ConnectivityResult.none);
    if (offline) {
      await _trackingSubscription?.cancel();
      _trackingSubscription = null;
      setState(() {
        _isOffline = true;
        _isReconnecting = false;
      });
      return;
    }

    final wasOffline = _isOffline;
    setState(() {
      _isOffline = false;
      _isReconnecting = wasOffline;
    });
    if (wasOffline) await _retryNow();
  }

  Future<void> _retryNow() async {
    if (_pollInProgress) return;
    final results = await Connectivity().checkConnectivity();
    if (!mounted) return;
    if (results.contains(ConnectivityResult.none)) {
      setState(() {
        _isOffline = true;
        _isReconnecting = false;
      });
      return;
    }

    setState(() {
      _isOffline = false;
      _isReconnecting = true;
      _refreshFailed = false;
    });
    final succeeded = await _refreshTracking();
    if (!mounted) return;
    final order = ref.read(orderProvider).selectedOrder;
    if (succeeded && order?.isTrackingTerminal != true) {
      _startLiveUpdates();
      _startPolling();
    }
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
    _connectivitySubscription?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(orderProvider);
    final selectedOrder = state.selectedOrder;
    final order = selectedOrder?.id == widget.orderId ? selectedOrder : null;

    return Scaffold(
      appBar: const CustomAppBar(title: 'Track Order'),
      body: state.isLoading && order == null
          ? const LoadingWidget(message: 'Loading tracking...')
          : order == null
          ? _TrackingUnavailableState(
              isOffline: _isOffline,
              isRetrying: _isReconnecting,
              message: state.errorMessage,
              onRetry: _retryNow,
            )
          : ListView(
              padding: const EdgeInsets.all(20),
              children: [
                if (!order.isTrackingTerminal &&
                    (_isOffline || _isReconnecting || _refreshFailed)) ...[
                  _TrackingConnectionBanner(
                    isOffline: _isOffline,
                    isReconnecting: _isReconnecting,
                    refreshFailed: _refreshFailed,
                    onRetry: _retryNow,
                  ),
                  const SizedBox(height: 14),
                ],
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

class _TrackingConnectionBanner extends StatelessWidget {
  const _TrackingConnectionBanner({
    required this.isOffline,
    required this.isReconnecting,
    required this.refreshFailed,
    required this.onRetry,
  });

  final bool isOffline;
  final bool isReconnecting;
  final bool refreshFailed;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    final title = isOffline
        ? 'Offline'
        : isReconnecting
        ? 'Reconnecting to live tracking'
        : 'Live updates unavailable';
    final message = isOffline
        ? 'Showing the last known rider location. Updates will resume automatically when you reconnect.'
        : isReconnecting
        ? 'Checking for the latest rider location…'
        : 'The live stream and latest refresh could not connect. Your last known location is still shown.';

    return Semantics(
      liveRegion: true,
      label: '$title. $message',
      child: DailyCartCard(
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (isReconnecting)
              const SizedBox(
                width: 22,
                height: 22,
                child: CircularProgressIndicator(strokeWidth: 2.5),
              )
            else
              Icon(
                isOffline
                    ? Icons.cloud_off_rounded
                    : Icons.sync_problem_rounded,
                color: Colors.orange.shade800,
              ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(fontWeight: FontWeight.w900),
                  ),
                  const SizedBox(height: 4),
                  Text(message),
                  if (refreshFailed && !isOffline && !isReconnecting) ...[
                    const SizedBox(height: 8),
                    TextButton.icon(
                      onPressed: onRetry,
                      icon: const Icon(Icons.refresh_rounded),
                      label: const Text('Retry now'),
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _TrackingUnavailableState extends StatelessWidget {
  const _TrackingUnavailableState({
    required this.isOffline,
    required this.isRetrying,
    required this.message,
    required this.onRetry,
  });

  final bool isOffline;
  final bool isRetrying;
  final String? message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              isOffline
                  ? Icons.cloud_off_rounded
                  : Icons.location_searching_rounded,
              size: 44,
              color: AppColors.primaryGreen,
            ),
            const SizedBox(height: 12),
            Text(
              isOffline ? 'You are offline' : 'Tracking could not be loaded',
              style: Theme.of(
                context,
              ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w900),
            ),
            const SizedBox(height: 6),
            Text(
              isOffline
                  ? 'Reconnect to load the latest delivery location.'
                  : message ?? 'Please check your connection and try again.',
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 16),
            FilledButton.icon(
              onPressed: isRetrying ? null : onRetry,
              icon: isRetrying
                  ? const SizedBox(
                      width: 16,
                      height: 16,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : const Icon(Icons.refresh_rounded),
              label: Text(isRetrying ? 'Reconnecting…' : 'Retry'),
            ),
          ],
        ),
      ),
    );
  }
}
