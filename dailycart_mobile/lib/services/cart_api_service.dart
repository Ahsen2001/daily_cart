import 'package:dio/dio.dart';

import '../config/app_config.dart';
import '../models/cart_model.dart';
import '../utils/secure_storage_helper.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class CartApiService with AuthenticatedApiMixin {
  CartApiService({Dio? dio, SecureStorageHelper? storage})
    : _dio =
          dio ??
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

  Future<CartModel> getCart() async {
    try {
      final response = await _dio.get<dynamic>(
        '/cart',
        options: await authOptions(),
      );
      return CartModel.fromJson(response.data as Map<String, dynamic>? ?? {});
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
          'variant_id': ?variantId,
        },
        options: await authOptions(),
      );
      return CartModel.fromJson(response.data as Map<String, dynamic>? ?? {});
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
        '/cart/items/$cartItemId',
        data: {'quantity': quantity},
        options: await authOptions(),
      );
      return CartModel.fromJson(response.data as Map<String, dynamic>? ?? {});
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<CartModel> removeCartItem(int cartItemId) async {
    try {
      final response = await _dio.delete<dynamic>(
        '/cart/items/$cartItemId',
        options: await authOptions(),
      );
      return CartModel.fromJson(response.data as Map<String, dynamic>? ?? {});
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<CartModel> clearCart() async {
    try {
      final response = await _dio.delete<dynamic>(
        '/cart',
        options: await authOptions(),
      );
      return CartModel.fromJson(response.data as Map<String, dynamic>? ?? {});
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
