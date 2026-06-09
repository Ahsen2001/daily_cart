class CartSummaryModel {
  const CartSummaryModel({
    this.subtotal = 0,
    this.discount = 0,
    this.deliveryCharge = 0,
    this.serviceCharge = 0,
    this.grandTotal = 0,
  });

  final double subtotal;
  final double discount;
  final double deliveryCharge;
  final double serviceCharge;
  final double grandTotal;

  factory CartSummaryModel.fromJson(Map<String, dynamic> json) {
    return CartSummaryModel(
      subtotal: _toDouble(json['subtotal']),
      discount: _toDouble(json['discount']),
      deliveryCharge: _toDouble(json['delivery_charge']),
      serviceCharge: _toDouble(json['service_charge']),
      grandTotal: _toDouble(json['grand_total']),
    );
  }

  factory CartSummaryModel.fromItems({
    required double subtotal,
    double discount = 0,
    double deliveryCharge = 0,
    double serviceCharge = 0,
  }) {
    return CartSummaryModel(
      subtotal: subtotal,
      discount: discount,
      deliveryCharge: deliveryCharge,
      serviceCharge: serviceCharge,
      grandTotal: subtotal - discount + deliveryCharge + serviceCharge,
    );
  }

  CartSummaryModel copyWith({
    double? subtotal,
    double? discount,
    double? deliveryCharge,
    double? serviceCharge,
    double? grandTotal,
  }) {
    return CartSummaryModel(
      subtotal: subtotal ?? this.subtotal,
      discount: discount ?? this.discount,
      deliveryCharge: deliveryCharge ?? this.deliveryCharge,
      serviceCharge: serviceCharge ?? this.serviceCharge,
      grandTotal: grandTotal ?? this.grandTotal,
    );
  }

  static double _toDouble(Object? value) {
    if (value is num) {
      return value.toDouble();
    }
    return double.tryParse(value?.toString() ?? '') ?? 0;
  }
}
