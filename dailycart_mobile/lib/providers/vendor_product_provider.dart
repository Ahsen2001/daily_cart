import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/vendor_product_model.dart';
import '../services/auth_api_service.dart';
import '../services/vendor_product_api_service.dart';

final vendorProductApiServiceProvider =
    Provider<VendorProductApiService>((ref) {
  return VendorProductApiService();
});

final vendorProductProvider =
    ChangeNotifierProvider<VendorProductProvider>((ref) {
  return VendorProductProvider(ref.watch(vendorProductApiServiceProvider));
});

class VendorProductProvider extends ChangeNotifier {
  VendorProductProvider(this._apiService);

  final VendorProductApiService _apiService;

  List<VendorProductModel> products = const [];
  VendorProductModel? selectedProduct;
  bool isLoading = false;
  String? errorMessage;

  Future<void> getVendorProducts({String status = 'all'}) async {
    await _run(() async {
      products = await _apiService.getVendorProducts(status: status);
    });
  }

  Future<void> getProductDetails(int productId) async {
    await _run(() async {
      selectedProduct = await _apiService.getProductDetails(productId);
    });
  }

  Future<bool> addProduct(VendorProductModel product) async {
    return _run(() async {
      final created = await _apiService.addProduct(product);
      products = [created, ...products];
      selectedProduct = created;
    });
  }

  Future<bool> updateProduct(VendorProductModel product) async {
    return _run(() async {
      final updated = await _apiService.updateProduct(product);
      selectedProduct = updated;
      products = products
          .map((item) => item.id == updated.id ? updated : item)
          .toList(growable: false);
    });
  }

  Future<bool> deleteProduct(int productId) async {
    return _run(() async {
      await _apiService.deleteProduct(productId);
      products = products
          .where((item) => item.id != productId)
          .toList(growable: false);
    });
  }

  Future<bool> uploadProductImages({
    required int productId,
    required List<String> imagePaths,
  }) async {
    return _run(() async {
      selectedProduct = await _apiService.uploadProductImages(
        productId: productId,
        imagePaths: imagePaths,
      );
    });
  }

  Future<bool> updateInventory({
    required int productId,
    required int stockQuantity,
    DateTime? expiryDate,
  }) async {
    return _run(() async {
      selectedProduct = await _apiService.updateInventory(
        productId: productId,
        stockQuantity: stockQuantity,
        expiryDate: expiryDate,
      );
      products = products
          .map((item) => item.id == productId ? selectedProduct! : item)
          .toList(growable: false);
    });
  }

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
