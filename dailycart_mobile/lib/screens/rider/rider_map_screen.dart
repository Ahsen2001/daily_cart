import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:geolocator/geolocator.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';

import '../../providers/rider_delivery_provider.dart';
import '../../providers/rider_location_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/loading_widget.dart';

class RiderMapScreen extends ConsumerStatefulWidget {
  const RiderMapScreen({
    required this.deliveryId,
    super.key,
  });

  final int deliveryId;

  @override
  ConsumerState<RiderMapScreen> createState() => _RiderMapScreenState();
}

class _RiderMapScreenState extends ConsumerState<RiderMapScreen> {
  Position? _position;

  @override
  void initState() {
    super.initState();
    Future.microtask(() async {
      await ref.read(riderDeliveryProvider).getDeliveryDetails(widget.deliveryId);
      await _loadCurrentLocation();
    });
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
                const DailyCartCard(
                  child: Text(
                    'Route directions placeholder. Open Google Maps navigation from the customer location marker when platform URL handling is connected.',
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
                  onPressed: () {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Google Maps navigation placeholder.'),
                      ),
                    );
                  },
                ),
              ],
            ),
    );
  }

  Future<void> _loadCurrentLocation() async {
    final permission = await Geolocator.requestPermission();
    if (permission == LocationPermission.denied ||
        permission == LocationPermission.deniedForever) {
      return;
    }
    final position = await Geolocator.getCurrentPosition();
    if (mounted) {
      setState(() => _position = position);
    }
  }

  Future<void> _updateLocation() async {
    await _loadCurrentLocation();
    final position = _position;
    if (position == null) return;
    final ok = await ref.read(riderLocationProvider).updateRiderLocation(
          latitude: position.latitude,
          longitude: position.longitude,
        );
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(ok ? 'Location updated.' : 'Unable to update location.'),
      ),
    );
  }
}
