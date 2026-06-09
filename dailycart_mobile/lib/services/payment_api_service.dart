import 'package:dio/dio.dart';

import '../config/app_config.dart';
import '../models/checkout_response_model.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class PaymentApiService with AuthenticatedApiMixin {
  PaymentApiService({
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

  Future<String> getPaymentUrl(int orderId) async {
    try {
      final response = await _dio.get<Map<String, dynamic>>(
        '/payments/$orderId/payhere',
        options: await authOptions(),
      );
      return (response.data?['payment_url'] ?? '').toString();
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<OrderModel> checkPaymentStatus(int orderId) async {
    try {
      final response = await _dio.get<dynamic>(
        '/payments/$orderId/status',
        options: await authOptions(),
      );
      return OrderModel.fromJson(ApiListParser.extractObject(response.data));
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
