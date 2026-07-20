import 'package:dio/dio.dart';

import '../models/customer_finance_model.dart';
import '../models/order_model.dart';
import '../models/subscription_model.dart';
import '../networking/api_client.dart';
import '../networking/api_response.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class CustomerExtendedApiService with AuthenticatedApiMixin {
  CustomerExtendedApiService({
    Dio? dio,
    SecureStorageHelper? storage,
  })  : _dio = dio ?? ApiClient.shared.dio,
        _storage = storage ?? SecureStorageHelper();

  final Dio _dio;
  final SecureStorageHelper _storage;

  @override
  SecureStorageHelper get storage => _storage;

  Future<WalletModel> getWallet() async {
    try {
      final response = await _dio.get<dynamic>(
        '/wallet',
        options: await authOptions(),
      );
      final root = ApiResponseParser.requireMap(response.data);
      final wallet = ApiResponseParser.requireObject(root, key: 'wallet');
      final transactions = ApiResponseParser.requireMapList(
        root['transactions'],
        context: 'transactions',
      );
      return WalletModel(
        balance: _toDouble(wallet['balance']),
        currency: (wallet['currency'] ?? 'LKR').toString(),
        transactions:
            transactions.map(WalletTransactionModel.fromJson).toList(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<List<RefundModel>> getRefunds() async {
    try {
      final response = await _dio.get<dynamic>(
        '/refunds',
        options: await authOptions(),
      );
      return ApiListParser.extractList(response.data, key: 'refunds')
          .map(RefundModel.fromJson)
          .toList(growable: false);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<RefundModel> requestRefund({
    required int orderId,
    required double amount,
    required String reason,
  }) async {
    try {
      final response = await _dio.post<dynamic>(
        '/orders/$orderId/refunds',
        data: {'amount': amount, 'reason': reason},
        options: await authOptions(),
      );
      return RefundModel.fromJson(
        ApiListParser.extractObject(response.data, key: 'refund'),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<List<SubscriptionModel>> getSubscriptions() async {
    try {
      final response = await _dio.get<dynamic>(
        '/subscriptions',
        options: await authOptions(),
      );
      return ApiListParser.extractList(response.data, key: 'subscriptions')
          .map(SubscriptionModel.fromJson)
          .toList(growable: false);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<SubscriptionModel> createSubscription(
    Map<String, dynamic> data,
  ) async {
    try {
      final response = await _dio.post<dynamic>(
        '/subscriptions',
        data: data,
        options: await authOptions(),
      );
      return SubscriptionModel.fromJson(
        ApiListParser.extractObject(response.data, key: 'subscription'),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<SubscriptionModel> changeSubscriptionStatus(
    int subscriptionId,
    String action,
  ) async {
    try {
      final response = await _dio.patch<dynamic>(
        '/subscriptions/$subscriptionId/$action',
        options: await authOptions(),
      );
      return SubscriptionModel.fromJson(
        ApiListParser.extractObject(response.data, key: 'subscription'),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<List<OrderModel>> getScheduledOrders() async {
    try {
      final response = await _dio.get<dynamic>(
        '/scheduled-orders',
        options: await authOptions(),
      );
      return ApiListParser.extractList(response.data, key: 'orders')
          .map(OrderModel.fromJson)
          .toList(growable: false);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<OrderModel> cancelScheduledOrder(int orderId) async {
    try {
      final response = await _dio.patch<dynamic>(
        '/scheduled-orders/$orderId/cancel',
        options: await authOptions(),
      );
      return OrderModel.fromJson(
        ApiListParser.extractObject(response.data, key: 'order'),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<List<PolicyLinkModel>> getPolicies() async {
    try {
      final response = await _dio.get<dynamic>('/policies');
      return ApiListParser.extractList(response.data, key: 'policies')
          .map(PolicyLinkModel.fromJson)
          .toList(growable: false);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  static double _toDouble(Object? value) {
    if (value is num) return value.toDouble();
    return double.tryParse(value?.toString() ?? '') ?? 0;
  }
}
