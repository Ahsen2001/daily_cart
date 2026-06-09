import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/order_model.dart';
import '../services/auth_api_service.dart';
import '../services/order_api_service.dart';

final orderApiServiceProvider = Provider<OrderApiService>((ref) {
  return OrderApiService();
});

final orderProvider = ChangeNotifierProvider<OrderProvider>((ref) {
  return OrderProvider(ref.watch(orderApiServiceProvider));
});

class OrderProvider extends ChangeNotifier {
  OrderProvider(this._apiService);

  final OrderApiService _apiService;

  List<OrderModel> orders = const [];
  OrderModel? selectedOrder;
  bool isLoading = false;
  String? errorMessage;

  Future<void> getOrders({String filter = 'all'}) async {
    await _run(() async {
      orders = await _apiService.getOrders(filter: filter);
    });
  }

  Future<void> getOrderDetails(int orderId) async {
    await _run(() async {
      selectedOrder = await _apiService.getOrderDetails(orderId);
    });
  }

  Future<bool> cancelOrder(int orderId) async {
    return _run(() async {
      selectedOrder = await _apiService.cancelOrder(orderId);
      orders = orders
          .map((order) => order.id == orderId ? selectedOrder! : order)
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
