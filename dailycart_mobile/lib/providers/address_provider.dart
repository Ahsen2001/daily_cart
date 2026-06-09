import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/address_model.dart';
import '../services/address_api_service.dart';
import '../services/auth_api_service.dart';

final addressApiServiceProvider = Provider<AddressApiService>((ref) {
  return AddressApiService();
});

final addressProvider = ChangeNotifierProvider<AddressProvider>((ref) {
  return AddressProvider(ref.watch(addressApiServiceProvider));
});

class AddressProvider extends ChangeNotifier {
  AddressProvider(this._apiService);

  final AddressApiService _apiService;

  List<AddressModel> addresses = const [];
  AddressModel? selectedAddress;
  bool isLoading = false;
  String? errorMessage;

  Future<void> getAddresses() async {
    await _run(() async {
      addresses = await _apiService.getAddresses();
      AddressModel? defaultAddress;
      for (final address in addresses) {
        if (address.isDefault) {
          defaultAddress = address;
          break;
        }
      }
      selectedAddress =
          defaultAddress ?? (addresses.isEmpty ? null : addresses.first);
    });
  }

  Future<bool> addAddress(AddressModel address) async {
    return _run(() async {
      final saved = await _apiService.addAddress(address);
      addresses = [saved, ...addresses].toList(growable: false);
      selectedAddress ??= saved;
    });
  }

  Future<bool> updateAddress(AddressModel address) async {
    return _run(() async {
      final saved = await _apiService.updateAddress(address);
      addresses = addresses
          .map((item) => item.id == saved.id ? saved : item)
          .toList(growable: false);
      if (selectedAddress?.id == saved.id) {
        selectedAddress = saved;
      }
    });
  }

  Future<bool> deleteAddress(int addressId) async {
    return _run(() async {
      await _apiService.deleteAddress(addressId);
      addresses = addresses
          .where((item) => item.id != addressId)
          .toList(growable: false);
      if (selectedAddress?.id == addressId) {
        selectedAddress = addresses.isEmpty ? null : addresses.first;
      }
    });
  }

  Future<bool> setDefaultAddress(AddressModel address) async {
    return _run(() async {
      await _apiService.setDefaultAddress(address.id);
      addresses = addresses
          .map((item) => item.copyWith(isDefault: item.id == address.id))
          .toList(growable: false);
      selectedAddress = address.copyWith(isDefault: true);
    });
  }

  void selectAddress(AddressModel address) {
    selectedAddress = address;
    notifyListeners();
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
