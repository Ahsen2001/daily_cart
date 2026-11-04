import 'dart:convert';
import 'dart:math';

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
    final payload = request.toJson();
    final requestFingerprint = jsonEncode(payload);
    var idempotencyKey = await _storage.getCheckoutIdempotencyKey();
    final savedFingerprint = await _storage.getCheckoutRequestFingerprint();

    if (idempotencyKey == null || savedFingerprint != requestFingerprint) {
      idempotencyKey = _newIdempotencyKey();
      await _storage.saveCheckoutIdempotency(
        key: idempotencyKey,
        requestFingerprint: requestFingerprint,
      );
    }

    try {
      final response = await _dio.post<Map<String, dynamic>>(
        '/orders',
        data: payload,
        options: (await authOptions()).copyWith(
          headers: {'Idempotency-Key': idempotencyKey},
        ),
      );
      final checkout = CheckoutResponseModel.fromJson(
        ApiResponseParser.requireMap(response.data),
      );
      await _storage.clearCheckoutIdempotency();
      return checkout;
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  String _newIdempotencyKey() {
    final random = Random.secure();
    final bytes = List<int>.generate(16, (_) => random.nextInt(256));
    bytes[6] = (bytes[6] & 0x0f) | 0x40;
    bytes[8] = (bytes[8] & 0x3f) | 0x80;
    final hex = bytes.map((byte) => byte.toRadixString(16).padLeft(2, '0')).join();
    return '${hex.substring(0, 8)}-${hex.substring(8, 12)}-'
        '${hex.substring(12, 16)}-${hex.substring(16, 20)}-${hex.substring(20)}';
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
