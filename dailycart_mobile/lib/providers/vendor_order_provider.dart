import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/vendor_order_model.dart';
import '../services/auth_api_service.dart';
import '../services/vendor_order_api_service.dart';

final vendorOrderApiServiceProvider = Provider<VendorOrderApiService>((ref) {
  return VendorOrderApiService();
});

final vendorOrderProvider = ChangeNotifierProvider<VendorOrderProvider>((ref) {
  return VendorOrderProvider(ref.watch(vendorOrderApiServiceProvider));
});

class VendorOrderProvider extends ChangeNotifier {
  VendorOrderProvider(this._apiService);

  final VendorOrderApiService _apiService;

  List<VendorOrderModel> orders = const [];
  VendorOrderModel? selectedOrder;
  bool isLoading = false;
  String? errorMessage;

  Future<void> getVendorOrders({String status = 'all'}) async {
    await _run(() async {
      orders = await _apiService.getVendorOrders(status: status);
    });
  }

  Future<void> getVendorOrderDetails(int orderId) async {
    await _run(() async {
      selectedOrder = await _apiService.getVendorOrderDetails(orderId);
    });
  }

  Future<bool> confirmOrder(int orderId) {
    return _updateOrder(() => _apiService.confirmOrder(orderId));
  }

  Future<bool> markOrderPacked(int orderId) {
    return _updateOrder(() => _apiService.markOrderPacked(orderId));
  }

  Future<bool> cancelOrder(int orderId, String reason) {
    return _updateOrder(() => _apiService.cancelOrder(orderId, reason));
  }

  Future<bool> _updateOrder(Future<VendorOrderModel> Function() request) async {
    return _run(() async {
      final updated = await request();
      selectedOrder = updated;
      orders = orders
          .map((item) => item.id == updated.id ? updated : item)
          .toList(growable: false);
    });
  }

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
