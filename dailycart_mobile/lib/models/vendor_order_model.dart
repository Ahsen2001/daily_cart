class VendorOrderModel {
  const VendorOrderModel({
    required this.id,
    required this.orderNumber,
    required this.status,
    required this.paymentStatus,
    this.paymentMethod = '',
    required this.createdAt,
    this.customerName = '',
    this.customerPhone = '',
    this.deliveryAddress = '',
    this.scheduledDeliveryTime,
    this.totalAmount = 0,
    this.items = const [],
    this.riderName = '',
    this.riderPhone = '',
    this.deliveryStatus = '',
    this.timeline = const [],
  });

  final int id;
  final String orderNumber;
  final String status;
  final String paymentStatus;
  final String paymentMethod;
  final String customerName;
  final String customerPhone;
  final String deliveryAddress;
  final DateTime? scheduledDeliveryTime;
  final DateTime createdAt;
  final double totalAmount;
  final List<VendorOrderItemModel> items;
  final String riderName;
  final String riderPhone;
  final String deliveryStatus;
  final List<VendorOrderTimelineModel> timeline;

  bool get canConfirm => status.toLowerCase() == 'pending';
  bool get canPack => status.toLowerCase() == 'confirmed';
  bool get canCancel {
    final value = status.toLowerCase();
    return value != 'delivered' && value != 'refunded' && value != 'cancelled';
  }

  factory VendorOrderModel.fromJson(Map<String, dynamic> json) {
    final customer = json['customer'] ?? json['user'];
    final rider = json['rider'];
    final delivery = json['delivery'];
    return VendorOrderModel(
      id: _toInt(json['id']),
      orderNumber: (json['order_number'] ?? '').toString(),
      status: (json['order_status'] ?? json['status'] ?? 'pending').toString(),
      paymentStatus: (json['payment_status'] ?? 'pending').toString(),
      paymentMethod: (json['payment_method'] ?? '').toString(),
      customerName:
          (json['customer_name'] ??
                  (customer is Map<String, dynamic>
                      ? customer['name']
                      : null) ??
                  '')
              .toString(),
      customerPhone:
          (json['customer_phone'] ??
                  (customer is Map<String, dynamic>
                      ? customer['phone']
                      : null) ??
                  '')
              .toString(),
      deliveryAddress: (json['delivery_address'] ?? json['address'] ?? '')
          .toString(),
      scheduledDeliveryTime: _nullableDate(
        json['scheduled_delivery_at'] ??
            json['scheduled_delivery_time'] ??
            json['scheduled_at'],
      ),
      createdAt:
          DateTime.tryParse((json['created_at'] ?? '').toString()) ??
          DateTime.now(),
      totalAmount: _toDouble(json['total_amount'] ?? json['grand_total']),
      items: _listFrom(
        json['items'] ?? json['order_items'],
      ).map(VendorOrderItemModel.fromJson).toList(growable: false),
      riderName: (rider is Map ? rider['name'] ?? '' : '').toString(),
      riderPhone: (rider is Map ? rider['phone'] ?? '' : '').toString(),
      deliveryStatus: (delivery is Map ? delivery['status'] ?? '' : '')
          .toString(),
      timeline: _listFrom(
        json['timeline'],
      ).map(VendorOrderTimelineModel.fromJson).toList(growable: false),
    );
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

  static DateTime? _nullableDate(Object? value) {
    if (value == null || value.toString().isEmpty) {
      return null;
    }
    return DateTime.tryParse(value.toString());
  }
}

class VendorOrderTimelineModel {
  const VendorOrderTimelineModel({
    required this.status,
    required this.timestamp,
    this.remarks = '',
  });
  final String status;
  final String remarks;
  final DateTime? timestamp;

  factory VendorOrderTimelineModel.fromJson(Map<String, dynamic> json) =>
      VendorOrderTimelineModel(
        status: (json['status'] ?? '').toString(),
        remarks: (json['remarks'] ?? '').toString(),
        timestamp: VendorOrderModel._nullableDate(json['timestamp']),
      );
}

class VendorOrderItemModel {
  const VendorOrderItemModel({
    required this.productName,
    required this.quantity,
    required this.price,
    required this.subtotal,
    this.image = '',
  });

  final String productName;
  final String image;
  final int quantity;
  final double price;
  final double subtotal;

  factory VendorOrderItemModel.fromJson(Map<String, dynamic> json) {
    return VendorOrderItemModel(
      productName: (json['product_name'] ?? json['name'] ?? '').toString(),
      image: (json['image'] ?? json['image_url'] ?? '').toString(),
      quantity: VendorOrderModel._toInt(json['quantity']),
      price: VendorOrderModel._toDouble(json['unit_price'] ?? json['price']),
      subtotal: VendorOrderModel._toDouble(
        json['total_price'] ?? json['subtotal'],
      ),
    );
  }
}
