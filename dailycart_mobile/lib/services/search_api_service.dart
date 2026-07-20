import 'package:dio/dio.dart';

import '../models/product_model.dart';
import '../networking/api_client.dart';
import '../networking/api_exception.dart';
import '../networking/api_response.dart';
import 'auth_api_service.dart';

class SearchApiService {
  SearchApiService({Dio? dio})
      : _dio = dio ?? ApiClient.shared.dio;

  final Dio _dio;

  Future<List<ProductModel>> searchProducts(String query) async {
    try {
      final response = await _dio.get<dynamic>(
        '/products',
        queryParameters: {
          'search': query,
        },
      );

      return ApiPage<ProductModel>.fromJson(
        response.data,
        key: 'products',
        decoder: (json) {
          if (json['id'] == null ||
              json['name'] == null ||
              json['price'] == null) {
            throw ApiException.parsing('A search result is malformed.');
          }
          return ProductModel.fromJson(json);
        },
      )
          .items
          .where((product) => product.isVisibleForCustomer)
          .toList(growable: false);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
