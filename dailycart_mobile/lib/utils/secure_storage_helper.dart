import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../models/user_model.dart';
import '../models/user_role.dart';

class SecureStorageHelper {
  SecureStorageHelper({FlutterSecureStorage? storage})
    : _storage = storage ?? const FlutterSecureStorage();

  final FlutterSecureStorage _storage;

  static const authTokenKey = 'auth_token';
  static const userIdKey = 'user_id';
  static const userRoleKey = 'user_role';
  static const userNameKey = 'user_name';
  static const userEmailKey = 'user_email';
  static const userPhoneKey = 'user_phone';
  static const userStatusKey = 'user_status';
  static const userApprovalStatusKey = 'user_approval_status';
  static const userEmailVerifiedKey = 'user_email_verified';
  static const userPhoneVerifiedKey = 'user_phone_verified';
  static const userApprovedKey = 'user_is_approved';
  static const tokenExpiresAtKey = 'auth_token_expires_at';
  static const trackingDeliveryIdKey = 'active_tracking_delivery_id';
  static const trackingTokenKey = 'active_tracking_token';
  static const trackingStatusKey = 'active_tracking_status';
  static const checkoutIdempotencyKey = 'checkout_idempotency_key';
  static const checkoutRequestFingerprintKey = 'checkout_request_fingerprint';

  Future<void> saveToken(String token) {
    return _storage.write(key: authTokenKey, value: token);
  }

  Future<String?> getToken() {
    return _storage.read(key: authTokenKey);
  }

  Future<void> saveTokenExpiration(DateTime? expiresAt) {
    return _storage.write(
      key: tokenExpiresAtKey,
      value: expiresAt?.toUtc().toIso8601String(),
    );
  }

  Future<DateTime?> getTokenExpiration() async {
    final value = await _storage.read(key: tokenExpiresAtKey);
    return value == null ? null : DateTime.tryParse(value);
  }

  Future<void> saveUser(UserModel user) async {
    await _storage.write(key: userIdKey, value: user.id.toString());
    await _storage.write(key: userRoleKey, value: user.role.name);
    await _storage.write(key: userNameKey, value: user.name);
    await _storage.write(key: userEmailKey, value: user.email);
    await _storage.write(key: userPhoneKey, value: user.phone);
    await _storage.write(key: userStatusKey, value: user.status ?? '');
    await _storage.write(
      key: userApprovalStatusKey,
      value: user.approvalStatus ?? '',
    );
    await _storage.write(
      key: userEmailVerifiedKey,
      value: user.isEmailVerified.toString(),
    );
    await _storage.write(
      key: userPhoneVerifiedKey,
      value: user.isPhoneVerified.toString(),
    );
    await _storage.write(
      key: userApprovedKey,
      value: user.isApproved.toString(),
    );
  }

  Future<UserModel?> getUser() async {
    return UserModel.fromStorageMap({
      'id': await _storage.read(key: userIdKey),
      'name': await _storage.read(key: userNameKey),
      'email': await _storage.read(key: userEmailKey),
      'phone': await _storage.read(key: userPhoneKey),
      'role': await _storage.read(key: userRoleKey),
      'status': await _storage.read(key: userStatusKey),
      'approval_status': await _storage.read(key: userApprovalStatusKey),
      'is_email_verified': await _storage.read(key: userEmailVerifiedKey),
      'is_phone_verified': await _storage.read(key: userPhoneVerifiedKey),
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
    DateTime? expiresAt,
  }) async {
    await saveToken(token);
    await saveTokenExpiration(expiresAt);
    await saveUser(user);
  }

  Future<void> clearAuthData() async {
    await _storage.delete(key: authTokenKey);
    await _storage.delete(key: tokenExpiresAtKey);
    await _storage.delete(key: userIdKey);
    await _storage.delete(key: userRoleKey);
    await _storage.delete(key: userNameKey);
    await _storage.delete(key: userEmailKey);
    await _storage.delete(key: userPhoneKey);
    await _storage.delete(key: userStatusKey);
    await _storage.delete(key: userApprovalStatusKey);
    await _storage.delete(key: userEmailVerifiedKey);
    await _storage.delete(key: userPhoneVerifiedKey);
    await _storage.delete(key: userApprovedKey);
    await clearCheckoutIdempotency();
    await clearTrackingSession();
  }

  Future<void> saveCheckoutIdempotency({
    required String key,
    required String requestFingerprint,
  }) async {
    await _storage.write(key: checkoutIdempotencyKey, value: key);
    await _storage.write(
      key: checkoutRequestFingerprintKey,
      value: requestFingerprint,
    );
  }

  Future<String?> getCheckoutIdempotencyKey() {
    return _storage.read(key: checkoutIdempotencyKey);
  }

  Future<String?> getCheckoutRequestFingerprint() {
    return _storage.read(key: checkoutRequestFingerprintKey);
  }

  Future<void> clearCheckoutIdempotency() async {
    await _storage.delete(key: checkoutIdempotencyKey);
    await _storage.delete(key: checkoutRequestFingerprintKey);
  }

  Future<void> saveTrackingSession(
    int deliveryId,
    String token, {
    String? status,
  }) async {
    await _storage.write(
      key: trackingDeliveryIdKey,
      value: deliveryId.toString(),
    );
    await _storage.write(key: trackingTokenKey, value: token);
    if (status != null) {
      await _storage.write(key: trackingStatusKey, value: status);
    }
  }

  Future<int?> getTrackingDeliveryId() async {
    return int.tryParse(await _storage.read(key: trackingDeliveryIdKey) ?? '');
  }

  Future<String?> getTrackingToken() {
    return _storage.read(key: trackingTokenKey);
  }

  Future<String?> getTrackingStatus() {
    return _storage.read(key: trackingStatusKey);
  }

  Future<void> clearTrackingSession() async {
    await _storage.delete(key: trackingDeliveryIdKey);
    await _storage.delete(key: trackingTokenKey);
    await _storage.delete(key: trackingStatusKey);
  }
}
