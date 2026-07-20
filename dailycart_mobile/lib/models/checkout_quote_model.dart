import 'cart_summary_model.dart';

class CheckoutQuoteModel {
  const CheckoutQuoteModel({
    required this.summary,
    required this.loyaltyPoints,
    required this.loyaltyDiscount,
    required this.estimatedDeliveryMinutes,
    required this.freeDeliveryEligible,
    required this.deliveryRuleScope,
  });

  final CartSummaryModel summary;
  final int loyaltyPoints;
  final double loyaltyDiscount;
  final int estimatedDeliveryMinutes;
  final bool freeDeliveryEligible;
  final String deliveryRuleScope;

  factory CheckoutQuoteModel.fromJson(Map<String, dynamic> json) {
    return CheckoutQuoteModel(
      summary: CartSummaryModel.fromJson({
        ...json,
        'delivery_charge': json['delivery_fee'],
      }),
      loyaltyPoints: _toInt(json['loyalty_points']),
      loyaltyDiscount: _toDouble(json['loyalty_discount']),
      estimatedDeliveryMinutes:
          _toInt(json['estimated_delivery_minutes']),
      freeDeliveryEligible: json['free_delivery_eligible'] == true,
      deliveryRuleScope: (json['delivery_rule_scope'] ?? '').toString(),
    );
  }

  static int _toInt(Object? value) {
    if (value is int) return value;
    return int.tryParse(value?.toString() ?? '') ?? 0;
  }

  static double _toDouble(Object? value) {
    if (value is num) return value.toDouble();
    return double.tryParse(value?.toString() ?? '') ?? 0;
  }
}
