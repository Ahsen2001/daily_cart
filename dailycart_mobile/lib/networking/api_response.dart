import 'api_exception.dart';

typedef JsonMap = Map<String, dynamic>;
typedef JsonDecoder<T> = T Function(JsonMap json);

class ApiEnvelope<T> {
  const ApiEnvelope({
    required this.data,
    required this.success,
    this.message,
    this.requestId,
  });

  final T data;
  final bool success;
  final String? message;
  final String? requestId;

  factory ApiEnvelope.fromJson(
    Object? source, {
    required JsonDecoder<T> decoder,
    String? dataKey,
    String? requestId,
  }) {
    final json = ApiResponseParser.requireMap(source, requestId: requestId);
    final payload = dataKey == null
        ? ApiResponseParser.requireMap(json['data'], requestId: requestId)
        : ApiResponseParser.requireObject(
            json,
            key: dataKey,
            requestId: requestId,
          );

    return ApiEnvelope(
      data: ApiResponseParser.decode(payload, decoder, requestId: requestId),
      success: json['success'] is bool ? json['success'] as bool : true,
      message: json['message']?.toString(),
      requestId: requestId,
    );
  }
}

class PaginationMeta {
  const PaginationMeta({
    required this.currentPage,
    required this.perPage,
    required this.total,
    required this.totalPages,
  });

  final int currentPage;
  final int perPage;
  final int total;
  final int totalPages;

  factory PaginationMeta.fromJson(
    Object? source, {
    String? requestId,
  }) {
    final json = ApiResponseParser.requireMap(source, requestId: requestId);
    return PaginationMeta(
      currentPage: _requiredInt(
        json,
        const ['current_page', 'page'],
        requestId,
      ),
      perPage: _requiredInt(json, const ['per_page'], requestId),
      total: _requiredInt(json, const ['total'], requestId),
      totalPages: _requiredInt(
        json,
        const ['total_pages', 'last_page'],
        requestId,
      ),
    );
  }

  static int _requiredInt(
    JsonMap json,
    List<String> keys,
    String? requestId,
  ) {
    for (final key in keys) {
      final value = json[key];
      if (value is int) {
        return value;
      }
      final parsed = int.tryParse(value?.toString() ?? '');
      if (parsed != null) {
        return parsed;
      }
    }
    throw ApiException.parsing(
      'The pagination response is missing ${keys.first}.',
      requestId: requestId,
    );
  }
}

class ApiPage<T> {
  const ApiPage({
    required this.items,
    this.pagination,
    this.message,
    this.requestId,
  });

  final List<T> items;
  final PaginationMeta? pagination;
  final String? message;
  final String? requestId;

  factory ApiPage.fromJson(
    Object? source, {
    required String key,
    required JsonDecoder<T> decoder,
    String? requestId,
  }) {
    final root = ApiResponseParser.requireMap(source, requestId: requestId);
    final container = root[key];
    late final List<JsonMap> rows;
    Object? paginationSource = root['pagination'];

    if (container is List) {
      rows = ApiResponseParser.requireMapList(
        container,
        context: key,
        requestId: requestId,
      );
    } else if (container is Map<String, dynamic>) {
      rows = ApiResponseParser.requireMapList(
        container['data'],
        context: '$key.data',
        requestId: requestId,
      );
      paginationSource ??= container;
    } else {
      throw ApiException.parsing(
        'The server response is missing the "$key" list.',
        requestId: requestId,
      );
    }

    return ApiPage(
      items: rows
          .map((row) => ApiResponseParser.decode(
                row,
                decoder,
                requestId: requestId,
              ))
          .toList(growable: false),
      pagination: paginationSource == null
          ? null
          : PaginationMeta.fromJson(
              paginationSource,
              requestId: requestId,
            ),
      message: root['message']?.toString(),
      requestId: requestId,
    );
  }
}

class ApiResponseParser {
  const ApiResponseParser._();

  static JsonMap requireMap(Object? source, {String? requestId}) {
    if (source is Map<String, dynamic>) {
      return source;
    }
    if (source is Map) {
      return source.map((key, value) => MapEntry(key.toString(), value));
    }
    throw ApiException.parsing(
      'The server returned a malformed object.',
      requestId: requestId,
    );
  }

  static JsonMap requireObject(
    JsonMap source, {
    required String key,
    String? requestId,
  }) {
    final value = source[key];
    if (value is Map) {
      return requireMap(value, requestId: requestId);
    }
    throw ApiException.parsing(
      'The server response is missing the "$key" object.',
      requestId: requestId,
    );
  }

  static List<JsonMap> requireMapList(
    Object? source, {
    required String context,
    String? requestId,
  }) {
    if (source is! List) {
      throw ApiException.parsing(
        'The server response is missing the "$context" list.',
        requestId: requestId,
      );
    }

    final result = <JsonMap>[];
    for (var index = 0; index < source.length; index++) {
      final value = source[index];
      if (value is! Map) {
        throw ApiException.parsing(
          'The "$context" list contains a malformed item at index $index.',
          requestId: requestId,
        );
      }
      result.add(requireMap(value, requestId: requestId));
    }
    return List.unmodifiable(result);
  }

  static T decode<T>(
    JsonMap source,
    JsonDecoder<T> decoder, {
    String? requestId,
  }) {
    try {
      return decoder(source);
    } on ApiException {
      rethrow;
    } catch (_) {
      throw ApiException.parsing(
        'The server returned an invalid data object.',
        requestId: requestId,
      );
    }
  }
}
