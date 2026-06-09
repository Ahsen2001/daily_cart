class ReviewModel {
  const ReviewModel({
    required this.id,
    required this.productId,
    required this.productName,
    required this.rating,
    required this.comment,
    required this.createdAt,
    this.orderId,
    this.productImage = '',
    this.reviewImage = '',
    this.customerName = '',
    this.canEdit = false,
    this.canDelete = false,
    this.averageRating = 0,
    this.reviewCount = 0,
  });

  final int id;
  final int? orderId;
  final int productId;
  final String productName;
  final String productImage;
  final int rating;
  final String comment;
  final String reviewImage;
  final String customerName;
  final DateTime createdAt;
  final bool canEdit;
  final bool canDelete;
  final double averageRating;
  final int reviewCount;

  factory ReviewModel.fromJson(Map<String, dynamic> json) {
    final product = json['product'];
    return ReviewModel(
      id: _toInt(json['id']),
      orderId: json['order_id'] == null ? null : _toInt(json['order_id']),
      productId: _toInt(json['product_id'] ??
          (product is Map<String, dynamic> ? product['id'] : null)),
      productName: (json['product_name'] ??
              (product is Map<String, dynamic> ? product['name'] : null) ??
              '')
          .toString(),
      productImage: (json['product_image'] ??
              (product is Map<String, dynamic> ? product['image'] : null) ??
              '')
          .toString(),
      rating: _toInt(json['rating']).clamp(1, 5).toInt(),
      comment: (json['comment'] ?? json['review'] ?? '').toString(),
      reviewImage: (json['image'] ?? json['review_image'] ?? '').toString(),
      customerName: (json['customer_name'] ?? json['user_name'] ?? '').toString(),
      createdAt: DateTime.tryParse((json['created_at'] ?? '').toString()) ??
          DateTime.now(),
      canEdit: json['can_edit'] == true,
      canDelete: json['can_delete'] == true,
      averageRating: _toDouble(json['average_rating']),
      reviewCount: _toInt(json['review_count']),
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
