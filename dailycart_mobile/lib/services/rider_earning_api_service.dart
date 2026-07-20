import 'package:dio/dio.dart';

import '../models/rider_earning_model.dart';
import '../networking/api_client.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class RiderEarningApiService with AuthenticatedApiMixin {
  RiderEarningApiService({Dio? dio, SecureStorageHelper? storage})
      : _dio = dio ?? ApiClient.shared.dio,
        _storage = storage ?? SecureStorageHelper();

  final Dio _dio;
  final SecureStorageHelper _storage;

  @override
  SecureStorageHelper get storage => _storage;

  Future<RiderEarningModel> getRiderEarnings() async {
    try {
      final response = await _dio.get<dynamic>(
        '/rider/earnings',
        options: await authOptions(),
      );
      return RiderEarningModel.fromJson(
        ApiListParser.extractObject(response.data),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
