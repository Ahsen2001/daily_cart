import 'package:dio/dio.dart';

import '../models/order_model.dart';
import '../networking/api_client.dart';
import '../networking/api_response.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class OrderApiService with AuthenticatedApiMixin {
  OrderApiService({
    Dio? dio,
    SecureStorageHelper? storage,
  })  : _dio = dio ?? ApiClient.shared.dio,
        _storage = storage ?? SecureStorageHelper();

  final Dio _dio;
  final SecureStorageHelper _storage;

  @override
  SecureStorageHelper get storage => _storage;

  Future<List<OrderModel>> getOrders({String? filter}) async {
    return (await getOrdersPage(filter: filter)).items;
  }

  Future<ApiPage<OrderModel>> getOrdersPage({String? filter}) async {
    try {
      final response = await _dio.get<dynamic>(
        '/orders',
        queryParameters: {
          if (filter != null && filter != 'all') 'filter': filter,
        },
        options: await authOptions(),
      );
      return ApiPage<OrderModel>.fromJson(
        response.data,
        key: 'orders',
        decoder: OrderModel.fromJson,
      );
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

  Future<OrderModel> cancelOrder(
    int orderId, {
    String reason = 'Customer requested cancellation from the mobile app.',
  }) async {
    try {
      final response = await _dio.patch<dynamic>(
        '/orders/$orderId/cancel',
        data: {'reason': reason},
        options: await authOptions(),
      );
      return OrderModel.fromJson(ApiListParser.extractObject(response.data));
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
