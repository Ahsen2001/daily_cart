import 'package:dio/dio.dart';

import '../config/app_config.dart';
import '../models/vendor_product_model.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class VendorProductApiService with AuthenticatedApiMixin {
  VendorProductApiService({Dio? dio, SecureStorageHelper? storage})
      : _dio = dio ??
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
      final files = <MultipartFile>[];
      for (final path in imagePaths) {
        files.add(await MultipartFile.fromFile(path));
      }
      final response = await _dio.post<dynamic>(
        '/vendor/products/$productId/images',
        data: FormData.fromMap({'images[]': files}),
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
}
