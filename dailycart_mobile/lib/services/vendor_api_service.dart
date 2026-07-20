import 'package:dio/dio.dart';

import '../models/vendor_dashboard_model.dart';
import '../models/vendor_profile_model.dart';
import '../networking/api_client.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class VendorApiService with AuthenticatedApiMixin {
  VendorApiService({Dio? dio, SecureStorageHelper? storage})
      : _dio = dio ?? ApiClient.shared.dio,
        _storage = storage ?? SecureStorageHelper();

  final Dio _dio;
  final SecureStorageHelper _storage;

  @override
  SecureStorageHelper get storage => _storage;

  Future<VendorDashboardModel> getVendorDashboard() async {
    try {
      final response = await _dio.get<dynamic>(
        '/vendor/dashboard',
        options: await authOptions(),
      );
      return VendorDashboardModel.fromJson(
        ApiListParser.extractObject(response.data),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<VendorProfileModel> getVendorProfile() async {
    try {
      final response = await _dio.get<dynamic>(
        '/vendor/profile',
        options: await authOptions(),
      );
      return VendorProfileModel.fromJson(
        ApiListParser.extractObject(response.data),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<VendorProfileModel> updateVendorProfile(
    VendorProfileModel profile,
  ) async {
    try {
      final response = await _dio.put<dynamic>(
        '/vendor/profile',
        data: profile.toJson(),
        options: await authOptions(),
      );
      return VendorProfileModel.fromJson(
        ApiListParser.extractObject(response.data),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
