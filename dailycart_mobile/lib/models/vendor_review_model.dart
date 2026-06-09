class VendorReviewModel {
  const VendorReviewModel({
    required this.id,
    required this.productName,
    required this.customerName,
    required this.rating,
    required this.comment,
    required this.createdAt,
  });

  final int id;
  final String productName;
  final String customerName;
  final double rating;
  final String comment;
  final DateTime createdAt;

  factory VendorReviewModel.fromJson(Map<String, dynamic> json) {
    final product = json['product'];
    final customer = json['customer'] ?? json['user'];
    return VendorReviewModel(
      id: _toInt(json['id']),
      productName: (json['product_name'] ??
              (product is Map<String, dynamic> ? product['name'] : null) ??
              '')
          .toString(),
      customerName: (json['customer_name'] ??
              (customer is Map<String, dynamic> ? customer['name'] : null) ??
              'Customer')
          .toString(),
      rating: _toDouble(json['rating']),
      comment: (json['comment'] ?? json['review'] ?? '').toString(),
      createdAt:
          DateTime.tryParse((json['created_at'] ?? '').toString()) ??
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
