import 'package:dio/dio.dart';

import '../config/app_config.dart';
import '../models/category_model.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';

class CategoryApiService {
  CategoryApiService({Dio? dio})
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

  Future<List<CategoryModel>> getCategories() async {
    try {
      final response = await _dio.get<dynamic>('/categories');
      return ApiListParser.extractList(response.data, key: 'categories')
          .map(CategoryModel.fromJson)
          .toList(growable: false);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
