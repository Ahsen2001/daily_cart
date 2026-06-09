import 'ticket_reply_model.dart';

class SupportTicketModel {
  const SupportTicketModel({
    required this.id,
    required this.subject,
    required this.message,
    required this.priority,
    required this.status,
    required this.createdAt,
    this.ticketNumber = '',
    this.orderId,
    this.attachment = '',
    this.replies = const [],
  });

  final int id;
  final String ticketNumber;
  final String subject;
  final String message;
  final String priority;
  final String status;
  final int? orderId;
  final String attachment;
  final DateTime createdAt;
  final List<TicketReplyModel> replies;

  factory SupportTicketModel.fromJson(Map<String, dynamic> json) {
    return SupportTicketModel(
      id: _toInt(json['id']),
      ticketNumber: (json['ticket_number'] ?? json['number'] ?? '').toString(),
      subject: (json['subject'] ?? '').toString(),
      message: (json['message'] ?? '').toString(),
      priority: (json['priority'] ?? 'medium').toString(),
      status: (json['status'] ?? 'open').toString(),
      orderId: json['order_id'] == null ? null : _toInt(json['order_id']),
      attachment: (json['attachment'] ?? '').toString(),
      createdAt: DateTime.tryParse((json['created_at'] ?? '').toString()) ??
          DateTime.now(),
      replies: _listFrom(json['replies'])
          .map(TicketReplyModel.fromJson)
          .toList(growable: false),
    );
  }

  bool get canReply {
    final value = status.toLowerCase();
    return value != 'closed' && value != 'resolved';
  }

  static List<Map<String, dynamic>> _listFrom(Object? value) {
    if (value is List) {
      return value.whereType<Map<String, dynamic>>().toList(growable: false);
    }
    return const [];
  }

  static int _toInt(Object? value) {
    if (value is int) {
      return value;
    }
    return int.tryParse(value?.toString() ?? '') ?? 0;
  }
}
