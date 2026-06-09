import 'package:dio/dio.dart';

import '../config/app_config.dart';
import '../models/order_model.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class OrderApiService with AuthenticatedApiMixin {
  OrderApiService({
    Dio? dio,
    SecureStorageHelper? storage,
  })  : _dio = dio ??
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

  Future<List<OrderModel>> getOrders({String? filter}) async {
    try {
      final response = await _dio.get<dynamic>(
        '/orders',
        queryParameters: {
          if (filter != null && filter != 'all') 'filter': filter,
        },
        options: await authOptions(),
      );
      return ApiListParser.extractList(response.data, key: 'orders')
          .map(OrderModel.fromJson)
          .toList(growable: false);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<OrderModel> getOrderDetails(int orderId) async {
    try {
      final response = await _dio.get<dynamic>(
        '/orders/$orderId',
        options: await authOptions(),
      );
      return OrderModel.fromJson(ApiListParser.extractObject(response.data));
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<OrderModel> cancelOrder(int orderId) async {
    try {
      final response = await _dio.patch<dynamic>(
        '/orders/$orderId/cancel',
        options: await authOptions(),
      );
      return OrderModel.fromJson(ApiListParser.extractObject(response.data));
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
