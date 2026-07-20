class WalletModel {
  const WalletModel({
    required this.balance,
    required this.currency,
    this.transactions = const [],
  });

  final double balance;
  final String currency;
  final List<WalletTransactionModel> transactions;
}

class WalletTransactionModel {
  const WalletTransactionModel({
    required this.id,
    required this.type,
    required this.amount,
    required this.balanceAfter,
    required this.currency,
    required this.description,
    required this.createdAt,
  });

  final int id;
  final String type;
  final double amount;
  final double balanceAfter;
  final String currency;
  final String description;
  final DateTime? createdAt;

  factory WalletTransactionModel.fromJson(Map<String, dynamic> json) {
    return WalletTransactionModel(
      id: _toInt(json['id']),
      type: (json['type'] ?? json['transaction_type'] ?? '').toString(),
      amount: _toDouble(json['amount']),
      balanceAfter: _toDouble(json['balance_after']),
      currency: (json['currency'] ?? 'LKR').toString(),
      description: (json['description'] ?? json['source'] ?? '').toString(),
      createdAt: DateTime.tryParse(json['created_at']?.toString() ?? ''),
    );
  }
}

class RefundModel {
  const RefundModel({
    required this.id,
    required this.orderId,
    required this.orderNumber,
    required this.amount,
    required this.status,
    required this.reason,
    this.adminNote,
    this.requestedAt,
  });

  final int id;
  final int orderId;
  final String orderNumber;
  final double amount;
  final String status;
  final String reason;
  final String? adminNote;
  final DateTime? requestedAt;

  factory RefundModel.fromJson(Map<String, dynamic> json) {
    return RefundModel(
      id: _toInt(json['id']),
      orderId: _toInt(json['order_id']),
      orderNumber: (json['order_number'] ?? '').toString(),
      amount: _toDouble(json['amount']),
      status: (json['status'] ?? '').toString(),
      reason: (json['reason'] ?? '').toString(),
      adminNote: json['admin_note']?.toString(),
      requestedAt: DateTime.tryParse(json['requested_at']?.toString() ?? ''),
    );
  }
}

int _toInt(Object? value) {
  if (value is int) return value;
  return int.tryParse(value?.toString() ?? '') ?? 0;
}

double _toDouble(Object? value) {
  if (value is num) return value.toDouble();
  return double.tryParse(value?.toString() ?? '') ?? 0;
}
