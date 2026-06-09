import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';

import 'notification_api_service.dart';

@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  // Firebase initializes in main.dart. Keep this handler light and side-effect safe.
}

class FirebaseNotificationService {
  FirebaseNotificationService({
    FirebaseMessaging? firebaseMessaging,
    FlutterLocalNotificationsPlugin? localNotifications,
    NotificationApiService? apiService,
  })  : _firebaseMessaging = firebaseMessaging ?? FirebaseMessaging.instance,
        _localNotifications =
            localNotifications ?? FlutterLocalNotificationsPlugin(),
        _apiService = apiService ?? NotificationApiService();

  final FirebaseMessaging _firebaseMessaging;
  final FlutterLocalNotificationsPlugin _localNotifications;
  final NotificationApiService _apiService;

  Future<void> initialize({
    void Function(int orderId)? onOrderNotificationOpened,
  }) async {
    await _firebaseMessaging.requestPermission();
    FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);

    const androidSettings = AndroidInitializationSettings('@mipmap/ic_launcher');
    const iosSettings = DarwinInitializationSettings();
    const settings = InitializationSettings(
      android: androidSettings,
      iOS: iosSettings,
    );
    await _localNotifications.initialize(settings);

    try {
      await saveDeviceToken();
    } catch (_) {
      // The user may not be logged in yet. Token sync can be retried later.
    }

    FirebaseMessaging.onMessage.listen(_showForegroundNotification);
    FirebaseMessaging.onMessageOpenedApp.listen((message) {
      final orderId = _orderIdFrom(message);
      if (orderId != null) {
        onOrderNotificationOpened?.call(orderId);
      }
    });

    final initialMessage = await _firebaseMessaging.getInitialMessage();
    final initialOrderId =
        initialMessage == null ? null : _orderIdFrom(initialMessage);
    if (initialOrderId != null) {
      onOrderNotificationOpened?.call(initialOrderId);
    }
  }

  Future<void> saveDeviceToken() async {
    final token = await _firebaseMessaging.getToken();
    if (token != null && token.isNotEmpty) {
      await _apiService.saveDeviceToken(token);
    }
  }

  Future<void> _showForegroundNotification(RemoteMessage message) async {
    final notification = message.notification;
    if (notification == null) {
      return;
    }

    const details = NotificationDetails(
      android: AndroidNotificationDetails(
        'dailycart_customer',
        'DailyCart Customer Notifications',
        channelDescription: 'Order, payment, promotion, and system updates.',
        importance: Importance.high,
        priority: Priority.high,
      ),
      iOS: DarwinNotificationDetails(),
    );

    await _localNotifications.show(
      notification.hashCode,
      notification.title,
      notification.body,
      details,
    );
  }

  int? _orderIdFrom(RemoteMessage message) {
    final value = message.data['order_id'];
    return int.tryParse(value?.toString() ?? '');
  }
}
