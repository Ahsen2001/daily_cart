class OrderModel {
  const OrderModel({
    required this.id,
    required this.orderNumber,
    required this.orderDate,
    required this.status,
    required this.paymentStatus,
    required this.paymentMethod,
    required this.deliveryAddress,
    this.scheduledDeliveryTime,
    this.estimatedDeliveryTime,
    this.riderName = '',
    this.riderPhone = '',
    this.subtotal = 0,
    this.discount = 0,
    this.deliveryCharge = 0,
    this.serviceCharge = 0,
    this.grandTotal = 0,
    this.items = const [],
  });

  final int id;
  final String orderNumber;
  final DateTime orderDate;
  final String status;
  final String paymentStatus;
  final String paymentMethod;
  final String deliveryAddress;
  final DateTime? scheduledDeliveryTime;
  final DateTime? estimatedDeliveryTime;
  final String riderName;
  final String riderPhone;
  final double subtotal;
  final double discount;
  final double deliveryCharge;
  final double serviceCharge;
  final double grandTotal;
  final List<OrderItemModel> items;

  factory OrderModel.fromJson(Map<String, dynamic> json) {
    final rider = json['rider'];
    return OrderModel(
      id: _toInt(json['id']),
      orderNumber: (json['order_number'] ?? '').toString(),
      orderDate: _toDate(json['created_at'] ?? json['order_date']),
      status: (json['status'] ?? 'pending').toString(),
      paymentStatus: (json['payment_status'] ?? 'pending').toString(),
      paymentMethod: (json['payment_method'] ?? '').toString(),
      deliveryAddress:
          (json['delivery_address'] ?? json['address'] ?? '').toString(),
      scheduledDeliveryTime: _toNullableDate(
        json['scheduled_delivery_time'] ?? json['scheduled_at'],
      ),
      estimatedDeliveryTime: _toNullableDate(
        json['estimated_delivery_time'] ?? json['eta'],
      ),
      riderName: rider is Map<String, dynamic>
          ? (rider['name'] ?? '').toString()
          : (json['rider_name'] ?? '').toString(),
      riderPhone: rider is Map<String, dynamic>
          ? (rider['phone'] ?? '').toString()
          : (json['rider_phone'] ?? '').toString(),
      subtotal: _toDouble(json['subtotal']),
      discount: _toDouble(json['discount']),
      deliveryCharge: _toDouble(json['delivery_charge']),
      serviceCharge: _toDouble(json['service_charge']),
      grandTotal: _toDouble(json['grand_total'] ?? json['total']),
      items: _listFrom(json['items'] ?? json['order_items'])
          .map(OrderItemModel.fromJson)
          .toList(growable: false),
    );
  }

  bool get isPending => status.toLowerCase() == 'pending';

  bool get isCompleted => status.toLowerCase() == 'delivered';

  bool get isCancelled => status.toLowerCase() == 'cancelled';

  bool get isRefunded => status.toLowerCase() == 'refunded';

  bool get isActive {
    return !isCompleted && !isCancelled && !isRefunded;
  }

  static List<Map<String, dynamic>> _listFrom(Object? value) {
    if (value is List) {
      return value.whereType<Map<String, dynamic>>().toList(growable: false);
    }
    return const [];
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

  static DateTime _toDate(Object? value) {
    return DateTime.tryParse(value?.toString() ?? '') ?? DateTime.now();
  }

  static DateTime? _toNullableDate(Object? value) {
    if (value == null || value.toString().isEmpty) {
      return null;
    }
    return DateTime.tryParse(value.toString());
  }
}

class OrderItemModel {
  const OrderItemModel({
    required this.id,
    required this.productName,
    required this.image,
    required this.quantity,
    required this.price,
    required this.subtotal,
  });

  final int id;
  final String productName;
  final String image;
  final int quantity;
  final double price;
  final double subtotal;

  factory OrderItemModel.fromJson(Map<String, dynamic> json) {
    return OrderItemModel(
      id: OrderModel._toInt(json['id']),
      productName: (json['product_name'] ?? json['name'] ?? '').toString(),
      image: (json['image'] ?? json['image_url'] ?? '').toString(),
      quantity: OrderModel._toInt(json['quantity']),
      price: OrderModel._toDouble(json['price']),
      subtotal: OrderModel._toDouble(json['subtotal']),
    );
  }
}
