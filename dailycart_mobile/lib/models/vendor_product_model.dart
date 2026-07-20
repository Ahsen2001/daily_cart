class VendorProductModel {
  const VendorProductModel({
    required this.id,
    required this.name,
    this.categoryId = 0,
    this.categoryName = '',
    this.brand = '',
    this.description = '',
    this.price = 0,
    this.discountPrice,
    this.unitType = '',
    this.weight = '',
    this.sku = '',
    this.barcode = '',
    this.stockQuantity = 0,
    this.expiryDate,
    this.status = 'pending',
    this.image = '',
    this.images = const [],
    this.variants = const [],
    this.gallery = const [],
    this.isSubscriptionEligible = false,
    this.lowStockThreshold = 5,
    this.rejectionReason = '',
  });

  final int id;
  final int categoryId;
  final String categoryName;
  final String name;
  final String brand;
  final String description;
  final double price;
  final double? discountPrice;
  final String unitType;
  final String weight;
  final String sku;
  final String barcode;
  final int stockQuantity;
  final DateTime? expiryDate;
  final String status;
  final String image;
  final List<String> images;
  final List<VendorProductVariantModel> variants;
  final List<VendorProductImageModel> gallery;
  final bool isSubscriptionEligible;
  final int lowStockThreshold;
  final String rejectionReason;

  bool get isLowStock => stockQuantity <= 5;

  factory VendorProductModel.fromJson(Map<String, dynamic> json) {
    final category = json['category'];
    return VendorProductModel(
      id: _toInt(json['id']),
      categoryId: _toInt(
        json['category_id'] ??
            (category is Map<String, dynamic> ? category['id'] : null),
      ),
      categoryName: (json['category_name'] ??
              (category is Map<String, dynamic> ? category['name'] : null) ??
              '')
          .toString(),
      name: (json['name'] ?? json['product_name'] ?? '').toString(),
      brand: (json['brand'] ?? '').toString(),
      description: (json['description'] ?? '').toString(),
      price: _toDouble(json['price']),
      discountPrice: _nullableDouble(json['discount_price']),
      unitType: (json['unit_type'] ?? json['unit'] ?? '').toString(),
      weight: (json['weight'] ?? '').toString(),
      sku: (json['sku'] ?? '').toString(),
      barcode: (json['barcode'] ?? '').toString(),
      stockQuantity: _toInt(json['stock_quantity'] ?? json['stock']),
      expiryDate: _nullableDate(json['expiry_date']),
      status: (json['status'] ?? 'pending').toString(),
      image: (json['image'] ?? json['image_url'] ?? '').toString(),
      images: _stringList(json['images']),
      variants: _listFrom(json['variants'])
          .map(VendorProductVariantModel.fromJson)
          .toList(growable: false),
      gallery: _listFrom(json['images'])
          .map(VendorProductImageModel.fromJson)
          .toList(growable: false),
      isSubscriptionEligible:
          json['is_subscription_eligible'] == true ||
              json['is_subscription_eligible']?.toString() == '1',
      lowStockThreshold: _toInt(json['low_stock_threshold'] ?? 5),
      rejectionReason: (json['rejection_reason'] ?? '').toString(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'category_id': categoryId,
      'name': name,
      'brand': brand,
      'description': description,
      'price': price,
      'discount_price': discountPrice,
      'unit_type': unitType,
      'weight': weight,
      'sku': sku,
      'barcode': barcode,
      'stock_quantity': stockQuantity,
      'expiry_date': expiryDate?.toIso8601String(),
      'is_subscription_eligible': isSubscriptionEligible,
    };
  }

  static List<Map<String, dynamic>> _listFrom(Object? value) {
    if (value is List) {
      return value.whereType<Map<String, dynamic>>().toList(growable: false);
    }
    return const [];
  }

  static List<String> _stringList(Object? value) {
    if (value is List) {
      return value.map((item) {
        if (item is Map<String, dynamic>) {
          return (item['url'] ?? item['image'] ?? item['image_url'] ?? '')
              .toString();
        }
        return item.toString();
      }).where((item) => item.isNotEmpty).toList(growable: false);
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

  static double? _nullableDouble(Object? value) {
    if (value == null || value.toString().isEmpty) {
      return null;
    }
    return _toDouble(value);
  }

  static DateTime? _nullableDate(Object? value) {
    if (value == null || value.toString().isEmpty) {
      return null;
    }
    return DateTime.tryParse(value.toString());
  }
}

class VendorProductImageModel {
  const VendorProductImageModel({
    required this.id,
    required this.url,
    this.isPrimary = false,
  });

  final int id;
  final String url;
  final bool isPrimary;

  factory VendorProductImageModel.fromJson(Map<String, dynamic> json) {
    return VendorProductImageModel(
      id: VendorProductModel._toInt(json['id']),
      url: (json['url'] ?? '').toString(),
      isPrimary: json['is_primary'] == true,
    );
  }
}

class VendorProductVariantModel {
  const VendorProductVariantModel({
    required this.id,
    required this.name,
    required this.value,
    this.price,
    this.stockQuantity = 0,
  });

  final int id;
  final String name;
  final String value;
  final double? price;
  final int stockQuantity;

  factory VendorProductVariantModel.fromJson(Map<String, dynamic> json) {
    return VendorProductVariantModel(
      id: VendorProductModel._toInt(json['id']),
      name: (json['name'] ?? '').toString(),
      value: (json['value'] ?? '').toString(),
      price: VendorProductModel._nullableDouble(json['price']),
      stockQuantity: VendorProductModel._toInt(
        json['stock_quantity'] ?? json['stock'],
      ),
    );
  }
}
