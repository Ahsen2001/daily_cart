import 'dart:async';
import 'dart:io';
import 'dart:ui';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_background_service/flutter_background_service.dart';
import 'package:flutter_dotenv/flutter_dotenv.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:geolocator/geolocator.dart';

import '../config/app_identity.dart';
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
  RiderDeliveryTrackingService._({bool backgroundWorker = false})
    : _backgroundWorker = backgroundWorker;

  static final instance = RiderDeliveryTrackingService._();
  static final _workerInstance = RiderDeliveryTrackingService._(
    backgroundWorker: true,
  );

  static const _trackingStateEvent = 'rider_tracking_state';
  static const _startTrackingEvent = 'rider_tracking_start';
  static const _stopTrackingEvent = 'rider_tracking_stop';
  static const _androidTrackingChannelId = 'dailycart_active_delivery_tracking';
  static bool _androidServiceConfigured = false;

  final bool _backgroundWorker;
  StreamSubscription<Map<String, dynamic>?>? _workerStateSubscription;

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
  Future<bool>? _startOperation;
  String? _deliveryStatus;
  Position? _lastAcceptedPosition;
  DateTime? _lastSentAt;

  static Future<void> initializeBackgroundWorker() async {
    if (!Platform.isAndroid || _androidServiceConfigured) return;
    await _ensureAndroidTrackingNotificationChannel();
    final service = FlutterBackgroundService();
    await service.configure(
      androidConfiguration: AndroidConfiguration(
        onStart: riderTrackingBackgroundEntryPoint,
        autoStart: false,
        autoStartOnBoot: true,
        isForegroundMode: true,
        notificationChannelId: _androidTrackingChannelId,
        initialNotificationTitle: 'DailyCart active delivery',
        initialNotificationContent:
            'DailyCart is sharing your location for an active delivery.',
        foregroundServiceNotificationId: 4182,
        foregroundServiceTypes: const [AndroidForegroundType.location],
      ),
      iosConfiguration: IosConfiguration(autoStart: false),
    );
    _androidServiceConfigured = true;
    instance._listenToWorkerState(service);
  }

  static Future<void> _ensureAndroidTrackingNotificationChannel() async {
    final notifications = FlutterLocalNotificationsPlugin();
    final android = notifications
        .resolvePlatformSpecificImplementation<
          AndroidFlutterLocalNotificationsPlugin
        >();
    await android?.createNotificationChannel(
      const AndroidNotificationChannel(
        _androidTrackingChannelId,
        'Active delivery tracking',
        description:
            'Shows when DailyCart is sharing location for an active delivery.',
        importance: Importance.low,
        playSound: false,
        enableVibration: false,
        showBadge: false,
      ),
    );
  }

  void _listenToWorkerState(FlutterBackgroundService service) {
    _workerStateSubscription ??= service.on(_trackingStateEvent).listen((data) {
      if (data == null) return;
      final phaseName = data['phase']?.toString();
      final phase = RiderTrackingPhase.values.firstWhere(
        (value) => value.name == phaseName,
        orElse: () => RiderTrackingPhase.stopped,
      );
      state.value = RiderDeliveryTrackingState(
        deliveryId: int.tryParse(data['delivery_id']?.toString() ?? ''),
        isTracking: data['is_tracking'] == true,
        message: data['message']?.toString(),
        phase: phase,
      );
    });
  }

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
    if (Platform.isAndroid && !_backgroundWorker) {
      return _startAndroidWorker(deliveryId, normalizedStatus);
    }
    return _startInProcess(deliveryId, normalizedStatus);
  }

  Future<bool> _startAndroidWorker(int deliveryId, String status) async {
    await initializeBackgroundWorker();
    final permissionGranted = await _ensurePermission();
    if (!permissionGranted) {
      state.value = RiderDeliveryTrackingState(
        deliveryId: deliveryId,
        message: Platform.isAndroid
            ? 'Location permission required: allow all the time'
            : 'Location permission required',
        phase: RiderTrackingPhase.permissionRequired,
      );
      return false;
    }
    try {
      final token = await _api.startTracking(deliveryId);
      await _storage.saveTrackingSession(deliveryId, token, status: status);
      final service = FlutterBackgroundService();
      if (!await service.isRunning()) {
        await service.startService();
      }
      service.invoke(_startTrackingEvent, {
        'delivery_id': deliveryId,
        'status': status,
      });
      state.value = RiderDeliveryTrackingState(
        deliveryId: deliveryId,
        isTracking: true,
        message: 'Tracking active',
        phase: RiderTrackingPhase.active,
      );
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

  Future<bool> _startInProcess(
    int deliveryId,
    String normalizedStatus, {
    bool reuseStoredSession = false,
  }) async {
    final ongoing = _startOperation;
    if (ongoing != null) return ongoing;
    final operation = _startInProcessOnce(
      deliveryId,
      normalizedStatus,
      reuseStoredSession: reuseStoredSession,
    );
    _startOperation = operation;
    try {
      return await operation;
    } finally {
      _startOperation = null;
    }
  }

  Future<bool> _startInProcessOnce(
    int deliveryId,
    String normalizedStatus, {
    required bool reuseStoredSession,
  }) async {
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
        message: Platform.isAndroid
            ? 'Location permission required: allow all the time'
            : 'Location permission required',
        phase: RiderTrackingPhase.permissionRequired,
      );
      return false;
    }

    try {
      final existingDeliveryId = await _storage.getTrackingDeliveryId();
      final existingToken = await _storage.getTrackingToken();
      final token =
          reuseStoredSession &&
              existingDeliveryId == deliveryId &&
              existingToken != null
          ? existingToken
          : await _api.startTracking(deliveryId);
      await _storage.saveTrackingSession(
        deliveryId,
        token,
        status: normalizedStatus,
      );
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

      _connectivitySubscription = Connectivity().onConnectivityChanged.listen((
        results,
      ) {
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
      });

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
    if (!state.value.isTracking) {
      state.value = RiderDeliveryTrackingState(
        deliveryId: state.value.deliveryId,
        position: state.value.position,
        message: state.value.phase == RiderTrackingPhase.permissionRequired
            ? 'Location permission is required before updating your location.'
            : 'Location updates are available only during an active delivery.',
        phase: state.value.phase,
      );
      return false;
    }
    try {
      final position = await Geolocator.getCurrentPosition(
        locationSettings: _singleFixLocationSettings(),
      );
      if (!_isValidPosition(position)) {
        state.value = RiderDeliveryTrackingState(
          deliveryId: state.value.deliveryId,
          position: state.value.position,
          isTracking: true,
          message: 'GPS accuracy is too low. Move to an open area and retry.',
          phase: RiderTrackingPhase.active,
        );
        return false;
      }
      _lastAcceptedPosition = position;
      state.value = RiderDeliveryTrackingState(
        deliveryId: state.value.deliveryId,
        position: position,
        isTracking: true,
        message: 'Tracking active',
        phase: RiderTrackingPhase.active,
      );
      // Persist the manual fix first and let the durable queue upload it in
      // the background. The button must not remain blocked by a slow API.
      await _send(position, waitForUpload: false);
      return state.value.isTracking;
    } on TimeoutException {
      state.value = RiderDeliveryTrackingState(
        deliveryId: state.value.deliveryId,
        position: state.value.position,
        isTracking: true,
        message: 'GPS timed out. Move to an open area and retry.',
        phase: RiderTrackingPhase.active,
      );
      return false;
    } catch (_) {
      state.value = RiderDeliveryTrackingState(
        deliveryId: state.value.deliveryId,
        position: state.value.position,
        isTracking: true,
        message: 'Unable to read GPS. Check that device location is enabled.',
        phase: RiderTrackingPhase.active,
      );
      return false;
    }
  }

  Future<void> stop({
    bool sendToServer = true,
    bool clearQueuedPoints = true,
  }) async {
    final deliveryId =
        state.value.deliveryId ?? await _storage.getTrackingDeliveryId();
    if (Platform.isAndroid && !_backgroundWorker) {
      final service = FlutterBackgroundService();
      if (await service.isRunning()) {
        service.invoke(_stopTrackingEvent);
      }
      state.value = const RiderDeliveryTrackingState(
        message: 'Tracking stopped',
        phase: RiderTrackingPhase.stopped,
      );
      if (sendToServer &&
          deliveryId != null &&
          await _storage.getToken() != null) {
        try {
          await _api.stopTracking(deliveryId);
        } catch (_) {}
      }
      await _storage.clearTrackingSession();
      if (clearQueuedPoints && deliveryId != null) {
        await _queue.clearDelivery(deliveryId);
      }
      return;
    }
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

  Future<void> _send(Position position, {bool waitForUpload = true}) async {
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
    if (waitForUpload) {
      await _flushQueue();
    } else {
      unawaited(_flushQueue());
    }
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
    if (Platform.isAndroid) {
      // Tracking is always started while the Rider app is visible and then
      // continues through Android's location foreground service. Requiring
      // "Allow all the time" here incorrectly rejects a valid while-in-use
      // grant and prevents the foreground service from starting.
      return permission == LocationPermission.always ||
          permission == LocationPermission.whileInUse;
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
        foregroundNotificationConfig: _backgroundWorker
            ? null
            : const ForegroundNotificationConfig(
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

  LocationSettings _singleFixLocationSettings() {
    const timeout = Duration(seconds: 15);
    if (Platform.isAndroid) {
      return AndroidSettings(
        accuracy: LocationAccuracy.high,
        distanceFilter: 0,
        timeLimit: timeout,
      );
    }
    if (Platform.isIOS) {
      return AppleSettings(
        accuracy: LocationAccuracy.high,
        distanceFilter: 0,
        timeLimit: timeout,
      );
    }
    return const LocationSettings(
      accuracy: LocationAccuracy.high,
      distanceFilter: 0,
      timeLimit: timeout,
    );
  }
}

@pragma('vm:entry-point')
Future<void> riderTrackingBackgroundEntryPoint(ServiceInstance service) async {
  WidgetsFlutterBinding.ensureInitialized();
  DartPluginRegistrant.ensureInitialized();
  AppIdentity.configure(DailyCartAppFlavor.rider);
  try {
    await dotenv.load(fileName: '.env');
  } catch (_) {}

  final tracker = RiderDeliveryTrackingService._workerInstance;

  void publishState() {
    final current = tracker.state.value;
    service.invoke(RiderDeliveryTrackingService._trackingStateEvent, {
      'delivery_id': current.deliveryId,
      'is_tracking': current.isTracking,
      'message': current.message,
      'phase': current.phase.name,
    });
  }

  tracker.state.addListener(publishState);
  service.on(RiderDeliveryTrackingService._startTrackingEvent).listen((data) {
    final deliveryId = int.tryParse(data?['delivery_id']?.toString() ?? '');
    final status = data?['status']?.toString();
    if (deliveryId != null && status != null) {
      unawaited(
        tracker._startInProcess(
          deliveryId,
          status.toLowerCase(),
          reuseStoredSession: true,
        ),
      );
    }
  });
  service.on(RiderDeliveryTrackingService._stopTrackingEvent).listen((_) async {
    await tracker.stop(sendToServer: false);
    tracker.state.removeListener(publishState);
    await service.stopSelf();
  });

  if (service is AndroidServiceInstance) {
    await service.setAsForegroundService();
    service.setForegroundNotificationInfo(
      title: 'DailyCart active delivery',
      content: 'DailyCart is sharing your location for an active delivery.',
    );
  }

  final deliveryId = await tracker._storage.getTrackingDeliveryId();
  final status = await tracker._storage.getTrackingStatus();
  final token = await tracker._storage.getTrackingToken();
  if (deliveryId == null ||
      status == null ||
      token == null ||
      !RiderTrackingLifecycle.shouldTrack(status)) {
    tracker.state.removeListener(publishState);
    await service.stopSelf();
    return;
  }
  await tracker._startInProcess(deliveryId, status, reuseStoredSession: true);
  publishState();
}
