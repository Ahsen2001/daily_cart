import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/promotion_model.dart';
import '../models/store_model.dart';
import '../networking/api_exception.dart';
import '../services/store_api_service.dart';

final storeApiServiceProvider = Provider<StoreApiService>(
  (ref) => StoreApiService(),
);

final storeProvider = ChangeNotifierProvider<StoreProvider>(
  (ref) => StoreProvider(ref.watch(storeApiServiceProvider)),
);

class StoreProvider extends ChangeNotifier {
  StoreProvider(this._api);
  final StoreApiService _api;

  List<PromotionModel> offers = const [];
  List<StoreModel> featuredStores = const [];
  List<StoreModel> popularStores = const [];
  List<StoreModel> stores = const [];
  StoreModel? selectedStore;
  bool isLoading = false;
  bool isHomeLoading = false;
  String? errorMessage;

  Future<void> loadHomeDiscovery() async {
    isHomeLoading = true;
    notifyListeners();
    try {
      final discovery = await _api.getHomeDiscovery();
      offers = discovery.offers;
      featuredStores = discovery.featuredStores;
      popularStores = discovery.popularStores;
    } catch (error) {
      errorMessage = _message(error);
    } finally {
      isHomeLoading = false;
      notifyListeners();
    }
  }

  Future<void> getStores({String? search, bool featured = false}) async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();
    try {
      stores = await _api.getStores(search: search, featured: featured);
    } catch (error) {
      errorMessage = _message(error);
      stores = const [];
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> getStore(String slug) async {
    isLoading = true;
    errorMessage = null;
    selectedStore = null;
    notifyListeners();
    try {
      selectedStore = await _api.getStore(slug);
    } catch (error) {
      errorMessage = _message(error);
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> toggleFollow() async {
    final store = selectedStore;
    if (store == null) return false;
    final following = !store.isFollowing;
    try {
      await _api.setFollowing(store.slug, following);
      selectedStore = store.copyWith(
        isFollowing: following,
        followersCount: (store.followersCount + (following ? 1 : -1))
            .clamp(0, 1 << 31)
            .toInt(),
      );
      notifyListeners();
      return true;
    } catch (error) {
      errorMessage = _message(error);
      notifyListeners();
      return false;
    }
  }

  String _message(Object error) => error is ApiException
      ? error.message
      : 'Something went wrong. Please try again.';
}
