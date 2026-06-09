import 'package:dio/dio.dart';

import '../config/app_config.dart';
import '../models/promotion_model.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class PromotionApiService with AuthenticatedApiMixin {
  PromotionApiService({Dio? dio, SecureStorageHelper? storage})
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

  Future<List<PromotionModel>> getPromotions() async {
    try {
      final response = await _dio.get<dynamic>(
        '/promotions',
        options: await authOptions(),
      );
      return ApiListParser.extractList(response.data, key: 'promotions')
          .map(PromotionModel.fromJson)
          .toList(growable: false);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<PromotionModel> getPromotionDetails(int id) async {
    try {
      final response = await _dio.get<dynamic>(
        '/promotions/$id',
        options: await authOptions(),
      );
      return PromotionModel.fromJson(ApiListParser.extractObject(response.data));
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
