import 'package:dio/dio.dart';

import '../models/checkout_request_model.dart';
import '../models/checkout_response_model.dart';
import '../networking/api_client.dart';
import '../networking/api_response.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class CheckoutApiService with AuthenticatedApiMixin {
  CheckoutApiService({
    Dio? dio,
    SecureStorageHelper? storage,
  })  : _dio = dio ?? ApiClient.shared.dio,
        _storage = storage ?? SecureStorageHelper();

  final Dio _dio;
  final SecureStorageHelper _storage;

  @override
  SecureStorageHelper get storage => _storage;

  Future<CheckoutResponseModel> createOrder(
    CheckoutRequestModel request,
  ) async {
    try {
      final response = await _dio.post<Map<String, dynamic>>(
        '/checkout',
        data: request.toJson(),
        options: await authOptions(),
      );
      return CheckoutResponseModel.fromJson(
        ApiResponseParser.requireMap(response.data),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<OrderModel> getOrderStatus(int orderId) async {
    try {
      final response = await _dio.get<dynamic>(
        '/orders/$orderId/status',
        options: await authOptions(),
      );
      return OrderModel.fromJson(ApiListParser.extractObject(response.data));
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
