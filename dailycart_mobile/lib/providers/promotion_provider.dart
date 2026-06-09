import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/promotion_model.dart';
import '../services/auth_api_service.dart';
import '../services/promotion_api_service.dart';

final promotionApiServiceProvider = Provider<PromotionApiService>((ref) {
  return PromotionApiService();
});

final promotionProvider = ChangeNotifierProvider<PromotionProvider>((ref) {
  return PromotionProvider(ref.watch(promotionApiServiceProvider));
});

class PromotionProvider extends ChangeNotifier {
  PromotionProvider(this._apiService);

  final PromotionApiService _apiService;

  List<PromotionModel> promotions = const [];
  PromotionModel? selectedPromotion;
  bool isLoading = false;
  String? errorMessage;

  Future<void> getPromotions() async {
    await _run(() async {
      promotions = await _apiService.getPromotions();
    });
  }

  Future<void> getPromotionDetails(int id) async {
    await _run(() async {
      selectedPromotion = await _apiService.getPromotionDetails(id);
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
