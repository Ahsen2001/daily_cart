import 'dart:convert';

import 'package:dailycart_mobile/models/rider_location_queue_point.dart';
import 'package:dailycart_mobile/networking/api_exception.dart';
import 'package:dailycart_mobile/services/rider_location_api_service.dart';
import 'package:dailycart_mobile/services/rider_location_queue.dart';
import 'package:dailycart_mobile/services/rider_location_queue_storage.dart';
import 'package:dailycart_mobile/services/rider_location_queue_synchronizer.dart';
import 'package:dailycart_mobile/services/rider_tracking_lifecycle.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  setUp(() {
    SharedPreferences.setMockInitialValues({});
  });

  test('tracking starts only for active delivery states', () {
    expect(RiderTrackingLifecycle.shouldTrack('accepted'), isTrue);
    expect(RiderTrackingLifecycle.shouldTrack('picked_up'), isTrue);
    expect(RiderTrackingLifecycle.shouldTrack('on_the_way'), isTrue);
    expect(RiderTrackingLifecycle.shouldTrack('assigned'), isFalse);
    expect(RiderTrackingLifecycle.shouldTrack('pending'), isFalse);
  });

  test('tracking stops for terminal states, reassignment, and logout', () {
    for (final status in ['delivered', 'failed', 'cancelled', 'reassigned']) {
      expect(
        RiderTrackingLifecycle.shouldStopForStatus(status),
        isTrue,
        reason: status,
      );
    }
    expect(RiderTrackingLifecycle.shouldStopForLogout(), isTrue);
  });

  test('offline points persist and duplicate UUIDs are stored once', () async {
    final storage = _MemoryQueueStorage();
    final queue = RiderLocationQueue(storage: storage);
    final point = _point('point-1', 1);

    await queue.add(point);
    await queue.add(point);

    final restoredQueue = RiderLocationQueue(storage: storage);
    final restored = await restoredQueue.readAll();
    expect(restored, hasLength(1));
    expect(restored.single.pointUuid, 'point-1');
  });

  test('legacy plaintext points migrate to encrypted queue storage', () async {
    final point = _point('legacy-point', 1);
    SharedPreferences.setMockInitialValues({
      'rider_delivery_location_queue_v2': jsonEncode([point.toJson()]),
    });
    final storage = _MemoryQueueStorage();

    final restored = await RiderLocationQueue(storage: storage).readAll();

    expect(restored.single.pointUuid, 'legacy-point');
    expect(await storage.read('rider_delivery_location_queue_v2'), isNotNull);
    final preferences = await SharedPreferences.getInstance();
    expect(
      preferences.containsKey('rider_delivery_location_queue_v2'),
      isFalse,
    );
  });

  test('queued points retry after reconnect and are not resubmitted', () async {
    final queue = RiderLocationQueue(storage: _MemoryQueueStorage());
    await queue.add(_point('point-1', 1));
    await queue.add(_point('point-2', 2));

    final offlineSynchronizer = RiderLocationQueueSynchronizer(
      queue: queue,
      sender: (_, _, _) async =>
          throw const ApiException('offline', kind: ApiErrorKind.connectivity),
    );

    await expectLater(
      offlineSynchronizer.flush(deliveryId: 77, trackingToken: 'token'),
      throwsA(isA<ApiException>()),
    );
    expect(await queue.readAll(), hasLength(2));

    var submittedBatches = 0;
    final onlineSynchronizer = RiderLocationQueueSynchronizer(
      queue: queue,
      sender: (_, _, points) async {
        submittedBatches++;
        return RiderLocationBatchResult(
          acceptedPointUuids: points.map((point) => point.pointUuid).toSet(),
          duplicatePointUuids: const {},
          rejectedPointUuids: const {},
        );
      },
    );

    expect(
      await onlineSynchronizer.flush(deliveryId: 77, trackingToken: 'token'),
      2,
    );
    expect(await queue.readAll(), isEmpty);

    expect(
      await onlineSynchronizer.flush(deliveryId: 77, trackingToken: 'token'),
      0,
    );
    expect(submittedBatches, 1);
  });
}

class _MemoryQueueStorage implements RiderLocationQueueStorage {
  final _values = <String, String>{};

  @override
  Future<void> delete(String key) async => _values.remove(key);

  @override
  Future<String?> read(String key) async => _values[key];

  @override
  Future<void> write(String key, String value) async => _values[key] = value;
}

RiderLocationQueuePoint _point(String uuid, int sequence) {
  return RiderLocationQueuePoint(
    pointUuid: uuid,
    deliveryId: 77,
    latitude: 7.717,
    longitude: 81.7,
    accuracyMeters: 8,
    recordedAt: DateTime.now().toUtc(),
    sequenceNumber: sequence,
  );
}
