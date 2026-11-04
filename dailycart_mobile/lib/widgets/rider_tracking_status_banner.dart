import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';

import '../services/rider_delivery_tracking_service.dart';

class RiderTrackingStatusBanner extends StatelessWidget {
  const RiderTrackingStatusBanner({super.key});

  @override
  Widget build(BuildContext context) {
    final tracking = RiderDeliveryTrackingService.instance;
    return ValueListenableBuilder<RiderDeliveryTrackingState>(
      valueListenable: tracking.state,
      builder: (context, state, _) {
        final presentation = _presentation(state.phase);
        return Semantics(
          liveRegion: true,
          label: presentation.label,
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            decoration: BoxDecoration(
              color: presentation.color.withValues(alpha: 0.10),
              border: Border.all(
                color: presentation.color.withValues(alpha: 0.35),
              ),
              borderRadius: BorderRadius.circular(16),
            ),
            child: Row(
              children: [
                Icon(presentation.icon, color: presentation.color),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        presentation.label,
                        style: TextStyle(
                          color: presentation.color,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                      if (state.phase == RiderTrackingPhase.active &&
                          state.deliveryId != null)
                        Text(
                          'Delivery #${state.deliveryId} · location is shared only during this active delivery.',
                          style: Theme.of(context).textTheme.bodySmall,
                        ),
                      if (state.phase == RiderTrackingPhase.permissionRequired)
                        Text(
                          state.message ??
                              'Allow location all the time to resume an active delivery after restart.',
                          style: Theme.of(context).textTheme.bodySmall,
                        ),
                    ],
                  ),
                ),
                if (state.phase == RiderTrackingPhase.permissionRequired)
                  TextButton(
                    onPressed: Geolocator.openAppSettings,
                    child: const Text('Settings'),
                  ),
              ],
            ),
          ),
        );
      },
    );
  }

  _TrackingPresentation _presentation(RiderTrackingPhase phase) {
    return switch (phase) {
      RiderTrackingPhase.active => const _TrackingPresentation(
        'Tracking active',
        Icons.location_searching_rounded,
        Color(0xFF15803D),
      ),
      RiderTrackingPhase.offlineWaiting => const _TrackingPresentation(
        'Offline: location updates waiting',
        Icons.cloud_off_rounded,
        Color(0xFFB45309),
      ),
      RiderTrackingPhase.permissionRequired => const _TrackingPresentation(
        'Location permission required',
        Icons.location_disabled_rounded,
        Color(0xFFB91C1C),
      ),
      RiderTrackingPhase.stopped => const _TrackingPresentation(
        'Tracking stopped',
        Icons.location_off_outlined,
        Color(0xFF64748B),
      ),
    };
  }
}

class _TrackingPresentation {
  const _TrackingPresentation(this.label, this.icon, this.color);

  final String label;
  final IconData icon;
  final Color color;
}
