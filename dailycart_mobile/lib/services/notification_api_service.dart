import 'package:dio/dio.dart';

import '../models/notification_model.dart';
import '../config/app_identity.dart';
import '../networking/api_client.dart';
import '../networking/api_response.dart';
import '../utils/secure_storage_helper.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class NotificationApiService with AuthenticatedApiMixin {
  NotificationApiService({Dio? dio, SecureStorageHelper? storage})
    : _dio = dio ?? ApiClient.shared.dio,
      _storage = storage ?? SecureStorageHelper();

  final Dio _dio;
  final SecureStorageHelper _storage;

  @override
  SecureStorageHelper get storage => _storage;

  String get _prefix => AppIdentity.isVendor
      ? '/vendor'
      : AppIdentity.isRider
      ? '/rider'
      : '';

  Future<List<NotificationModel>> getNotifications() async {
    try {
      final response = await _dio.get<dynamic>(
        '$_prefix/notifications',
        options: await authOptions(),
      );
      return ApiPage<NotificationModel>.fromJson(
        response.data,
        key: 'notifications',
        decoder: NotificationModel.fromJson,
      ).items;
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> markAsRead(String id) async {
    try {
      await _dio.patch<void>(
        '$_prefix/notifications/$id/read',
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> markAllAsRead() async {
    try {
      await _dio.patch<void>(
        '$_prefix/notifications/read-all',
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> deleteNotification(String id) async {
    try {
      await _dio.delete<void>(
        '$_prefix/notifications/$id',
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> registerDeviceToken({
    required String token,
    required String deviceId,
    required String platform,
    required String appVersion,
  }) async {
    try {
      await _dio.post<void>(
        '/notifications/device-tokens',
        data: {
          'device_token': token,
          'device_id': deviceId,
          'platform': platform,
          'app_role': AppIdentity.flavor.name,
          'app_version': appVersion,
        },
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> refreshDeviceToken({
    required String token,
    required String deviceId,
    required String platform,
    required String appVersion,
    String? oldToken,
  }) async {
    try {
      await _dio.patch<void>(
        '/notifications/device-tokens',
        data: {
          'device_token': token,
          'old_device_token': oldToken,
          'device_id': deviceId,
          'platform': platform,
          'app_role': AppIdentity.flavor.name,
          'app_version': appVersion,
        },
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> revokeDeviceToken({
    required String deviceId,
    String? token,
  }) async {
    try {
      await _dio.delete<void>(
        '/notifications/device-tokens',
        data: {
          'device_id': deviceId,
          'device_token': token,
          'app_role': AppIdentity.flavor.name,
        },
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<NotificationPreferences> getPreferences() async {
    try {
      final response = await _dio.get<dynamic>(
        '/notifications/preferences',
        options: await authOptions(),
      );
      final json = ApiResponseParser.requireObject(
        ApiResponseParser.requireMap(response.data),
        key: 'preferences',
      );
      return NotificationPreferences.fromJson(json);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<NotificationPreferences> updatePreferences(
    NotificationPreferences preferences,
  ) async {
    try {
      final response = await _dio.patch<dynamic>(
        '/notifications/preferences',
        data: {...preferences.toJson(), 'app_role': AppIdentity.flavor.name},
        options: await authOptions(),
      );
      final json = ApiResponseParser.requireObject(
        ApiResponseParser.requireMap(response.data),
        key: 'preferences',
      );
      return NotificationPreferences.fromJson(json);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
