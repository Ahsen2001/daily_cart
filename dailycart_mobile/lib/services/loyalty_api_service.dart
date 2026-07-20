import 'package:dio/dio.dart';

import '../models/loyalty_point_model.dart';
import '../networking/api_client.dart';
import '../networking/api_exception.dart';
import '../networking/api_response.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class LoyaltyApiService with AuthenticatedApiMixin {
  LoyaltyApiService({Dio? dio, SecureStorageHelper? storage})
      : _dio = dio ?? ApiClient.shared.dio,
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
      final data = ApiResponseParser.requireMap(response.data);
      final value = data['balance'] ?? data['points'] ?? data['data'];
      if (value is int) {
        return value;
      }
      final parsed = int.tryParse(value?.toString() ?? '');
      if (parsed == null) {
        throw ApiException.parsing(
          'The loyalty response is missing a numeric balance.',
        );
      }
      return parsed;
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
