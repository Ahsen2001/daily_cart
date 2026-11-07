import 'package:flutter/material.dart';
import 'package:geocoding/geocoding.dart';
import 'package:geolocator/geolocator.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';

import '../../widgets/custom_app_bar.dart';
import '../../widgets/custom_button.dart';

class MapPickerScreen extends StatefulWidget {
  const MapPickerScreen({
    this.initialLatitude,
    this.initialLongitude,
    super.key,
  });

  final double? initialLatitude;
  final double? initialLongitude;

  @override
  State<MapPickerScreen> createState() => _MapPickerScreenState();
}

class _MapPickerScreenState extends State<MapPickerScreen> {
  static const _colombo = LatLng(6.9271, 79.8612);
  late LatLng _cameraTarget;
  LatLng? _selected;
  GoogleMapController? _controller;
  bool _locating = false;
  bool _cameraMoved = false;

  @override
  void initState() {
    super.initState();
    final hasInitialLocation =
        widget.initialLatitude != null && widget.initialLongitude != null;
    _cameraTarget = hasInitialLocation
        ? LatLng(widget.initialLatitude!, widget.initialLongitude!)
        : _colombo;
    _selected = hasInitialLocation ? _cameraTarget : null;
    if (widget.initialLatitude == null) {
      WidgetsBinding.instance.addPostFrameCallback(
        (_) => _useCurrentLocation(),
      );
    }
  }

  @override
  void dispose() {
    _controller?.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: const CustomAppBar(title: 'Choose delivery location'),
      body: Stack(
        children: [
          GoogleMap(
            initialCameraPosition: CameraPosition(
              target: _cameraTarget,
              zoom: 16,
            ),
            myLocationButtonEnabled: false,
            myLocationEnabled: true,
            onMapCreated: (controller) => _controller = controller,
            onCameraMoveStarted: () => _cameraMoved = true,
            onCameraMove: (position) => _cameraTarget = position.target,
            onCameraIdle: () {
              if (!_cameraMoved || !mounted) return;
              setState(() {
                _selected = _cameraTarget;
                _cameraMoved = false;
              });
            },
          ),
          Center(
            child: Padding(
              padding: const EdgeInsets.only(bottom: 38),
              child: Icon(
                Icons.location_pin,
                size: 48,
                color: _selected == null ? Colors.grey : Colors.red,
              ),
            ),
          ),
          Positioned(
            right: 16,
            top: 16,
            child: FloatingActionButton.small(
              heroTag: 'current-location',
              onPressed: _locating ? null : _useCurrentLocation,
              child: _locating
                  ? const SizedBox.square(
                      dimension: 18,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : const Icon(Icons.my_location),
            ),
          ),
          Positioned(
            left: 20,
            right: 20,
            bottom: 24,
            child: CustomButton(
              label: _selected == null
                  ? 'Move map or use current location'
                  : 'Use this location',
              icon: Icons.check_circle_outline,
              onPressed: _selected == null ? null : _confirm,
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _useCurrentLocation() async {
    setState(() => _locating = true);
    try {
      var permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }
      if (permission == LocationPermission.denied ||
          permission == LocationPermission.deniedForever) {
        _showMessage('Location permission is required to use your position.');
        return;
      }
      final position = await Geolocator.getCurrentPosition();
      _selected = LatLng(position.latitude, position.longitude);
      _cameraTarget = _selected!;
      await _controller?.animateCamera(
        CameraUpdate.newLatLngZoom(_selected!, 16),
      );
    } catch (_) {
      _showMessage('Unable to get your current location.');
    } finally {
      if (mounted) setState(() => _locating = false);
    }
  }

  Future<void> _confirm() async {
    final selected = _selected;
    if (selected == null) {
      _showMessage('Choose the exact delivery point first.');
      return;
    }
    Placemark? place;
    try {
      final places = await placemarkFromCoordinates(
        selected.latitude,
        selected.longitude,
      );
      place = places.isEmpty ? null : places.first;
    } catch (_) {
      // Coordinates remain usable when reverse geocoding is unavailable.
    }
    if (!mounted) return;
    Navigator.of(context).pop(<String, Object?>{
      'latitude': selected.latitude,
      'longitude': selected.longitude,
      'address_line_1': place?.street,
      'city': place?.locality ?? place?.subAdministrativeArea,
      'district': place?.administrativeArea,
      'postal_code': place?.postalCode,
    });
  }

  void _showMessage(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(
      context,
    ).showSnackBar(SnackBar(content: Text(message)));
  }
}
