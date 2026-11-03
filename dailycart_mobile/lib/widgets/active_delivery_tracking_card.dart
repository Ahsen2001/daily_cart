import 'dart:async';

import 'package:flutter/material.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';

import '../services/delivery_tracking_api_service.dart';
import '../theme/app_colors.dart';
import 'dailycart_card.dart';

class ActiveDeliveryTrackingCard extends StatefulWidget {
  const ActiveDeliveryTrackingCard({required this.orderId, super.key});

  final int orderId;

  @override
  State<ActiveDeliveryTrackingCard> createState() =>
      _ActiveDeliveryTrackingCardState();
}

class _ActiveDeliveryTrackingCardState
    extends State<ActiveDeliveryTrackingCard> {
  final _api = DeliveryTrackingApiService();
  StreamSubscription<Map<String, dynamic>>? _subscription;
  Timer? _fallback;
  Map<String, dynamic>? _tracking;
  String? _error;
  DateTime? _lastRealtimeUpdate;

  @override
  void initState() {
    super.initState();
    _load();
    _subscription = _api.watchTracking(widget.orderId).listen((payload) {
      _lastRealtimeUpdate = DateTime.now();
      _applyPayload(payload);
    });
    _fallback = Timer.periodic(const Duration(seconds: 20), (_) {
      final lastRealtimeUpdate = _lastRealtimeUpdate;
      if (lastRealtimeUpdate == null ||
          DateTime.now().difference(lastRealtimeUpdate) >=
              const Duration(seconds: 25)) {
        _load();
      }
    });
  }

  @override
  void dispose() {
    _subscription?.cancel();
    _fallback?.cancel();
    super.dispose();
  }

  Future<void> _load() async {
    try {
      final payload = await _api.getTracking(widget.orderId);
      if (mounted) {
        _applyPayload(payload);
      }
    } catch (_) {
      if (mounted && _tracking == null) {
        setState(() => _error = 'Live rider location is unavailable.');
      }
    }
  }

  void _applyPayload(Map<String, dynamic> payload) {
    if (!mounted) return;
    setState(() {
      _tracking = payload;
      _error = null;
    });
    final freshness = payload['freshness'];
    if (freshness is Map && freshness['status'] == 'completed') {
      _subscription?.cancel();
      _subscription = null;
      _fallback?.cancel();
      _fallback = null;
    }
  }

  @override
  Widget build(BuildContext context) {
    final tracking = _tracking?['tracking'] as Map?;
    final location = _tracking?['location'] as Map?;
    final destination = _tracking?['destination'] as Map?;
    final freshness = _tracking?['freshness'] as Map?;
    final active = tracking?['is_active'] == true;
    final rider = location == null
        ? null
        : LatLng(
            (location['latitude'] as num).toDouble(),
            (location['longitude'] as num).toDouble(),
          );
    final dropOff = destination == null ||
            destination['latitude'] == null ||
            destination['longitude'] == null
        ? null
        : LatLng(
            (destination['latitude'] as num).toDouble(),
            (destination['longitude'] as num).toDouble(),
          );

    return DailyCartCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                active
                    ? Icons.location_searching_rounded
                    : Icons.location_off_outlined,
                color: active ? AppColors.primaryGreen : AppColors.mutedText,
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  'Active delivery tracking',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
              if (active)
                const Text(
                  'LIVE',
                  style: TextStyle(
                    color: AppColors.primaryGreen,
                    fontWeight: FontWeight.w900,
                  ),
                ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            _error ??
                (freshness?['message']?.toString() ??
                    (rider == null
                        ? 'Waiting for rider location'
                        : 'Live · updated just now')),
          ),
          if (freshness?['is_outdated'] == true) ...[
            const SizedBox(height: 8),
            const Text(
              'Location may be outdated',
              style: TextStyle(
                color: Colors.deepOrange,
                fontWeight: FontWeight.w800,
              ),
            ),
          ],
          if (location?['recorded_at'] != null) ...[
            const SizedBox(height: 4),
            Text(
              'Last updated: ${_time(location?['recorded_at'])}',
              style: Theme.of(context).textTheme.bodySmall,
            ),
          ],
          if (rider != null) ...[
            const SizedBox(height: 12),
            ClipRRect(
              borderRadius: BorderRadius.circular(18),
              child: SizedBox(
                height: 220,
                child: GoogleMap(
                  key: ValueKey('${rider.latitude}:${rider.longitude}'),
                  initialCameraPosition: CameraPosition(
                    target: rider,
                    zoom: 14,
                  ),
                  markers: {
                    Marker(
                      markerId: const MarkerId('rider'),
                      position: rider,
                      infoWindow: const InfoWindow(
                        title: 'Rider live location',
                      ),
                    ),
                    if (dropOff != null)
                      Marker(
                        markerId: const MarkerId('destination'),
                        position: dropOff,
                        infoWindow: const InfoWindow(title: 'Delivery address'),
                      ),
                  },
                  zoomControlsEnabled: false,
                  mapToolbarEnabled: false,
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  String _time(Object? value) {
    final parsed = DateTime.tryParse(value?.toString() ?? '');
    return parsed?.toLocal().toString().split('.').first ?? '-';
  }
}
