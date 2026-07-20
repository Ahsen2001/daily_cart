import 'package:dio/dio.dart';

import '../models/user_model.dart';
import '../models/user_role.dart';
import '../networking/api_client.dart';
import '../networking/api_exception.dart';
import '../networking/api_response.dart';

export '../networking/api_exception.dart' show ApiException;

class AuthApiService {
  AuthApiService({Dio? dio})
    : _dio = dio ?? ApiClient.shared.dio;

  final Dio _dio;

  Future<AuthResponse> login({
    required String email,
    required String password,
  }) async {
    try {
      final response = await _dio.post<Map<String, dynamic>>(
        '/login',
        data: {
          'email': email,
          'password': password,
          'device_name': 'dailycart_mobile',
        },
      );

      return AuthResponse.fromJson(
        ApiResponseParser.requireMap(response.data),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<AuthResponse> register({
    required String name,
    required String email,
    required String phone,
    required String password,
    required String passwordConfirmation,
    required UserRole role,
    Map<String, dynamic> roleData = const {},
  }) async {
    try {
      final response = await _dio.post<Map<String, dynamic>>(
        '/register/${role.name}',
        data: {
          'name': name,
          'email': email,
          'phone': phone,
          'password': password,
          'password_confirmation': passwordConfirmation,
          'device_name': 'dailycart_${role.name}',
          ...roleData,
        },
      );

      return AuthResponse.fromJson(
        ApiResponseParser.requireMap(response.data),
      );
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<void> logout(String token) async {
    try {
      await _dio.post<void>('/logout', options: _authOptions(token));
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<String> forgotPassword(String email) async {
    try {
      final response = await _dio.post<Map<String, dynamic>>(
        '/password/forgot',
        data: {'email': email},
      );

      return _messageFrom(response.data) ??
          'If an account exists, a password reset code has been sent.';
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<String> resetPassword({
    required String email,
    required String code,
    required String password,
    required String passwordConfirmation,
  }) async {
    try {
      final response = await _dio.post<Map<String, dynamic>>(
        '/password/reset',
        data: {
          'email': email,
          'code': code,
          'password': password,
          'password_confirmation': passwordConfirmation,
        },
      );

      return _messageFrom(response.data) ?? 'Password reset successfully.';
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<String> sendVerificationCode({
    required String token,
    required VerificationChannel channel,
  }) async {
    try {
      final response = await _dio.post<Map<String, dynamic>>(
        channel.sendPath,
        options: _authOptions(token),
      );

      return _messageFrom(response.data) ?? 'Verification code sent.';
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<UserModel> verifyCode({
    required String token,
    required String code,
    required VerificationChannel channel,
  }) async {
    try {
      final response = await _dio.post<Map<String, dynamic>>(
        channel.verifyPath,
        data: {'code': code},
        options: _authOptions(token),
      );
      final data = ApiResponseParser.requireMap(response.data);
      final userJson = data['user'];

      if (userJson is! Map<String, dynamic>) {
        throw const ApiException('Invalid verification response from server.');
      }

      return UserModel.fromJson(userJson);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<UserModel> getProfile(String token) async {
    try {
      final response = await _dio.get<Map<String, dynamic>>(
        '/profile',
        options: _authOptions(token),
      );

      final data = ApiResponseParser.requireMap(response.data);
      final userJson = data['user'];
      if (userJson is! Map<String, dynamic>) {
        throw const ApiException('Invalid profile response from server.');
      }

      return UserModel.fromJson(userJson);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<UserModel> refreshUser(String token) => getProfile(token);

  Options _authOptions(String token) {
    return Options(headers: {'Authorization': 'Bearer $token'});
  }

  static String? _messageFrom(Map<String, dynamic>? data) {
    final message = data?['message'];
    return message is String && message.isNotEmpty ? message : null;
  }
}

enum VerificationChannel {
  email,
  phone;

  String get sendPath => switch (this) {
    VerificationChannel.email => '/email/verification-otp',
    VerificationChannel.phone => '/phone/verification-otp',
  };

  String get verifyPath => '$sendPath/verify';
}

class AuthResponse {
  const AuthResponse({
    required this.success,
    required this.message,
    required this.requiresVerification,
    required this.requiresApproval,
    this.token,
    this.expiresAt,
    this.user,
  });

  final bool success;
  final String message;
  final bool requiresVerification;
  final bool requiresApproval;
  final String? token;
  final DateTime? expiresAt;
  final UserModel? user;

  factory AuthResponse.fromJson(Map<String, dynamic> json) {
    final userJson = json['user'];
    final user = userJson is Map<String, dynamic>
        ? UserModel.fromJson(userJson)
        : null;
    final token = (json['access_token'] ?? json['token'])?.toString();

    return AuthResponse(
      success: json['success'] == true || (token != null && user != null),
      message: (json['message'] ?? '').toString(),
      requiresVerification:
          json['requires_verification'] == true ||
          user?.requiresVerification == true,
      requiresApproval:
          json['requires_approval'] == true ||
          user?.isPendingApproval == true,
      token: token,
      expiresAt: DateTime.tryParse(json['expires_at']?.toString() ?? ''),
      user: user,
    );
  }
}
