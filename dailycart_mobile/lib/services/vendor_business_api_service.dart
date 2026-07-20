import 'package:dio/dio.dart';

import '../models/vendor_business_model.dart';
import '../models/vendor_order_model.dart';
import '../networking/api_client.dart';
import '../networking/api_response.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class VendorBusinessApiService with AuthenticatedApiMixin {
  VendorBusinessApiService({Dio? dio, SecureStorageHelper? storage})
      : _dio = dio ?? ApiClient.shared.dio,
        _storage = storage ?? SecureStorageHelper();

  final Dio _dio;
  final SecureStorageHelper _storage;

  @override
  SecureStorageHelper get storage => _storage;

  Future<VendorWalletModel> getWallet() async {
    try {
      final response = await _dio.get<dynamic>(
        '/vendor/wallet',
        options: await authOptions(),
      );
      final root = ApiResponseParser.requireMap(response.data);
      final wallet = ApiResponseParser.requireObject(root, key: 'wallet');
      return VendorWalletModel(
        balance: _double(wallet['balance']),
        availableBalance: _double(wallet['available_balance']),
        pendingBalance: _double(wallet['pending_balance']),
        totalEarned: _double(wallet['total_earned']),
        totalWithdrawn: _double(wallet['total_withdrawn']),
        payouts: ApiResponseParser.requireMapList(
          root['payouts'],
          context: 'payouts',
        ).map(VendorPayoutModel.fromJson).toList(growable: false),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<VendorPayoutModel> requestPayout(Map<String, dynamic> data) async {
    try {
      final response = await _dio.post<dynamic>(
        '/vendor/payouts',
        data: data,
        options: await authOptions(),
      );
      return VendorPayoutModel.fromJson(
        ApiListParser.extractObject(response.data, key: 'payout'),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<List<VendorRefundModel>> getRefunds() async {
    return _getList(
      '/vendor/refunds',
      'refunds',
      VendorRefundModel.fromJson,
    );
  }

  Future<VendorRefundModel> respondToRefund(int id, String note) async {
    try {
      final response = await _dio.patch<dynamic>(
        '/vendor/refunds/$id/response',
        data: {'vendor_note': note},
        options: await authOptions(),
      );
      return VendorRefundModel.fromJson(
        ApiListParser.extractObject(response.data, key: 'refund'),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<List<VendorCouponModel>> getCoupons() async {
    return _getList('/vendor/coupons', 'coupons', VendorCouponModel.fromJson);
  }

  Future<VendorCouponModel> saveCoupon(
    Map<String, dynamic> data, {
    int? id,
  }) async {
    try {
      final response = id == null
          ? await _dio.post<dynamic>(
              '/vendor/coupons',
              data: data,
              options: await authOptions(),
            )
          : await _dio.patch<dynamic>(
              '/vendor/coupons/$id',
              data: data,
              options: await authOptions(),
            );
      return VendorCouponModel.fromJson(
        ApiListParser.extractObject(response.data, key: 'coupon'),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> deleteCoupon(int id) =>
      _delete('/vendor/coupons/$id');

  Future<List<VendorPromotionModel>> getPromotions() async {
    return _getList(
      '/vendor/promotions',
      'promotions',
      VendorPromotionModel.fromJson,
    );
  }

  Future<VendorPromotionModel> savePromotion(
    Map<String, dynamic> data, {
    int? id,
  }) async {
    try {
      final response = await _dio.post<dynamic>(
        id == null ? '/vendor/promotions' : '/vendor/promotions/$id',
        data: data,
        options: await authOptions(),
      );
      return VendorPromotionModel.fromJson(
        ApiListParser.extractObject(response.data, key: 'promotion'),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> deletePromotion(int id) =>
      _delete('/vendor/promotions/$id');

  Future<List<VendorSubscriptionModel>> getSubscriptions() async {
    return _getList(
      '/vendor/subscriptions',
      'subscriptions',
      VendorSubscriptionModel.fromJson,
    );
  }

  Future<List<VendorOrderModel>> getScheduledOrders() async {
    return _getList(
      '/vendor/scheduled-orders',
      'orders',
      VendorOrderModel.fromJson,
    );
  }

  Future<VendorReportModel> getReports() async {
    try {
      final response = await _dio.get<dynamic>(
        '/vendor/reports',
        options: await authOptions(),
      );
      return VendorReportModel.fromJson(
        ApiListParser.extractObject(response.data, key: 'report'),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<List<T>> _getList<T>(
    String path,
    String key,
    T Function(Map<String, dynamic>) decode,
  ) async {
    try {
      final response =
          await _dio.get<dynamic>(path, options: await authOptions());
      return ApiListParser.extractList(response.data, key: key)
          .map(decode)
          .toList(growable: false);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> _delete(String path) async {
    try {
      await _dio.delete<void>(path, options: await authOptions());
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  static double _double(Object? value) {
    if (value is num) return value.toDouble();
    return double.tryParse(value?.toString() ?? '') ?? 0;
  }
}
