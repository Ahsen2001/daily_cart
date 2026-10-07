import 'package:dio/dio.dart';

import '../models/rider_rating_model.dart';
import '../networking/api_client.dart';
import '../networking/api_response.dart';
import '../utils/secure_storage_helper.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class RiderRatingApiService with AuthenticatedApiMixin {
  RiderRatingApiService({Dio? dio, SecureStorageHelper? storage})
      : _dio = dio ?? ApiClient.shared.dio,
        _storage = storage ?? SecureStorageHelper();

  final Dio _dio;
  final SecureStorageHelper _storage;

  @override
  SecureStorageHelper get storage => _storage;

  Future<RiderRatingContext> customerRating(int orderId) async {
    try {
      final response = await _dio.get<dynamic>(
        '/orders/$orderId/rider-rating',
        options: await authOptions(),
      );
      final json = ApiResponseParser.requireMap(response.data);
      final rider = json['rider'] is Map
          ? ApiResponseParser.requireMap(json['rider'])
          : const <String, dynamic>{};
      final rating = json['rating'] is Map
          ? RiderRatingModel.fromJson(
              ApiResponseParser.requireMap(json['rating']),
            )
          : null;
      return RiderRatingContext(
        canRate: json['can_rate'] == true,
        riderName: (rider['name'] ?? 'your rider').toString(),
        rating: rating,
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> submit({
    required int orderId,
    required int rating,
    required String comment,
    required List<String> tags,
  }) async {
    try {
      await _dio.post<void>(
        '/orders/$orderId/rider-rating',
        data: {'rating': rating, 'comment': comment, 'tags': tags},
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<RiderRatingSummary> riderRatings() async {
    try {
      final response = await _dio.get<dynamic>(
        '/rider/ratings',
        options: await authOptions(),
      );
      final json = ApiResponseParser.requireMap(response.data);
      final stats = json['statistics'] is Map
          ? ApiResponseParser.requireMap(json['statistics'])
          : const <String, dynamic>{};
      final rows = json['ratings'] is List ? json['ratings'] as List : const [];
      return RiderRatingSummary(
        average: double.tryParse(
              (stats['average'] ?? stats['average_rating'] ?? 0).toString(),
            ) ??
            0,
        total: int.tryParse(
              (stats['total'] ?? stats['total_ratings'] ?? rows.length)
                  .toString(),
            ) ??
            rows.length,
        ratings: rows
            .whereType<Map>()
            .map((row) => RiderRatingModel.fromJson(
                  row.map((key, value) => MapEntry(key.toString(), value)),
                ))
            .toList(growable: false),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> report(int ratingId, String reason) async {
    try {
      await _dio.patch<void>(
        '/rider/ratings/$ratingId/report',
        data: {'reason': reason},
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
