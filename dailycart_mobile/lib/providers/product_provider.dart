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

      featuredProducts = _orFallback(results[0]);
      bestSellingProducts = _orFallback(results[1]);
      newArrivals = _orFallback(results[2]);
      flashDeals = _orFallback(results[3]);
      recommendedProducts = _orFallback(results[4]);
      recentlyViewedProducts = featuredProducts.take(4).toList(growable: false);
    } catch (error) {
      errorMessage = error.toString();
      // Fallback data keeps the UI testable while backend endpoints are being
      // connected or if the development API is temporarily unavailable.
      featuredProducts = _fallbackProducts;
      bestSellingProducts = _fallbackProducts.reversed.toList(growable: false);
      newArrivals = _fallbackProducts.take(3).toList(growable: false);
      flashDeals = _fallbackProducts.skip(1).take(3).toList(growable: false);
      recommendedProducts = _fallbackProducts;
      recentlyViewedProducts = _fallbackProducts.take(2).toList(growable: false);
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
      products = _orFallback(apiProducts);
    } catch (error) {
      errorMessage = error.toString();
      products = _fallbackProducts;
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
      selectedProduct = _fallbackProducts.firstWhere(
        (product) => product.id == productId,
        orElse: () => _fallbackProducts.first,
      );
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

  List<ProductModel> _orFallback(List<ProductModel> items) {
    return items.isEmpty ? _fallbackProducts : items;
  }

  static const _fallbackProducts = [
    ProductModel(
      id: 1,
      name: 'Fresh Carrot',
      price: 250,
      discountPrice: 200,
      image: '',
      rating: 4.8,
      vendorName: 'DailyCart Fresh',
      brand: 'DailyCart',
      categoryName: 'Vegetables',
      description: 'Fresh carrots selected from trusted vendors.',
      stockQuantity: 40,
    ),
    ProductModel(
      id: 2,
      name: 'Green Apples',
      price: 1500,
      discountPrice: 1350,
      image: '',
      rating: 4.7,
      vendorName: 'Fruit House',
      brand: 'Fruit House',
      categoryName: 'Fruits',
      description: 'Crisp green apples packed for freshness.',
      stockQuantity: 25,
    ),
    ProductModel(
      id: 3,
      name: 'Wholemeal Bread',
      price: 520,
      image: '',
      rating: 4.5,
      vendorName: 'Daily Bakery',
      brand: 'Daily Bakery',
      categoryName: 'Bakery',
      description: 'Soft wholemeal bread baked daily.',
      stockQuantity: 18,
    ),
    ProductModel(
      id: 4,
      name: 'Fresh Milk 1L',
      price: 430,
      image: '',
      rating: 4.6,
      vendorName: 'Lanka Dairy',
      brand: 'Lanka Dairy',
      categoryName: 'Beverages',
      description: 'Fresh milk for daily family use.',
      stockQuantity: 32,
    ),
  ];
}
