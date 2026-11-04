class VendorWalletModel {
  const VendorWalletModel({
    required this.balance,
    required this.availableBalance,
    required this.pendingBalance,
    required this.totalEarned,
    required this.totalWithdrawn,
    this.payouts = const [],
  });

  final double balance;
  final double availableBalance;
  final double pendingBalance;
  final double totalEarned;
  final double totalWithdrawn;
  final List<VendorPayoutModel> payouts;
}

class VendorPayoutModel {
  const VendorPayoutModel({
    required this.id,
    required this.amount,
    required this.status,
    required this.bankName,
    required this.accountNumber,
    this.createdAt,
  });

  final int id;
  final double amount;
  final String status;
  final String bankName;
  final String accountNumber;
  final DateTime? createdAt;

  factory VendorPayoutModel.fromJson(Map<String, dynamic> json) {
    return VendorPayoutModel(
      id: _toInt(json['id']),
      amount: _toDouble(json['amount']),
      status: (json['status'] ?? '').toString(),
      bankName: (json['bank_name'] ?? '').toString(),
      accountNumber: (json['account_number'] ?? '').toString(),
      createdAt: _toDate(json['created_at']),
    );
  }
}

class VendorRefundModel {
  const VendorRefundModel({
    required this.id,
    required this.orderId,
    required this.orderNumber,
    required this.customerName,
    required this.amount,
    required this.reason,
    required this.status,
    this.refundMethod = 'wallet',
    this.refundType = 'full',
    this.gatewayRefundId,
    this.vendorNote,
  });

  final int id;
  final int orderId;
  final String orderNumber;
  final String customerName;
  final double amount;
  final String reason;
  final String status;
  final String refundMethod;
  final String refundType;
  final String? gatewayRefundId;
  final String? vendorNote;

  factory VendorRefundModel.fromJson(Map<String, dynamic> json) {
    return VendorRefundModel(
      id: _toInt(json['id']),
      orderId: _toInt(json['order_id']),
      orderNumber: (json['order_number'] ?? '').toString(),
      customerName: (json['customer_name'] ?? '').toString(),
      amount: _toDouble(json['amount']),
      reason: (json['reason'] ?? '').toString(),
      status: (json['status'] ?? '').toString(),
      refundMethod: (json['refund_method'] ?? 'wallet').toString(),
      refundType: (json['refund_type'] ?? 'full').toString(),
      gatewayRefundId: json['gateway_refund_id']?.toString(),
      vendorNote: json['vendor_note']?.toString(),
    );
  }
}

class VendorCouponModel {
  const VendorCouponModel({
    required this.id,
    required this.code,
    required this.title,
    required this.discountType,
    required this.discountValue,
    required this.status,
    this.expiresAt,
    this.startsAt,
    this.description = '',
    this.minimumOrderAmount = 0,
  });

  final int id;
  final String code;
  final String title;
  final String discountType;
  final double discountValue;
  final String status;
  final DateTime? expiresAt;
  final DateTime? startsAt;
  final String description;
  final double minimumOrderAmount;

  factory VendorCouponModel.fromJson(Map<String, dynamic> json) {
    return VendorCouponModel(
      id: _toInt(json['id']),
      code: (json['code'] ?? '').toString(),
      title: (json['title'] ?? '').toString(),
      discountType: (json['discount_type'] ?? '').toString(),
      discountValue: _toDouble(json['discount_value']),
      status: (json['status'] ?? '').toString(),
      expiresAt: _toDate(json['expires_at']),
      startsAt: _toDate(json['starts_at']),
      description: (json['description'] ?? '').toString(),
      minimumOrderAmount: _toDouble(json['minimum_order_amount']),
    );
  }
}

class VendorPromotionModel {
  const VendorPromotionModel({
    required this.id,
    required this.title,
    required this.productName,
    required this.discountValue,
    required this.status,
    this.endsAt,
    this.startsAt,
    this.description = '',
    this.promotionType = 'featured_offer',
    this.discountType = 'percentage',
    this.targetId = 0,
  });

  final int id;
  final String title;
  final String productName;
  final double discountValue;
  final String status;
  final DateTime? endsAt;
  final DateTime? startsAt;
  final String description;
  final String promotionType;
  final String discountType;
  final int targetId;

  factory VendorPromotionModel.fromJson(Map<String, dynamic> json) {
    return VendorPromotionModel(
      id: _toInt(json['id']),
      title: (json['title'] ?? '').toString(),
      productName: (json['product_name'] ?? '').toString(),
      discountValue: _toDouble(json['discount_value']),
      status: (json['status'] ?? '').toString(),
      endsAt: _toDate(json['ends_at']),
      startsAt: _toDate(json['starts_at']),
      description: (json['description'] ?? '').toString(),
      promotionType: (json['promotion_type'] ?? 'featured_offer').toString(),
      discountType: (json['discount_type'] ?? 'percentage').toString(),
      targetId: _toInt(json['target_id']),
    );
  }
}

class VendorSubscriptionModel {
  const VendorSubscriptionModel({
    required this.id,
    required this.customerName,
    required this.productName,
    required this.frequency,
    required this.quantity,
    required this.status,
    this.nextDeliveryDate,
  });

  final int id;
  final String customerName;
  final String productName;
  final String frequency;
  final int quantity;
  final String status;
  final DateTime? nextDeliveryDate;

  factory VendorSubscriptionModel.fromJson(Map<String, dynamic> json) {
    return VendorSubscriptionModel(
      id: _toInt(json['id']),
      customerName: (json['customer_name'] ?? '').toString(),
      productName: (json['product_name'] ?? '').toString(),
      frequency: (json['frequency'] ?? '').toString(),
      quantity: _toInt(json['quantity']),
      status: (json['status'] ?? '').toString(),
      nextDeliveryDate: _toDate(json['next_delivery_date']),
    );
  }
}

class VendorSubscriptionStockModel {
  const VendorSubscriptionStockModel({
    required this.productName,
    required this.requiredQuantity,
    this.variantName = '',
  });
  final String productName;
  final String variantName;
  final int requiredQuantity;

  factory VendorSubscriptionStockModel.fromJson(Map<String, dynamic> json) =>
      VendorSubscriptionStockModel(
        productName: (json['product_name'] ?? '').toString(),
        variantName: (json['variant_name'] ?? '').toString(),
        requiredQuantity: _toInt(json['required_quantity']),
      );
}

class VendorSubscriptionBundle {
  const VendorSubscriptionBundle({
    required this.subscriptions,
    required this.stockRequirements,
  });
  final List<VendorSubscriptionModel> subscriptions;
  final List<VendorSubscriptionStockModel> stockRequirements;
}

class VendorReportModel {
  const VendorReportModel({
    required this.summary,
    required this.bestSelling,
    required this.lowStock,
    this.charts = const {},
  });

  final Map<String, dynamic> summary;
  final List<Map<String, dynamic>> bestSelling;
  final List<Map<String, dynamic>> lowStock;
  final Map<String, dynamic> charts;

  factory VendorReportModel.fromJson(Map<String, dynamic> json) {
    return VendorReportModel(
      summary: _map(json['summary']),
      bestSelling: _maps(json['best_selling']),
      lowStock: _maps(json['low_stock']),
      charts: _map(json['charts']),
    );
  }
}

Map<String, dynamic> _map(Object? value) {
  if (value is Map<String, dynamic>) return value;
  if (value is Map) {
    return value.map((key, item) => MapEntry(key.toString(), item));
  }
  return const {};
}

List<Map<String, dynamic>> _maps(Object? value) {
  if (value is! List) return const [];
  return value.map(_map).toList(growable: false);
}

int _toInt(Object? value) {
  if (value is int) return value;
  return int.tryParse(value?.toString() ?? '') ?? 0;
}

double _toDouble(Object? value) {
  if (value is num) return value.toDouble();
  return double.tryParse(value?.toString() ?? '') ?? 0;
}

DateTime? _toDate(Object? value) {
  return DateTime.tryParse(value?.toString() ?? '');
}
