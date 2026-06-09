import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/vendor_earning_model.dart';
import '../services/auth_api_service.dart';
import '../services/vendor_earning_api_service.dart';

final vendorEarningApiServiceProvider =
    Provider<VendorEarningApiService>((ref) {
  return VendorEarningApiService();
});

final vendorEarningProvider =
    ChangeNotifierProvider<VendorEarningProvider>((ref) {
  return VendorEarningProvider(ref.watch(vendorEarningApiServiceProvider));
});

class VendorEarningProvider extends ChangeNotifier {
  VendorEarningProvider(this._apiService);

  final VendorEarningApiService _apiService;

  VendorEarningModel? earnings;
  bool isLoading = false;
  String? errorMessage;

  Future<void> getVendorEarnings() async {
    await _run(() async {
      earnings = await _apiService.getVendorEarnings();
    });
  }

  Future<void> getVendorEarningDetails() {
    return getVendorEarnings();
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
