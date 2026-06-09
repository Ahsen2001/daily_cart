import 'package:flutter_dotenv/flutter_dotenv.dart';

class AppConfig {
  static String get apiBaseUrl {
    return dotenv.env['API_BASE_URL'] ?? 'https://dailycart.lk/api';
  }

  static String get testingApiBaseUrl {
    return dotenv.env['TESTING_API_BASE_URL'] ??
        'https://your-laravel-cloud-url.laravel.cloud/api';
  }

  static String get googleMapsApiKey {
    return dotenv.env['GOOGLE_MAPS_API_KEY'] ?? '';
  }
}
