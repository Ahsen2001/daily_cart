class CheckoutResponseModel {
  const CheckoutResponseModel({
    required this.success,
    required this.message,
    required this.orders,
    this.paymentUrl,
  });

  final bool success;
  final String message;
  final List<OrderModel> orders;
  final String? paymentUrl;

  OrderModel? get order => orders.isEmpty ? null : orders.first;

  factory CheckoutResponseModel.fromJson(Map<String, dynamic> json) {
    return CheckoutResponseModel(
      success: json['success'] != false,
      message: (json['message'] ?? '').toString(),
      orders: _ordersFrom(json),
      paymentUrl: json['payment_url']?.toString(),
    );
  }

  static List<OrderModel> _ordersFrom(Map<String, dynamic> json) {
    final source = json['orders'] is List
        ? json['orders'] as List
        : json['order'] is Map<String, dynamic>
            ? [json['order']]
            : null;
    if (source == null) {
      throw const FormatException('Order response is missing orders.');
    }
    return source.map((item) {
      if (item is! Map<String, dynamic>) {
        throw const FormatException('Order response contains malformed data.');
      }
      return OrderModel.fromJson(item);
    }).toList(growable: false);
  }
}

class OrderModel {
  const OrderModel({
    required this.id,
    required this.orderNumber,
    required this.status,
    required this.paymentStatus,
    required this.grandTotal,
  });

  final int id;
  final String orderNumber;
  final String status;
  final String paymentStatus;
  final double grandTotal;

  factory OrderModel.fromJson(Map<String, dynamic> json) {
    return OrderModel(
      id: _toInt(json['id']),
      orderNumber: (json['order_number'] ?? '').toString(),
      status: (json['status'] ?? json['order_status'] ?? '').toString(),
      paymentStatus: (json['payment_status'] ?? '').toString(),
      grandTotal: _toDouble(json['grand_total'] ?? json['total_amount']),
    );
  }

  bool get isPaid => paymentStatus.toLowerCase() == 'paid';

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
