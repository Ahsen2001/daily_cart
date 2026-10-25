import 'package:dio/dio.dart';

import '../models/vendor_store_model.dart';
import '../networking/api_client.dart';
import '../utils/secure_storage_helper.dart';
import 'api_list_parser.dart';
import 'auth_api_service.dart';
import 'authenticated_api_mixin.dart';

class VendorStoreApiService with AuthenticatedApiMixin {
  VendorStoreApiService({Dio? dio, SecureStorageHelper? storage})
    : _dio = dio ?? ApiClient.shared.dio,
      _storage = storage ?? SecureStorageHelper();

  final Dio _dio;
  final SecureStorageHelper _storage;
  @override
  SecureStorageHelper get storage => _storage;

  Future<VendorStoreModel> getStore() async {
    try {
      final response = await _dio.get<dynamic>(
        '/vendor/store',
        options: await authOptions(),
      );
      return VendorStoreModel.fromJson(
        ApiListParser.extractObject(response.data, key: 'store'),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<VendorStoreAnalyticsModel> getAnalytics() async {
    try {
      final response = await _dio.get<dynamic>(
        '/vendor/store/analytics',
        options: await authOptions(),
      );
      return VendorStoreAnalyticsModel.fromJson(
        ApiListParser.extractObject(response.data, key: 'analytics'),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<VendorStoreModel> updateStore(
    Map<String, dynamic> fields, {
    String? logoPath,
    String? coverPath,
  }) async {
    try {
      final response = await _dio.post<dynamic>(
        '/vendor/store',
        data: await ApiClient.shared.multipart(
          fields: fields,
          files: [
            if (logoPath != null) ApiUploadFile(field: 'logo', path: logoPath),
            if (coverPath != null)
              ApiUploadFile(field: 'cover_image', path: coverPath),
          ],
        ),
        options: await authOptions(),
      );
      return VendorStoreModel.fromJson(
        ApiListParser.extractObject(response.data, key: 'store'),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<VendorStoreBannerModel> uploadBanner(
    String imagePath, {
    String title = '',
    String linkUrl = '',
  }) async {
    try {
      final response = await _dio.post<dynamic>(
        '/vendor/store/banners',
        data: await ApiClient.shared.multipart(
          fields: {'title': title, if (linkUrl.isNotEmpty) 'link_url': linkUrl},
          files: [ApiUploadFile(field: 'image', path: imagePath)],
        ),
        options: await authOptions(),
      );
      return VendorStoreBannerModel.fromJson(
        ApiListParser.extractObject(response.data, key: 'banner'),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> deleteBanner(int id) async {
    try {
      await _dio.delete<void>(
        '/vendor/store/banners/$id',
        options: await authOptions(),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }
}
