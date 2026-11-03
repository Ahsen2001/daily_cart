import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:geolocator/geolocator.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../providers/rider_delivery_provider.dart';
import '../../services/active_delivery_tracking_service.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/loading_widget.dart';

class RiderMapScreen extends ConsumerStatefulWidget {
  const RiderMapScreen({required this.deliveryId, super.key});

  final int deliveryId;

  @override
  ConsumerState<RiderMapScreen> createState() => _RiderMapScreenState();
}

class _RiderMapScreenState extends ConsumerState<RiderMapScreen> {
  Position? _position;
  final _tracking = ActiveDeliveryTrackingService.instance;

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
    final destination = LatLng(
      delivery?.latitude ?? 6.9271,
      delivery?.longitude ?? 79.8612,
    );
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
                ClipRRect(
                  borderRadius: BorderRadius.circular(24),
                  child: SizedBox(
                    height: 320,
                    child: GoogleMap(
                      initialCameraPosition: CameraPosition(
                        target: destination,
                        zoom: 14,
                      ),
                      myLocationEnabled: true,
                      markers: {
                        Marker(
                          markerId: const MarkerId('customer'),
                          position: destination,
                          infoWindow: const InfoWindow(title: 'Customer'),
                        ),
                        Marker(
                          markerId: const MarkerId('rider'),
                          position: riderLatLng,
                          infoWindow: const InfoWindow(title: 'You'),
                        ),
                      },
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                DailyCartCard(
                  child: Text(
                    _tracking.state.value.isTracking
                        ? 'Live location sharing is active for this delivery, including while the app is in the background.'
                        : (_tracking.state.value.message ??
                              'Live location sharing is inactive for this delivery.'),
                  ),
                ),
                const SizedBox(height: 16),
                CustomButton(
                  label: 'Update Current Location',
                  icon: Icons.my_location_rounded,
                  onPressed: _updateLocation,
                ),
                const SizedBox(height: 10),
                CustomButton(
                  label: 'Open Google Maps Navigation',
                  icon: Icons.navigation_outlined,
                  variant: CustomButtonVariant.secondary,
                  onPressed: delivery == null
                      ? null
                      : () => _openNavigation(destination),
                ),
              ],
            ),
    );
  }

  Future<void> _updateLocation() async {
    final ok = await _tracking.captureNow();
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(ok ? 'Location updated.' : 'Unable to update location.'),
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
