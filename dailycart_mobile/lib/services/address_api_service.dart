import 'package:dio/dio.dart';

import '../config/app_config.dart';
import '../models/address_model.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class AddressApiService with AuthenticatedApiMixin {
  AddressApiService({
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

  Future<List<AddressModel>> getAddresses() async {
    try {
      final response = await _dio.get<dynamic>(
        '/addresses',
        options: await authOptions(),
      );
      return ApiListParser.extractList(response.data, key: 'addresses')
          .map(AddressModel.fromJson)
          .toList(growable: false);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<AddressModel> addAddress(AddressModel address) async {
    try {
      final response = await _dio.post<dynamic>(
        '/addresses',
        data: address.toJson(),
        options: await authOptions(),
      );
      return AddressModel.fromJson(ApiListParser.extractObject(response.data));
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<AddressModel> updateAddress(AddressModel address) async {
    try {
      final response = await _dio.patch<dynamic>(
        '/addresses/${address.id}',
        data: address.toJson(),
        options: await authOptions(),
      );
      return AddressModel.fromJson(ApiListParser.extractObject(response.data));
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> deleteAddress(int addressId) async {
    try {
      await _dio.delete<void>(
        '/addresses/$addressId',
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> setDefaultAddress(int addressId) async {
    try {
      await _dio.patch<void>(
        '/addresses/$addressId/default',
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
