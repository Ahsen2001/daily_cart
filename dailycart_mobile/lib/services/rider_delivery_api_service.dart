import 'package:dio/dio.dart';

import '../models/delivery_model.dart';
import '../networking/api_client.dart';
import '../networking/api_response.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class RiderDeliveryApiService with AuthenticatedApiMixin {
  RiderDeliveryApiService({Dio? dio, SecureStorageHelper? storage})
      : _dio = dio ?? ApiClient.shared.dio,
        _storage = storage ?? SecureStorageHelper();

  final Dio _dio;
  final SecureStorageHelper _storage;

  @override
  SecureStorageHelper get storage => _storage;

  Future<List<DeliveryModel>> getAssignedDeliveries({String? status}) async {
    return (await getAssignedDeliveriesPage(status: status)).items;
  }

  Future<ApiPage<DeliveryModel>> getAssignedDeliveriesPage({
    String? status,
  }) async {
    try {
      final response = await _dio.get<dynamic>(
        '/rider/deliveries',
        queryParameters: {if (status != null && status != 'all') 'status': status},
        options: await authOptions(),
      );
      return ApiPage<DeliveryModel>.fromJson(
        response.data,
        key: 'deliveries',
        decoder: DeliveryModel.fromJson,
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<DeliveryModel> getDeliveryDetails(int deliveryId) async {
    try {
      final response = await _dio.get<dynamic>(
        '/rider/deliveries/$deliveryId',
        options: await authOptions(),
      );
      return DeliveryModel.fromJson(ApiListParser.extractObject(response.data));
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<DeliveryModel> updateDeliveryStatus({
    required int deliveryId,
    required String status,
    Map<String, dynamic>? data,
  }) async {
    try {
      final response = await _dio.patch<dynamic>(
        '/rider/deliveries/$deliveryId/status',
        data: {'status': status, ...?data},
        options: await authOptions(),
      );
      return DeliveryModel.fromJson(ApiListParser.extractObject(response.data));
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<DeliveryModel> markPickedUp(int deliveryId) {
    return updateDeliveryStatus(deliveryId: deliveryId, status: 'picked_up');
  }

  Future<DeliveryModel> markOnTheWay(int deliveryId) {
    return updateDeliveryStatus(deliveryId: deliveryId, status: 'on_the_way');
  }

  Future<DeliveryModel> markFailed(int deliveryId, String reason) {
    return updateDeliveryStatus(
      deliveryId: deliveryId,
      status: 'failed',
      data: {'failed_reason': reason},
    );
  }

  Future<DeliveryModel> markDelivered({
    required int deliveryId,
    required String proofImagePath,
    String note = '',
  }) async {
    try {
      final response = await _dio.patch<dynamic>(
        '/rider/deliveries/$deliveryId/status',
        data: await ApiClient.shared.multipart(
          fields: {
            'status': 'delivered',
            'note': note,
          },
          files: [
            ApiUploadFile(field: 'proof_image', path: proofImagePath),
          ],
        ),
        options: await authOptions(),
      );
      return DeliveryModel.fromJson(ApiListParser.extractObject(response.data));
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
