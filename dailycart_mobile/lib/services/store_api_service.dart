import 'package:dio/dio.dart';

import '../models/promotion_model.dart';
import '../models/store_model.dart';
import '../networking/api_client.dart';
import '../networking/api_exception.dart';
import '../networking/api_response.dart';
import '../utils/secure_storage_helper.dart';
import 'authenticated_api_mixin.dart';

class StoreApiService with AuthenticatedApiMixin {
  StoreApiService({Dio? dio, SecureStorageHelper? storage})
    : _dio = dio ?? ApiClient.shared.dio,
      _storage = storage ?? SecureStorageHelper();

  final Dio _dio;
  final SecureStorageHelper _storage;

  @override
  SecureStorageHelper get storage => _storage;

  Future<StoreDiscovery> getHomeDiscovery() async {
    try {
      final response = await _dio.get<dynamic>('/stores/home');
      final root = ApiResponseParser.requireMap(response.data);
      return StoreDiscovery(
        offers: _maps(root['offers']).map(PromotionModel.fromJson).toList(),
        featuredStores: _maps(
          root['featured_stores'],
        ).map(StoreModel.fromJson).toList(),
        popularStores: _maps(
          root['popular_stores'],
        ).map(StoreModel.fromJson).toList(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<List<StoreModel>> getStores({
    String? search,
    bool featured = false,
  }) async {
    try {
      final response = await _dio.get<dynamic>(
        '/stores',
        queryParameters: {
          if (search != null && search.trim().isNotEmpty)
            'search': search.trim(),
          if (featured) 'featured': 1,
        },
      );
      final root = ApiResponseParser.requireMap(response.data);
      return _maps(
        root['stores'],
      ).map(StoreModel.fromJson).toList(growable: false);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<StoreModel> getStore(String slug) async {
    try {
      final response = await _dio.get<dynamic>('/stores/$slug');
      final root = ApiResponseParser.requireMap(response.data);
      return StoreModel.fromJson(
        ApiResponseParser.requireObject(root, key: 'store'),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> setFollowing(String slug, bool follow) async {
    try {
      final options = await authOptions();
      if (follow) {
        await _dio.post<dynamic>('/stores/$slug/follow', options: options);
      } else {
        await _dio.delete<dynamic>('/stores/$slug/follow', options: options);
      }
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  List<Map<String, dynamic>> _maps(Object? value) {
    if (value is! List) return const [];
    return value
        .whereType<Map>()
        .map((item) => Map<String, dynamic>.from(item))
        .toList(growable: false);
  }
}

class StoreDiscovery {
  const StoreDiscovery({
    required this.offers,
    required this.featuredStores,
    required this.popularStores,
  });
  final List<PromotionModel> offers;
  final List<StoreModel> featuredStores;
  final List<StoreModel> popularStores;
}
