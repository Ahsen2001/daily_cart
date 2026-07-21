import 'package:dio/dio.dart';

import '../utils/secure_storage_helper.dart';
import 'auth_api_service.dart';

mixin AuthenticatedApiMixin {
  SecureStorageHelper get storage;

  Future<void> ensureAuthenticated() async {
    final token = await storage.getToken();
    if (token == null || token.isEmpty) {
      throw const ApiException('Unauthorized user. Please login again.');
    }
  }

  @Deprecated('Authorization is attached by the shared ApiClient interceptor.')
  Future<Options> authOptions() async {
    await ensureAuthenticated();
    return Options();
  }
}
