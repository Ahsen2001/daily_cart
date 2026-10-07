class RiderRatingModel {
  const RiderRatingModel({
    required this.id,
    required this.orderId,
    required this.rating,
    required this.status,
    this.orderNumber = '',
    this.comment = '',
    this.tags = const [],
    this.reportReason,
    this.createdAt,
  });

  final int id;
  final int orderId;
  final int rating;
  final String status;
  final String orderNumber;
  final String comment;
  final List<String> tags;
  final String? reportReason;
  final DateTime? createdAt;

  factory RiderRatingModel.fromJson(Map<String, dynamic> json) {
    return RiderRatingModel(
      id: int.tryParse(json['id']?.toString() ?? '') ?? 0,
      orderId: int.tryParse(json['order_id']?.toString() ?? '') ?? 0,
      rating: int.tryParse(json['rating']?.toString() ?? '') ?? 0,
      status: (json['status'] ?? 'visible').toString(),
      orderNumber: (json['order_number'] ?? '').toString(),
      comment: (json['comment'] ?? '').toString(),
      tags: json['tags'] is List
          ? (json['tags'] as List).map((item) => item.toString()).toList()
          : const [],
      reportReason: json['report_reason']?.toString(),
      createdAt: DateTime.tryParse(json['created_at']?.toString() ?? ''),
    );
  }
}

class RiderRatingContext {
  const RiderRatingContext({
    required this.canRate,
    this.riderName = 'your rider',
    this.rating,
  });

  final bool canRate;
  final String riderName;
  final RiderRatingModel? rating;
}

class RiderRatingSummary {
  const RiderRatingSummary({
    required this.average,
    required this.total,
    required this.ratings,
  });

  final double average;
  final int total;
  final List<RiderRatingModel> ratings;
}
