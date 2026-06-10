import 'package:dio/dio.dart';

import '../config/app_config.dart';
import '../models/rider_location_model.dart';
import '../utils/secure_storage_helper.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class RiderLocationApiService with AuthenticatedApiMixin {
  RiderLocationApiService({Dio? dio, SecureStorageHelper? storage})
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

  Future<void> updateRiderLocation(RiderLocationModel location) async {
    try {
      await _dio.post<void>(
        '/rider/location',
        data: location.toJson(),
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
