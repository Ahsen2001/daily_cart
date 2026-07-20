class CartItemModel {
  const CartItemModel({
    required this.id,
    required this.productId,
    required this.name,
    required this.image,
    required this.price,
    required this.quantity,
    required this.subtotal,
    this.variant,
    this.variantId,
    this.availableStock = 999,
    this.status = 'active',
    this.approvalStatus = 'approved',
  });

  final int id;
  final int productId;
  final String name;
  final String image;
  final String? variant;
  final int? variantId;
  final double price;
  final int quantity;
  final double subtotal;
  final int availableStock;
  final String status;
  final String approvalStatus;

  factory CartItemModel.fromJson(Map<String, dynamic> json) {
    return CartItemModel(
      id: _toInt(json['id']),
      productId: _toInt(json['product_id']),
      name: (json['name'] ?? json['product_name'] ?? '').toString(),
      image: (json['image'] ?? json['image_url'] ?? '').toString(),
      variant: (json['variant'] ?? json['variant_name'])?.toString(),
      variantId: json['variant_id'] == null ? null : _toInt(json['variant_id']),
      price: _toDouble(json['price'] ?? json['unit_price']),
      quantity: _toInt(json['quantity']),
      subtotal: _toDouble(json['subtotal'] ?? json['total_price']),
      availableStock: _toInt(json['available_stock'] ?? json['stock'] ?? 999),
      status: (json['status'] ?? 'active').toString(),
      approvalStatus: (json['approval_status'] ?? 'approved').toString(),
    );
  }

  bool get canOrder {
    return const {'active', 'approved'}.contains(status.toLowerCase()) &&
        !const {'pending', 'rejected', 'inactive'}
            .contains(approvalStatus.toLowerCase()) &&
        availableStock > 0;
  }

  CartItemModel copyWith({
    int? quantity,
    double? subtotal,
  }) {
    return CartItemModel(
      id: id,
      productId: productId,
      name: name,
      image: image,
      variant: variant,
      variantId: variantId,
      price: price,
      quantity: quantity ?? this.quantity,
      subtotal: subtotal ?? this.subtotal,
      availableStock: availableStock,
      status: status,
      approvalStatus: approvalStatus,
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
