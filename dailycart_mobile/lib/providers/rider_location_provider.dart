import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/rider_location_model.dart';
import '../services/auth_api_service.dart';
import '../services/rider_location_api_service.dart';
import '../utils/secure_storage_helper.dart';

final riderLocationApiServiceProvider = Provider<RiderLocationApiService>((
  ref,
) {
  return RiderLocationApiService();
});

final riderLocationProvider = ChangeNotifierProvider<RiderLocationProvider>((
  ref,
) {
  return RiderLocationProvider(ref.watch(riderLocationApiServiceProvider));
});

class RiderLocationProvider extends ChangeNotifier {
  RiderLocationProvider(this._apiService);

  final RiderLocationApiService _apiService;
  final SecureStorageHelper _storage = SecureStorageHelper();

  RiderLocationModel? currentLocation;
  bool isLoading = false;
  String? errorMessage;

  Future<bool> updateRiderLocation({
    required double latitude,
    required double longitude,
    required int deliveryId,
  }) async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();
    try {
      final location = RiderLocationModel(
        latitude: latitude,
        longitude: longitude,
        updatedAt: DateTime.now(),
      );
      await _apiService.updateRiderLocation(
        location,
        deliveryId: deliveryId,
        trackingToken:
            await _storage.getTrackingToken() ??
            (throw const ApiException('Active tracking session not found.')),
        recordedAt: location.updatedAt,
      );
      currentLocation = location;
      return true;
    } on ApiException catch (error) {
      errorMessage = error.message;
      return false;
    } catch (_) {
      errorMessage = 'Unable to update rider location.';
      return false;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }
}
