enum NotificationType {
  order,
  payment,
  promotion,
  system;

  static NotificationType fromName(String? value) {
    final normalized = value?.trim().toLowerCase();
    if (normalized?.contains('promotion') == true ||
        normalized?.contains('coupon') == true) {
      return NotificationType.promotion;
    }
    if (normalized?.contains('payment') == true ||
        normalized?.contains('wallet') == true ||
        normalized?.contains('refund') == true ||
        normalized?.contains('payout') == true) {
      return NotificationType.payment;
    }
    if (normalized?.contains('order') == true ||
        normalized?.contains('delivery') == true ||
        normalized?.contains('rider') == true) {
      return NotificationType.order;
    }
    return NotificationType.system;
  }
}

class NotificationModel {
  const NotificationModel({
    required this.id,
    required this.title,
    required this.message,
    required this.type,
    required this.createdAt,
    this.isRead = false,
    this.orderId,
    this.deepLink,
    this.data = const {},
  });

  final String id;
  final String title;
  final String message;
  final NotificationType type;
  final DateTime createdAt;
  final bool isRead;
  final int? orderId;
  final String? deepLink;
  final Map<String, dynamic> data;

  factory NotificationModel.fromJson(Map<String, dynamic> json) {
    final data = json['data'] is Map<String, dynamic>
        ? json['data'] as Map<String, dynamic>
        : json;

    return NotificationModel(
      id: (json['id'] ?? data['id'] ?? '').toString(),
      title: (data['title'] ?? json['title'] ?? '').toString(),
      message: (data['message'] ?? json['message'] ?? '').toString(),
      type: NotificationType.fromName(
        (data['type'] ?? json['type'])?.toString(),
      ),
      createdAt:
          DateTime.tryParse(
            (json['created_at'] ?? data['created_at'] ?? '').toString(),
          ) ??
          DateTime.now(),
      isRead: json['read_at'] != null || json['is_read'] == true,
      orderId: data['order_id'] == null ? null : _toInt(data['order_id']),
      deepLink: (json['deep_link'] ?? data['deep_link'])?.toString(),
      data: Map<String, dynamic>.from(data),
    );
  }

  NotificationModel copyWith({bool? isRead}) {
    return NotificationModel(
      id: id,
      title: title,
      message: message,
      type: type,
      createdAt: createdAt,
      isRead: isRead ?? this.isRead,
      orderId: orderId,
      deepLink: deepLink,
      data: data,
    );
  }

  static int _toInt(Object? value) {
    if (value is int) {
      return value;
    }
    return int.tryParse(value?.toString() ?? '') ?? 0;
  }
}

class NotificationPreferences {
  const NotificationPreferences({
    this.pushEnabled = true,
    this.orderUpdates = true,
    this.deliveryUpdates = true,
    this.walletUpdates = true,
    this.supportUpdates = true,
    this.promotions = true,
  });

  final bool pushEnabled;
  final bool orderUpdates;
  final bool deliveryUpdates;
  final bool walletUpdates;
  final bool supportUpdates;
  final bool promotions;

  factory NotificationPreferences.fromJson(Map<String, dynamic> json) {
    return NotificationPreferences(
      pushEnabled: json['push_enabled'] != false,
      orderUpdates: json['order_updates'] != false,
      deliveryUpdates: json['delivery_updates'] != false,
      walletUpdates: json['wallet_updates'] != false,
      supportUpdates: json['support_updates'] != false,
      promotions: json['promotions'] != false,
    );
  }

  Map<String, dynamic> toJson() => {
    'push_enabled': pushEnabled,
    'order_updates': orderUpdates,
    'delivery_updates': deliveryUpdates,
    'wallet_updates': walletUpdates,
    'support_updates': supportUpdates,
    'promotions': promotions,
  };

  NotificationPreferences copyWith({
    bool? pushEnabled,
    bool? orderUpdates,
    bool? deliveryUpdates,
    bool? walletUpdates,
    bool? supportUpdates,
    bool? promotions,
  }) {
    return NotificationPreferences(
      pushEnabled: pushEnabled ?? this.pushEnabled,
      orderUpdates: orderUpdates ?? this.orderUpdates,
      deliveryUpdates: deliveryUpdates ?? this.deliveryUpdates,
      walletUpdates: walletUpdates ?? this.walletUpdates,
      supportUpdates: supportUpdates ?? this.supportUpdates,
      promotions: promotions ?? this.promotions,
    );
  }
}
