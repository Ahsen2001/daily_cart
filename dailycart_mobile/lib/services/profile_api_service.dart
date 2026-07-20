import 'package:dio/dio.dart';

import '../models/profile_model.dart';
import '../networking/api_client.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class ProfileApiService with AuthenticatedApiMixin {
  ProfileApiService({
    Dio? dio,
    SecureStorageHelper? storage,
  })  : _dio = dio ?? ApiClient.shared.dio,
        _storage = storage ?? SecureStorageHelper();

  final Dio _dio;
  final SecureStorageHelper _storage;

  @override
  SecureStorageHelper get storage => _storage;

  Future<ProfileModel> getProfile() async {
    try {
      final response = await _dio.get<dynamic>(
        '/profile',
        options: await authOptions(),
      );
      return ProfileModel.fromJson(ApiListParser.extractObject(response.data));
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<ProfileModel> updateProfile(ProfileModel profile) async {
    try {
      final response = await _dio.patch<dynamic>(
        '/profile',
        data: profile.toJson(),
        options: await authOptions(),
      );
      return ProfileModel.fromJson(ApiListParser.extractObject(response.data));
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<ProfileModel> uploadProfilePhoto(String filePath) async {
    try {
      final response = await _dio.post<dynamic>(
        '/profile/photo',
        data: await ApiClient.shared.multipart(
          files: [ApiUploadFile(field: 'photo', path: filePath)],
        ),
        options: await authOptions(),
      );
      return ProfileModel.fromJson(ApiListParser.extractObject(response.data));
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> changePassword({
    required String currentPassword,
    required String newPassword,
    required String confirmPassword,
  }) async {
    try {
      await _dio.patch<void>(
        '/profile/password',
        data: {
          'current_password': currentPassword,
          'password': newPassword,
          'password_confirmation': confirmPassword,
        },
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
