import 'package:dio/dio.dart';

import '../models/wishlist_model.dart';
import '../networking/api_client.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class WishlistApiService with AuthenticatedApiMixin {
  WishlistApiService({
    Dio? dio,
    SecureStorageHelper? storage,
  })  : _dio = dio ?? ApiClient.shared.dio,
        _storage = storage ?? SecureStorageHelper();

  final Dio _dio;
  final SecureStorageHelper _storage;

  @override
  SecureStorageHelper get storage => _storage;

  Future<List<WishlistModel>> getWishlist() async {
    try {
      final response = await _dio.get<dynamic>(
        '/wishlist',
        options: await authOptions(),
      );
      return ApiListParser.extractList(response.data, key: 'wishlist')
          .map(WishlistModel.fromJson)
          .where((item) => item.product.isVisibleForCustomer)
          .toList(growable: false);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> addToWishlist(int productId) async {
    try {
      await _dio.post<void>(
        '/wishlist',
        data: {'product_id': productId},
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> removeFromWishlist(int productId) async {
    try {
      await _dio.delete<void>(
        '/wishlist/$productId',
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> moveToCart(int productId) async {
    try {
      await _dio.post<void>(
        '/wishlist/$productId/move-to-cart',
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
