enum CouponType {
  fixedAmount,
  percentage,
  freeDelivery;

  static CouponType fromName(String? value) {
    final normalized = value?.trim().toLowerCase();
    return switch (normalized) {
      'percentage' => CouponType.percentage,
      'free_delivery' => CouponType.freeDelivery,
      'free delivery' => CouponType.freeDelivery,
      _ => CouponType.fixedAmount,
    };
  }
}

class CouponModel {
  const CouponModel({
    required this.code,
    required this.type,
    required this.discount,
    this.id = 0,
    this.title = '',
    this.description = '',
    this.minOrderAmount = 0,
    this.expiresAt,
    this.isValid = true,
    this.message = '',
  });

  final int id;
  final String code;
  final String title;
  final String description;
  final CouponType type;
  final double discount;
  final double minOrderAmount;
  final DateTime? expiresAt;
  final bool isValid;
  final String message;

  factory CouponModel.fromJson(Map<String, dynamic> json) {
    return CouponModel(
      id: _toInt(json['id']),
      code: (json['code'] ?? json['coupon_code'] ?? '').toString(),
      title: (json['title'] ?? json['name'] ?? '').toString(),
      description: (json['description'] ?? '').toString(),
      type: CouponType.fromName(json['type']?.toString()),
      discount: _toDouble(json['discount'] ?? json['discount_amount']),
      minOrderAmount: _toDouble(json['min_order_amount']),
      expiresAt: _toNullableDate(json['expires_at'] ?? json['end_date']),
      isValid: json['is_valid'] != false && json['active'] != false,
      message: (json['message'] ?? '').toString(),
    );
  }

  String get typeLabel {
    return switch (type) {
      CouponType.fixedAmount => 'Fixed Amount',
      CouponType.percentage => 'Percentage',
      CouponType.freeDelivery => 'Free Delivery',
    };
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

  static DateTime? _toNullableDate(Object? value) {
    if (value == null || value.toString().isEmpty) {
      return null;
    }
    return DateTime.tryParse(value.toString());
  }
}
