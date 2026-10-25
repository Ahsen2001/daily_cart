class VendorDashboardModel {
  const VendorDashboardModel({
    this.totalProducts = 0,
    this.pendingProducts = 0,
    this.approvedProducts = 0,
    this.totalOrders = 0,
    this.pendingOrders = 0,
    this.completedOrders = 0,
    this.cancelledOrders = 0,
    this.todayEarnings = 0,
    this.totalEarnings = 0,
    this.lowStockProducts = 0,
    this.customerReviews = 0,
    this.approvalStatus = 'pending',
  });

  final int totalProducts;
  final int pendingProducts;
  final int approvedProducts;
  final int totalOrders;
  final int pendingOrders;
  final int completedOrders;
  final int cancelledOrders;
  final double todayEarnings;
  final double totalEarnings;
  final int lowStockProducts;
  final int customerReviews;
  final String approvalStatus;

  bool get isApproved =>
      const {'approved', 'verified'}.contains(approvalStatus.toLowerCase());

  factory VendorDashboardModel.fromJson(Map<String, dynamic> json) {
    return VendorDashboardModel(
      totalProducts: _toInt(json['total_products']),
      pendingProducts: _toInt(json['pending_products']),
      approvedProducts: _toInt(json['approved_products']),
      totalOrders: _toInt(json['total_orders']),
      pendingOrders: _toInt(json['pending_orders']),
      completedOrders: _toInt(json['completed_orders']),
      cancelledOrders: _toInt(json['cancelled_orders']),
      todayEarnings: _toDouble(json['today_earnings'] ?? json['today_sales']),
      totalEarnings: _toDouble(json['total_earnings'] ?? json['earnings']),
      lowStockProducts: _toInt(json['low_stock_products']),
      customerReviews: _toInt(json['customer_reviews']),
      approvalStatus: (json['approval_status'] ?? json['status'] ?? 'pending')
          .toString(),
    );
  }

  static int _toInt(Object? value) {
    if (value is int) {
      return value;
    }
    return int.tryParse(value?.toString() ?? '') ?? 0;
  }

  static double _toDouble(Object? value) {
    if (value is num) {
      return value.toDouble();
    }
    return double.tryParse(value?.toString() ?? '') ?? 0;
  }
}
