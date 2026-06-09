import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/vendor_review_model.dart';
import '../services/auth_api_service.dart';
import '../services/vendor_review_api_service.dart';

final vendorReviewApiServiceProvider = Provider<VendorReviewApiService>((ref) {
  return VendorReviewApiService();
});

final vendorReviewProvider =
    ChangeNotifierProvider<VendorReviewProvider>((ref) {
  return VendorReviewProvider(ref.watch(vendorReviewApiServiceProvider));
});

class VendorReviewProvider extends ChangeNotifier {
  VendorReviewProvider(this._apiService);

  final VendorReviewApiService _apiService;

  List<VendorReviewModel> reviews = const [];
  bool isLoading = false;
  String? errorMessage;

  double get averageRating {
    if (reviews.isEmpty) {
      return 0;
    }
    final total = reviews.fold<double>(0, (sum, item) => sum + item.rating);
    return total / reviews.length;
  }

  Future<void> getVendorReviews({int? rating}) async {
    await _run(() async {
      reviews = await _apiService.getVendorReviews(rating: rating);
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
