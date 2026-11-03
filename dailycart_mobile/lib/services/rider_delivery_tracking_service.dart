import 'dart:async';
import 'dart:io';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:flutter/foundation.dart';
import 'package:geolocator/geolocator.dart';

import '../models/rider_location_queue_point.dart';
import '../networking/api_exception.dart';
import '../utils/secure_storage_helper.dart';
import 'rider_location_api_service.dart';
import 'rider_location_queue.dart';
import 'rider_location_queue_synchronizer.dart';
import 'rider_tracking_lifecycle.dart';

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

  final state = ValueNotifier<RiderDeliveryTrackingState>(
    const RiderDeliveryTrackingState(),
  );
  final _api = RiderLocationApiService();
  final _queue = RiderLocationQueue();
  final _storage = SecureStorageHelper();
  late final _queueSynchronizer = RiderLocationQueueSynchronizer(
    queue: _queue,
    sender: _api.sendLocationBatch,
  );
  StreamSubscription<Position>? _subscription;
  StreamSubscription<List<ConnectivityResult>>? _connectivitySubscription;
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
      if (deliveryId == null ||
          status == null ||
          !RiderTrackingLifecycle.shouldTrack(status)) {
        await stop(sendToServer: false);
        return;
      }
      await start(deliveryId, status);
    } on ApiException catch (error) {
      if (!error.canRetry) {
        await stop(sendToServer: false);
      }
    }
  }

  Future<bool> start(int deliveryId, String status) async {
    final normalizedStatus = status.toLowerCase();
    if (!RiderTrackingLifecycle.shouldTrack(normalizedStatus)) {
      await stop();
      return false;
    }
    if (_subscription != null &&
        state.value.deliveryId == deliveryId &&
        _deliveryStatus == normalizedStatus) {
      return true;
    }
    final previousDeliveryId =
        state.value.deliveryId ?? await _storage.getTrackingDeliveryId();
    await stop(
      sendToServer: false,
      clearQueuedPoints:
          previousDeliveryId != null && previousDeliveryId != deliveryId,
    );
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

      _connectivitySubscription = Connectivity().onConnectivityChanged.listen(
        (results) {
          if (results.any((result) => result != ConnectivityResult.none)) {
            unawaited(_flushQueue());
          } else {
            state.value = RiderDeliveryTrackingState(
              deliveryId: deliveryId,
              position: state.value.position,
              isTracking: true,
              message: 'Offline: location updates waiting',
              phase: RiderTrackingPhase.offlineWaiting,
            );
          }
        },
      );

      unawaited(_flushQueue());
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
    if (RiderTrackingLifecycle.shouldTrack(status)) {
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

  Future<void> stop({
    bool sendToServer = true,
    bool clearQueuedPoints = true,
  }) async {
    final deliveryId =
        state.value.deliveryId ?? await _storage.getTrackingDeliveryId();
    await _subscription?.cancel();
    _subscription = null;
    _stationaryTimer?.cancel();
    _stationaryTimer = null;
    await _connectivitySubscription?.cancel();
    _connectivitySubscription = null;
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
    if (clearQueuedPoints && deliveryId != null) {
      await _queue.clearDelivery(deliveryId);
    }
  }

  Future<void> _send(Position position) async {
    final deliveryId = await _storage.getTrackingDeliveryId();
    if (deliveryId == null || !state.value.isTracking) return;
    final sequenceNumber = await _queue.nextSequence(deliveryId);
    await _queue.add(
      RiderLocationQueuePoint(
        pointUuid: _queue.newPointUuid(),
        deliveryId: deliveryId,
        latitude: position.latitude,
        longitude: position.longitude,
        accuracyMeters: position.accuracy,
        speed: position.speed < 0 ? null : position.speed,
        heading: position.heading < 0 ? null : position.heading,
        recordedAt: position.timestamp.toUtc(),
        sequenceNumber: sequenceNumber,
      ),
    );
    await _flushQueue();
  }

  Future<void> _flushQueue() async {
    if (_sending || !state.value.isTracking) return;
    final deliveryId = await _storage.getTrackingDeliveryId();
    final token = await _storage.getTrackingToken();
    if (deliveryId == null || token == null) return;

    _sending = true;
    try {
      final acknowledgedCount = await _queueSynchronizer.flush(
        deliveryId: deliveryId,
        trackingToken: token,
        shouldContinue: () => state.value.isTracking,
      );
      if (acknowledgedCount > 0) {
        _lastSentAt = DateTime.now();
      }
      state.value = RiderDeliveryTrackingState(
        deliveryId: deliveryId,
        position: state.value.position,
        isTracking: true,
        message: 'Tracking active',
        phase: RiderTrackingPhase.active,
      );
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
    } finally {
      _sending = false;
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
