import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/vendor_store_model.dart';
import '../services/auth_api_service.dart';
import '../services/vendor_store_api_service.dart';

final vendorStoreApiServiceProvider = Provider<VendorStoreApiService>(
  (ref) => VendorStoreApiService(),
);
final vendorStoreProvider = ChangeNotifierProvider<VendorStoreProvider>(
  (ref) => VendorStoreProvider(ref.watch(vendorStoreApiServiceProvider)),
);

class VendorStoreProvider extends ChangeNotifier {
  VendorStoreProvider(this._api);
  final VendorStoreApiService _api;
  VendorStoreModel? store;
  VendorStoreAnalyticsModel? analytics;
  bool isLoading = false;
  String? errorMessage;

  Future<bool> load() => _run(() async {
    final values = await Future.wait([_api.getStore(), _api.getAnalytics()]);
    store = values[0] as VendorStoreModel;
    analytics = values[1] as VendorStoreAnalyticsModel;
  });

  Future<bool> save(
    Map<String, dynamic> fields, {
    String? logoPath,
    String? coverPath,
  }) => _run(() async {
    store = await _api.updateStore(
      fields,
      logoPath: logoPath,
      coverPath: coverPath,
    );
  });

  Future<bool> addBanner(
    String imagePath, {
    String title = '',
    String linkUrl = '',
  }) => _run(() async {
    await _api.uploadBanner(imagePath, title: title, linkUrl: linkUrl);
    if (store != null) store = await _api.getStore();
  });

  Future<bool> deleteBanner(int id) => _run(() async {
    await _api.deleteBanner(id);
    if (store != null) store = await _api.getStore();
  });

  Future<bool> _run(Future<void> Function() action) async {
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
