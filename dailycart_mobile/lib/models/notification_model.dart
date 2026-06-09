enum NotificationType {
  order,
  payment,
  promotion,
  system;

  static NotificationType fromName(String? value) {
    final normalized = value?.trim().toLowerCase();
    return switch (normalized) {
      'order' || 'orders' => NotificationType.order,
      'payment' || 'payments' => NotificationType.payment,
      'promotion' || 'promotions' => NotificationType.promotion,
      _ => NotificationType.system,
    };
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
  });

  final int id;
  final String title;
  final String message;
  final NotificationType type;
  final DateTime createdAt;
  final bool isRead;
  final int? orderId;

  factory NotificationModel.fromJson(Map<String, dynamic> json) {
    final data = json['data'] is Map<String, dynamic>
        ? json['data'] as Map<String, dynamic>
        : json;

    return NotificationModel(
      id: _toInt(json['id']),
      title: (data['title'] ?? json['title'] ?? '').toString(),
      message: (data['message'] ?? json['message'] ?? '').toString(),
      type: NotificationType.fromName(
        (data['type'] ?? json['type'])?.toString(),
      ),
      createdAt: DateTime.tryParse(
            (json['created_at'] ?? data['created_at'] ?? '').toString(),
          ) ??
          DateTime.now(),
      isRead: json['read_at'] != null || json['is_read'] == true,
      orderId: data['order_id'] == null ? null : _toInt(data['order_id']),
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
    );
  }

  static int _toInt(Object? value) {
    if (value is int) {
      return value;
    }
    return int.tryParse(value?.toString() ?? '') ?? 0;
  }
}
