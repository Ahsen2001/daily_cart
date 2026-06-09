import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/loyalty_point_model.dart';
import '../services/auth_api_service.dart';
import '../services/loyalty_api_service.dart';

final loyaltyApiServiceProvider = Provider<LoyaltyApiService>((ref) {
  return LoyaltyApiService();
});

final loyaltyProvider = ChangeNotifierProvider<LoyaltyProvider>((ref) {
  return LoyaltyProvider(ref.watch(loyaltyApiServiceProvider));
});

class LoyaltyProvider extends ChangeNotifier {
  LoyaltyProvider(this._apiService);

  final LoyaltyApiService _apiService;

  int loyaltyBalance = 0;
  List<LoyaltyPointModel> loyaltyHistory = const [];
  bool isLoading = false;
  String? errorMessage;

  double get balanceValue => loyaltyBalance.toDouble();

  Future<void> getBalance() async {
    await _run(() async {
      loyaltyBalance = await _apiService.getBalance();
    });
  }

  Future<void> getHistory() async {
    await _run(() async {
      loyaltyHistory = await _apiService.getHistory();
    });
  }

  Future<void> loadLoyalty() async {
    await _run(() async {
      loyaltyBalance = await _apiService.getBalance();
      loyaltyHistory = await _apiService.getHistory();
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
