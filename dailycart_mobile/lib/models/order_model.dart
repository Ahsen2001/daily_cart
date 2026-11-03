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
    this.riderLatitude,
    this.riderLongitude,
    this.riderLocationRecordedAt,
    this.riderLocationAgeSeconds,
    this.riderLocationIsStale = false,
    this.riderLocationIsOutdated = false,
    this.riderLocationMessage = 'Waiting for rider location',
    this.deliveryStatus = '',
    this.trackingActive = false,
    this.deliveryLatitude,
    this.deliveryLongitude,
    this.subtotal = 0,
    this.discount = 0,
    this.deliveryCharge = 0,
    this.serviceCharge = 0,
    this.grandTotal = 0,
    this.items = const [],
    this.timeline = const [],
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
  final double? riderLatitude;
  final double? riderLongitude;
  final DateTime? riderLocationRecordedAt;
  final int? riderLocationAgeSeconds;
  final bool riderLocationIsStale;
  final bool riderLocationIsOutdated;
  final String riderLocationMessage;
  final String deliveryStatus;
  final bool trackingActive;
  final double? deliveryLatitude;
  final double? deliveryLongitude;
  final double subtotal;
  final double discount;
  final double deliveryCharge;
  final double serviceCharge;
  final double grandTotal;
  final List<OrderItemModel> items;
  final List<OrderTimelineEntry> timeline;

  factory OrderModel.fromJson(Map<String, dynamic> json) {
    final rider = json['rider'];
    final delivery = json['delivery'];
    final parsedDeliveryStatus = delivery is Map<String, dynamic>
        ? (delivery['status'] ?? '').toString()
        : (json['delivery_status'] ?? '').toString();
    final trackingStatus = delivery is Map<String, dynamic>
        ? (delivery['tracking_status'] ?? '').toString()
        : '';
    return OrderModel(
      id: _toInt(json['id']),
      orderNumber: (json['order_number'] ?? '').toString(),
      orderDate: _toDate(
        json['placed_at'] ?? json['created_at'] ?? json['order_date'],
      ),
      status: (json['order_status'] ?? json['status'] ?? 'pending').toString(),
      paymentStatus: (json['payment_status'] ?? 'pending').toString(),
      paymentMethod: (json['payment_method'] ?? '').toString(),
      deliveryAddress: (json['delivery_address'] ?? json['address'] ?? '')
          .toString(),
      scheduledDeliveryTime: _toNullableDate(
        json['scheduled_delivery_at'] ??
            json['scheduled_delivery_time'] ??
            json['scheduled_at'],
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
      riderLatitude: rider is Map<String, dynamic>
          ? _toNullableDouble(rider['latitude'])
          : _toNullableDouble(json['rider_latitude']),
      riderLongitude: rider is Map<String, dynamic>
          ? _toNullableDouble(rider['longitude'])
          : _toNullableDouble(json['rider_longitude']),
      riderLocationRecordedAt: rider is Map<String, dynamic>
          ? _toNullableDate(rider['location_recorded_at'])
          : _toNullableDate(json['rider_location_recorded_at']),
      riderLocationAgeSeconds: rider is Map<String, dynamic>
          ? _toNullableInt(rider['location_age_seconds'])
          : _toNullableInt(json['rider_location_age_seconds']),
      riderLocationIsStale:
          rider is Map<String, dynamic> && rider['location_is_stale'] == true,
      riderLocationIsOutdated:
          rider is Map<String, dynamic> && rider['location_is_outdated'] == true,
      riderLocationMessage: rider is Map<String, dynamic>
          ? (rider['location_message'] ?? 'Waiting for rider location')
                .toString()
          : 'Waiting for rider location',
      deliveryStatus: parsedDeliveryStatus,
      trackingActive:
          trackingStatus == 'active' &&
          !{'delivered', 'failed', 'cancelled'}.contains(parsedDeliveryStatus),
      deliveryLatitude: _toNullableDouble(json['delivery_latitude']),
      deliveryLongitude: _toNullableDouble(json['delivery_longitude']),
      subtotal: _toDouble(json['subtotal']),
      discount: _toDouble(json['discount_amount'] ?? json['discount']),
      deliveryCharge: _toDouble(
        json['delivery_fee'] ?? json['delivery_charge'],
      ),
      serviceCharge: _toDouble(json['service_charge']),
      grandTotal: _toDouble(
        json['total_amount'] ?? json['grand_total'] ?? json['total'],
      ),
      items: _listFrom(
        json['items'] ?? json['order_items'],
      ).map(OrderItemModel.fromJson).toList(growable: false),
      timeline: _listFrom(json['timeline'])
          .map(OrderTimelineEntry.fromJson)
          .toList(growable: false),
    );
  }

  bool get isPending => status.toLowerCase() == 'pending';

  bool get isCompleted => status.toLowerCase() == 'delivered';

  bool get isCancelled => status.toLowerCase() == 'cancelled';

  bool get isRefunded => status.toLowerCase() == 'refunded';

  bool get isFailed =>
      status.toLowerCase() == 'failed' || deliveryStatus == 'failed';

  bool get isTrackingTerminal =>
      isCompleted ||
      isCancelled ||
      isRefunded ||
      isFailed ||
      {'delivered', 'failed', 'cancelled'}.contains(deliveryStatus);

  bool get isActive {
    return !isTrackingTerminal;
  }

  OrderModel copyWithTracking(Map<String, dynamic> payload) {
    final tracking = payload['tracking'];
    final location = payload['location'];
    final destination = payload['destination'];
    final freshness = payload['freshness'];
    return OrderModel(
      id: id,
      orderNumber: orderNumber,
      orderDate: orderDate,
      status: (payload['order_status'] ?? status).toString(),
      paymentStatus: paymentStatus,
      paymentMethod: paymentMethod,
      deliveryAddress: destination is Map<String, dynamic>
          ? (destination['address'] ?? deliveryAddress).toString()
          : deliveryAddress,
      scheduledDeliveryTime:
          _toNullableDate(payload['scheduled_delivery_time']) ??
          scheduledDeliveryTime,
      estimatedDeliveryTime: estimatedDeliveryTime,
      riderName: (payload['rider_display_name'] ?? riderName).toString(),
      riderPhone: riderPhone,
      riderLatitude: location is Map<String, dynamic>
          ? _toNullableDouble(location['latitude'])
          : null,
      riderLongitude: location is Map<String, dynamic>
          ? _toNullableDouble(location['longitude'])
          : null,
      riderLocationRecordedAt: location is Map<String, dynamic>
          ? _toNullableDate(location['recorded_at'])
          : null,
      riderLocationAgeSeconds: freshness is Map<String, dynamic>
          ? _toNullableInt(freshness['age_seconds'])
          : null,
      riderLocationIsStale:
          freshness is Map<String, dynamic> && freshness['is_stale'] == true,
      riderLocationIsOutdated:
          freshness is Map<String, dynamic> &&
          freshness['is_outdated'] == true,
      riderLocationMessage: freshness is Map<String, dynamic>
          ? (freshness['message'] ?? 'Waiting for rider location').toString()
          : 'Waiting for rider location',
      deliveryStatus: (payload['delivery_status'] ?? deliveryStatus).toString(),
      trackingActive:
          tracking is Map<String, dynamic> && tracking['is_active'] == true,
      deliveryLatitude: destination is Map<String, dynamic>
          ? _toNullableDouble(destination['latitude'])
          : deliveryLatitude,
      deliveryLongitude: destination is Map<String, dynamic>
          ? _toNullableDouble(destination['longitude'])
          : deliveryLongitude,
      subtotal: subtotal,
      discount: discount,
      deliveryCharge: deliveryCharge,
      serviceCharge: serviceCharge,
      grandTotal: grandTotal,
      items: items,
      timeline: timeline,
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

  static double? _toNullableDouble(Object? value) {
    if (value == null || value.toString().isEmpty) return null;
    return value is num ? value.toDouble() : double.tryParse(value.toString());
  }

  static int? _toNullableInt(Object? value) {
    if (value == null || value.toString().isEmpty) return null;
    return value is num ? value.toInt() : int.tryParse(value.toString());
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

class OrderTimelineEntry {
  const OrderTimelineEntry({
    required this.status,
    this.remarks = '',
    this.timestamp,
  });

  final String status;
  final String remarks;
  final DateTime? timestamp;

  factory OrderTimelineEntry.fromJson(Map<String, dynamic> json) {
    return OrderTimelineEntry(
      status: (json['status'] ?? '').toString(),
      remarks: (json['remarks'] ?? '').toString(),
      timestamp: OrderModel._toNullableDate(
        json['timestamp'] ?? json['created_at'],
      ),
    );
  }
}

class OrderItemModel {
  const OrderItemModel({
    required this.id,
    required this.productId,
    required this.productName,
    required this.image,
    required this.quantity,
    required this.price,
    required this.subtotal,
  });

  final int id;
  final int productId;
  final String productName;
  final String image;
  final int quantity;
  final double price;
  final double subtotal;

  factory OrderItemModel.fromJson(Map<String, dynamic> json) {
    return OrderItemModel(
      id: OrderModel._toInt(json['id']),
      productId: OrderModel._toInt(json['product_id']),
      productName: (json['product_name'] ?? json['name'] ?? '').toString(),
      image: (json['image'] ?? json['image_url'] ?? '').toString(),
      quantity: OrderModel._toInt(json['quantity']),
      price: OrderModel._toDouble(json['unit_price'] ?? json['price']),
      subtotal: OrderModel._toDouble(json['total_price'] ?? json['subtotal']),
    );
  }
}
