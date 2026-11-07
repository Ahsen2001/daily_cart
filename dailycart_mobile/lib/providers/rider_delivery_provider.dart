import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/delivery_model.dart';
import '../services/auth_api_service.dart';
import '../services/rider_delivery_api_service.dart';
import '../services/rider_delivery_tracking_service.dart';

final riderDeliveryApiServiceProvider = Provider<RiderDeliveryApiService>((
  ref,
) {
  return RiderDeliveryApiService();
});

final riderDeliveryProvider = ChangeNotifierProvider<RiderDeliveryProvider>((
  ref,
) {
  return RiderDeliveryProvider(ref.watch(riderDeliveryApiServiceProvider));
});

class RiderDeliveryProvider extends ChangeNotifier {
  RiderDeliveryProvider(this._apiService);

  final RiderDeliveryApiService _apiService;

  List<DeliveryModel> deliveries = const [];
  DeliveryModel? selectedDelivery;
  bool isLoading = false;
  String? errorMessage;
  int _deliveriesRequestId = 0;
  int _detailsRequestId = 0;

  Future<void> getAssignedDeliveries({String status = 'all'}) async {
    final requestId = ++_deliveriesRequestId;
    isLoading = true;
    errorMessage = null;
    notifyListeners();
    try {
      final result = await _apiService.getAssignedDeliveries(status: status);
      if (requestId != _deliveriesRequestId) return;
      deliveries = result;
    } on ApiException catch (error) {
      if (requestId == _deliveriesRequestId) errorMessage = error.message;
    } catch (_) {
      if (requestId == _deliveriesRequestId) {
        errorMessage = 'Something went wrong. Please try again.';
      }
    } finally {
      if (requestId == _deliveriesRequestId) {
        isLoading = false;
        notifyListeners();
      }
    }
  }

  Future<void> getDeliveryDetails(int deliveryId) async {
    final requestId = ++_detailsRequestId;
    if (selectedDelivery?.id != deliveryId) selectedDelivery = null;
    isLoading = true;
    errorMessage = null;
    notifyListeners();
    try {
      final result = await _apiService.getDeliveryDetails(deliveryId);
      if (requestId != _detailsRequestId) return;
      selectedDelivery = result;
    } on ApiException catch (error) {
      if (requestId == _detailsRequestId) errorMessage = error.message;
    } catch (_) {
      if (requestId == _detailsRequestId) {
        errorMessage = 'Something went wrong. Please try again.';
      }
    } finally {
      if (requestId == _detailsRequestId) {
        isLoading = false;
        notifyListeners();
      }
    }
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

  Future<bool> markFailed(int deliveryId, String category, String reason) {
    return _update(() => _apiService.markFailed(deliveryId, category, reason));
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
      await RiderDeliveryTrackingService.instance.syncWithStatus(
        updated.id,
        updated.status,
      );
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
