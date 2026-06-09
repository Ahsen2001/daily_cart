class VendorDashboardModel {
  const VendorDashboardModel({
    this.totalProducts = 0,
    this.pendingProducts = 0,
    this.approvedProducts = 0,
    this.totalOrders = 0,
    this.pendingOrders = 0,
    this.completedOrders = 0,
    this.todaySales = 0,
    this.totalEarnings = 0,
    this.lowStockProducts = 0,
    this.approvalStatus = 'pending',
  });

  final int totalProducts;
  final int pendingProducts;
  final int approvedProducts;
  final int totalOrders;
  final int pendingOrders;
  final int completedOrders;
  final double todaySales;
  final double totalEarnings;
  final int lowStockProducts;
  final String approvalStatus;

  bool get isApproved => approvalStatus.toLowerCase() == 'approved';

  factory VendorDashboardModel.fromJson(Map<String, dynamic> json) {
    return VendorDashboardModel(
      totalProducts: _toInt(json['total_products']),
      pendingProducts: _toInt(json['pending_products']),
      approvedProducts: _toInt(json['approved_products']),
      totalOrders: _toInt(json['total_orders']),
      pendingOrders: _toInt(json['pending_orders']),
      completedOrders: _toInt(json['completed_orders']),
      todaySales: _toDouble(json['today_sales']),
      totalEarnings: _toDouble(json['total_earnings']),
      lowStockProducts: _toInt(json['low_stock_products']),
      approvalStatus:
          (json['approval_status'] ?? json['status'] ?? 'pending').toString(),
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
