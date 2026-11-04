import 'dart:async';
import 'dart:convert';
import 'dart:math';

import 'package:shared_preferences/shared_preferences.dart';

import '../models/rider_location_queue_point.dart';
import 'rider_location_queue_storage.dart';

class RiderLocationQueue {
  RiderLocationQueue({RiderLocationQueueStorage? storage})
    : _storage = storage ?? SecureRiderLocationQueueStorage();

  static const _queueKey = 'rider_delivery_location_queue_v2';
  static const _sequencePrefix = 'rider_delivery_location_sequence_v2_';
  static const _migrationKey = 'rider_delivery_location_queue_secure_v1';
  static const maxSize = 500;
  static const maxAge = Duration(hours: 24);
  final RiderLocationQueueStorage _storage;
  Future<void> _operationTail = Future<void>.value();

  Future<int> nextSequence(int deliveryId) => _serialized(() async {
    await _migrateLegacyDataIfNeeded();
    final key = '$_sequencePrefix$deliveryId';
    final next = (int.tryParse(await _storage.read(key) ?? '') ?? 0) + 1;
    await _storage.write(key, next.toString());
    return next;
  });

  String newPointUuid() {
    final random = Random.secure();
    final bytes = List<int>.generate(16, (_) => random.nextInt(256));
    bytes[6] = (bytes[6] & 0x0f) | 0x40;
    bytes[8] = (bytes[8] & 0x3f) | 0x80;
    final hex = bytes
        .map((byte) => byte.toRadixString(16).padLeft(2, '0'))
        .join();
    return '${hex.substring(0, 8)}-${hex.substring(8, 12)}-'
        '${hex.substring(12, 16)}-${hex.substring(16, 20)}-'
        '${hex.substring(20)}';
  }

  Future<void> add(RiderLocationQueuePoint point) => _serialized(() async {
    final points = await _readAll();
    if (!points.any((queued) => queued.pointUuid == point.pointUuid)) {
      points.add(point);
    }
    await _write(_prune(points));
  });

  Future<List<RiderLocationQueuePoint>> batchFor(
    int deliveryId, {
    int limit = 25,
  }) => _serialized(() async {
    final points = _prune(await _readAll());
    await _write(points);
    return points
        .where((point) => point.deliveryId == deliveryId)
        .take(limit)
        .toList(growable: false);
  });

  Future<List<RiderLocationQueuePoint>> readAll() => _serialized(_readAll);

  Future<List<RiderLocationQueuePoint>> _readAll() async {
    await _migrateLegacyDataIfNeeded();
    final raw = await _storage.read(_queueKey);
    if (raw == null || raw.isEmpty) return [];
    try {
      return (jsonDecode(raw) as List)
          .whereType<Map>()
          .map(
            (item) => RiderLocationQueuePoint.fromJson(
              Map<String, dynamic>.from(item),
            ),
          )
          .toList();
    } catch (_) {
      // A corrupt queue cannot be retried safely because UUIDs/coordinates may
      // be incomplete. Reset only this app-local cache, never backend history.
      await _storage.delete(_queueKey);
      return [];
    }
  }

  Future<void> removeUuids(Iterable<String> pointUuids) =>
      _serialized(() async {
        final ids = pointUuids.toSet();
        if (ids.isEmpty) return;
        final points = await _readAll();
        points.removeWhere((point) => ids.contains(point.pointUuid));
        await _write(points);
      });

  Future<void> clearDelivery(int deliveryId) => _serialized(() async {
    final points = await _readAll();
    points.removeWhere((point) => point.deliveryId == deliveryId);
    await _write(points);
    await _storage.delete('$_sequencePrefix$deliveryId');
  });

  List<RiderLocationQueuePoint> _prune(List<RiderLocationQueuePoint> points) {
    final cutoff = DateTime.now().toUtc().subtract(maxAge);
    final unique = <String, RiderLocationQueuePoint>{};
    for (final point in points) {
      if (!point.recordedAt.isBefore(cutoff)) {
        unique[point.pointUuid] = point;
      }
    }
    final retained = unique.values.toList()
      ..sort((a, b) => b.recordedAt.compareTo(a.recordedAt));
    return retained.take(maxSize).toList(growable: false);
  }

  Future<void> _write(List<RiderLocationQueuePoint> points) async {
    if (points.isEmpty) {
      await _storage.delete(_queueKey);
      return;
    }
    await _storage.write(
      _queueKey,
      jsonEncode(points.map((point) => point.toJson()).toList()),
    );
  }

  Future<void> _migrateLegacyDataIfNeeded() async {
    if (await _storage.read(_migrationKey) == 'complete') return;

    final preferences = await SharedPreferences.getInstance();
    final legacyQueue = preferences.getString(_queueKey);
    final secureQueue = await _storage.read(_queueKey);
    if ((secureQueue == null || secureQueue.isEmpty) &&
        legacyQueue != null &&
        legacyQueue.isNotEmpty) {
      try {
        // Validate the old payload before moving it into encrypted storage.
        final decoded = jsonDecode(legacyQueue);
        if (decoded is List) {
          await _storage.write(_queueKey, legacyQueue);
        }
      } catch (_) {
        // A malformed legacy cache cannot be restored safely.
      } finally {
        // Remove only this obsolete app-local plaintext cache. Backend history
        // and every other preference remain untouched.
        await preferences.remove(_queueKey);
      }
    }

    for (final key in preferences.getKeys()) {
      if (!key.startsWith(_sequencePrefix)) continue;
      final sequence = preferences.getInt(key);
      if (sequence != null && await _storage.read(key) == null) {
        await _storage.write(key, sequence.toString());
      }
      await preferences.remove(key);
    }
    await _storage.write(_migrationKey, 'complete');
  }

  Future<T> _serialized<T>(Future<T> Function() operation) {
    final completer = Completer<T>();
    _operationTail = _operationTail.then((_) async {
      try {
        completer.complete(await operation());
      } catch (error, stackTrace) {
        completer.completeError(error, stackTrace);
      }
    });
    return completer.future;
  }
}
