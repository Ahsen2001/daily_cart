class RiderDashboardModel {
  const RiderDashboardModel({
    this.todayDeliveries = 0,
    this.assignedDeliveries = 0,
    this.completedDeliveries = 0,
    this.failedDeliveries = 0,
    this.todayEarnings = 0,
    this.weeklyEarnings = 0,
    this.monthlyEarnings = 0,
    this.approvalStatus = 'pending',
    this.availabilityStatus = 'unavailable',
  });

  final int todayDeliveries;
  final int assignedDeliveries;
  final int completedDeliveries;
  final int failedDeliveries;
  final double todayEarnings;
  final double weeklyEarnings;
  final double monthlyEarnings;
  final String approvalStatus;
  final String availabilityStatus;

  bool get isApproved => approvalStatus.toLowerCase() == 'approved';

  factory RiderDashboardModel.fromJson(Map<String, dynamic> json) {
    return RiderDashboardModel(
      todayDeliveries: _toInt(json['today_deliveries']),
      assignedDeliveries: _toInt(json['assigned_deliveries']),
      completedDeliveries: _toInt(json['completed_deliveries']),
      failedDeliveries: _toInt(json['failed_deliveries']),
      todayEarnings: _toDouble(json['today_earnings']),
      weeklyEarnings: _toDouble(json['weekly_earnings']),
      monthlyEarnings: _toDouble(json['monthly_earnings']),
      approvalStatus:
          (json['approval_status'] ?? json['status'] ?? 'pending').toString(),
      availabilityStatus:
          (json['availability_status'] ?? 'unavailable').toString(),
    );
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
