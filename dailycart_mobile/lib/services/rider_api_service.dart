import 'package:dio/dio.dart';

import '../models/rider_dashboard_model.dart';
import '../models/rider_profile_model.dart';
import '../networking/api_client.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class RiderApiService with AuthenticatedApiMixin {
  RiderApiService({Dio? dio, SecureStorageHelper? storage})
      : _dio = dio ?? ApiClient.shared.dio,
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

  Future<RiderProfileModel> updateAvailability(String status) async {
    try {
      final response = await _dio.patch<dynamic>(
        '/rider/availability',
        data: {'availability_status': status},
        options: await authOptions(),
      );
      return RiderProfileModel.fromJson(
        ApiListParser.extractObject(response.data, key: 'rider'),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<Map<String, dynamic>> getReports() async {
    try {
      final response = await _dio.get<dynamic>(
        '/rider/reports',
        options: await authOptions(),
      );
      return ApiListParser.extractObject(response.data, key: 'report');
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
