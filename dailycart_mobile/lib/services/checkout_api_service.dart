import 'package:dio/dio.dart';

import '../models/checkout_request_model.dart';
import '../models/checkout_quote_model.dart';
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
        '/orders',
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

  Future<CheckoutQuoteModel> getQuote({
    String? couponCode,
    int loyaltyPoints = 0,
    String? deliveryDistrict,
    int? deliveryDistanceMeters,
  }) async {
    try {
      final response = await _dio.post<dynamic>(
        '/checkout/quote',
        data: {
          if (couponCode != null && couponCode.isNotEmpty)
            'coupon_code': couponCode,
          if (loyaltyPoints > 0) 'loyalty_points': loyaltyPoints,
          if (deliveryDistrict != null && deliveryDistrict.isNotEmpty)
            'delivery_district': deliveryDistrict,
          'delivery_distance_meters': ?deliveryDistanceMeters,
        },
        options: await authOptions(),
      );
      return CheckoutQuoteModel.fromJson(
        ApiListParser.extractObject(response.data, key: 'quote'),
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
