import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/product_model.dart';
import '../services/product_api_service.dart';

final productApiServiceProvider = Provider<ProductApiService>((ref) {
  return ProductApiService();
});

final productProvider = ChangeNotifierProvider<ProductProvider>((ref) {
  return ProductProvider(ref.watch(productApiServiceProvider));
});

class ProductProvider extends ChangeNotifier {
  ProductProvider(this._apiService);

  final ProductApiService _apiService;

  List<ProductModel> products = const [];
  List<ProductModel> featuredProducts = const [];
  List<ProductModel> bestSellingProducts = const [];
  List<ProductModel> newArrivals = const [];
  List<ProductModel> flashDeals = const [];
  List<ProductModel> recommendedProducts = const [];
  List<ProductModel> recentlyViewedProducts = const [];
  ProductModel? selectedProduct;
  bool isLoading = false;
  String? errorMessage;
  String selectedSort = 'latest';

  Future<void> loadHomeProducts() async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();

    try {
      // Load the home shelves together so the first screen feels quick.
      final results = await Future.wait([
        _apiService.getFeaturedProducts(),
        _apiService.getBestSellingProducts(),
        _apiService.getNewArrivals(),
        _apiService.getFlashDeals(),
        _apiService.getRecommendedProducts(),
      ]);

      featuredProducts = results[0];
      bestSellingProducts = results[1];
      newArrivals = results[2];
      flashDeals = results[3];
      recommendedProducts = results[4];
      recentlyViewedProducts = featuredProducts.take(4).toList(growable: false);
    } catch (error) {
      errorMessage = error.toString();
      featuredProducts = const [];
      bestSellingProducts = const [];
      newArrivals = const [];
      flashDeals = const [];
      recommendedProducts = const [];
      recentlyViewedProducts = const [];
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> getProducts({
    int? categoryId,
    double? minPrice,
    double? maxPrice,
    double? rating,
    bool? available,
    String? brand,
    String? sort,
    String? query,
  }) async {
    isLoading = true;
    errorMessage = null;
    selectedSort = sort ?? selectedSort;
    notifyListeners();

    try {
      final apiProducts = await _apiService.getProducts(
        categoryId: categoryId,
        minPrice: minPrice,
        maxPrice: maxPrice,
        rating: rating,
        available: available,
        brand: brand,
        sort: selectedSort,
        query: query,
      );
      products = apiProducts;
    } catch (error) {
      errorMessage = error.toString();
      products = const [];
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> getProductDetails(int productId) async {
    isLoading = true;
    errorMessage = null;
    selectedProduct = null;
    notifyListeners();

    try {
      selectedProduct = await _apiService.getProductDetails(productId);
    } catch (error) {
      errorMessage = error.toString();
      selectedProduct = null;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  void addRecentlyViewed(ProductModel product) {
    // Keep the newest viewed item first and avoid duplicates.
    recentlyViewedProducts = [
      product,
      ...recentlyViewedProducts.where((item) => item.id != product.id),
    ].take(6).toList(growable: false);
    notifyListeners();
  }

}
