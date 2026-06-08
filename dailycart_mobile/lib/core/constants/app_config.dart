import 'package:flutter_dotenv/flutter_dotenv.dart';

class AppConfig {
  static String get apiBaseUrl =>
      dotenv.env['API_BASE_URL'] ?? 'http://10.0.2.2:8000/api';

  static String get payHereReturnUrl =>
      dotenv.env['PAYHERE_RETURN_URL'] ?? 'http://10.0.2.2:8000/customer/payments';

  static String get googleMapsApiKey => dotenv.env['GOOGLE_MAPS_API_KEY'] ?? '';
}
