enum NotificationType {
  order,
  delivery,
  payment,
  support,
  account,
  review,
  inventory,
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
    if (normalized?.contains('support') == true ||
        normalized?.contains('ticket') == true) {
      return NotificationType.support;
    }
    if (normalized?.contains('rating') == true ||
        normalized?.contains('review') == true) {
      return NotificationType.review;
    }
    if (normalized?.contains('stock') == true ||
        normalized?.contains('product') == true) {
      return NotificationType.inventory;
    }
    if (normalized?.contains('account') == true ||
        normalized?.contains('registration') == true ||
        normalized?.contains('approved') == true ||
        normalized?.contains('rejected') == true) {
      return NotificationType.account;
    }
    if (normalized?.contains('delivery') == true ||
        normalized?.contains('rider_assigned') == true ||
        normalized?.contains('picked_up') == true ||
        normalized?.contains('out_for_delivery') == true) {
      return NotificationType.delivery;
    }
    if (normalized?.contains('order') == true) {
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
    required this.rawType,
    required this.createdAt,
    this.isRead = false,
    this.orderId,
    this.deepLink,
    this.data = const {},
    this.orderStatus,
    this.timeline = const [],
  });

  final String id;
  final String title;
  final String message;
  final NotificationType type;
  final String rawType;
  final DateTime createdAt;
  final bool isRead;
  final int? orderId;
  final String? deepLink;
  final Map<String, dynamic> data;
  final String? orderStatus;
  final List<NotificationTimelineEntry> timeline;

  factory NotificationModel.fromJson(Map<String, dynamic> json) {
    final data = json['data'] is Map<String, dynamic>
        ? json['data'] as Map<String, dynamic>
        : json;

    final rawType = (json['type'] ?? data['type'] ?? 'system').toString();
    final timelineSource = json['activity_timeline'] ?? data['activity_timeline'];

    return NotificationModel(
      id: (json['id'] ?? data['id'] ?? '').toString(),
      title: (data['title'] ?? json['title'] ?? '').toString(),
      message: (data['message'] ?? json['message'] ?? '').toString(),
      type: NotificationType.fromName(rawType),
      rawType: rawType,
      createdAt:
          DateTime.tryParse(
            (json['created_at'] ?? data['created_at'] ?? '').toString(),
          ) ??
          DateTime.now(),
      isRead: json['read_at'] != null || json['is_read'] == true,
      orderId: (json['order_id'] ?? data['order_id']) == null
          ? null
          : _toInt(json['order_id'] ?? data['order_id']),
      deepLink: (json['deep_link'] ?? data['deep_link'])?.toString(),
      data: Map<String, dynamic>.from(data),
      orderStatus: (json['order_status'] ?? data['order_status'])?.toString(),
      timeline: timelineSource is List
          ? timelineSource
                .whereType<Map>()
                .map((item) => NotificationTimelineEntry.fromJson(
                      item.map((key, value) => MapEntry(key.toString(), value)),
                    ))
                .toList(growable: false)
          : const [],
    );
  }

  NotificationModel copyWith({bool? isRead}) {
    return NotificationModel(
      id: id,
      title: title,
      message: message,
      type: type,
      rawType: rawType,
      createdAt: createdAt,
      isRead: isRead ?? this.isRead,
      orderId: orderId,
      deepLink: deepLink,
      data: data,
      orderStatus: orderStatus,
      timeline: timeline,
    );
  }

  bool get requiresAction {
    final value = rawType.toLowerCase();
    return value.contains('request') ||
        value.contains('pending') ||
        value.contains('failed') ||
        value.contains('reported') ||
        value.contains('low_stock') ||
        const {'new_order', 'delivery_assigned', 'support_ticket_assigned'}
            .contains(value);
  }

  String get categoryLabel => switch (type) {
        NotificationType.order => 'Orders',
        NotificationType.delivery => 'Delivery',
        NotificationType.payment => 'Payments',
        NotificationType.support => 'Support',
        NotificationType.account => 'Account',
        NotificationType.review => 'Reviews',
        NotificationType.inventory => 'Inventory',
        NotificationType.promotion => 'Offers',
        NotificationType.system => 'Other',
      };

  Map<String, dynamic> toJson() => {
        'id': id,
        'title': title,
        'message': message,
        'type': rawType,
        'created_at': createdAt.toIso8601String(),
        'read_at': isRead ? createdAt.toIso8601String() : null,
        'order_id': orderId,
        'deep_link': deepLink,
        'data': data,
        'order_status': orderStatus,
        'activity_timeline': timeline.map((item) => item.toJson()).toList(),
      };

  static int _toInt(Object? value) {
    if (value is int) {
      return value;
    }
    return int.tryParse(value?.toString() ?? '') ?? 0;
  }
}

class NotificationTimelineEntry {
  const NotificationTimelineEntry({
    required this.status,
    required this.timestamp,
    this.remarks = '',
  });

  final String status;
  final String remarks;
  final DateTime? timestamp;

  factory NotificationTimelineEntry.fromJson(Map<String, dynamic> json) {
    return NotificationTimelineEntry(
      status: (json['status'] ?? '').toString(),
      remarks: (json['remarks'] ?? '').toString(),
      timestamp: DateTime.tryParse((json['timestamp'] ?? '').toString()),
    );
  }

  Map<String, dynamic> toJson() => {
        'status': status,
        'remarks': remarks,
        'timestamp': timestamp?.toIso8601String(),
      };
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
