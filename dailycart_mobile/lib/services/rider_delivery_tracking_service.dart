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

enum RiderTrackingPhase { active, offlineWaiting, permissionRequired, stopped }

class RiderDeliveryTrackingState {
  const RiderDeliveryTrackingState({
    this.deliveryId,
    this.position,
    this.isTracking = false,
    this.message,
    this.phase = RiderTrackingPhase.stopped,
  });

  final int? deliveryId;
  final Position? position;
  final bool isTracking;
  final String? message;
  final RiderTrackingPhase phase;
}

class RiderDeliveryTrackingService {
  RiderDeliveryTrackingService._();

  static final instance = RiderDeliveryTrackingService._();
  static const _pendingLocationKey = 'pending_active_delivery_location';
  static const _trackableStatuses = {'accepted', 'picked_up', 'on_the_way'};

  final state = ValueNotifier<RiderDeliveryTrackingState>(
    const RiderDeliveryTrackingState(),
  );
  final _api = RiderLocationApiService();
  final _storage = SecureStorageHelper();
  StreamSubscription<Position>? _subscription;
  Timer? _stationaryTimer;
  bool _sending = false;
  String? _deliveryStatus;
  Position? _lastAcceptedPosition;
  DateTime? _lastSentAt;

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
      await start(deliveryId, status!);
    } on ApiException catch (error) {
      if (!error.canRetry) {
        await stop(sendToServer: false);
      }
    }
  }

  Future<bool> start(int deliveryId, String status) async {
    final normalizedStatus = status.toLowerCase();
    if (!_trackableStatuses.contains(normalizedStatus)) {
      await stop();
      return false;
    }
    if (_subscription != null &&
        state.value.deliveryId == deliveryId &&
        _deliveryStatus == normalizedStatus) {
      return true;
    }
    await stop(sendToServer: false);
    _deliveryStatus = normalizedStatus;

    final permissionGranted = await _ensurePermission();
    if (!permissionGranted) {
      state.value = RiderDeliveryTrackingState(
        deliveryId: deliveryId,
        message: 'Location permission required',
        phase: RiderTrackingPhase.permissionRequired,
      );
      return false;
    }

    try {
      final token = await _api.startTracking(deliveryId);
      await _storage.saveTrackingSession(deliveryId, token);
      state.value = RiderDeliveryTrackingState(
        deliveryId: deliveryId,
        isTracking: true,
        message: 'Tracking active',
        phase: RiderTrackingPhase.active,
      );

      _subscription =
          Geolocator.getPositionStream(
            locationSettings: _locationSettings(normalizedStatus),
          ).listen(
            (position) {
              if (!_isValidPosition(position)) return;
              _lastAcceptedPosition = position;
              state.value = RiderDeliveryTrackingState(
                deliveryId: deliveryId,
                position: position,
                isTracking: true,
                message: 'Tracking active',
                phase: RiderTrackingPhase.active,
              );
              unawaited(_send(position));
            },
            onError: (Object error) {
              state.value = RiderDeliveryTrackingState(
                deliveryId: deliveryId,
                position: state.value.position,
                isTracking: true,
                message: 'Offline: location updates waiting',
                phase: RiderTrackingPhase.offlineWaiting,
              );
            },
          );
      _stationaryTimer = Timer.periodic(const Duration(seconds: 60), (_) {
        final lastSentAt = _lastSentAt;
        if (lastSentAt == null ||
            DateTime.now().difference(lastSentAt) >=
                const Duration(seconds: 55)) {
          unawaited(captureNow());
        }
      });

      unawaited(_flushPending());
      try {
        final current = await Geolocator.getCurrentPosition(
          locationSettings: _locationSettings(normalizedStatus),
        );
        if (_isValidPosition(current)) {
          _lastAcceptedPosition = current;
          state.value = RiderDeliveryTrackingState(
            deliveryId: deliveryId,
            position: current,
            isTracking: true,
            message: 'Tracking active',
            phase: RiderTrackingPhase.active,
          );
          unawaited(_send(current));
        }
      } catch (_) {
        // The stream remains authoritative if an immediate fix is unavailable.
      }
      return true;
    } on ApiException catch (error) {
      state.value = RiderDeliveryTrackingState(
        deliveryId: deliveryId,
        message: error.message,
        phase: RiderTrackingPhase.stopped,
      );
      return false;
    }
  }

  Future<void> syncWithStatus(int deliveryId, String status) async {
    if (_trackableStatuses.contains(status.toLowerCase())) {
      await start(deliveryId, status);
    } else if (state.value.deliveryId == deliveryId) {
      await stop();
    }
  }

  Future<bool> captureNow() async {
    if (!state.value.isTracking) return false;
    try {
      final position = await Geolocator.getCurrentPosition(
        locationSettings: _locationSettings(_deliveryStatus ?? 'accepted'),
      );
      if (!_isValidPosition(position)) return false;
      _lastAcceptedPosition = position;
      state.value = RiderDeliveryTrackingState(
        deliveryId: state.value.deliveryId,
        position: position,
        isTracking: true,
        message: 'Tracking active',
        phase: RiderTrackingPhase.active,
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
    _stationaryTimer?.cancel();
    _stationaryTimer = null;
    _deliveryStatus = null;
    _lastAcceptedPosition = null;
    _lastSentAt = null;
    state.value = const RiderDeliveryTrackingState(
      message: 'Tracking stopped',
      phase: RiderTrackingPhase.stopped,
    );

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
      _lastSentAt = DateTime.now();
      state.value = RiderDeliveryTrackingState(
        deliveryId: deliveryId,
        position: position,
        isTracking: true,
        message: 'Tracking active',
        phase: RiderTrackingPhase.active,
      );
    } on ApiException catch (error) {
      if (error.statusCode == 401 ||
          error.statusCode == 403 ||
          error.statusCode == 422) {
        await stop(sendToServer: false);
      } else {
        await _savePending(position);
        state.value = RiderDeliveryTrackingState(
          deliveryId: deliveryId,
          position: position,
          isTracking: true,
          message: 'Offline: location updates waiting',
          phase: RiderTrackingPhase.offlineWaiting,
        );
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
      if ({401, 403, 422}.contains(error.statusCode)) {
        await stop(sendToServer: false);
      } else {
        state.value = RiderDeliveryTrackingState(
          deliveryId: deliveryId,
          position: state.value.position,
          isTracking: true,
          message: 'Offline: location updates waiting',
          phase: RiderTrackingPhase.offlineWaiting,
        );
      }
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

  bool _isValidPosition(Position position) {
    final latitude = position.latitude;
    final longitude = position.longitude;
    if (!latitude.isFinite ||
        !longitude.isFinite ||
        latitude < -90 ||
        latitude > 90 ||
        longitude < -180 ||
        longitude > 180) {
      return false;
    }
    if (!position.accuracy.isFinite ||
        position.accuracy <= 0 ||
        position.accuracy > 100) {
      return false;
    }
    final now = DateTime.now();
    if (position.timestamp.isBefore(now.subtract(const Duration(minutes: 2))) ||
        position.timestamp.isAfter(now.add(const Duration(seconds: 30)))) {
      return false;
    }
    if (position.speed.isFinite && position.speed > 70) return false;

    final previous = _lastAcceptedPosition;
    if (previous != null) {
      if (!position.timestamp.isAfter(previous.timestamp)) return false;
      final seconds =
          position.timestamp
              .difference(previous.timestamp)
              .inMilliseconds
              .abs() /
          1000;
      if (seconds > 0) {
        final distance = Geolocator.distanceBetween(
          previous.latitude,
          previous.longitude,
          latitude,
          longitude,
        );
        if (distance / seconds > 70) return false;
      }
    }
    return true;
  }

  LocationSettings _locationSettings(String status) {
    final onTheWay = status == 'on_the_way';
    final distanceFilter = onTheWay ? 20 : 100;
    final interval = onTheWay
        ? const Duration(seconds: 7)
        : const Duration(seconds: 45);
    if (Platform.isAndroid) {
      return AndroidSettings(
        accuracy: LocationAccuracy.high,
        distanceFilter: distanceFilter,
        intervalDuration: interval,
        foregroundNotificationConfig: const ForegroundNotificationConfig(
          notificationTitle: 'DailyCart active delivery',
          notificationText:
              'DailyCart is sharing your location for an active delivery.',
          enableWakeLock: true,
          setOngoing: true,
        ),
      );
    }
    if (Platform.isIOS) {
      return AppleSettings(
        accuracy: LocationAccuracy.high,
        activityType: ActivityType.automotiveNavigation,
        distanceFilter: distanceFilter,
        pauseLocationUpdatesAutomatically: false,
        showBackgroundLocationIndicator: true,
      );
    }
    return LocationSettings(
      accuracy: LocationAccuracy.high,
      distanceFilter: distanceFilter,
    );
  }
}
