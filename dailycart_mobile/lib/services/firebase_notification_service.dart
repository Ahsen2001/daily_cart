import 'dart:math';

import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:package_info_plus/package_info_plus.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../config/app_identity.dart';
import '../models/notification_model.dart';
import 'notification_api_service.dart';

@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
}

class FirebaseNotificationService {
  FirebaseNotificationService({
    FirebaseMessaging? firebaseMessaging,
    FlutterLocalNotificationsPlugin? localNotifications,
    NotificationApiService? apiService,
  }) : _firebaseMessaging = firebaseMessaging ?? FirebaseMessaging.instance,
       _localNotifications =
           localNotifications ?? FlutterLocalNotificationsPlugin(),
       _apiService = apiService ?? NotificationApiService();

  final FirebaseMessaging _firebaseMessaging;
  final FlutterLocalNotificationsPlugin _localNotifications;
  final NotificationApiService _apiService;

  void Function(String deepLink)? _onDeepLinkOpened;

  String get _channelId => 'dailycart_${AppIdentity.flavor.name}';

  String get _channelName => '${AppIdentity.displayName} Notifications';

  String get _tokenStorageKey => 'firebase_token_${AppIdentity.flavor.name}';

  Future<void> initialize({
    required void Function(String deepLink) onDeepLinkOpened,
  }) async {
    _onDeepLinkOpened = onDeepLinkOpened;
    FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);

    await _firebaseMessaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );
    await _firebaseMessaging.setForegroundNotificationPresentationOptions(
      alert: true,
      badge: true,
      sound: true,
    );

    const androidSettings = AndroidInitializationSettings(
      '@mipmap/ic_launcher',
    );
    const iosSettings = DarwinInitializationSettings();
    const settings = InitializationSettings(
      android: androidSettings,
      iOS: iosSettings,
    );
    await _localNotifications.initialize(
      settings: settings,
      onDidReceiveNotificationResponse: (response) {
        final deepLink = response.payload;
        if (deepLink != null && deepLink.isNotEmpty) {
          _onDeepLinkOpened?.call(deepLink);
        }
      },
    );

    final android = _localNotifications
        .resolvePlatformSpecificImplementation<
          AndroidFlutterLocalNotificationsPlugin
        >();
    await android?.createNotificationChannel(
      AndroidNotificationChannel(
        _channelId,
        _channelName,
        description: 'Private account updates and public promotions.',
        importance: Importance.high,
      ),
    );

    _firebaseMessaging.onTokenRefresh.listen((token) async {
      try {
        await syncDeviceToken(token: token, isRefresh: true);
      } catch (_) {
        // Authentication or connectivity recovery will retry the current token.
      }
    });

    FirebaseMessaging.onMessage.listen(_showForegroundNotification);
    FirebaseMessaging.onMessageOpenedApp.listen(_openMessage);

    final initialMessage = await _firebaseMessaging.getInitialMessage();
    if (initialMessage != null) {
      _openMessage(initialMessage);
    }
  }

  Future<void> syncDeviceToken({String? token, bool isRefresh = false}) async {
    final currentToken = token ?? await _firebaseMessaging.getToken();
    if (currentToken == null || currentToken.isEmpty) {
      return;
    }

    final preferences = await SharedPreferences.getInstance();
    final oldToken = preferences.getString(_tokenStorageKey);
    final deviceId = await _deviceId(preferences);
    final package = await PackageInfo.fromPlatform();

    if (isRefresh || (oldToken != null && oldToken != currentToken)) {
      await _apiService.refreshDeviceToken(
        token: currentToken,
        oldToken: oldToken,
        deviceId: deviceId,
        platform: _platform,
        appVersion: '${package.version}+${package.buildNumber}',
      );
    } else {
      await _apiService.registerDeviceToken(
        token: currentToken,
        deviceId: deviceId,
        platform: _platform,
        appVersion: '${package.version}+${package.buildNumber}',
      );
    }

    await preferences.setString(_tokenStorageKey, currentToken);
    final notificationPreferences = await _apiService.getPreferences();
    await applyPreferences(notificationPreferences);
  }

  Future<void> revokeCurrentDevice() async {
    final preferences = await SharedPreferences.getInstance();
    final deviceId = await _deviceId(preferences);
    final token =
        preferences.getString(_tokenStorageKey) ??
        await _firebaseMessaging.getToken();

    await _apiService.revokeDeviceToken(deviceId: deviceId, token: token);
    await _firebaseMessaging.unsubscribeFromTopic(_promotionTopic);
    await preferences.remove(_tokenStorageKey);
  }

  Future<void> disableLocalDevice() async {
    final preferences = await SharedPreferences.getInstance();
    await _firebaseMessaging.unsubscribeFromTopic(_promotionTopic);
    await _firebaseMessaging.deleteToken();
    await preferences.remove(_tokenStorageKey);
  }

  Future<void> applyPreferences(NotificationPreferences preferences) async {
    if (preferences.pushEnabled && preferences.promotions) {
      await _firebaseMessaging.subscribeToTopic(_promotionTopic);
    } else {
      await _firebaseMessaging.unsubscribeFromTopic(_promotionTopic);
    }
  }

  Future<void> _showForegroundNotification(RemoteMessage message) async {
    final notification = message.notification;
    final title = notification?.title ?? message.data['title']?.toString();
    final body = notification?.body ?? message.data['message']?.toString();
    if (title == null && body == null) {
      return;
    }

    final details = NotificationDetails(
      android: AndroidNotificationDetails(
        _channelId,
        _channelName,
        channelDescription: 'Private account updates and public promotions.',
        importance: Importance.high,
        priority: Priority.high,
      ),
      iOS: const DarwinNotificationDetails(),
    );

    await _localNotifications.show(
      id: message.messageId?.hashCode ?? message.hashCode,
      title: title,
      body: body,
      notificationDetails: details,
      payload: _deepLinkFrom(message),
    );
  }

  void _openMessage(RemoteMessage message) {
    final deepLink = _deepLinkFrom(message);
    if (deepLink != null) {
      _onDeepLinkOpened?.call(deepLink);
    }
  }

  String? _deepLinkFrom(RemoteMessage message) {
    final explicit = message.data['deep_link']?.toString();
    if (explicit != null && explicit.startsWith('/')) {
      return explicit;
    }

    final orderId = int.tryParse(message.data['order_id']?.toString() ?? '');
    final deliveryId = int.tryParse(
      message.data['delivery_id']?.toString() ?? '',
    );
    if (AppIdentity.isVendor && orderId != null) {
      return '/vendor-order-details/$orderId';
    }
    if (AppIdentity.isRider && deliveryId != null) {
      return '/delivery-details/$deliveryId';
    }
    if (AppIdentity.isCustomer && orderId != null) {
      return '/order-details/$orderId';
    }
    return null;
  }

  Future<String> _deviceId(SharedPreferences preferences) async {
    const key = 'dailycart_installation_device_id';
    final existing = preferences.getString(key);
    if (existing != null && existing.isNotEmpty) {
      return existing;
    }

    final random = Random.secure();
    final generated = List.generate(
      24,
      (_) => random.nextInt(256).toRadixString(16).padLeft(2, '0'),
    ).join();
    await preferences.setString(key, generated);
    return generated;
  }

  String get _platform {
    return defaultTargetPlatform == TargetPlatform.iOS ? 'ios' : 'android';
  }

  String get _promotionTopic =>
      'dailycart_public_promotions_${AppIdentity.flavor.name}';
}
