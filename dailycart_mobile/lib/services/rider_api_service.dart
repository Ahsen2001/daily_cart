import 'package:dio/dio.dart';

import '../config/app_config.dart';
import '../models/rider_dashboard_model.dart';
import '../models/rider_profile_model.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class RiderApiService with AuthenticatedApiMixin {
  RiderApiService({Dio? dio, SecureStorageHelper? storage})
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

  Future<RiderDashboardModel> getRiderDashboard() async {
    try {
      final response = await _dio.get<dynamic>(
        '/rider/dashboard',
        options: await authOptions(),
      );
      return RiderDashboardModel.fromJson(
        ApiListParser.extractObject(response.data),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<RiderProfileModel> getRiderProfile() async {
    try {
      final response = await _dio.get<dynamic>(
        '/rider/profile',
        options: await authOptions(),
      );
      return RiderProfileModel.fromJson(
        ApiListParser.extractObject(response.data),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<RiderProfileModel> updateRiderProfile(
    RiderProfileModel profile,
  ) async {
    try {
      final response = await _dio.put<dynamic>(
        '/rider/profile',
        data: profile.toJson(),
        options: await authOptions(),
      );
      return RiderProfileModel.fromJson(
        ApiListParser.extractObject(response.data),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
