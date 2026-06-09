import 'product_model.dart';

class WishlistModel {
  const WishlistModel({
    required this.id,
    required this.product,
  });

  final int id;
  final ProductModel product;

  int get productId => product.id;

  factory WishlistModel.fromJson(Map<String, dynamic> json) {
    final productJson = json['product'] is Map<String, dynamic>
        ? json['product'] as Map<String, dynamic>
        : json;

    return WishlistModel(
      id: _toInt(json['id'] ?? json['wishlist_id']),
      product: ProductModel.fromJson(productJson),
    );
  }

  static int _toInt(Object? value) {
    if (value is int) {
      return value;
    }
    return int.tryParse(value?.toString() ?? '') ?? 0;
  }
}
