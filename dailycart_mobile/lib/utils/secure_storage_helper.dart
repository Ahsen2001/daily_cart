import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../models/user_model.dart';
import '../models/user_role.dart';

class SecureStorageHelper {
  SecureStorageHelper({
    FlutterSecureStorage? storage,
  }) : _storage = storage ?? const FlutterSecureStorage();

  final FlutterSecureStorage _storage;

  static const authTokenKey = 'auth_token';
  static const userIdKey = 'user_id';
  static const userRoleKey = 'user_role';
  static const userNameKey = 'user_name';
  static const userEmailKey = 'user_email';
  static const userPhoneKey = 'user_phone';
  static const userStatusKey = 'user_status';
  static const userApprovedKey = 'user_is_approved';

  Future<void> saveToken(String token) {
    return _storage.write(key: authTokenKey, value: token);
  }

  Future<String?> getToken() {
    return _storage.read(key: authTokenKey);
  }

  Future<void> saveUser(UserModel user) async {
    await _storage.write(key: userIdKey, value: user.id.toString());
    await _storage.write(key: userRoleKey, value: user.role.name);
    await _storage.write(key: userNameKey, value: user.name);
    await _storage.write(key: userEmailKey, value: user.email);
    await _storage.write(key: userPhoneKey, value: user.phone);
    await _storage.write(key: userStatusKey, value: user.status ?? '');
    await _storage.write(key: userApprovedKey, value: user.isApproved.toString());
  }

  Future<UserModel?> getUser() async {
    return UserModel.fromStorageMap({
      'id': await _storage.read(key: userIdKey),
      'name': await _storage.read(key: userNameKey),
      'email': await _storage.read(key: userEmailKey),
      'phone': await _storage.read(key: userPhoneKey),
      'role': await _storage.read(key: userRoleKey),
      'status': await _storage.read(key: userStatusKey),
      'is_approved': await _storage.read(key: userApprovedKey),
    });
  }

  Future<UserRole?> getRole() async {
    final role = await _storage.read(key: userRoleKey);
    if (role == null) {
      return null;
    }
    return UserRole.fromName(role);
  }

  Future<void> saveAuthData({
    required String token,
    required UserModel user,
  }) async {
    await saveToken(token);
    await saveUser(user);
  }

  Future<void> clearAuthData() async {
    await _storage.delete(key: authTokenKey);
    await _storage.delete(key: userIdKey);
    await _storage.delete(key: userRoleKey);
    await _storage.delete(key: userNameKey);
    await _storage.delete(key: userEmailKey);
    await _storage.delete(key: userPhoneKey);
    await _storage.delete(key: userStatusKey);
    await _storage.delete(key: userApprovedKey);
  }
}
