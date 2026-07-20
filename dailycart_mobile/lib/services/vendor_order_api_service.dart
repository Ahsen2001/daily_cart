import 'package:dio/dio.dart';

import '../models/vendor_order_model.dart';
import '../networking/api_client.dart';
import '../networking/api_response.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class VendorOrderApiService with AuthenticatedApiMixin {
  VendorOrderApiService({Dio? dio, SecureStorageHelper? storage})
      : _dio = dio ?? ApiClient.shared.dio,
        _storage = storage ?? SecureStorageHelper();

  final Dio _dio;
  final SecureStorageHelper _storage;

  @override
  SecureStorageHelper get storage => _storage;

  Future<List<VendorOrderModel>> getVendorOrders({String? status}) async {
    return (await getVendorOrdersPage(status: status)).items;
  }

  Future<ApiPage<VendorOrderModel>> getVendorOrdersPage({
    String? status,
  }) async {
    try {
      final response = await _dio.get<dynamic>(
        '/vendor/orders',
        queryParameters: {if (status != null && status != 'all') 'status': status},
        options: await authOptions(),
      );
      return ApiPage<VendorOrderModel>.fromJson(
        response.data,
        key: 'orders',
        decoder: VendorOrderModel.fromJson,
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<VendorOrderModel> getVendorOrderDetails(int orderId) async {
    try {
      final response = await _dio.get<dynamic>(
        '/vendor/orders/$orderId',
        options: await authOptions(),
      );
      return VendorOrderModel.fromJson(
        ApiListParser.extractObject(response.data),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<VendorOrderModel> confirmOrder(int orderId) {
    return _updateStatus(orderId, '/confirm');
  }

  Future<VendorOrderModel> markOrderPacked(int orderId) {
    return _updateStatus(orderId, '/packed');
  }

  Future<VendorOrderModel> cancelOrder(int orderId, String reason) {
    return _updateStatus(orderId, '/cancel', data: {'reason': reason});
  }

  Future<VendorOrderModel> _updateStatus(
    int orderId,
    String action, {
    Map<String, dynamic>? data,
  }) async {
    try {
      final response = await _dio.patch<dynamic>(
        '/vendor/orders/$orderId$action',
        data: data,
        options: await authOptions(),
      );
      return VendorOrderModel.fromJson(
        ApiListParser.extractObject(response.data),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
