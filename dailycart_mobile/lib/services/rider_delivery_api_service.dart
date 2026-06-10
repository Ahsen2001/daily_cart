import 'package:dio/dio.dart';

import '../config/app_config.dart';
import '../models/delivery_model.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class RiderDeliveryApiService with AuthenticatedApiMixin {
  RiderDeliveryApiService({Dio? dio, SecureStorageHelper? storage})
      : _dio = dio ??
            Dio(
              BaseOptions(
                baseUrl: AppConfig.apiBaseUrl,
                connectTimeout: const Duration(seconds: 20),
                receiveTimeout: const Duration(seconds: 20),
                headers: const {'Accept': 'application/json'},
              ),
            ),
        _storage = storage ?? SecureStorageHelper();

  final Dio _dio;
  final SecureStorageHelper _storage;

  @override
  SecureStorageHelper get storage => _storage;

  Future<List<DeliveryModel>> getAssignedDeliveries({String? status}) async {
    try {
      final response = await _dio.get<dynamic>(
        '/rider/deliveries',
        queryParameters: {if (status != null && status != 'all') 'status': status},
        options: await authOptions(),
      );
      return ApiListParser.extractList(response.data, key: 'deliveries')
          .map(DeliveryModel.fromJson)
          .toList(growable: false);
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
      data: {'reason': reason},
    );
  }

  Future<DeliveryModel> markDelivered({
    required int deliveryId,
    required String proofImagePath,
    String note = '',
  }) async {
    try {
      final response = await _dio.post<dynamic>(
        '/rider/deliveries/$deliveryId/delivered',
        data: FormData.fromMap({
          'status': 'delivered',
          'note': note,
          'delivered_at': DateTime.now().toIso8601String(),
          if (proofImagePath.isNotEmpty)
            'proof_image': await MultipartFile.fromFile(proofImagePath),
        }),
        options: await authOptions(),
      );
      return DeliveryModel.fromJson(ApiListParser.extractObject(response.data));
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
