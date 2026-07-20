import 'address_model.dart';
import 'payment_method_model.dart';

class CheckoutRequestModel {
  const CheckoutRequestModel({
    required this.address,
    required this.deliveryTime,
    required this.paymentMethod,
    this.couponCode,
    this.loyaltyPoints = 0,
    this.deliveryDistanceMeters,
  });

  final AddressModel address;
  final DateTime deliveryTime;
  final PaymentMethodType paymentMethod;
  final String? couponCode;
  final int loyaltyPoints;
  final int? deliveryDistanceMeters;

  Map<String, dynamic> toJson() {
    return {
      'delivery_address': address.displayAddress,
      'delivery_district': address.district,
      'delivery_latitude': address.latitude,
      'delivery_longitude': address.longitude,
      'delivery_distance_meters': deliveryDistanceMeters,
      'scheduled_delivery_at': deliveryTime.toUtc().toIso8601String(),
      'payment_method': paymentMethod.apiValue,
      if (couponCode != null && couponCode!.isNotEmpty) 'coupon_code': couponCode,
      if (loyaltyPoints > 0) 'loyalty_points': loyaltyPoints,
    };
  }
}
