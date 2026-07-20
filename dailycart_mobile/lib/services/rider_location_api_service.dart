import 'package:dio/dio.dart';

import '../models/rider_location_model.dart';
import '../networking/api_client.dart';
import '../utils/secure_storage_helper.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class RiderLocationApiService with AuthenticatedApiMixin {
  RiderLocationApiService({Dio? dio, SecureStorageHelper? storage})
      : _dio = dio ?? ApiClient.shared.dio,
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
