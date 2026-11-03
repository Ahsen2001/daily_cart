import 'package:dio/dio.dart';

import '../models/rider_location_model.dart';
import '../models/rider_location_queue_point.dart';
import '../networking/api_client.dart';
import '../utils/secure_storage_helper.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class RiderLocationApiService with AuthenticatedApiMixin {
  RiderLocationApiService({Dio? dio, SecureStorageHelper? storage})
    : _dio = dio ?? ApiClient.shared.dio,
      _storage = storage ?? SecureStorageHelper();

  final Dio _dio;
  final SecureStorageHelper _storage;

  @override
  SecureStorageHelper get storage => _storage;

  Future<Map<String, dynamic>?> activeDelivery() async {
    try {
      final response = await _dio.get<Map<String, dynamic>>(
        '/rider/tracking/active',
        options: await authOptions(),
      );
      final data = response.data;
      return data?['active'] == true && data?['delivery'] is Map
          ? Map<String, dynamic>.from(data!['delivery'] as Map)
          : null;
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<String> startTracking(int deliveryId) async {
    try {
      final response = await _dio.post<Map<String, dynamic>>(
        '/rider/deliveries/$deliveryId/tracking/start',
        options: await authOptions(),
      );
      final token = response.data?['tracking_token']?.toString();
      if (token == null || token.isEmpty) {
        throw const ApiException(
          'The server did not create a tracking session.',
        );
      }
      return token;
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> updateRiderLocation(
    RiderLocationModel location, {
    required int deliveryId,
    required String trackingToken,
    double? accuracy,
    double? altitude,
    double? speed,
    double? heading,
    DateTime? recordedAt,
  }) async {
    try {
      await _dio.post<void>(
        '/rider/deliveries/$deliveryId/tracking/locations',
        data: {
          ...location.toJson(),
          'tracking_token': trackingToken,
          'accuracy': accuracy,
          'altitude': altitude,
          'speed': speed,
          'heading': heading,
          'recorded_at': recordedAt?.toUtc().toIso8601String(),
        },
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<RiderLocationBatchResult> sendLocationBatch(
    int deliveryId,
    String trackingToken,
    List<RiderLocationQueuePoint> points,
  ) async {
    try {
      final response = await _dio.post<Map<String, dynamic>>(
        '/rider/deliveries/$deliveryId/location-updates',
        data: {
          'tracking_token': trackingToken,
          'points': points.map((point) => point.toJson()).toList(),
        },
        options: await authOptions(),
      );
      return RiderLocationBatchResult.fromJson(response.data ?? const {});
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> stopTracking(int deliveryId) async {
    try {
      await _dio.post<void>(
        '/rider/deliveries/$deliveryId/tracking/stop',
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}

class RiderLocationBatchResult {
  const RiderLocationBatchResult({
    required this.acceptedPointUuids,
    required this.duplicatePointUuids,
    required this.rejectedPointUuids,
  });

  final Set<String> acceptedPointUuids;
  final Set<String> duplicatePointUuids;
  final Set<String> rejectedPointUuids;

  Set<String> get acknowledgedPointUuids => {
    ...acceptedPointUuids,
    ...duplicatePointUuids,
    ...rejectedPointUuids,
  };

  factory RiderLocationBatchResult.fromJson(Map<String, dynamic> json) {
    Set<String> strings(Object? value) => value is List
        ? value.map((item) => item.toString()).toSet()
        : <String>{};
    final rejected = json['rejected_points'];
    return RiderLocationBatchResult(
      acceptedPointUuids: strings(json['accepted_point_uuids']),
      duplicatePointUuids: strings(json['duplicate_point_uuids']),
      rejectedPointUuids: rejected is List
          ? rejected
                .whereType<Map>()
                .map((item) => item['point_uuid']?.toString())
                .whereType<String>()
                .toSet()
          : const {},
    );
  }
}
