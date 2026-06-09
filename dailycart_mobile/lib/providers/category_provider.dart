import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/category_model.dart';
import '../services/category_api_service.dart';

final categoryApiServiceProvider = Provider<CategoryApiService>((ref) {
  return CategoryApiService();
});

final categoryProvider = ChangeNotifierProvider<CategoryProvider>((ref) {
  return CategoryProvider(ref.watch(categoryApiServiceProvider));
});

class CategoryProvider extends ChangeNotifier {
  CategoryProvider(this._apiService);

  final CategoryApiService _apiService;

  List<CategoryModel> categories = const [];
  bool isLoading = false;
  String? errorMessage;

  Future<void> getCategories() async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();

    try {
      final apiCategories = await _apiService.getCategories();
      categories = apiCategories.isEmpty ? _fallbackCategories : apiCategories;
    } catch (error) {
      errorMessage = error.toString();
      categories = _fallbackCategories;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  static const _fallbackCategories = [
    CategoryModel(id: 1, name: 'Grocery', image: '', productCount: 120),
    CategoryModel(id: 2, name: 'Vegetables', image: '', productCount: 86),
    CategoryModel(id: 3, name: 'Fruits', image: '', productCount: 74),
    CategoryModel(id: 4, name: 'Household', image: '', productCount: 95),
    CategoryModel(id: 5, name: 'Powder Products', image: '', productCount: 38),
    CategoryModel(id: 6, name: 'Frozen Food', image: '', productCount: 42),
    CategoryModel(id: 7, name: 'Bakery', image: '', productCount: 31),
    CategoryModel(id: 8, name: 'Beverages', image: '', productCount: 64),
    CategoryModel(id: 9, name: 'Pharmacy', image: '', productCount: 52),
    CategoryModel(id: 10, name: 'Baby Care', image: '', productCount: 28),
    CategoryModel(id: 11, name: 'Personal Care', image: '', productCount: 77),
    CategoryModel(id: 12, name: 'Pet Supplies', image: '', productCount: 19),
  ];
}
