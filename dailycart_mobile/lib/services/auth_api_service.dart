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
  }) async {
    try {
      final response = await _dio.post<Map<String, dynamic>>(
        '/register',
        data: {
          'name': name,
          'email': email,
          'phone': phone,
          'password': password,
          'password_confirmation': passwordConfirmation,
          'role': role.name,
          'device_name': 'dailycart_mobile',
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
        '/forgot-password',
        data: {'email': email},
      );

      return _messageFrom(response.data) ??
          'Password reset instructions were sent to your email.';
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<String> verifyOtp({
    required String otp,
    String? email,
    String? phone,
    String purpose = 'phone_verification',
  }) async {
    try {
      final response = await _dio.post<Map<String, dynamic>>(
        '/otp/verify',
        data: {
          'otp': otp,
          'email': ?email,
          'phone': ?phone,
          'purpose': purpose,
        },
      );

      return _messageFrom(response.data) ?? 'OTP verified successfully.';
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<UserModel> getUser(String token) async {
    try {
      final response = await _dio.get<Map<String, dynamic>>(
        '/user',
        options: _authOptions(token),
      );

      final data = response.data ?? {};
      final userJson = data['user'] is Map<String, dynamic>
          ? data['user'] as Map<String, dynamic>
          : data;

      return UserModel.fromJson(userJson);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  Future<UserModel> refreshUser(String token) {
    return getUser(token);
  }

  Options _authOptions(String token) {
    return Options(headers: {'Authorization': 'Bearer $token'});
  }

  static String? _messageFrom(Map<String, dynamic>? data) {
    final message = data?['message'];
    return message is String && message.isNotEmpty ? message : null;
  }
}

class AuthResponse {
  const AuthResponse({
    required this.success,
    required this.message,
    this.token,
    this.user,
  });

  final bool success;
  final String message;
  final String? token;
  final UserModel? user;

  factory AuthResponse.fromJson(Map<String, dynamic> json) {
    final userJson = json['user'];

    return AuthResponse(
      success: json['success'] == true,
      message: (json['message'] ?? '').toString(),
      token: (json['token'] ?? json['access_token'])?.toString(),
      user: userJson is Map<String, dynamic>
          ? UserModel.fromJson(userJson)
          : null,
    );
  }

  bool get requiresApproval {
    final messageLower = message.toLowerCase();
    return user?.isPendingApproval == true ||
        messageLower.contains('pending approval') ||
        messageLower.contains('waiting for admin approval');
  }
}

class ApiException implements Exception {
  const ApiException(this.message);

  final String message;

  factory ApiException.fromDio(DioException error) {
    if (error.type == DioExceptionType.connectionTimeout ||
        error.type == DioExceptionType.receiveTimeout ||
        error.type == DioExceptionType.sendTimeout ||
        error.type == DioExceptionType.connectionError) {
      return const ApiException('Network error. Please check your connection.');
    }

    final data = error.response?.data;
    if (data is Map<String, dynamic>) {
      final validationMessage = _validationMessage(data['errors']);
      if (validationMessage != null) {
        return ApiException(validationMessage);
      }

      final message = data['message'];
      if (message is String && message.isNotEmpty) {
        return ApiException(message);
      }
    }

    if (error.response?.statusCode == 401) {
      return const ApiException('Invalid email or password.');
    }

    if (error.response?.statusCode == 500) {
      return const ApiException('Server error. Please try again later.');
    }

    return const ApiException('Something went wrong. Please try again.');
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
