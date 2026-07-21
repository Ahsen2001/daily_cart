import 'package:go_router/go_router.dart';

import '../config/app_identity.dart';
import '../models/notification_model.dart';
import '../routes/app_router.dart';
import 'firebase_notification_service.dart';

class NotificationService {
  NotificationService._();

  static final FirebaseNotificationService _firebase =
      FirebaseNotificationService();
  static String? _pendingDeepLink;

  static Future<void> initialize() {
    return _firebase.initialize(onDeepLinkOpened: openDeepLink);
  }

  static Future<void> syncDevice() => _firebase.syncDeviceToken();

  static Future<void> revokeDevice() => _firebase.revokeCurrentDevice();

  static Future<void> disableLocalDevice() => _firebase.disableLocalDevice();

  static Future<void> applyPreferences(NotificationPreferences preferences) {
    return _firebase.applyPreferences(preferences);
  }

  static void openDeepLink(String deepLink) {
    final safeLink = _safeDeepLink(deepLink);
    if (safeLink == null) {
      return;
    }

    final context = rootNavigatorKey.currentContext;
    if (context == null) {
      _pendingDeepLink = safeLink;
      return;
    }

    GoRouter.of(context).push(safeLink);
  }

  static String? takePendingDeepLink() {
    final value = _pendingDeepLink;
    _pendingDeepLink = null;
    return value;
  }

  static String? _safeDeepLink(String value) {
    final uri = Uri.tryParse(value);
    if (uri == null || uri.hasScheme || !value.startsWith('/')) {
      return null;
    }

    if (AppIdentity.isVendor) {
      return value.startsWith('/vendor-') ? value : null;
    }
    if (AppIdentity.isRider) {
      const allowed = [
        '/rider-',
        '/assigned-deliveries',
        '/delivery-details',
        '/delivery-proof',
      ];
      return allowed.any(value.startsWith) ? value : null;
    }

    const forbidden = [
      '/vendor-',
      '/rider-',
      '/assigned-deliveries',
      '/delivery-details',
      '/delivery-proof',
    ];
    return forbidden.any(value.startsWith) ? null : value;
  }
}
