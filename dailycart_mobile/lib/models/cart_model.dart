import 'cart_item_model.dart';
import 'cart_summary_model.dart';

class CartModel {
  const CartModel({
    required this.items,
    required this.summary,
  });

  final List<CartItemModel> items;
  final CartSummaryModel summary;

  factory CartModel.fromJson(Map<String, dynamic> json) {
    final cartJson = json['cart'] is Map<String, dynamic>
        ? json['cart'] as Map<String, dynamic>
        : json;
    final itemList = cartJson['items'] is List ? cartJson['items'] as List : [];
    final items = itemList
        .whereType<Map<String, dynamic>>()
        .map(CartItemModel.fromJson)
        .toList(growable: false);

    final summaryJson = cartJson['summary'] is Map<String, dynamic>
        ? cartJson['summary'] as Map<String, dynamic>
        : <String, dynamic>{};

    return CartModel(
      items: items,
      summary: summaryJson.isEmpty
          ? CartSummaryModel.fromItems(
              subtotal: items.fold<double>(
                0,
                (total, item) => total + item.subtotal,
              ),
            )
          : CartSummaryModel.fromJson(summaryJson),
    );
  }

  static const empty = CartModel(
    items: [],
    summary: CartSummaryModel(),
  );
}
