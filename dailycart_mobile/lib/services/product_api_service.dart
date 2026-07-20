import 'package:dio/dio.dart';

import '../models/product_model.dart';
import '../networking/api_client.dart';
import '../networking/api_exception.dart';
import '../networking/api_response.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';

class ProductApiService {
  ProductApiService({Dio? dio})
    : _dio = dio ?? ApiClient.shared.dio;

  final Dio _dio;

  Future<List<ProductModel>> getFeaturedProducts() {
    return _getProductList('/products/featured');
  }

  Future<List<ProductModel>> getBestSellingProducts() {
    return _getProductList('/products/best-selling');
  }

  Future<List<ProductModel>> getNewArrivals() {
    return _getProductList('/products/new-arrivals');
  }

  Future<List<ProductModel>> getFlashDeals() {
    return _getProductList('/products/flash-deals');
  }

  Future<List<ProductModel>> getRecommendedProducts() {
    return _getProductList('/products/recommended');
  }

  Future<List<ProductModel>> getProducts({
    int? categoryId,
    double? minPrice,
    double? maxPrice,
    double? rating,
    bool? available,
    String? brand,
    String? sort,
    String? query,
  }) async {
    // The backend should enforce approved/active products, but these query
    // parameters make the mobile intent explicit as well.
    final params = <String, dynamic>{
      'status': 'active',
      'approval_status': 'approved',
      'category_id': ?categoryId,
      'min_price': ?minPrice,
      'max_price': ?maxPrice,
      'rating': ?rating,
      if (available != null) 'available': available ? 1 : 0,
      if (brand != null && brand.isNotEmpty) 'brand': brand,
      if (sort != null && sort.isNotEmpty) 'sort': sort,
      if (query != null && query.isNotEmpty) 'search': query,
    };

    return _getProductList('/products', queryParameters: params);
  }

  Future<ProductModel> getProductDetails(int productId) async {
    try {
      final response = await _dio.get<dynamic>('/products/$productId');
      return _parseProduct(
        ApiResponseParser.requireObject(
          ApiResponseParser.requireMap(response.data),
          key: 'product',
        ),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<List<ProductModel>> _getProductList(
    String path, {
    Map<String, dynamic>? queryParameters,
  }) async {
    try {
      final response = await _dio.get<dynamic>(
        path,
        queryParameters: queryParameters,
      );
      // Keep customer screens clean by hiding inactive, rejected, or pending
      // products even if an API response accidentally includes them.
      return ApiPage<ProductModel>.fromJson(
        response.data,
        key: 'products',
        decoder: _parseProduct,
      )
          .items
          .where((product) => product.isVisibleForCustomer)
          .toList(growable: false);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  ProductModel _parseProduct(Map<String, dynamic> json) {
    if (json['id'] == null ||
        json['name'] == null ||
        json['name'].toString().trim().isEmpty ||
        json['price'] == null) {
      throw ApiException.parsing(
        'A product is missing its id, name, or price.',
      );
    }
    return ProductModel.fromJson(json);
  }
}
