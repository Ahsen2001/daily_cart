class LoyaltyPointModel {
  const LoyaltyPointModel({
    required this.id,
    required this.type,
    required this.points,
    required this.value,
    required this.description,
    required this.createdAt,
  });

  final int id;
  final String type;
  final int points;
  final double value;
  final String description;
  final DateTime createdAt;

  factory LoyaltyPointModel.fromJson(Map<String, dynamic> json) {
    final points = _toInt(json['points']);
    return LoyaltyPointModel(
      id: _toInt(json['id']),
      type: (json['type'] ?? 'earned').toString(),
      points: points,
      value: _toDouble(json['value'] ?? json['amount'] ?? points),
      description: (json['description'] ?? json['message'] ?? '').toString(),
      createdAt: DateTime.tryParse((json['created_at'] ?? '').toString()) ??
          DateTime.now(),
    );
  }

  static int _toInt(Object? value) {
    if (value is int) {
      return value;
    }
    return int.tryParse(value?.toString() ?? '') ?? 0;
  }

  static double _toDouble(Object? value) {
    if (value is num) {
      return value.toDouble();
    }
    return double.tryParse(value?.toString() ?? '') ?? 0;
  }
}
