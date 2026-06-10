class RiderEarningModel {
  const RiderEarningModel({
    this.dailyEarnings = 0,
    this.weeklyEarnings = 0,
    this.monthlyEarnings = 0,
    this.completedDeliveryCount = 0,
    this.failedDeliveryCount = 0,
    this.history = const [],
  });

  final double dailyEarnings;
  final double weeklyEarnings;
  final double monthlyEarnings;
  final int completedDeliveryCount;
  final int failedDeliveryCount;
  final List<RiderEarningHistoryModel> history;

  factory RiderEarningModel.fromJson(Map<String, dynamic> json) {
    return RiderEarningModel(
      dailyEarnings: _toDouble(json['daily_earnings'] ?? json['today_earnings']),
      weeklyEarnings: _toDouble(json['weekly_earnings']),
      monthlyEarnings: _toDouble(json['monthly_earnings']),
      completedDeliveryCount: _toInt(json['completed_delivery_count']),
      failedDeliveryCount: _toInt(json['failed_delivery_count']),
      history: _listFrom(json['history'])
          .map(RiderEarningHistoryModel.fromJson)
          .toList(growable: false),
    );
  }

  static List<Map<String, dynamic>> _listFrom(Object? value) {
    if (value is List) {
      return value.whereType<Map<String, dynamic>>().toList(growable: false);
    }
    return const [];
  }

  static int _toInt(Object? value) {
    if (value is int) return value;
    return int.tryParse(value?.toString() ?? '') ?? 0;
  }

  static double _toDouble(Object? value) {
    if (value is num) return value.toDouble();
    return double.tryParse(value?.toString() ?? '') ?? 0;
  }
}

class RiderEarningHistoryModel {
  const RiderEarningHistoryModel({
    required this.title,
    required this.amount,
    required this.createdAt,
  });

  final String title;
  final double amount;
  final DateTime createdAt;

  factory RiderEarningHistoryModel.fromJson(Map<String, dynamic> json) {
    return RiderEarningHistoryModel(
      title: (json['title'] ?? json['description'] ?? '').toString(),
      amount: RiderEarningModel._toDouble(json['amount']),
      createdAt: DateTime.tryParse((json['created_at'] ?? '').toString()) ??
          DateTime.now(),
    );
  }
}
