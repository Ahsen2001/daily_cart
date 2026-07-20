import 'package:dio/dio.dart';

import '../models/vendor_product_model.dart';
import '../networking/api_client.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class VendorProductApiService with AuthenticatedApiMixin {
  VendorProductApiService({Dio? dio, SecureStorageHelper? storage})
      : _dio = dio ?? ApiClient.shared.dio,
        _storage = storage ?? SecureStorageHelper();

  final Dio _dio;
  final SecureStorageHelper _storage;

  @override
  SecureStorageHelper get storage => _storage;

  Future<List<VendorProductModel>> getVendorProducts({
    String? status,
  }) async {
    try {
      final response = await _dio.get<dynamic>(
        '/vendor/products',
        queryParameters: {if (status != null && status != 'all') 'status': status},
        options: await authOptions(),
      );
      return ApiListParser.extractList(response.data, key: 'products')
          .map(VendorProductModel.fromJson)
          .toList(growable: false);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<VendorProductModel> getProductDetails(int productId) async {
    try {
      final response = await _dio.get<dynamic>(
        '/vendor/products/$productId',
        options: await authOptions(),
      );
      return VendorProductModel.fromJson(
        ApiListParser.extractObject(response.data),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<VendorProductModel> addProduct(VendorProductModel product) async {
    try {
      final response = await _dio.post<dynamic>(
        '/vendor/products',
        data: product.toJson(),
        options: await authOptions(),
      );
      return VendorProductModel.fromJson(
        ApiListParser.extractObject(response.data),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<VendorProductModel> updateProduct(VendorProductModel product) async {
    try {
      final response = await _dio.put<dynamic>(
        '/vendor/products/${product.id}',
        data: product.toJson(),
        options: await authOptions(),
      );
      return VendorProductModel.fromJson(
        ApiListParser.extractObject(response.data),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> deleteProduct(int productId) async {
    try {
      await _dio.delete<void>(
        '/vendor/products/$productId',
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<VendorProductModel> uploadProductImages({
    required int productId,
    required List<String> imagePaths,
  }) async {
    try {
      final response = await _dio.post<dynamic>(
        '/vendor/products/$productId/images',
        data: await ApiClient.shared.multipart(
          files: [
            for (final path in imagePaths)
              ApiUploadFile(field: 'images[]', path: path),
          ],
        ),
        options: await authOptions(),
      );
      return VendorProductModel.fromJson(
        ApiListParser.extractObject(response.data),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<VendorProductModel> updateInventory({
    required int productId,
    required int stockQuantity,
    DateTime? expiryDate,
  }) async {
    try {
      final response = await _dio.patch<dynamic>(
        '/vendor/products/$productId/inventory',
        data: {
          'stock_quantity': stockQuantity,
          if (expiryDate != null) 'expiry_date': expiryDate.toIso8601String(),
        },
        options: await authOptions(),
      );
      return VendorProductModel.fromJson(
        ApiListParser.extractObject(response.data),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<VendorProductModel> addVariant({
    required int productId,
    required String name,
    required double price,
    required int stockQuantity,
    String? sku,
  }) async {
    try {
      final response = await _dio.post<dynamic>(
        '/vendor/products/$productId/variants',
        data: {
          'name': name,
          'price': price,
          'stock_quantity': stockQuantity,
          if (sku != null && sku.isNotEmpty) 'sku': sku,
        },
        options: await authOptions(),
      );
      return VendorProductModel.fromJson(
        ApiListParser.extractObject(response.data, key: 'product'),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<VendorProductModel> deleteVariant({
    required int productId,
    required int variantId,
  }) async {
    try {
      final response = await _dio.delete<dynamic>(
        '/vendor/products/$productId/variants/$variantId',
        options: await authOptions(),
      );
      return VendorProductModel.fromJson(
        ApiListParser.extractObject(response.data, key: 'product'),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<VendorProductModel> deleteImage({
    required int productId,
    required int imageId,
  }) async {
    try {
      final response = await _dio.delete<dynamic>(
        '/vendor/products/$productId/images/$imageId',
        options: await authOptions(),
      );
      return VendorProductModel.fromJson(
        ApiListParser.extractObject(response.data, key: 'product'),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
