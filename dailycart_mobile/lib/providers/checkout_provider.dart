import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/address_model.dart';
import '../models/checkout_request_model.dart';
import '../models/checkout_quote_model.dart';
import '../models/checkout_response_model.dart';
import '../models/payment_method_model.dart';
import '../services/auth_api_service.dart';
import '../services/checkout_api_service.dart';

final checkoutApiServiceProvider = Provider<CheckoutApiService>((ref) {
  return CheckoutApiService();
});

final checkoutProvider = ChangeNotifierProvider<CheckoutProvider>((ref) {
  return CheckoutProvider(ref.watch(checkoutApiServiceProvider));
});

class CheckoutProvider extends ChangeNotifier {
  CheckoutProvider(this._apiService);

  final CheckoutApiService _apiService;

  AddressModel? selectedAddress;
  DateTime? selectedDeliveryTime;
  PaymentMethodType selectedPaymentMethod = PaymentMethodType.cashOnDelivery;
  OrderModel? order;
  List<OrderModel> orders = const [];
  CheckoutQuoteModel? quote;
  String? paymentUrl;
  bool isLoading = false;
  String? errorMessage;

  DateTime get minimumDeliveryTime {
    return DateTime.now().add(const Duration(minutes: 30));
  }

  void selectAddress(AddressModel address) {
    selectedAddress = address;
    notifyListeners();
  }

  Future<bool> refreshQuote({
    String? couponCode,
    int loyaltyPoints = 0,
    int? deliveryDistanceMeters,
  }) async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();
    try {
      quote = await _apiService.getQuote(
        couponCode: couponCode,
        loyaltyPoints: loyaltyPoints,
        deliveryDistrict: selectedAddress?.district,
        deliveryDistanceMeters: deliveryDistanceMeters,
      );
      return true;
    } on ApiException catch (error) {
      errorMessage = error.message;
      return false;
    } catch (_) {
      errorMessage = 'Unable to calculate checkout totals.';
      return false;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  bool selectDeliveryTime(DateTime deliveryTime) {
    if (deliveryTime.isBefore(minimumDeliveryTime)) {
      errorMessage =
          'Delivery time must be at least 30 minutes after placing the order.';
      notifyListeners();
      return false;
    }

    selectedDeliveryTime = deliveryTime;
    errorMessage = null;
    notifyListeners();
    return true;
  }

  void selectPaymentMethod(PaymentMethodType method) {
    selectedPaymentMethod = method;
    notifyListeners();
  }

  Future<bool> createOrder({String? couponCode}) async {
    if (selectedAddress == null) {
      errorMessage = 'Please select a delivery address.';
      notifyListeners();
      return false;
    }
    if (selectedDeliveryTime == null) {
      errorMessage = 'Please select a delivery time.';
      notifyListeners();
      return false;
    }
    final cutoff = DateTime.now().add(const Duration(minutes: 25));
    if (selectedDeliveryTime!.isBefore(cutoff)) {
      errorMessage =
          'Delivery time must be at least 30 minutes after placing the order.';
      notifyListeners();
      return false;
    }

    isLoading = true;
    errorMessage = null;
    notifyListeners();

    try {
      final quoteReady = await refreshQuote(couponCode: couponCode);
      if (!quoteReady) {
        return false;
      }
      final response = await _apiService.createOrder(
        CheckoutRequestModel(
          address: selectedAddress!,
          deliveryTime: selectedDeliveryTime!,
          paymentMethod: selectedPaymentMethod,
          couponCode: couponCode,
        ),
      );
      orders = response.orders;
      order = response.order;
      paymentUrl = response.paymentUrl;
      return response.success;
    } on ApiException catch (error) {
      errorMessage = error.message;
      return false;
    } catch (_) {
      errorMessage = 'Unable to create order. Please try again.';
      return false;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> refreshOrderStatus() async {
    final currentOrder = order;
    if (currentOrder == null) {
      return;
    }

    order = await _apiService.getOrderStatus(currentOrder.id);
    notifyListeners();
  }
}
