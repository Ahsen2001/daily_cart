import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../config/app_identity.dart';
import '../models/notification_model.dart';
import '../services/auth_api_service.dart';
import '../services/notification_api_service.dart';
import '../services/notification_service.dart';

final notificationApiServiceProvider = Provider<NotificationApiService>((ref) {
  return NotificationApiService();
});

final notificationProvider = ChangeNotifierProvider<NotificationProvider>((
  ref,
) {
  return NotificationProvider(ref.watch(notificationApiServiceProvider));
});

class NotificationProvider extends ChangeNotifier {
  NotificationProvider(this._apiService);

  final NotificationApiService _apiService;

  List<NotificationModel> notifications = const [];
  NotificationPreferences preferences = const NotificationPreferences();
  bool isLoading = false;
  bool isUsingCachedData = false;
  DateTime? lastSyncedAt;
  String? errorMessage;

  int get unreadCount {
    return notifications.where((item) => !item.isRead).length;
  }

  Future<void> getNotifications() async {
    if (notifications.isEmpty) {
      await _loadCache();
    }

    isLoading = true;
    errorMessage = null;
    notifyListeners();
    try {
      notifications = await _apiService.getNotifications();
      try {
        preferences = await _apiService.getPreferences();
      } catch (_) {
        // Notifications remain usable when preferences cannot be refreshed.
      }
      lastSyncedAt = DateTime.now();
      isUsingCachedData = false;
      await _saveCache();
    } on ApiException catch (error) {
      errorMessage = error.message;
      isUsingCachedData = notifications.isNotEmpty;
    } catch (_) {
      errorMessage = 'Unable to refresh notifications. Check your connection.';
      isUsingCachedData = notifications.isNotEmpty;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> markAsRead(String id) async {
    return _run(() async {
      await _apiService.markAsRead(id);
      notifications = notifications
          .map((item) => item.id == id ? item.copyWith(isRead: true) : item)
          .toList(growable: false);
      await _saveCache();
    });
  }

  Future<bool> markAllAsRead() async {
    return _run(() async {
      await _apiService.markAllAsRead();
      notifications = notifications
          .map((item) => item.copyWith(isRead: true))
          .toList(growable: false);
      await _saveCache();
    });
  }

  Future<bool> deleteNotification(String id) async {
    return _run(() async {
      await _apiService.deleteNotification(id);
      notifications = notifications
          .where((item) => item.id != id)
          .toList(growable: false);
      await _saveCache();
    });
  }

  Future<bool> updatePreferences(NotificationPreferences updated) async {
    return _run(() async {
      preferences = await _apiService.updatePreferences(updated);
      await NotificationService.applyPreferences(preferences);
    });
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

  String get _cacheKey =>
      'dailycart_${AppIdentity.flavor.name}_notifications_cache_v2';

  Future<void> _loadCache() async {
    try {
      final raw = (await SharedPreferences.getInstance()).getString(_cacheKey);
      if (raw == null) return;
      final decoded = jsonDecode(raw);
      if (decoded is! Map<String, dynamic>) return;
      final items = decoded['notifications'];
      if (items is List) {
        notifications = items
            .whereType<Map>()
            .map((item) => NotificationModel.fromJson(
                  item.map((key, value) => MapEntry(key.toString(), value)),
                ))
            .toList(growable: false);
      }
      lastSyncedAt = DateTime.tryParse(decoded['synced_at']?.toString() ?? '');
      isUsingCachedData = notifications.isNotEmpty;
    } catch (_) {
      // A corrupt cache is ignored and replaced after the next successful sync.
    }
  }

  Future<void> _saveCache() async {
    final payload = jsonEncode({
      'synced_at': lastSyncedAt?.toIso8601String(),
      'notifications': notifications.map((item) => item.toJson()).toList(),
    });
    await (await SharedPreferences.getInstance()).setString(_cacheKey, payload);
  }
}
