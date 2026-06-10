import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/rider_earning_model.dart';
import '../services/auth_api_service.dart';
import '../services/rider_earning_api_service.dart';

final riderEarningApiServiceProvider =
    Provider<RiderEarningApiService>((ref) {
  return RiderEarningApiService();
});

final riderEarningProvider =
    ChangeNotifierProvider<RiderEarningProvider>((ref) {
  return RiderEarningProvider(ref.watch(riderEarningApiServiceProvider));
});

class RiderEarningProvider extends ChangeNotifier {
  RiderEarningProvider(this._apiService);

  final RiderEarningApiService _apiService;

  RiderEarningModel? earnings;
  bool isLoading = false;
  String? errorMessage;

  Future<void> getRiderEarnings() async {
    await _run(() async {
      earnings = await _apiService.getRiderEarnings();
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
