import 'package:dio/dio.dart';

import '../models/cart_model.dart';
import '../networking/api_client.dart';
import '../networking/api_response.dart';
import '../utils/secure_storage_helper.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class CartApiService with AuthenticatedApiMixin {
  CartApiService({Dio? dio, SecureStorageHelper? storage})
    : _dio = dio ?? ApiClient.shared.dio,
      _storage = storage ?? SecureStorageHelper();

  final Dio _dio;
  final SecureStorageHelper _storage;

  @override
  SecureStorageHelper get storage => _storage;

  Future<CartModel> getCart() async {
    try {
      final response = await _dio.get<dynamic>(
        '/cart',
        options: await authOptions(),
      );
      return CartModel.fromJson(ApiResponseParser.requireMap(response.data));
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<CartModel> addToCart({
    required int productId,
    required int quantity,
    int? variantId,
  }) async {
    try {
      final response = await _dio.post<dynamic>(
        '/cart',
        data: {
          'product_id': productId,
          'quantity': quantity,
          'product_variant_id': ?variantId,
        },
        options: await authOptions(),
      );
      return CartModel.fromJson(ApiResponseParser.requireMap(response.data));
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<CartModel> updateCartItem({
    required int cartItemId,
    required int quantity,
  }) async {
    try {
      final response = await _dio.patch<dynamic>(
        '/cart-items/$cartItemId',
        data: {'quantity': quantity},
        options: await authOptions(),
      );
      return CartModel.fromJson(ApiResponseParser.requireMap(response.data));
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<CartModel> removeCartItem(int cartItemId) async {
    try {
      final response = await _dio.delete<dynamic>(
        '/cart-items/$cartItemId',
        options: await authOptions(),
      );
      return CartModel.fromJson(ApiResponseParser.requireMap(response.data));
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<CartModel> clearCart() async {
    try {
      final response = await _dio.delete<dynamic>(
        '/cart/clear',
        options: await authOptions(),
      );
      return CartModel.fromJson(ApiResponseParser.requireMap(response.data));
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
