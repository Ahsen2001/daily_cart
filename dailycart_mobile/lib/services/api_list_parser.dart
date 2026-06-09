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
        return extractObject(data['data'] as Map<String, dynamic>);
      }
      if (data['product'] is Map<String, dynamic>) {
        return data['product'] as Map<String, dynamic>;
      }
      if (data['address'] is Map<String, dynamic>) {
        return data['address'] as Map<String, dynamic>;
      }
      if (data['order'] is Map<String, dynamic>) {
        return data['order'] as Map<String, dynamic>;
      }
      if (data['payment'] is Map<String, dynamic>) {
        return data['payment'] as Map<String, dynamic>;
      }
      if (data['review'] is Map<String, dynamic>) {
        return data['review'] as Map<String, dynamic>;
      }
      if (data['ticket'] is Map<String, dynamic>) {
        return data['ticket'] as Map<String, dynamic>;
      }
      if (data['coupon'] is Map<String, dynamic>) {
        return data['coupon'] as Map<String, dynamic>;
      }
      if (data['promotion'] is Map<String, dynamic>) {
        return data['promotion'] as Map<String, dynamic>;
      }
      if (data['profile'] is Map<String, dynamic>) {
        return data['profile'] as Map<String, dynamic>;
      }
      if (data['dashboard'] is Map<String, dynamic>) {
        return data['dashboard'] as Map<String, dynamic>;
      }
      if (data['vendor'] is Map<String, dynamic>) {
        return data['vendor'] as Map<String, dynamic>;
      }
      if (data['earning'] is Map<String, dynamic>) {
        return data['earning'] as Map<String, dynamic>;
      }
      return data;
    }
    return const {};
  }
}
