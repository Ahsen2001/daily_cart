class ApiListParser {
  static List<Map<String, dynamic>> extractList(
    Object? responseData, {
    String? key,
  }) {
    final data = responseData;

    if (data is List) {
      return data.whereType<Map<String, dynamic>>().toList(growable: false);
    }

    if (data is Map<String, dynamic>) {
      if (key != null && data[key] is List) {
        return (data[key] as List)
            .whereType<Map<String, dynamic>>()
            .toList(growable: false);
      }

      final keyedValue = key == null ? null : data[key];
      if (keyedValue is Map<String, dynamic> && keyedValue['data'] is List) {
        return (keyedValue['data'] as List)
            .whereType<Map<String, dynamic>>()
            .toList(growable: false);
      }

      if (data['data'] is List) {
        return (data['data'] as List)
            .whereType<Map<String, dynamic>>()
            .toList(growable: false);
      }

      if (data['data'] is Map<String, dynamic> &&
          (data['data'] as Map<String, dynamic>)['data'] is List) {
        return ((data['data'] as Map<String, dynamic>)['data'] as List)
            .whereType<Map<String, dynamic>>()
            .toList(growable: false);
      }
    }

    return const [];
  }

  static Map<String, dynamic> extractObject(Object? responseData) {
    final data = responseData;
    if (data is Map<String, dynamic>) {
      if (data['data'] is Map<String, dynamic>) {
        return data['data'] as Map<String, dynamic>;
      }
      if (data['product'] is Map<String, dynamic>) {
        return data['product'] as Map<String, dynamic>;
      }
      return data;
    }
    return const {};
  }
}
