import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/notification_model.dart';
import '../services/auth_api_service.dart';
import '../services/notification_api_service.dart';

final notificationApiServiceProvider = Provider<NotificationApiService>((ref) {
  return NotificationApiService();
});

final notificationProvider = ChangeNotifierProvider<NotificationProvider>((ref) {
  return NotificationProvider(ref.watch(notificationApiServiceProvider));
});

class NotificationProvider extends ChangeNotifier {
  NotificationProvider(this._apiService);

  final NotificationApiService _apiService;

  List<NotificationModel> notifications = const [];
  bool isLoading = false;
  String? errorMessage;

  int get unreadCount {
    return notifications.where((item) => !item.isRead).length;
  }

  Future<void> getNotifications() async {
    await _run(() async {
      notifications = await _apiService.getNotifications();
    });
  }

  Future<bool> markAsRead(String id) async {
    return _run(() async {
      await _apiService.markAsRead(id);
      notifications = notifications
          .map((item) => item.id == id ? item.copyWith(isRead: true) : item)
          .toList(growable: false);
    });
  }

  Future<bool> markAllAsRead() async {
    return _run(() async {
      await _apiService.markAllAsRead();
      notifications = notifications
          .map((item) => item.copyWith(isRead: true))
          .toList(growable: false);
    });
  }

  Future<bool> deleteNotification(String id) async {
    return _run(() async {
      await _apiService.deleteNotification(id);
      notifications = notifications
          .where((item) => item.id != id)
          .toList(growable: false);
    });
  }

  Future<void> saveDeviceToken(String token) async {
    await _apiService.saveDeviceToken(token);
  }

  Future<bool> _run(Future<void> Function() action) async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();

    try {
      await action();
      return true;
    } on ApiException catch (error) {
      errorMessage = error.message;
      return false;
    } catch (_) {
      errorMessage = 'Something went wrong. Please try again.';
      return false;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }
}
