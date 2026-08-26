import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/checkout_response_model.dart';
import '../models/bank_transfer_model.dart';
import '../services/auth_api_service.dart';
import '../services/payment_api_service.dart';

final paymentApiServiceProvider = Provider<PaymentApiService>((ref) {
  return PaymentApiService();
});

final paymentProvider = ChangeNotifierProvider<PaymentProvider>((ref) {
  return PaymentProvider(ref.watch(paymentApiServiceProvider));
});

class PaymentProvider extends ChangeNotifier {
  PaymentProvider(this._apiService);

  final PaymentApiService _apiService;

  String? paymentUrl;
  OrderModel? order;
  final Map<int, BankTransferModel> bankTransfers = {};
  bool isLoading = false;
  String? errorMessage;

  Future<bool> getPaymentUrl(int orderId) async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();

    try {
      paymentUrl = await _apiService.getPaymentUrl(orderId);
      return paymentUrl != null && paymentUrl!.isNotEmpty;
    } on ApiException catch (error) {
      errorMessage = error.message;
      return false;
    } catch (_) {
      errorMessage = 'Unable to load payment URL.';
      return false;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<OrderModel?> checkPaymentStatus(int orderId) async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();

    try {
      order = await _apiService.checkPaymentStatus(orderId);
      return order;
    } on ApiException catch (error) {
      errorMessage = error.message;
      return null;
    } catch (_) {
      errorMessage = 'Unable to check payment status.';
      return null;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<BankTransferModel?> getBankTransfer(int orderId) async {
    return _loadTransfer(orderId, () => _apiService.getBankTransfer(orderId));
  }

  Future<BankTransferModel?> uploadBankTransferSlip(
    int orderId,
    String path,
  ) async {
    return _loadTransfer(
      orderId,
      () => _apiService.uploadBankTransferSlip(orderId, path),
    );
  }

  Future<BankTransferModel?> _loadTransfer(
    int orderId,
    Future<BankTransferModel> Function() action,
  ) async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();
    try {
      final transfer = await action();
      bankTransfers[orderId] = transfer;
      return transfer;
    } on ApiException catch (error) {
      errorMessage = error.message;
      return null;
    } catch (_) {
      errorMessage = 'Unable to load bank transfer details.';
      return null;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }
}
