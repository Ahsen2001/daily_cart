import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/cart_item_model.dart';
import '../models/cart_summary_model.dart';
import '../models/product_model.dart';
import '../services/auth_api_service.dart';
import '../services/cart_api_service.dart';

final cartApiServiceProvider = Provider<CartApiService>((ref) {
  return CartApiService();
});

final cartProvider = ChangeNotifierProvider<CartProvider>((ref) {
  return CartProvider(ref.watch(cartApiServiceProvider));
});

class CartProvider extends ChangeNotifier {
  CartProvider(this._apiService);

  final CartApiService _apiService;

  List<CartItemModel> cartItems = const [];
  CartSummaryModel summary = const CartSummaryModel();
  bool isLoading = false;
  String? errorMessage;

  int get cartCount {
    return cartItems.fold<int>(0, (total, item) => total + item.quantity);
  }

  double get subtotal => summary.subtotal;
  double get discount => summary.discount;
  double get deliveryCharge => summary.deliveryCharge;
  double get serviceCharge => summary.serviceCharge;
  double get grandTotal => summary.grandTotal;

  Future<void> getCart() async {
    await _runCartAction(() async {
      final cart = await _apiService.getCart();
      _setCart(cart.items, cart.summary);
    });
  }

  Future<bool> addToCart({
    required ProductModel product,
    required int quantity,
    int? variantId,
  }) async {
    if (!product.isVisibleForCustomer) {
      errorMessage = 'Only approved and active products can be added to cart.';
      notifyListeners();
      return false;
    }
    if (!product.isAvailable) {
      errorMessage = 'This product is out of stock.';
      notifyListeners();
      return false;
    }
    if (quantity > product.stockQuantity) {
      errorMessage = 'Quantity exceeds available stock.';
      notifyListeners();
      return false;
    }

    return _runCartAction(() async {
      final cart = await _apiService.addToCart(
        productId: product.id,
        quantity: quantity,
        variantId: variantId,
      );
      _setCart(cart.items, cart.summary);
    });
  }

  Future<bool> updateCartItem({
    required CartItemModel item,
    required int quantity,
  }) async {
    if (quantity < 1) {
      return removeCartItem(item.id);
    }
    if (quantity > item.availableStock) {
      errorMessage = 'Quantity exceeds available stock.';
      notifyListeners();
      return false;
    }

    return _runCartAction(() async {
      final cart = await _apiService.updateCartItem(
        cartItemId: item.id,
        quantity: quantity,
      );
      _setCart(cart.items, cart.summary);
    });
  }

  Future<bool> increaseQuantity(CartItemModel item) {
    return updateCartItem(item: item, quantity: item.quantity + 1);
  }

  Future<bool> decreaseQuantity(CartItemModel item) {
    return updateCartItem(item: item, quantity: item.quantity - 1);
  }

  Future<bool> removeCartItem(int cartItemId) async {
    return _runCartAction(() async {
      final cart = await _apiService.removeCartItem(cartItemId);
      _setCart(cart.items, cart.summary);
    });
  }

  Future<bool> clearCart() async {
    return _runCartAction(() async {
      final cart = await _apiService.clearCart();
      _setCart(cart.items, cart.summary);
    });
  }

  void updateSummaryDiscount(double discount) {
    summary = CartSummaryModel.fromItems(
      subtotal: summary.subtotal,
      discount: discount,
      deliveryCharge: summary.deliveryCharge,
      serviceCharge: summary.serviceCharge,
    );
    notifyListeners();
  }

  void removeDiscount() {
    updateSummaryDiscount(0);
  }

  void _setCart(List<CartItemModel> items, CartSummaryModel nextSummary) {
    cartItems = items.where((item) => item.canOrder).toList(growable: false);
    summary = nextSummary;
  }

  Future<bool> _runCartAction(Future<void> Function() action) async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();

    try {
      await action();
      return true;
    } on ApiException catch (error) {
      errorMessage = error.message;
      return false;
    } catch (_) {
      errorMessage = 'Something went wrong. Please try again.';
      return false;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }
}
