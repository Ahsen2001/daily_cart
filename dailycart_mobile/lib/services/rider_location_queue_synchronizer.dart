import '../models/rider_location_queue_point.dart';
import 'rider_location_api_service.dart';
import 'rider_location_queue.dart';

typedef RiderLocationBatchSender =
    Future<RiderLocationBatchResult> Function(
      int deliveryId,
      String trackingToken,
      List<RiderLocationQueuePoint> points,
    );

class RiderLocationQueueSynchronizer {
  RiderLocationQueueSynchronizer({
    required RiderLocationQueue queue,
    required RiderLocationBatchSender sender,
  }) : _queue = queue,
       _sender = sender;

  final RiderLocationQueue _queue;
  final RiderLocationBatchSender _sender;
  bool _isFlushing = false;

  Future<int> flush({
    required int deliveryId,
    required String trackingToken,
    bool Function()? shouldContinue,
  }) async {
    if (_isFlushing) return 0;
    _isFlushing = true;
    var acknowledgedCount = 0;

    try {
      while (shouldContinue?.call() ?? true) {
        final batch = await _queue.batchFor(deliveryId);
        if (batch.isEmpty) break;

        final result = await _sender(deliveryId, trackingToken, batch);
        final acknowledged = result.acknowledgedPointUuids;
        if (acknowledged.isEmpty) break;

        await _queue.removeUuids(acknowledged);
        acknowledgedCount += acknowledged.length;
      }

      return acknowledgedCount;
    } finally {
      _isFlushing = false;
    }
  }
}
