import 'address_model.dart';
import 'payment_method_model.dart';

class CheckoutRequestModel {
  const CheckoutRequestModel({
    required this.address,
    required this.deliveryTime,
    required this.paymentMethod,
    this.couponCode,
  });

  final AddressModel address;
  final DateTime deliveryTime;
  final PaymentMethodType paymentMethod;
  final String? couponCode;

  Map<String, dynamic> toJson() {
    return {
      'address_id': address.id == 0 ? null : address.id,
      'delivery_address': address.toJson(),
      'scheduled_delivery_time': deliveryTime.toIso8601String(),
      'payment_method': paymentMethod.apiValue,
      'currency': 'LKR',
      if (couponCode != null && couponCode!.isNotEmpty) 'coupon_code': couponCode,
    };
  }
}
