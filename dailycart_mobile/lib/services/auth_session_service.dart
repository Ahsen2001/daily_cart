import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../models/user_role.dart';

class AuthSessionService {
  static const _secureStorage = FlutterSecureStorage();
  static const _tokenKey = 'dailycart_auth_token';
  static const _roleKey = 'dailycart_user_role';

  Future<bool> get isLoggedIn async {
    final token = await _secureStorage.read(key: _tokenKey);
    return token != null && token.isNotEmpty;
  }

  Future<UserRole> get role async {
    final prefs = await SharedPreferences.getInstance();
    return UserRole.fromName(prefs.getString(_roleKey));
  }

  Future<void> saveSession({
    required String token,
    required UserRole role,
  }) async {
    final prefs = await SharedPreferences.getInstance();
    await _secureStorage.write(key: _tokenKey, value: token);
    await prefs.setString(_roleKey, role.name);
  }

  Future<void> clearSession() async {
    final prefs = await SharedPreferences.getInstance();
    await _secureStorage.delete(key: _tokenKey);
    await prefs.remove(_roleKey);
  }
}
