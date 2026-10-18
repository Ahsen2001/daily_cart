import 'package:flutter_dotenv/flutter_dotenv.dart';

class AppConfig {
  static String get apiBaseUrl {
    return dotenv.env['API_BASE_URL'] ?? 'https://dailycart.lk/api/v1';
  }

  static String get testingApiBaseUrl {
    return dotenv.env['TESTING_API_BASE_URL'] ??
        'https://your-laravel-cloud-url.laravel.cloud/api/v1';
  }

  static String get webBaseUrl {
    final configured = dotenv.env['WEB_BASE_URL']?.trim();
    if (configured != null && configured.isNotEmpty) {
      return configured.replaceFirst(RegExp(r'/$'), '');
    }
    return apiBaseUrl.replaceFirst(RegExp(r'/api/v1/?$'), '');
  }

  static String get googleMapsApiKey {
    return dotenv.env['GOOGLE_MAPS_API_KEY'] ?? '';
  }
}
