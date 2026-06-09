import 'package:dio/dio.dart';

import '../config/app_config.dart';
import '../models/product_model.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';

class SearchApiService {
  SearchApiService({Dio? dio})
      : _dio = dio ??
            Dio(
              BaseOptions(
                baseUrl: AppConfig.apiBaseUrl,
                connectTimeout: const Duration(seconds: 20),
                receiveTimeout: const Duration(seconds: 20),
                headers: const {'Accept': 'application/json'},
              ),
            );

  final Dio _dio;

  Future<List<ProductModel>> searchProducts(String query) async {
    try {
      final response = await _dio.get<dynamic>(
        '/products/search',
        queryParameters: {
          'q': query,
          'status': 'active',
          'approval_status': 'approved',
          // Search should cover product name, brand, category, SKU, and barcode.
          'fields': 'name,brand,category,sku,barcode',
        },
      );

      return ApiListParser.extractList(response.data, key: 'products')
          .map(ProductModel.fromJson)
          .where((product) => product.isVisibleForCustomer)
          .toList(growable: false);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
