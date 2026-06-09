import 'package:dio/dio.dart';

import '../config/app_config.dart';
import '../models/notification_model.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class NotificationApiService with AuthenticatedApiMixin {
  NotificationApiService({
    Dio? dio,
    SecureStorageHelper? storage,
  })  : _dio = dio ??
            Dio(
              BaseOptions(
                baseUrl: AppConfig.apiBaseUrl,
                connectTimeout: const Duration(seconds: 20),
                receiveTimeout: const Duration(seconds: 20),
                headers: const {
                  'Accept': 'application/json',
                  'Content-Type': 'application/json',
                },
              ),
            ),
        _storage = storage ?? SecureStorageHelper();

  final Dio _dio;
  final SecureStorageHelper _storage;

  @override
  SecureStorageHelper get storage => _storage;

  Future<List<NotificationModel>> getNotifications() async {
    try {
      final response = await _dio.get<dynamic>(
        '/notifications',
        options: await authOptions(),
      );
      return ApiListParser.extractList(response.data, key: 'notifications')
          .map(NotificationModel.fromJson)
          .toList(growable: false);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> markAsRead(String id) async {
    try {
      await _dio.patch<void>(
        '/notifications/$id/read',
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> markAllAsRead() async {
    try {
      await _dio.patch<void>(
        '/notifications/read-all',
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> deleteNotification(String id) async {
    try {
      await _dio.delete<void>(
        '/notifications/$id',
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> saveDeviceToken(String token) async {
    try {
      await _dio.post<void>(
        '/notifications/device-token',
        data: {'device_token': token, 'platform': 'flutter'},
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
