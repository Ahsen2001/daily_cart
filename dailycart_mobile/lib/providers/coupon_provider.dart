import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/coupon_model.dart';
import '../services/auth_api_service.dart';
import '../services/coupon_api_service.dart';
import 'cart_provider.dart';

final couponApiServiceProvider = Provider<CouponApiService>((ref) {
  return CouponApiService();
});

final couponProvider = ChangeNotifierProvider<CouponProvider>((ref) {
  return CouponProvider(
    apiService: ref.watch(couponApiServiceProvider),
    ref: ref,
  );
});

class CouponProvider extends ChangeNotifier {
  CouponProvider({
    required CouponApiService apiService,
    required Ref ref,
  })  : _apiService = apiService,
        _ref = ref;

  final CouponApiService _apiService;
  final Ref _ref;

  CouponModel? appliedCoupon;
  bool isLoading = false;
  String? errorMessage;

  Future<bool> applyCoupon(String code) async {
    final trimmedCode = code.trim();
    if (trimmedCode.isEmpty) {
      errorMessage = 'Enter a coupon code.';
      notifyListeners();
      return false;
    }

    isLoading = true;
    errorMessage = null;
    notifyListeners();

    try {
      appliedCoupon = await _apiService.applyCoupon(trimmedCode);
      _ref.read(cartProvider).updateSummaryDiscount(appliedCoupon!.discount);
      return true;
    } on ApiException catch (error) {
      errorMessage = error.message;
      return false;
    } catch (_) {
      errorMessage = 'Invalid or expired coupon.';
      return false;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> removeCoupon() async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();

    try {
      await _apiService.removeCoupon();
    } catch (_) {
      // Local coupon state should still clear even if API removal fails.
    } finally {
      appliedCoupon = null;
      _ref.read(cartProvider).removeDiscount();
      isLoading = false;
      notifyListeners();
    }
  }
}
