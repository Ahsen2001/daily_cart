import 'package:dio/dio.dart';

enum ApiErrorKind {
  connectivity,
  timeout,
  unauthorized,
  forbidden,
  validation,
  notFound,
  conflict,
  rateLimited,
  server,
  parsing,
  unknown,
}

class ApiException implements Exception {
  const ApiException(
    this.message, {
    this.statusCode,
    this.kind = ApiErrorKind.unknown,
    this.requestId,
    this.validationErrors = const {},
  });

  final String message;
  final int? statusCode;
  final ApiErrorKind kind;
  final String? requestId;
  final Map<String, List<String>> validationErrors;

  bool get isUnauthorized => kind == ApiErrorKind.unauthorized;
  bool get isConnectivityFailure => kind == ApiErrorKind.connectivity;
  bool get canRetry =>
      kind == ApiErrorKind.connectivity ||
      kind == ApiErrorKind.timeout ||
      kind == ApiErrorKind.server;

  factory ApiException.parsing(String message, {String? requestId}) {
    return ApiException(
      message,
      kind: ApiErrorKind.parsing,
      requestId: requestId,
    );
  }

  factory ApiException.fromDio(DioException error) {
    final requestId = error.requestOptions.headers['X-Request-ID']?.toString();
    if (error.error is ApiException) {
      final exception = error.error! as ApiException;
      return ApiException(
        exception.message,
        statusCode: exception.statusCode,
        kind: exception.kind,
        requestId: exception.requestId ?? requestId,
        validationErrors: exception.validationErrors,
      );
    }

    if (error.type == DioExceptionType.connectionTimeout ||
        error.type == DioExceptionType.receiveTimeout ||
        error.type == DioExceptionType.sendTimeout) {
      return ApiException(
        'The server took too long to respond. Please try again.',
        kind: ApiErrorKind.timeout,
        requestId: requestId,
      );
    }
    if (error.type == DioExceptionType.connectionError) {
      return ApiException(
        'No internet connection. Check your connection and try again.',
        kind: ApiErrorKind.connectivity,
        requestId: requestId,
      );
    }

    final statusCode = error.response?.statusCode;
    final responseData = error.response?.data;
    final message = _messageFrom(responseData);
    final validationErrors = _validationErrorsFrom(responseData);
    final kind = switch (statusCode) {
      401 => ApiErrorKind.unauthorized,
      403 => ApiErrorKind.forbidden,
      404 => ApiErrorKind.notFound,
      409 => ApiErrorKind.conflict,
      422 => ApiErrorKind.validation,
      429 => ApiErrorKind.rateLimited,
      >= 500 => ApiErrorKind.server,
      _ => ApiErrorKind.unknown,
    };

    return ApiException(
      message ?? _defaultMessage(kind),
      statusCode: statusCode,
      kind: kind,
      requestId: requestId,
      validationErrors: validationErrors,
    );
  }

  static String? _messageFrom(Object? responseData) {
    if (responseData is Map) {
      final message = responseData['message'];
      if (message is String && message.trim().isNotEmpty) {
        return message;
      }
    }
    return null;
  }

  static Map<String, List<String>> _validationErrorsFrom(
    Object? responseData,
  ) {
    if (responseData is! Map || responseData['errors'] is! Map) {
      return const {};
    }

    final result = <String, List<String>>{};
    (responseData['errors'] as Map).forEach((key, value) {
      if (value is List) {
        result[key.toString()] =
            value.map((item) => item.toString()).toList(growable: false);
      } else if (value != null) {
        result[key.toString()] = [value.toString()];
      }
    });
    return result;
  }

  static String _defaultMessage(ApiErrorKind kind) {
    return switch (kind) {
      ApiErrorKind.unauthorized =>
        'Your session has expired. Please sign in again.',
      ApiErrorKind.forbidden =>
        'You do not have permission to perform this action.',
      ApiErrorKind.notFound => 'The requested information was not found.',
      ApiErrorKind.conflict =>
        'The request conflicts with the current server state.',
      ApiErrorKind.validation => 'Please check the submitted information.',
      ApiErrorKind.rateLimited =>
        'Too many requests. Please wait and try again.',
      ApiErrorKind.server => 'Server error. Please try again later.',
      ApiErrorKind.connectivity =>
        'No internet connection. Check your connection and try again.',
      ApiErrorKind.timeout =>
        'The server took too long to respond. Please try again.',
      ApiErrorKind.parsing =>
        'The server returned information the app could not read.',
      ApiErrorKind.unknown => 'Something went wrong. Please try again.',
    };
  }

  @override
  String toString() => message;
}
