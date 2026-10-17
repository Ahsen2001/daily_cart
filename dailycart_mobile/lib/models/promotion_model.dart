class PromotionModel {
  const PromotionModel({
    required this.id,
    required this.title,
    required this.type,
    required this.description,
    this.image = '',
    this.discountText = '',
    this.startsAt,
    this.endsAt,
    this.isActive = true,
    this.productId,
    this.targetType = '',
    this.vendorName = '',
  });

  final int id;
  final String title;
  final String type;
  final String description;
  final String image;
  final String discountText;
  final DateTime? startsAt;
  final DateTime? endsAt;
  final bool isActive;
  final int? productId;
  final String targetType;
  final String vendorName;

  factory PromotionModel.fromJson(Map<String, dynamic> json) {
    return PromotionModel(
      id: _toInt(json['id']),
      title: (json['title'] ?? json['name'] ?? '').toString(),
      type: (json['type'] ?? 'featured_offer').toString(),
      description: (json['description'] ?? '').toString(),
      image: (json['image'] ?? json['banner'] ?? '').toString(),
      discountText: (json['discount_text'] ?? json['offer_text'] ?? '')
          .toString(),
      startsAt: _toNullableDate(json['starts_at'] ?? json['start_date']),
      endsAt: _toNullableDate(json['ends_at'] ?? json['end_date']),
      isActive: json['is_active'] != false,
      productId: json['product_id'] == null ? null : _toInt(json['product_id']),
      targetType: (json['target_type'] ?? '').toString(),
      vendorName: (json['vendor_name'] ?? '').toString(),
    );
  }

  static int _toInt(Object? value) {
    if (value is int) {
      return value;
    }
    return int.tryParse(value?.toString() ?? '') ?? 0;
  }

  static DateTime? _toNullableDate(Object? value) {
    if (value == null || value.toString().isEmpty) {
      return null;
    }
    return DateTime.tryParse(value.toString());
  }
}
