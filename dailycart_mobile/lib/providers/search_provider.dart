import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/product_model.dart';
import '../services/search_api_service.dart';

final searchApiServiceProvider = Provider<SearchApiService>((ref) {
  return SearchApiService();
});

final searchProvider = ChangeNotifierProvider<SearchProvider>((ref) {
  return SearchProvider(ref.watch(searchApiServiceProvider));
});

class SearchProvider extends ChangeNotifier {
  SearchProvider(this._apiService);

  final SearchApiService _apiService;

  List<ProductModel> searchResults = const [];
  List<String> recentSearches = const [];
  List<String> popularSearches = const [
    'Rice',
    'Carrot',
    'Milk',
    'Bread',
    'Baby care',
  ];
  bool isLoading = false;
  String? errorMessage;

  Future<void> searchProducts(String query) async {
    final trimmedQuery = query.trim();
    if (trimmedQuery.isEmpty) {
      searchResults = const [];
      notifyListeners();
      return;
    }

    isLoading = true;
    errorMessage = null;
    notifyListeners();

    try {
      searchResults = await _apiService.searchProducts(trimmedQuery);
      _saveRecentSearch(trimmedQuery);
    } catch (error) {
      errorMessage = error.toString();
      searchResults = const [];
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  void clearResults() {
    searchResults = const [];
    errorMessage = null;
    notifyListeners();
  }

  void _saveRecentSearch(String query) {
    recentSearches = [
      query,
      ...recentSearches.where(
        (item) => item.toLowerCase() != query.toLowerCase(),
      ),
    ].take(8).toList(growable: false);
  }
}
