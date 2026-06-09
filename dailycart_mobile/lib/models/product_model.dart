import 'category_model.dart';

class ProductModel {
  const ProductModel({
    required this.id,
    required this.name,
    required this.price,
    required this.image,
    this.discountPrice,
    this.rating = 0,
    this.vendorName = '',
    this.brand = '',
    this.categoryName = '',
    this.description = '',
    this.stockQuantity = 0,
    this.sku = '',
    this.barcode = '',
    this.status = 'active',
    this.approvalStatus = 'approved',
    this.images = const [],
    this.variants = const [],
    this.reviews = const [],
    this.similarProducts = const [],
  });

  final int id;
  final String name;
  final double price;
  final double? discountPrice;
  final String image;
  final double rating;
  final String vendorName;
  final String brand;
  final String categoryName;
  final String description;
  final int stockQuantity;
  final String sku;
  final String barcode;
  final String status;
  final String approvalStatus;
  final List<ProductImageModel> images;
  final List<ProductVariantModel> variants;
  final List<ReviewModel> reviews;
  final List<ProductModel> similarProducts;

  factory ProductModel.fromJson(Map<String, dynamic> json) {
    final category = json['category'];
    final vendor = json['vendor'];

    return ProductModel(
      id: _toInt(json['id']),
      name: (json['name'] ?? '').toString(),
      price: _toDouble(json['price']),
      discountPrice: _nullableDouble(json['discount_price']),
      image: (json['image'] ?? json['image_url'] ?? '').toString(),
      rating: _toDouble(json['rating'] ?? json['average_rating']),
      vendorName: vendor is Map<String, dynamic>
          ? (vendor['name'] ?? '').toString()
          : (json['vendor_name'] ?? '').toString(),
      brand: (json['brand'] ?? json['brand_name'] ?? '').toString(),
      categoryName: category is Map<String, dynamic>
          ? CategoryModel.fromJson(category).name
          : (json['category_name'] ?? '').toString(),
      description: (json['description'] ?? '').toString(),
      stockQuantity: _toInt(json['stock'] ?? json['stock_quantity']),
      sku: (json['sku'] ?? '').toString(),
      barcode: (json['barcode'] ?? '').toString(),
      status: (json['status'] ?? 'active').toString(),
      approvalStatus: (json['approval_status'] ??
              json['approvalStatus'] ??
              json['moderation_status'] ??
              'approved')
          .toString(),
      images: _listFrom(json['images'])
          .map(ProductImageModel.fromJson)
          .toList(growable: false),
      variants: _listFrom(json['variants'])
          .map(ProductVariantModel.fromJson)
          .toList(growable: false),
      reviews: _listFrom(json['reviews'])
          .map(ReviewModel.fromJson)
          .toList(growable: false),
      similarProducts: _listFrom(json['similar_products'])
          .map(ProductModel.fromJson)
          .toList(growable: false),
    );
  }

  bool get hasDiscount => discountPrice != null && discountPrice! < price;

  double get displayPrice => hasDiscount ? discountPrice! : price;

  bool get isAvailable => stockQuantity > 0;

  bool get isVisibleForCustomer {
    return status.toLowerCase() == 'active' &&
        approvalStatus.toLowerCase() == 'approved';
  }

  List<String> get imageUrls {
    final urls = images.map((item) => item.url).where((url) => url.isNotEmpty);
    if (urls.isNotEmpty) {
      return urls.toList(growable: false);
    }
    return image.isEmpty ? const [] : [image];
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

  static double? _nullableDouble(Object? value) {
    if (value == null || value.toString().isEmpty) {
      return null;
    }
    return _toDouble(value);
  }
}

class ProductImageModel {
  const ProductImageModel({
    required this.id,
    required this.url,
  });

  final int id;
  final String url;

  factory ProductImageModel.fromJson(Map<String, dynamic> json) {
    return ProductImageModel(
      id: ProductModel._toInt(json['id']),
      url: (json['url'] ?? json['image'] ?? json['image_url'] ?? '').toString(),
    );
  }
}

class ProductVariantModel {
  const ProductVariantModel({
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

  factory ProductVariantModel.fromJson(Map<String, dynamic> json) {
    return ProductVariantModel(
      id: ProductModel._toInt(json['id']),
      name: (json['name'] ?? json['variant_name'] ?? '').toString(),
      value: (json['value'] ?? json['variant_value'] ?? '').toString(),
      price: ProductModel._nullableDouble(json['price']),
      stockQuantity: ProductModel._toInt(json['stock'] ?? json['stock_quantity']),
    );
  }
}

class ReviewModel {
  const ReviewModel({
    required this.id,
    required this.userName,
    required this.rating,
    required this.comment,
  });

  final int id;
  final String userName;
  final double rating;
  final String comment;

  factory ReviewModel.fromJson(Map<String, dynamic> json) {
    return ReviewModel(
      id: ProductModel._toInt(json['id']),
      userName: (json['user_name'] ?? json['customer_name'] ?? 'Customer')
          .toString(),
      rating: ProductModel._toDouble(json['rating']),
      comment: (json['comment'] ?? json['review'] ?? '').toString(),
    );
  }
}
