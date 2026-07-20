import 'firebase_notification_service.dart';
import '../routes/app_router.dart';
import '../routes/app_routes.dart';
import 'package:go_router/go_router.dart';

class NotificationService {
  static Future<void> initialize() async {
    await FirebaseNotificationService().initialize(
      onOrderNotificationOpened: (orderId) {
        final context = rootNavigatorKey.currentContext;
        if (context != null) {
          GoRouter.of(context).go('${AppRoutes.orderDetails}/$orderId');
        }
      },
    );
  }
}
