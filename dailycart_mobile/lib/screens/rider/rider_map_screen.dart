import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:geolocator/geolocator.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../providers/rider_delivery_provider.dart';
import '../../services/rider_delivery_tracking_service.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/rider_tracking_status_banner.dart';

class RiderMapScreen extends ConsumerStatefulWidget {
  const RiderMapScreen({required this.deliveryId, super.key});

  final int deliveryId;

  @override
  ConsumerState<RiderMapScreen> createState() => _RiderMapScreenState();
}

class _RiderMapScreenState extends ConsumerState<RiderMapScreen> {
  Position? _position;
  bool _isUpdatingLocation = false;
  final _tracking = RiderDeliveryTrackingService.instance;

  @override
  void initState() {
    super.initState();
    Future.microtask(() async {
      await ref
          .read(riderDeliveryProvider)
          .getDeliveryDetails(widget.deliveryId);
      final delivery = ref.read(riderDeliveryProvider).selectedDelivery;
      if (delivery != null) {
        await _tracking.syncWithStatus(delivery.id, delivery.status);
      }
    });
    _tracking.state.addListener(_trackingChanged);
  }

  @override
  void dispose() {
    _tracking.state.removeListener(_trackingChanged);
    super.dispose();
  }

  void _trackingChanged() {
    if (!mounted) return;
    setState(() => _position = _tracking.state.value.position);
  }

  @override
  Widget build(BuildContext context) {
    final deliveryState = ref.watch(riderDeliveryProvider);
    final delivery = deliveryState.selectedDelivery;
    final hasDestination = delivery?.hasValidDestinationCoordinates ?? false;
    final destination = hasDestination
        ? LatLng(delivery!.latitude!, delivery.longitude!)
        : null;
    final riderLatLng = _position == null
        ? destination
        : LatLng(_position!.latitude, _position!.longitude);

    return Scaffold(
      appBar: const CustomAppBar(title: 'Rider Map'),
      body: deliveryState.isLoading && delivery == null
          ? const LoadingWidget(message: 'Loading map...')
          : ListView(
              padding: const EdgeInsets.all(20),
              children: [
                if (destination != null)
                  ClipRRect(
                    borderRadius: BorderRadius.circular(24),
                    child: SizedBox(
                      height: 320,
                      child: GoogleMap(
                        initialCameraPosition: CameraPosition(
                          target: destination,
                          zoom: 16,
                        ),
                        myLocationEnabled: true,
                        markers: {
                          Marker(
                            markerId: const MarkerId('customer'),
                            position: destination,
                            infoWindow: const InfoWindow(
                              title: 'Customer delivery location',
                            ),
                          ),
                          if (riderLatLng != null)
                            Marker(
                              markerId: const MarkerId('rider'),
                              position: riderLatLng,
                              infoWindow: const InfoWindow(title: 'You'),
                            ),
                        },
                      ),
                    ),
                  )
                else
                  _MissingDeliveryLocation(
                    address: delivery?.deliveryAddress ?? '',
                  ),
                const SizedBox(height: 16),
                const RiderTrackingStatusBanner(),
                const SizedBox(height: 16),
                CustomButton(
                  label: 'Update Current Location',
                  icon: Icons.my_location_rounded,
                  isLoading: _isUpdatingLocation,
                  onPressed: _tracking.state.value.isTracking
                      ? _updateLocation
                      : null,
                ),
                const SizedBox(height: 10),
                CustomButton(
                  label: 'Open Google Maps Navigation',
                  icon: Icons.navigation_outlined,
                  variant: CustomButtonVariant.secondary,
                  onPressed: destination == null
                      ? null
                      : () => _openNavigation(destination),
                ),
              ],
            ),
    );
  }

  Future<void> _updateLocation() async {
    if (_isUpdatingLocation) return;
    setState(() => _isUpdatingLocation = true);
    final ok = await _tracking.captureNow();
    if (!mounted) return;
    setState(() => _isUpdatingLocation = false);
    final trackingMessage = _tracking.state.value.message;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          ok
              ? 'Location updated.'
              : trackingMessage ?? 'Unable to update location.',
        ),
      ),
    );
  }

  Future<void> _openNavigation(LatLng destination) async {
    final uri = Uri.parse(
      'https://www.google.com/maps/dir/?api=1&destination=${destination.latitude},${destination.longitude}&travelmode=driving',
    );
    final opened = await launchUrl(uri, mode: LaunchMode.externalApplication);
    if (!opened && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Unable to open a maps application.')),
      );
    }
  }
}

class _MissingDeliveryLocation extends StatelessWidget {
  const _MissingDeliveryLocation({required this.address});

  final String address;

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 260,
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.errorContainer,
        borderRadius: BorderRadius.circular(24),
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.location_off_outlined,
            size: 48,
            color: Theme.of(context).colorScheme.onErrorContainer,
          ),
          const SizedBox(height: 14),
          Text(
            'Customer map location unavailable',
            textAlign: TextAlign.center,
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.w800,
              color: Theme.of(context).colorScheme.onErrorContainer,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            address.isEmpty
                ? 'This order has no saved delivery coordinates.'
                : '$address\n\nThis order has no saved delivery coordinates.',
            textAlign: TextAlign.center,
            maxLines: 4,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(
              color: Theme.of(context).colorScheme.onErrorContainer,
            ),
          ),
        ],
      ),
    );
  }
}
