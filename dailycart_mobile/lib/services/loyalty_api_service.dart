import 'package:dio/dio.dart';

import '../config/app_config.dart';
import '../models/loyalty_point_model.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class LoyaltyApiService with AuthenticatedApiMixin {
  LoyaltyApiService({Dio? dio, SecureStorageHelper? storage})
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

  Future<int> getBalance() async {
    try {
      final response = await _dio.get<dynamic>(
        '/loyalty/balance',
        options: await authOptions(),
      );
      final data = response.data;
      if (data is Map<String, dynamic>) {
        final value = data['balance'] ?? data['points'] ?? data['data'];
        return value is int ? value : int.tryParse(value.toString()) ?? 0;
      }
      return 0;
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<List<LoyaltyPointModel>> getHistory() async {
    try {
      final response = await _dio.get<dynamic>(
        '/loyalty/history',
        options: await authOptions(),
      );
      return ApiListParser.extractList(response.data, key: 'history')
          .map(LoyaltyPointModel.fromJson)
          .toList(growable: false);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
