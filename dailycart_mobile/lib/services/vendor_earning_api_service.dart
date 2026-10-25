import 'package:dio/dio.dart';

import '../models/vendor_earning_model.dart';
import '../networking/api_client.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class VendorEarningApiService with AuthenticatedApiMixin {
  VendorEarningApiService({Dio? dio, SecureStorageHelper? storage})
    : _dio = dio ?? ApiClient.shared.dio,
      _storage = storage ?? SecureStorageHelper();

  final Dio _dio;
  final SecureStorageHelper _storage;

  @override
  SecureStorageHelper get storage => _storage;

  Future<VendorEarningModel> getVendorEarnings({
    DateTime? from,
    DateTime? to,
  }) async {
    try {
      final response = await _dio.get<dynamic>(
        '/vendor/earnings',
        queryParameters: {
          if (from != null) 'from': _date(from),
          if (to != null) 'to': _date(to),
        },
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

  static String _date(DateTime value) =>
      '${value.year.toString().padLeft(4, '0')}-${value.month.toString().padLeft(2, '0')}-${value.day.toString().padLeft(2, '0')}';
}
