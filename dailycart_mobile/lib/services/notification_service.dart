import 'firebase_notification_service.dart';
import '../routes/app_router.dart';
import '../routes/app_routes.dart';

class NotificationService {
  static Future<void> initialize() async {
    await FirebaseNotificationService().initialize(
      onOrderNotificationOpened: (orderId) {
        appRouter.go('${AppRoutes.orderDetails}/$orderId');
      },
    );
  }
}
