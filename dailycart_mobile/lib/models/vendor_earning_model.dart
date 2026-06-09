class VendorEarningModel {
  const VendorEarningModel({
    this.totalEarnings = 0,
    this.todayEarnings = 0,
    this.weeklyEarnings = 0,
    this.monthlyEarnings = 0,
    this.platformCommission = 0,
    this.pendingPayout = 0,
    this.completedPayout = 0,
    this.refundedAmount = 0,
    this.transactions = const [],
  });

  final double totalEarnings;
  final double todayEarnings;
  final double weeklyEarnings;
  final double monthlyEarnings;
  final double platformCommission;
  final double pendingPayout;
  final double completedPayout;
  final double refundedAmount;
  final List<VendorEarningTransactionModel> transactions;

  factory VendorEarningModel.fromJson(Map<String, dynamic> json) {
    return VendorEarningModel(
      totalEarnings: _toDouble(json['total_earnings']),
      todayEarnings: _toDouble(json['today_earnings']),
      weeklyEarnings: _toDouble(json['weekly_earnings']),
      monthlyEarnings: _toDouble(json['monthly_earnings']),
      platformCommission: _toDouble(json['platform_commission']),
      pendingPayout: _toDouble(json['pending_payout']),
      completedPayout: _toDouble(json['completed_payout']),
      refundedAmount: _toDouble(json['refunded_amount']),
      transactions: _listFrom(json['transactions'])
          .map(VendorEarningTransactionModel.fromJson)
          .toList(growable: false),
    );
  }

  static List<Map<String, dynamic>> _listFrom(Object? value) {
    if (value is List) {
      return value.whereType<Map<String, dynamic>>().toList(growable: false);
    }
    return const [];
  }

  static double _toDouble(Object? value) {
    if (value is num) {
      return value.toDouble();
    }
    return double.tryParse(value?.toString() ?? '') ?? 0;
  }
}

class VendorEarningTransactionModel {
  const VendorEarningTransactionModel({
    required this.title,
    required this.amount,
    required this.status,
    required this.createdAt,
  });

  final String title;
  final double amount;
  final String status;
  final DateTime createdAt;

  factory VendorEarningTransactionModel.fromJson(Map<String, dynamic> json) {
    return VendorEarningTransactionModel(
      title: (json['title'] ?? json['description'] ?? '').toString(),
      amount: VendorEarningModel._toDouble(json['amount']),
      status: (json['status'] ?? '').toString(),
      createdAt:
          DateTime.tryParse((json['created_at'] ?? '').toString()) ??
              DateTime.now(),
    );
  }
}
