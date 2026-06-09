import 'package:dio/dio.dart';

import '../config/app_config.dart';
import '../models/vendor_earning_model.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class VendorEarningApiService with AuthenticatedApiMixin {
  VendorEarningApiService({Dio? dio, SecureStorageHelper? storage})
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

  Future<VendorEarningModel> getVendorEarnings() async {
    try {
      final response = await _dio.get<dynamic>(
        '/vendor/earnings',
        options: await authOptions(),
      );
      return VendorEarningModel.fromJson(
        ApiListParser.extractObject(response.data),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<VendorEarningModel> getVendorEarningDetails() {
    return getVendorEarnings();
  }
}
