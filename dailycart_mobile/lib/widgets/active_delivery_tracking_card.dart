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

  @override
  void initState() {
    super.initState();
    _load();
    _subscription = _api.watchTracking(widget.orderId).listen((payload) {
      if (mounted) {
        setState(() => _tracking = payload);
      }
    });
    _fallback = Timer.periodic(const Duration(seconds: 30), (_) => _load());
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
        setState(() {
          _tracking = payload;
          _error = null;
        });
      }
    } catch (_) {
      if (mounted && _tracking == null) {
        setState(() => _error = 'Live rider location is unavailable.');
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final tracking = _tracking?['tracking'] as Map?;
    final location = _tracking?['location'] as Map?;
    final destination = _tracking?['destination'] as Map?;
    final active = tracking?['is_active'] == true;
    final rider = location == null
        ? null
        : LatLng(
            (location['latitude'] as num).toDouble(),
            (location['longitude'] as num).toDouble(),
          );
    final dropOff = destination == null
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
                (rider == null
                    ? 'Waiting for the rider’s first GPS update.'
                    : 'Last update: ${_time(location?['recorded_at'])}'),
          ),
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
