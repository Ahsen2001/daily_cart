import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/vendor_business_model.dart';
import '../models/vendor_order_model.dart';
import '../services/auth_api_service.dart';
import '../services/vendor_business_api_service.dart';

final vendorBusinessApiServiceProvider =
    Provider<VendorBusinessApiService>((ref) => VendorBusinessApiService());

final vendorBusinessProvider =
    ChangeNotifierProvider<VendorBusinessProvider>((ref) {
  return VendorBusinessProvider(ref.watch(vendorBusinessApiServiceProvider));
});

class VendorBusinessProvider extends ChangeNotifier {
  VendorBusinessProvider(this._api);

  final VendorBusinessApiService _api;

  VendorWalletModel? wallet;
  List<VendorRefundModel> refunds = const [];
  List<VendorCouponModel> coupons = const [];
  List<VendorPromotionModel> promotions = const [];
  List<VendorSubscriptionModel> subscriptions = const [];
  List<VendorOrderModel> scheduledOrders = const [];
  VendorReportModel? report;
  bool isLoading = false;
  String? errorMessage;

  Future<bool> loadWallet() => _run(() async => wallet = await _api.getWallet());

  Future<bool> requestPayout(Map<String, dynamic> data) {
    return _run(() async {
      await _api.requestPayout(data);
      wallet = await _api.getWallet();
    });
  }

  Future<bool> loadRefunds() =>
      _run(() async => refunds = await _api.getRefunds());

  Future<bool> respondToRefund(int id, String note) {
    return _run(() async {
      final updated = await _api.respondToRefund(id, note);
      refunds = refunds
          .map((item) => item.id == id ? updated : item)
          .toList(growable: false);
    });
  }

  Future<bool> loadCoupons() =>
      _run(() async => coupons = await _api.getCoupons());

  Future<bool> saveCoupon(Map<String, dynamic> data, {int? id}) {
    return _run(() async {
      final saved = await _api.saveCoupon(data, id: id);
      coupons = [
        saved,
        ...coupons.where((item) => item.id != saved.id),
      ];
    });
  }

  Future<bool> deleteCoupon(int id) {
    return _run(() async {
      await _api.deleteCoupon(id);
      coupons = coupons.where((item) => item.id != id).toList(growable: false);
    });
  }

  Future<bool> loadPromotions() =>
      _run(() async => promotions = await _api.getPromotions());

  Future<bool> savePromotion(Map<String, dynamic> data, {int? id}) {
    return _run(() async {
      final saved = await _api.savePromotion(data, id: id);
      promotions = [
        saved,
        ...promotions.where((item) => item.id != saved.id),
      ];
    });
  }

  Future<bool> deletePromotion(int id) {
    return _run(() async {
      await _api.deletePromotion(id);
      promotions =
          promotions.where((item) => item.id != id).toList(growable: false);
    });
  }

  Future<bool> loadSubscriptions() =>
      _run(() async => subscriptions = await _api.getSubscriptions());

  Future<bool> loadScheduledOrders() =>
      _run(() async => scheduledOrders = await _api.getScheduledOrders());

  Future<bool> loadReport() =>
      _run(() async => report = await _api.getReports());

  Future<bool> _run(Future<void> Function() action) async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();
    try {
      await action();
      return true;
    } on ApiException catch (error) {
      errorMessage = error.message;
      return false;
    } catch (_) {
      errorMessage = 'Something went wrong. Please try again.';
      return false;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }
}
