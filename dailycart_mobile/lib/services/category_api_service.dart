import 'package:dio/dio.dart';

import '../models/category_model.dart';
import '../networking/api_client.dart';
import '../networking/api_exception.dart';
import '../networking/api_response.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';

class CategoryApiService {
  CategoryApiService({Dio? dio})
      : _dio = dio ?? ApiClient.shared.dio;

  final Dio _dio;

  Future<List<CategoryModel>> getCategories() async {
    try {
      final response = await _dio.get<dynamic>('/categories');
      return ApiPage<CategoryModel>.fromJson(
        response.data,
        key: 'categories',
        decoder: _parseCategory,
      ).items;
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  CategoryModel _parseCategory(Map<String, dynamic> json) {
    if (json['id'] == null ||
        json['name'] == null ||
        json['name'].toString().trim().isEmpty) {
      throw ApiException.parsing('A category is missing its id or name.');
    }
    return CategoryModel.fromJson(json);
  }
}
