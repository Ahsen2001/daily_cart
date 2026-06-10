class RiderLocationModel {
  const RiderLocationModel({
    required this.latitude,
    required this.longitude,
    this.updatedAt,
  });

  final double latitude;
  final double longitude;
  final DateTime? updatedAt;

  Map<String, dynamic> toJson() {
    return {
      'latitude': latitude,
      'longitude': longitude,
    };
  }
}
