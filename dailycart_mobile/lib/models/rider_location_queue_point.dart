class RiderLocationQueuePoint {
  const RiderLocationQueuePoint({
    required this.pointUuid,
    required this.deliveryId,
    required this.latitude,
    required this.longitude,
    required this.accuracyMeters,
    required this.recordedAt,
    required this.sequenceNumber,
    this.heading,
    this.speed,
  });

  final String pointUuid;
  final int deliveryId;
  final double latitude;
  final double longitude;
  final double accuracyMeters;
  final double? heading;
  final double? speed;
  final DateTime recordedAt;
  final int sequenceNumber;

  Map<String, dynamic> toJson() => {
    'point_uuid': pointUuid,
    'delivery_id': deliveryId,
    'latitude': latitude,
    'longitude': longitude,
    'accuracy_meters': accuracyMeters,
    'heading': heading,
    'speed': speed,
    'recorded_at': recordedAt.toUtc().toIso8601String(),
    'sequence_number': sequenceNumber,
  };

  factory RiderLocationQueuePoint.fromJson(Map<String, dynamic> json) {
    return RiderLocationQueuePoint(
      pointUuid: json['point_uuid'].toString(),
      deliveryId: (json['delivery_id'] as num).toInt(),
      latitude: (json['latitude'] as num).toDouble(),
      longitude: (json['longitude'] as num).toDouble(),
      accuracyMeters: (json['accuracy_meters'] as num).toDouble(),
      heading: (json['heading'] as num?)?.toDouble(),
      speed: (json['speed'] as num?)?.toDouble(),
      recordedAt: DateTime.parse(json['recorded_at'].toString()).toUtc(),
      sequenceNumber: (json['sequence_number'] as num).toInt(),
    );
  }
}
