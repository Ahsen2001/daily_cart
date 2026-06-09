import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/vendor_dashboard_model.dart';
import '../models/vendor_profile_model.dart';
import '../services/auth_api_service.dart';
import '../services/vendor_api_service.dart';

final vendorApiServiceProvider = Provider<VendorApiService>((ref) {
  return VendorApiService();
});

final vendorProvider = ChangeNotifierProvider<VendorProvider>((ref) {
  return VendorProvider(ref.watch(vendorApiServiceProvider));
});

class VendorProvider extends ChangeNotifier {
  VendorProvider(this._apiService);

  final VendorApiService _apiService;

  VendorDashboardModel? dashboard;
  VendorProfileModel? profile;
  bool isLoading = false;
  String? errorMessage;

  bool get isApproved {
    return dashboard?.isApproved == true || profile?.isApproved == true;
  }

  Future<void> getVendorDashboard() async {
    await _run(() async {
      dashboard = await _apiService.getVendorDashboard();
    });
  }

  Future<void> getVendorProfile() async {
    await _run(() async {
      profile = await _apiService.getVendorProfile();
    });
  }

  Future<bool> updateVendorProfile(VendorProfileModel vendorProfile) async {
    return _run(() async {
      profile = await _apiService.updateVendorProfile(vendorProfile);
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
