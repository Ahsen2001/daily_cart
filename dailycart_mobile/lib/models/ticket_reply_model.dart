class TicketReplyModel {
  const TicketReplyModel({
    required this.id,
    required this.message,
    required this.createdAt,
    this.senderName = '',
    this.isCustomer = false,
    this.attachment = '',
  });

  final int id;
  final String message;
  final String senderName;
  final bool isCustomer;
  final String attachment;
  final DateTime createdAt;

  factory TicketReplyModel.fromJson(Map<String, dynamic> json) {
    return TicketReplyModel(
      id: _toInt(json['id']),
      message: (json['message'] ?? json['reply'] ?? '').toString(),
      senderName: (json['sender_name'] ?? json['user_name'] ?? '').toString(),
      isCustomer: json['is_customer'] == true ||
          json['sender_type']?.toString().toLowerCase() == 'customer',
      attachment: (json['attachment'] ?? '').toString(),
      createdAt: DateTime.tryParse((json['created_at'] ?? '').toString()) ??
          DateTime.now(),
    );
  }

  static int _toInt(Object? value) {
    if (value is int) {
      return value;
    }
    return int.tryParse(value?.toString() ?? '') ?? 0;
  }
}
