import 'dart:async';

import 'package:dio/dio.dart';

import '../config/app_config.dart';
import '../models/user_model.dart';
import '../models/user_role.dart';

class AuthApiService {
  AuthApiService({Dio? dio})
    : _dio =
          dio ??
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
          );

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

      return AuthResponse.fromJson(response.data ?? {});
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

      return AuthResponse.fromJson(response.data ?? {});
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
      final data = response.data ?? {};
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

      final data = response.data ?? {};
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

class ApiException implements Exception {
  const ApiException(this.message, {this.statusCode});

  static Future<void> Function()? _unauthorizedHandler;

  final String message;
  final int? statusCode;

  bool get isUnauthorized => statusCode == 401;

  static void setUnauthorizedHandler(Future<void> Function()? handler) {
    _unauthorizedHandler = handler;
  }

  factory ApiException.fromDio(DioException error) {
    if (error.type == DioExceptionType.connectionTimeout ||
        error.type == DioExceptionType.receiveTimeout ||
        error.type == DioExceptionType.sendTimeout ||
        error.type == DioExceptionType.connectionError) {
      return const ApiException('Network error. Please check your connection.');
    }

    final statusCode = error.response?.statusCode;
    if (statusCode == 401) {
      final handler = _unauthorizedHandler;
      if (handler != null) {
        unawaited(handler());
      }
      return const ApiException(
        'Your session has expired. Please sign in again.',
        statusCode: 401,
      );
    }

    final data = error.response?.data;
    if (data is Map<String, dynamic>) {
      final validationMessage = _validationMessage(data['errors']);
      if (validationMessage != null) {
        return ApiException(validationMessage, statusCode: statusCode);
      }

      final message = data['message'];
      if (message is String && message.isNotEmpty) {
        return ApiException(message, statusCode: statusCode);
      }
    }

    if (statusCode == 500) {
      return const ApiException(
        'Server error. Please try again later.',
        statusCode: 500,
      );
    }

    return ApiException(
      'Something went wrong. Please try again.',
      statusCode: statusCode,
    );
  }

  static String? _validationMessage(Object? errors) {
    if (errors is Map<String, dynamic>) {
      for (final value in errors.values) {
        if (value is List && value.isNotEmpty) {
          return value.first.toString();
        }
        if (value is String && value.isNotEmpty) {
          return value;
        }
      }
    }
    return null;
  }

  @override
  String toString() => message;
}
