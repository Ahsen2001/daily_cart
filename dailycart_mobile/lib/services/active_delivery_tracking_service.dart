import 'dart:async';
import 'dart:convert';
import 'dart:io';

import 'package:flutter/foundation.dart';
import 'package:geolocator/geolocator.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../models/rider_location_model.dart';
import '../networking/api_exception.dart';
import '../utils/secure_storage_helper.dart';
import 'rider_location_api_service.dart';

class ActiveDeliveryTrackingState {
  const ActiveDeliveryTrackingState({
    this.deliveryId,
    this.position,
    this.isTracking = false,
    this.message,
  });

  final int? deliveryId;
  final Position? position;
  final bool isTracking;
  final String? message;
}

class ActiveDeliveryTrackingService {
  ActiveDeliveryTrackingService._();

  static final instance = ActiveDeliveryTrackingService._();
  static const _pendingLocationKey = 'pending_active_delivery_location';
  static const _trackableStatuses = {'accepted', 'picked_up', 'on_the_way'};

  final state = ValueNotifier<ActiveDeliveryTrackingState>(
    const ActiveDeliveryTrackingState(),
  );
  final _api = RiderLocationApiService();
  final _storage = SecureStorageHelper();
  StreamSubscription<Position>? _subscription;
  bool _sending = false;

  Future<void> resumeIfNeeded() async {
    if (await _storage.getToken() == null) return;
    try {
      final active = await _api.activeDelivery();
      final deliveryId = int.tryParse(active?['id']?.toString() ?? '');
      final status = active?['status']?.toString().toLowerCase();
      if (deliveryId == null || !_trackableStatuses.contains(status)) {
        await stop(sendToServer: false);
        return;
      }
      await start(deliveryId);
    } on ApiException catch (error) {
      if (!error.canRetry) {
        await stop(sendToServer: false);
      }
    }
  }

  Future<bool> start(int deliveryId) async {
    if (_subscription != null && state.value.deliveryId == deliveryId) {
      return true;
    }
    await stop(sendToServer: false);

    final permissionGranted = await _ensurePermission();
    if (!permissionGranted) {
      state.value = ActiveDeliveryTrackingState(
        deliveryId: deliveryId,
        message: 'Allow location access to track this active delivery.',
      );
      return false;
    }

    try {
      final token = await _api.startTracking(deliveryId);
      await _storage.saveTrackingSession(deliveryId, token);
      state.value = ActiveDeliveryTrackingState(
        deliveryId: deliveryId,
        isTracking: true,
      );

      _subscription =
          Geolocator.getPositionStream(
            locationSettings: _locationSettings(),
          ).listen(
            (position) {
              state.value = ActiveDeliveryTrackingState(
                deliveryId: deliveryId,
                position: position,
                isTracking: true,
              );
              unawaited(_send(position));
            },
            onError: (Object error) {
              state.value = ActiveDeliveryTrackingState(
                deliveryId: deliveryId,
                position: state.value.position,
                isTracking: true,
                message: 'Waiting for a GPS location update.',
              );
            },
          );

      unawaited(_flushPending());
      try {
        final current = await Geolocator.getCurrentPosition(
          locationSettings: _locationSettings(),
        );
        state.value = ActiveDeliveryTrackingState(
          deliveryId: deliveryId,
          position: current,
          isTracking: true,
        );
        unawaited(_send(current));
      } catch (_) {
        // The stream remains authoritative if an immediate fix is unavailable.
      }
      return true;
    } on ApiException catch (error) {
      state.value = ActiveDeliveryTrackingState(
        deliveryId: deliveryId,
        message: error.message,
      );
      return false;
    }
  }

  Future<void> syncWithStatus(int deliveryId, String status) async {
    if (_trackableStatuses.contains(status.toLowerCase())) {
      await start(deliveryId);
    } else if (state.value.deliveryId == deliveryId) {
      await stop();
    }
  }

  Future<bool> captureNow() async {
    if (!state.value.isTracking) return false;
    try {
      final position = await Geolocator.getCurrentPosition(
        locationSettings: _locationSettings(),
      );
      state.value = ActiveDeliveryTrackingState(
        deliveryId: state.value.deliveryId,
        position: position,
        isTracking: true,
      );
      await _send(position);
      return state.value.isTracking;
    } catch (_) {
      return false;
    }
  }

  Future<void> stop({bool sendToServer = true}) async {
    final deliveryId =
        state.value.deliveryId ?? await _storage.getTrackingDeliveryId();
    await _subscription?.cancel();
    _subscription = null;
    state.value = const ActiveDeliveryTrackingState();

    if (sendToServer &&
        deliveryId != null &&
        await _storage.getToken() != null) {
      try {
        await _api.stopTracking(deliveryId);
      } catch (_) {
        // The backend independently invalidates terminal/reassigned sessions.
      }
    }
    await _storage.clearTrackingSession();
    final preferences = await SharedPreferences.getInstance();
    await preferences.remove(_pendingLocationKey);
  }

  Future<void> _send(Position position) async {
    if (_sending) {
      await _savePending(position);
      return;
    }
    final deliveryId = await _storage.getTrackingDeliveryId();
    final token = await _storage.getTrackingToken();
    if (deliveryId == null || token == null || !state.value.isTracking) return;

    _sending = true;
    try {
      await _api.updateRiderLocation(
        RiderLocationModel(
          latitude: position.latitude,
          longitude: position.longitude,
          updatedAt: position.timestamp,
        ),
        deliveryId: deliveryId,
        trackingToken: token,
        accuracy: position.accuracy,
        altitude: position.altitude,
        speed: position.speed < 0 ? null : position.speed,
        heading: position.heading < 0 ? null : position.heading,
        recordedAt: position.timestamp,
      );
      final preferences = await SharedPreferences.getInstance();
      await preferences.remove(_pendingLocationKey);
    } on ApiException catch (error) {
      if (error.statusCode == 401 ||
          error.statusCode == 403 ||
          error.statusCode == 422) {
        await stop(sendToServer: false);
      } else {
        await _savePending(position);
      }
    } finally {
      _sending = false;
    }
  }

  Future<void> _savePending(Position position) async {
    final preferences = await SharedPreferences.getInstance();
    await preferences.setString(
      _pendingLocationKey,
      jsonEncode({
        'latitude': position.latitude,
        'longitude': position.longitude,
        'accuracy': position.accuracy,
        'altitude': position.altitude,
        'speed': position.speed,
        'heading': position.heading,
        'recorded_at': position.timestamp.toUtc().toIso8601String(),
      }),
    );
  }

  Future<void> _flushPending() async {
    final preferences = await SharedPreferences.getInstance();
    final raw = preferences.getString(_pendingLocationKey);
    if (raw == null) return;
    final data = jsonDecode(raw) as Map<String, dynamic>;
    final deliveryId = await _storage.getTrackingDeliveryId();
    final token = await _storage.getTrackingToken();
    if (deliveryId == null || token == null) return;
    try {
      await _api.updateRiderLocation(
        RiderLocationModel(
          latitude: (data['latitude'] as num).toDouble(),
          longitude: (data['longitude'] as num).toDouble(),
        ),
        deliveryId: deliveryId,
        trackingToken: token,
        accuracy: (data['accuracy'] as num?)?.toDouble(),
        altitude: (data['altitude'] as num?)?.toDouble(),
        speed: (data['speed'] as num?)?.toDouble(),
        heading: (data['heading'] as num?)?.toDouble(),
        recordedAt: DateTime.tryParse(data['recorded_at']?.toString() ?? ''),
      );
      await preferences.remove(_pendingLocationKey);
    } on ApiException catch (error) {
      if (!error.canRetry) await stop(sendToServer: false);
    }
  }

  Future<bool> _ensurePermission() async {
    if (!await Geolocator.isLocationServiceEnabled()) return false;
    var permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
    }
    return permission == LocationPermission.always ||
        permission == LocationPermission.whileInUse;
  }

  LocationSettings _locationSettings() {
    if (Platform.isAndroid) {
      return AndroidSettings(
        accuracy: LocationAccuracy.high,
        distanceFilter: 15,
        intervalDuration: const Duration(seconds: 10),
        foregroundNotificationConfig: const ForegroundNotificationConfig(
          notificationTitle: 'DailyCart active delivery',
          notificationText: 'Sharing your location for the assigned delivery.',
          enableWakeLock: true,
          setOngoing: true,
        ),
      );
    }
    if (Platform.isIOS) {
      return AppleSettings(
        accuracy: LocationAccuracy.high,
        activityType: ActivityType.automotiveNavigation,
        distanceFilter: 15,
        pauseLocationUpdatesAutomatically: false,
        showBackgroundLocationIndicator: true,
      );
    }
    return const LocationSettings(
      accuracy: LocationAccuracy.high,
      distanceFilter: 15,
    );
  }
}
