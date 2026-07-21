import 'dart:async';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';

import '../config/app_config.dart';
import '../utils/secure_storage_helper.dart';
import 'api_exception.dart';

class ApiClient {
  ApiClient._({
    SecureStorageHelper? storage,
    Connectivity? connectivity,
  })  : _storage = storage ?? SecureStorageHelper(),
        _connectivity = connectivity ?? Connectivity() {
    dio = Dio(
      BaseOptions(
        baseUrl: AppConfig.apiBaseUrl,
        connectTimeout: const Duration(seconds: 20),
        sendTimeout: const Duration(seconds: 30),
        receiveTimeout: const Duration(seconds: 20),
        headers: const {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      ),
    );
    dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: _onRequest,
        onResponse: _onResponse,
        onError: _onError,
      ),
    );
  }

  static final ApiClient shared = ApiClient._();

  final SecureStorageHelper _storage;
  final Connectivity _connectivity;
  late final Dio dio;
  Future<void> Function()? _unauthorizedHandler;
  bool _handlingUnauthorized = false;
  int _requestSequence = 0;

  void setUnauthorizedHandler(Future<void> Function()? handler) {
    _unauthorizedHandler = handler;
  }

  Future<FormData> multipart({
    Map<String, dynamic> fields = const {},
    List<ApiUploadFile> files = const [],
  }) async {
    final data = FormData.fromMap(fields);
    for (final file in files) {
      data.files.add(
        MapEntry(
          file.field,
          await MultipartFile.fromFile(
            file.path,
            filename: file.filename,
          ),
        ),
      );
    }
    return data;
  }

  Future<void> _onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) async {
    // A platform connectivity probe is advisory; Dio remains authoritative for actual network calls.

    final token = await _storage.getToken();
    if (token != null && token.isNotEmpty) {
      options.headers['Authorization'] = 'Bearer $token';
    }

    final requestId =
        'dc-${DateTime.now().microsecondsSinceEpoch}-${_requestSequence++}';
    options.headers.putIfAbsent('X-Request-ID', () => requestId);
    options.extra['_started_at'] = DateTime.now();
    options.extra['_retry_count'] ??= 0;
    handler.next(options);
  }

  void _onResponse(
    Response<dynamic> response,
    ResponseInterceptorHandler handler,
  ) {
    _diagnose(
      response.requestOptions,
      statusCode: response.statusCode,
    );
    handler.next(response);
  }

  Future<void> _onError(
    DioException error,
    ErrorInterceptorHandler handler,
  ) async {
    var currentError = error;
    final options = error.requestOptions;
    final statusCode = error.response?.statusCode;

    if (statusCode == 401) {
      await _notifyUnauthorized();
    }

    if (_canRetry(error)) {
      final retryCount = options.extra['_retry_count'] as int? ?? 0;
      options.extra['_retry_count'] = retryCount + 1;
      await Future<void>.delayed(Duration(milliseconds: 300 * (retryCount + 1)));
      try {
        final response = await dio.fetch<dynamic>(options);
        handler.resolve(response);
        return;
      } on DioException catch (retryError) {
        currentError = retryError;
      }
    }

    _diagnose(
      options,
      statusCode: currentError.response?.statusCode,
      error: ApiException.fromDio(currentError),
    );
    handler.next(currentError);
  }

  bool _canRetry(DioException error) {
    final method = error.requestOptions.method.toUpperCase();
    if (!const {'GET', 'HEAD', 'OPTIONS'}.contains(method)) {
      return false;
    }

    final retryCount = error.requestOptions.extra['_retry_count'] as int? ?? 0;
    if (retryCount >= 2) {
      return false;
    }

    return error.type == DioExceptionType.connectionError ||
        error.type == DioExceptionType.connectionTimeout ||
        error.type == DioExceptionType.receiveTimeout ||
        const {502, 503, 504}.contains(error.response?.statusCode);
  }

  Future<void> _notifyUnauthorized() async {
    final callback = _unauthorizedHandler;
    if (callback == null || _handlingUnauthorized) {
      return;
    }

    _handlingUnauthorized = true;
    try {
      await callback();
    } finally {
      _handlingUnauthorized = false;
    }
  }

  void _diagnose(
    RequestOptions options, {
    int? statusCode,
    ApiException? error,
  }) {
    if (!kDebugMode) {
      return;
    }

    final startedAt = options.extra['_started_at'];
    final elapsed = startedAt is DateTime
        ? DateTime.now().difference(startedAt).inMilliseconds
        : null;
    final requestId = options.headers['X-Request-ID'];
    debugPrint(
      '[API] ${options.method} ${options.uri.path} '
      'status=${statusCode ?? '-'} request_id=$requestId '
      'duration_ms=${elapsed ?? '-'} retry=${options.extra['_retry_count'] ?? 0}'
      '${error == null ? '' : ' error=${error.kind.name}'}',
    );
  }
}

class ApiUploadFile {
  const ApiUploadFile({
    required this.field,
    required this.path,
    this.filename,
  });

  final String field;
  final String path;
  final String? filename;
}
