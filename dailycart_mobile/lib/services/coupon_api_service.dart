import 'package:dio/dio.dart';

import '../config/app_config.dart';
import '../models/coupon_model.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class CouponApiService with AuthenticatedApiMixin {
  CouponApiService({
    Dio? dio,
    SecureStorageHelper? storage,
  })  : _dio = dio ??
            Dio(
              BaseOptions(
                baseUrl: AppConfig.apiBaseUrl,
                connectTimeout: const Duration(seconds: 20),
                receiveTimeout: const Duration(seconds: 20),
                headers: const {
                  'Accept': 'application/json',
                  'Content-Type': 'application/json',
                },
              ),
            ),
        _storage = storage ?? SecureStorageHelper();

  final Dio _dio;
  final SecureStorageHelper _storage;

  @override
  SecureStorageHelper get storage => _storage;

  Future<CouponModel> applyCoupon(String code) async {
    try {
      final response = await _dio.post<Map<String, dynamic>>(
        '/coupons/apply',
        data: {'code': code},
        options: await authOptions(),
      );

      final data = response.data ?? {};
      return CouponModel.fromJson({
        ...data,
        'code': data['code'] ?? code,
      });
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> removeCoupon() async {
    try {
      await _dio.delete<void>(
        '/coupons/remove',
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<List<CouponModel>> getAvailableCoupons() async {
    try {
      final response = await _dio.get<dynamic>(
        '/coupons/available',
        options: await authOptions(),
      );
      return ApiListParser.extractList(response.data, key: 'coupons')
          .map(CouponModel.fromJson)
          .toList(growable: false);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<CouponModel> validateCoupon(String code) async {
    try {
      final response = await _dio.post<dynamic>(
        '/coupons/validate',
        data: {'code': code},
        options: await authOptions(),
      );
      return CouponModel.fromJson({
        ...ApiListParser.extractObject(response.data),
        'code': code,
      });
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
