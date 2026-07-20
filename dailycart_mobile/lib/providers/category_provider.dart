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
      categories = await _apiService.getCategories();
    } catch (error) {
      errorMessage = error.toString();
      categories = const [];
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

}
