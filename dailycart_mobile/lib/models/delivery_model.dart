class DeliveryModel {
  const DeliveryModel({
    required this.id,
    required this.orderNumber,
    required this.status,
    this.customerName = '',
    this.customerPhone = '',
    this.deliveryAddress = '',
    this.paymentMethod = '',
    this.paymentStatus = '',
    this.scheduledDeliveryTime,
    this.totalAmount = 0,
    this.latitude,
    this.longitude,
    this.items = const [],
    this.proofs = const [],
    this.acceptedAt,
    this.failedReason = '',
  });

  final int id;
  final String orderNumber;
  final String status;
  final String customerName;
  final String customerPhone;
  final String deliveryAddress;
  final String paymentMethod;
  final String paymentStatus;
  final DateTime? scheduledDeliveryTime;
  final double totalAmount;
  final double? latitude;
  final double? longitude;
  final List<DeliveryItemModel> items;
  final List<DeliveryProofSummaryModel> proofs;
  final DateTime? acceptedAt;
  final String failedReason;

  bool get canAccept => status.toLowerCase() == 'assigned';
  bool get canMarkPickedUp => status.toLowerCase() == 'accepted';
  bool get canMarkOnTheWay => status.toLowerCase() == 'picked_up';
  bool get canMarkDelivered => status.toLowerCase() == 'on_the_way';
  bool get canMarkFailed {
    final value = status.toLowerCase();
    return value != 'delivered' && value != 'failed' && value != 'cancelled';
  }

  factory DeliveryModel.fromJson(Map<String, dynamic> json) {
    final order = json['order'];
    final customer = json['customer'] ??
        json['user'] ??
        (order is Map<String, dynamic> ? order['customer'] : null);
    final source = order is Map<String, dynamic> ? order : json;

    return DeliveryModel(
      id: _toInt(json['id'] ?? source['delivery_id']),
      orderNumber: (source['order_number'] ?? json['order_number'] ?? '')
          .toString(),
      status: (json['status'] ?? source['delivery_status'] ?? 'assigned')
          .toString(),
      customerName: (json['customer_name'] ??
              source['customer_name'] ??
              (customer is Map<String, dynamic> ? customer['name'] : null) ??
              '')
          .toString(),
      customerPhone: (json['customer_phone'] ??
              source['customer_phone'] ??
              (customer is Map<String, dynamic> ? customer['phone'] : null) ??
              '')
          .toString(),
      deliveryAddress:
          (json['delivery_address'] ?? source['delivery_address'] ?? '').toString(),
      paymentMethod: (source['payment_method'] ?? '').toString(),
      paymentStatus: (source['payment_status'] ?? '').toString(),
      scheduledDeliveryTime: _nullableDate(
        source['scheduled_delivery_at'] ??
            source['scheduled_delivery_time'] ??
            json['scheduled_at'] ??
            source['scheduled_at'],
      ),
      totalAmount: _toDouble(source['grand_total'] ?? source['total_amount']),
      latitude: _nullableDouble(
        json['latitude'] ?? source['delivery_latitude'] ?? source['latitude'],
      ),
      longitude: _nullableDouble(
        json['longitude'] ??
            source['delivery_longitude'] ??
            source['longitude'],
      ),
      items: _listFrom(source['items'] ?? source['order_items'])
          .map(DeliveryItemModel.fromJson)
          .toList(growable: false),
      proofs: _listFrom(json['proofs'])
          .map(DeliveryProofSummaryModel.fromJson)
          .toList(growable: false),
      acceptedAt: _nullableDate(json['accepted_at']),
      failedReason: (json['failed_reason'] ?? '').toString(),
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

  static double? _nullableDouble(Object? value) {
    if (value == null || value.toString().isEmpty) return null;
    return _toDouble(value);
  }

  static DateTime? _nullableDate(Object? value) {
    if (value == null || value.toString().isEmpty) return null;
    return DateTime.tryParse(value.toString());
  }
}

class DeliveryItemModel {
  const DeliveryItemModel({
    required this.productName,
    required this.quantity,
    required this.subtotal,
  });

  final String productName;
  final int quantity;
  final double subtotal;

  factory DeliveryItemModel.fromJson(Map<String, dynamic> json) {
    return DeliveryItemModel(
      productName: (json['product_name'] ?? json['name'] ?? '').toString(),
      quantity: DeliveryModel._toInt(json['quantity']),
      subtotal: DeliveryModel._toDouble(
        json['total_price'] ?? json['subtotal'],
      ),
    );
  }
}

class DeliveryProofSummaryModel {
  const DeliveryProofSummaryModel({
    required this.id,
    required this.proofImage,
    required this.customerSignature,
    required this.note,
    this.submittedAt,
  });

  final int id;
  final String proofImage;
  final String customerSignature;
  final String note;
  final DateTime? submittedAt;

  factory DeliveryProofSummaryModel.fromJson(Map<String, dynamic> json) {
    return DeliveryProofSummaryModel(
      id: DeliveryModel._toInt(json['id']),
      proofImage: (json['proof_image'] ?? '').toString(),
      customerSignature: (json['customer_signature'] ?? '').toString(),
      note: (json['note'] ?? '').toString(),
      submittedAt: DeliveryModel._nullableDate(json['submitted_at']),
    );
  }
}
