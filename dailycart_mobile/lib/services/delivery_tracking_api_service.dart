import 'dart:async';
import 'dart:convert';

import 'package:dio/dio.dart';

import '../networking/api_client.dart';
import '../networking/api_exception.dart';
import '../networking/api_response.dart';
import '../utils/secure_storage_helper.dart';
import 'authenticated_api_mixin.dart';

class DeliveryTrackingApiService with AuthenticatedApiMixin {
  DeliveryTrackingApiService({Dio? dio, SecureStorageHelper? storage})
    : _dio = dio ?? ApiClient.shared.dio,
      _storage = storage ?? SecureStorageHelper();

  final Dio _dio;
  final SecureStorageHelper _storage;

  @override
  SecureStorageHelper get storage => _storage;

  Future<Map<String, dynamic>> getTracking(int orderId) async {
    try {
      final response = await _dio.get<dynamic>(
        '/orders/$orderId/tracking',
        options: await authOptions(),
      );
      return ApiResponseParser.requireMap(response.data);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Stream<Map<String, dynamic>> watchTracking(int orderId) async* {
    while (true) {
      try {
        await ensureAuthenticated();
        final response = await _dio.get<ResponseBody>(
          '/orders/$orderId/tracking/stream',
          options: Options(
            responseType: ResponseType.stream,
            receiveTimeout: const Duration(seconds: 40),
          ),
        );
        final body = response.data;
        if (body == null) return;
        await for (final line
            in body.stream
                .map<List<int>>((chunk) => chunk)
                .transform(utf8.decoder)
                .transform(const LineSplitter())) {
          if (line.startsWith('data: ')) {
            yield ApiResponseParser.requireMap(jsonDecode(line.substring(6)));
          }
        }
      } on DioException catch (error) {
        final exception = ApiException.fromDio(error);
        if ({401, 403, 404}.contains(exception.statusCode)) return;
      }
      await Future<void>.delayed(const Duration(seconds: 3));
    }
  }
}
