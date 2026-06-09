import 'package:dio/dio.dart';

import '../utils/secure_storage_helper.dart';
import 'auth_api_service.dart';

mixin AuthenticatedApiMixin {
  SecureStorageHelper get storage;

  Future<Options> authOptions() async {
    final token = await storage.getToken();
    if (token == null || token.isEmpty) {
      throw const ApiException('Unauthorized user. Please login again.');
    }

    return Options(headers: {'Authorization': 'Bearer $token'});
  }
}
