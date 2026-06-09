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
    this.message = '',
  });

  final String code;
  final CouponType type;
  final double discount;
  final String message;

  factory CouponModel.fromJson(Map<String, dynamic> json) {
    return CouponModel(
      code: (json['code'] ?? json['coupon_code'] ?? '').toString(),
      type: CouponType.fromName(json['type']?.toString()),
      discount: _toDouble(json['discount'] ?? json['discount_amount']),
      message: (json['message'] ?? '').toString(),
    );
  }

  static double _toDouble(Object? value) {
    if (value is num) {
      return value.toDouble();
    }
    return double.tryParse(value?.toString() ?? '') ?? 0;
  }
}
