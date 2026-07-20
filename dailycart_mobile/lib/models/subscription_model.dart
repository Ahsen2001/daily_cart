class SubscriptionModel {
  const SubscriptionModel({
    required this.id,
    required this.productId,
    required this.productName,
    required this.frequency,
    required this.quantity,
    required this.totalAmount,
    required this.deliveryAddress,
    required this.paymentMethod,
    required this.status,
    this.variantName,
    this.nextDeliveryDate,
  });

  final int id;
  final int productId;
  final String productName;
  final String? variantName;
  final String frequency;
  final int quantity;
  final double totalAmount;
  final String deliveryAddress;
  final String paymentMethod;
  final String status;
  final DateTime? nextDeliveryDate;

  factory SubscriptionModel.fromJson(Map<String, dynamic> json) {
    return SubscriptionModel(
      id: _toInt(json['id']),
      productId: _toInt(json['product_id']),
      productName: (json['product_name'] ?? '').toString(),
      variantName: json['variant_name']?.toString(),
      frequency: (json['frequency'] ?? '').toString(),
      quantity: _toInt(json['quantity']),
      totalAmount: _toDouble(json['total_amount']),
      deliveryAddress: (json['delivery_address'] ?? '').toString(),
      paymentMethod: (json['payment_method'] ?? '').toString(),
      status: (json['status'] ?? '').toString(),
      nextDeliveryDate:
          DateTime.tryParse(json['next_delivery_date']?.toString() ?? ''),
    );
  }

  bool get isActive => status.toLowerCase() == 'active';
  bool get isPaused => status.toLowerCase() == 'paused';

  static int _toInt(Object? value) {
    if (value is int) return value;
    return int.tryParse(value?.toString() ?? '') ?? 0;
  }

  static double _toDouble(Object? value) {
    if (value is num) return value.toDouble();
    return double.tryParse(value?.toString() ?? '') ?? 0;
  }
}

class PolicyLinkModel {
  const PolicyLinkModel({
    required this.key,
    required this.title,
    required this.url,
  });

  final String key;
  final String title;
  final String url;

  factory PolicyLinkModel.fromJson(Map<String, dynamic> json) {
    return PolicyLinkModel(
      key: (json['key'] ?? '').toString(),
      title: (json['title'] ?? '').toString(),
      url: (json['url'] ?? '').toString(),
    );
  }
}
