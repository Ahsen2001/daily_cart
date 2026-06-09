import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/product_model.dart';
import '../models/wishlist_model.dart';
import '../services/auth_api_service.dart';
import '../services/wishlist_api_service.dart';
import 'cart_provider.dart';

final wishlistApiServiceProvider = Provider<WishlistApiService>((ref) {
  return WishlistApiService();
});

final wishlistProvider = ChangeNotifierProvider<WishlistProvider>((ref) {
  return WishlistProvider(
    apiService: ref.watch(wishlistApiServiceProvider),
    ref: ref,
  );
});

class WishlistProvider extends ChangeNotifier {
  WishlistProvider({
    required WishlistApiService apiService,
    required Ref ref,
  })  : _apiService = apiService,
        _ref = ref;

  final WishlistApiService _apiService;
  final Ref _ref;

  List<WishlistModel> wishlistItems = const [];
  bool isLoading = false;
  String? errorMessage;

  Future<void> getWishlist() async {
    await _runWishlistAction(() async {
      wishlistItems = await _apiService.getWishlist();
    });
  }

  Future<bool> addToWishlist(ProductModel product) async {
    if (!product.isVisibleForCustomer) {
      errorMessage = 'Only approved and active products can be added.';
      notifyListeners();
      return false;
    }
    if (wishlistItems.any((item) => item.productId == product.id)) {
      errorMessage = 'This product is already in your wishlist.';
      notifyListeners();
      return false;
    }

    return _runWishlistAction(() async {
      await _apiService.addToWishlist(product.id);
      wishlistItems = await _apiService.getWishlist();
    });
  }

  Future<bool> removeFromWishlist(int productId) async {
    return _runWishlistAction(() async {
      await _apiService.removeFromWishlist(productId);
      wishlistItems = wishlistItems
          .where((item) => item.productId != productId)
          .toList(growable: false);
    });
  }

  Future<bool> moveToCart(WishlistModel item) async {
    if (!item.product.isAvailable) {
      errorMessage = 'This product is out of stock.';
      notifyListeners();
      return false;
    }

    return _runWishlistAction(() async {
      await _apiService.moveToCart(item.productId);
      await _ref.read(cartProvider).getCart();
      wishlistItems = wishlistItems
          .where((wishlistItem) => wishlistItem.productId != item.productId)
          .toList(growable: false);
    });
  }

  Future<bool> _runWishlistAction(Future<void> Function() action) async {
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
