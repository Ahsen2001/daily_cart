class BankTransferModel {
  const BankTransferModel({
    required this.id,
    required this.orderId,
    required this.referenceNumber,
    required this.expectedAmount,
    required this.currency,
    required this.status,
    required this.bankName,
    required this.accountHolder,
    required this.accountNumber,
    required this.branch,
    this.lastRejectionReason,
    this.slips = const [],
  });

  final int id;
  final int orderId;
  final String referenceNumber;
  final double expectedAmount;
  final String currency;
  final String status;
  final String bankName;
  final String accountHolder;
  final String accountNumber;
  final String branch;
  final String? lastRejectionReason;
  final List<BankTransferSlipModel> slips;

  bool get requiresSlip =>
      const {'pending_upload', 'rejected'}.contains(status);

  factory BankTransferModel.fromJson(Map<String, dynamic> json) {
    final bank = json['bank'] is Map<String, dynamic>
        ? json['bank'] as Map<String, dynamic>
        : const <String, dynamic>{};
    final slips = json['slips'] is List ? json['slips'] as List : const [];
    return BankTransferModel(
      id: _toInt(json['id']),
      orderId: _toInt(json['order_id']),
      referenceNumber: (json['reference_number'] ?? '').toString(),
      expectedAmount: _toDouble(json['expected_amount']),
      currency: (json['currency'] ?? 'LKR').toString(),
      status: (json['status'] ?? 'pending_upload').toString(),
      bankName: (bank['name'] ?? '').toString(),
      accountHolder: (bank['account_holder'] ?? '').toString(),
      accountNumber: (bank['account_number'] ?? '').toString(),
      branch: (bank['branch'] ?? '').toString(),
      lastRejectionReason: json['last_rejection_reason']?.toString(),
      slips: slips
          .whereType<Map<String, dynamic>>()
          .map(BankTransferSlipModel.fromJson)
          .toList(growable: false),
    );
  }
}

class BankTransferSlipModel {
  const BankTransferSlipModel({required this.id, required this.name});

  final int id;
  final String name;

  factory BankTransferSlipModel.fromJson(Map<String, dynamic> json) {
    return BankTransferSlipModel(
      id: _toInt(json['id']),
      name: (json['original_name'] ?? 'Payment slip').toString(),
    );
  }
}

int _toInt(Object? value) =>
    value is int ? value : int.tryParse(value?.toString() ?? '') ?? 0;

double _toDouble(Object? value) => value is num
    ? value.toDouble()
    : double.tryParse(value?.toString() ?? '') ?? 0;
