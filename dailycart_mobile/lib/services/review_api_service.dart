import 'package:dio/dio.dart';

import '../config/app_config.dart';
import '../models/review_model.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class ReviewApiService with AuthenticatedApiMixin {
  ReviewApiService({Dio? dio, SecureStorageHelper? storage})
      : _dio = dio ??
            Dio(
              BaseOptions(
                baseUrl: AppConfig.apiBaseUrl,
                connectTimeout: const Duration(seconds: 20),
                receiveTimeout: const Duration(seconds: 20),
                headers: const {'Accept': 'application/json'},
              ),
            ),
        _storage = storage ?? SecureStorageHelper();

  final Dio _dio;
  final SecureStorageHelper _storage;

  @override
  SecureStorageHelper get storage => _storage;

  Future<List<ReviewModel>> getProductReviews(int productId) async {
    try {
      final response = await _dio.get<dynamic>(
        '/products/$productId/reviews',
        options: await authOptions(),
      );
      return ApiListParser.extractList(response.data, key: 'reviews')
          .map(ReviewModel.fromJson)
          .toList(growable: false);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<List<ReviewModel>> getMyReviews() async {
    try {
      final response = await _dio.get<dynamic>(
        '/reviews/my',
        options: await authOptions(),
      );
      return ApiListParser.extractList(response.data, key: 'reviews')
          .map(ReviewModel.fromJson)
          .toList(growable: false);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<ReviewModel> addReview({
    required int orderId,
    required int productId,
    required int rating,
    required String comment,
    String? imagePath,
  }) async {
    try {
      final data = FormData.fromMap({
        'order_id': orderId,
        'product_id': productId,
        'rating': rating,
        'comment': comment,
        if (imagePath != null && imagePath.isNotEmpty)
          'image': await MultipartFile.fromFile(imagePath),
      });
      final response = await _dio.post<dynamic>(
        '/reviews',
        data: data,
        options: await authOptions(),
      );
      return ReviewModel.fromJson(ApiListParser.extractObject(response.data));
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<ReviewModel> updateReview({
    required int reviewId,
    required int rating,
    required String comment,
    String? imagePath,
  }) async {
    try {
      final data = FormData.fromMap({
        'rating': rating,
        'comment': comment,
        if (imagePath != null && imagePath.isNotEmpty)
          'image': await MultipartFile.fromFile(imagePath),
      });
      final response = await _dio.post<dynamic>(
        '/reviews/$reviewId',
        data: data,
        options: await authOptions(),
      );
      return ReviewModel.fromJson(ApiListParser.extractObject(response.data));
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> deleteReview(int reviewId) async {
    try {
      await _dio.delete<void>(
        '/reviews/$reviewId',
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
