import '../networking/api_exception.dart';
import '../networking/api_response.dart';

class ApiListParser {
  const ApiListParser._();

  static List<Map<String, dynamic>> extractList(
    Object? responseData, {
    String? key,
  }) {
    if (responseData is List) {
      return ApiResponseParser.requireMapList(
        responseData,
        context: key ?? 'data',
      );
    }

    final root = ApiResponseParser.requireMap(responseData);
    Object? value = key == null ? root['data'] : root[key];
    if (value is Map<String, dynamic>) {
      value = value['data'];
    }
    return ApiResponseParser.requireMapList(
      value,
      context: key ?? 'data',
    );
  }

  static Map<String, dynamic> extractObject(
    Object? responseData, {
    String? key,
  }) {
    final root = ApiResponseParser.requireMap(responseData);
    if (key != null) {
      return ApiResponseParser.requireObject(root, key: key);
    }

    const objectKeys = [
      'data',
      'product',
      'address',
      'order',
      'payment',
      'review',
      'ticket',
      'coupon',
      'promotion',
      'profile',
      'user',
      'dashboard',
      'summary',
      'vendor',
      'wallet',
      'earning',
      'rider',
      'delivery',
      'earnings',
    ];
    for (final candidate in objectKeys) {
      final value = root[candidate];
      if (value is Map) {
        return ApiResponseParser.requireMap(value);
      }
    }

    if (root.isNotEmpty) {
      return root;
    }
    throw ApiException.parsing('The server returned an empty object.');
  }
}
