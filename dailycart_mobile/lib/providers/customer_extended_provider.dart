import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/customer_finance_model.dart';
import '../models/order_model.dart';
import '../models/subscription_model.dart';
import '../services/auth_api_service.dart';
import '../services/customer_extended_api_service.dart';

final customerExtendedApiServiceProvider =
    Provider<CustomerExtendedApiService>((ref) {
  return CustomerExtendedApiService();
});

final customerExtendedProvider =
    ChangeNotifierProvider<CustomerExtendedProvider>((ref) {
  return CustomerExtendedProvider(ref.watch(customerExtendedApiServiceProvider));
});

class CustomerExtendedProvider extends ChangeNotifier {
  CustomerExtendedProvider(this._api);

  final CustomerExtendedApiService _api;

  WalletModel? wallet;
  List<RefundModel> refunds = const [];
  List<SubscriptionModel> subscriptions = const [];
  List<OrderModel> scheduledOrders = const [];
  List<PolicyLinkModel> policies = const [];
  bool isLoading = false;
  String? errorMessage;

  Future<bool> loadWallet() => _run(() async => wallet = await _api.getWallet());

  Future<bool> loadRefunds() =>
      _run(() async => refunds = await _api.getRefunds());

  Future<bool> requestRefund({
    required int orderId,
    required double amount,
    required String reason,
  }) {
    return _run(() async {
      final refund = await _api.requestRefund(
        orderId: orderId,
        amount: amount,
        reason: reason,
      );
      refunds = [refund, ...refunds.where((item) => item.id != refund.id)];
    });
  }

  Future<bool> loadSubscriptions() =>
      _run(() async => subscriptions = await _api.getSubscriptions());

  Future<bool> createSubscription(Map<String, dynamic> data) {
    return _run(() async {
      final subscription = await _api.createSubscription(data);
      subscriptions = [subscription, ...subscriptions];
    });
  }

  Future<bool> changeSubscriptionStatus(int id, String action) {
    return _run(() async {
      final updated = await _api.changeSubscriptionStatus(id, action);
      subscriptions = subscriptions
          .map((item) => item.id == id ? updated : item)
          .toList(growable: false);
    });
  }

  Future<bool> loadScheduledOrders() =>
      _run(() async => scheduledOrders = await _api.getScheduledOrders());

  Future<bool> cancelScheduledOrder(int orderId) {
    return _run(() async {
      final updated = await _api.cancelScheduledOrder(orderId);
      scheduledOrders = scheduledOrders
          .map((order) => order.id == orderId ? updated : order)
          .toList(growable: false);
    });
  }

  Future<bool> loadPolicies() =>
      _run(() async => policies = await _api.getPolicies());

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
