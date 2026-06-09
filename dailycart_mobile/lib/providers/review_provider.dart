import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/review_model.dart';
import '../services/auth_api_service.dart';
import '../services/review_api_service.dart';

final reviewApiServiceProvider = Provider<ReviewApiService>((ref) {
  return ReviewApiService();
});

final reviewProvider = ChangeNotifierProvider<ReviewProvider>((ref) {
  return ReviewProvider(ref.watch(reviewApiServiceProvider));
});

class ReviewProvider extends ChangeNotifier {
  ReviewProvider(this._apiService);

  final ReviewApiService _apiService;

  List<ReviewModel> reviews = const [];
  List<ReviewModel> productReviews = const [];
  bool isLoading = false;
  String? errorMessage;

  Future<void> getProductReviews(int productId) async {
    await _run(() async {
      productReviews = await _apiService.getProductReviews(productId);
    });
  }

  Future<void> getMyReviews() async {
    await _run(() async {
      reviews = await _apiService.getMyReviews();
    });
  }

  Future<bool> addReview({
    required int orderId,
    required int productId,
    required int rating,
    required String comment,
    String? imagePath,
  }) async {
    if (rating < 1 || rating > 5) {
      errorMessage = 'Rating is required.';
      notifyListeners();
      return false;
    }

    return _run(() async {
      final review = await _apiService.addReview(
        orderId: orderId,
        productId: productId,
        rating: rating,
        comment: comment,
        imagePath: imagePath,
      );
      reviews = [review, ...reviews];
    });
  }

  Future<bool> updateReview({
    required int reviewId,
    required int rating,
    required String comment,
    String? imagePath,
  }) async {
    return _run(() async {
      final review = await _apiService.updateReview(
        reviewId: reviewId,
        rating: rating,
        comment: comment,
        imagePath: imagePath,
      );
      reviews = reviews
          .map((item) => item.id == reviewId ? review : item)
          .toList(growable: false);
    });
  }

  Future<bool> deleteReview(int reviewId) async {
    return _run(() async {
      await _apiService.deleteReview(reviewId);
      reviews =
          reviews.where((item) => item.id != reviewId).toList(growable: false);
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
