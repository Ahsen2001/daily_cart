class CheckoutResponseModel {
  const CheckoutResponseModel({
    required this.success,
    required this.message,
    required this.order,
    this.paymentUrl,
  });

  final bool success;
  final String message;
  final OrderModel order;
  final String? paymentUrl;

  factory CheckoutResponseModel.fromJson(Map<String, dynamic> json) {
    return CheckoutResponseModel(
      success: json['success'] == true,
      message: (json['message'] ?? '').toString(),
      order: OrderModel.fromJson(
        json['order'] is Map<String, dynamic>
            ? json['order'] as Map<String, dynamic>
            : const {},
      ),
      paymentUrl: json['payment_url']?.toString(),
    );
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
      status: (json['status'] ?? '').toString(),
      paymentStatus: (json['payment_status'] ?? '').toString(),
      grandTotal: _toDouble(json['grand_total']),
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
