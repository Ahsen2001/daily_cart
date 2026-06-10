import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/rider_dashboard_model.dart';
import '../models/rider_profile_model.dart';
import '../services/auth_api_service.dart';
import '../services/rider_api_service.dart';

final riderApiServiceProvider = Provider<RiderApiService>((ref) {
  return RiderApiService();
});

final riderProvider = ChangeNotifierProvider<RiderProvider>((ref) {
  return RiderProvider(ref.watch(riderApiServiceProvider));
});

class RiderProvider extends ChangeNotifier {
  RiderProvider(this._apiService);

  final RiderApiService _apiService;

  RiderDashboardModel? dashboard;
  RiderProfileModel? profile;
  bool isLoading = false;
  String? errorMessage;

  bool get isApproved {
    return dashboard?.isApproved == true || profile?.isApproved == true;
  }

  Future<void> getRiderDashboard() async {
    await _run(() async {
      dashboard = await _apiService.getRiderDashboard();
    });
  }

  Future<void> getRiderProfile() async {
    await _run(() async {
      profile = await _apiService.getRiderProfile();
    });
  }

  Future<bool> updateRiderProfile(RiderProfileModel riderProfile) async {
    return _run(() async {
      profile = await _apiService.updateRiderProfile(riderProfile);
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
