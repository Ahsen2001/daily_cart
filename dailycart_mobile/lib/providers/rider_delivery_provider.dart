import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/delivery_model.dart';
import '../services/auth_api_service.dart';
import '../services/rider_delivery_api_service.dart';

final riderDeliveryApiServiceProvider =
    Provider<RiderDeliveryApiService>((ref) {
  return RiderDeliveryApiService();
});

final riderDeliveryProvider =
    ChangeNotifierProvider<RiderDeliveryProvider>((ref) {
  return RiderDeliveryProvider(ref.watch(riderDeliveryApiServiceProvider));
});

class RiderDeliveryProvider extends ChangeNotifier {
  RiderDeliveryProvider(this._apiService);

  final RiderDeliveryApiService _apiService;

  List<DeliveryModel> deliveries = const [];
  DeliveryModel? selectedDelivery;
  bool isLoading = false;
  String? errorMessage;

  Future<void> getAssignedDeliveries({String status = 'all'}) async {
    await _run(() async {
      deliveries = await _apiService.getAssignedDeliveries(status: status);
    });
  }

  Future<void> getDeliveryDetails(int deliveryId) async {
    await _run(() async {
      selectedDelivery = await _apiService.getDeliveryDetails(deliveryId);
    });
  }

  Future<bool> markPickedUp(int deliveryId) {
    return _update(() => _apiService.markPickedUp(deliveryId));
  }

  Future<bool> acceptDelivery(int deliveryId) {
    return _update(() => _apiService.acceptDelivery(deliveryId));
  }

  Future<bool> markOnTheWay(int deliveryId) {
    return _update(() => _apiService.markOnTheWay(deliveryId));
  }

  Future<bool> markFailed(int deliveryId, String reason) {
    return _update(() => _apiService.markFailed(deliveryId, reason));
  }

  Future<bool> markDelivered({
    required int deliveryId,
    required String proofImagePath,
    String? signatureImagePath,
    String note = '',
  }) {
    return _update(
      () => _apiService.markDelivered(
        deliveryId: deliveryId,
        proofImagePath: proofImagePath,
        signatureImagePath: signatureImagePath,
        note: note,
      ),
    );
  }

  Future<bool> replaceProof({
    required int deliveryId,
    required String proofImagePath,
    String? signatureImagePath,
    String note = '',
  }) {
    return _update(
      () => _apiService.replaceProof(
        deliveryId: deliveryId,
        proofImagePath: proofImagePath,
        signatureImagePath: signatureImagePath,
        note: note,
      ),
    );
  }

  Future<bool> _update(Future<DeliveryModel> Function() request) async {
    return _run(() async {
      final updated = await request();
      selectedDelivery = updated;
      deliveries = deliveries
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
