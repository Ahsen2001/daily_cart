import 'package:dio/dio.dart';

import '../models/notification_model.dart';
import '../config/app_identity.dart';
import '../networking/api_client.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class NotificationApiService with AuthenticatedApiMixin {
  NotificationApiService({
    Dio? dio,
    SecureStorageHelper? storage,
  })  : _dio = dio ?? ApiClient.shared.dio,
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

  Future<void> saveDeviceToken(String token) async {
    try {
      await _dio.post<void>(
        '$_prefix/notifications/device-token',
        data: {'device_token': token, 'platform': 'flutter'},
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
