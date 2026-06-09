import 'package:dio/dio.dart';

import '../config/app_config.dart';
import '../models/vendor_review_model.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class VendorReviewApiService with AuthenticatedApiMixin {
  VendorReviewApiService({Dio? dio, SecureStorageHelper? storage})
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

  Future<List<VendorReviewModel>> getVendorReviews({int? rating}) async {
    try {
      final response = await _dio.get<dynamic>(
        '/vendor/reviews',
        queryParameters: {if (rating != null && rating > 0) 'rating': rating},
        options: await authOptions(),
      );
      return ApiListParser.extractList(response.data, key: 'reviews')
          .map(VendorReviewModel.fromJson)
          .toList(growable: false);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
