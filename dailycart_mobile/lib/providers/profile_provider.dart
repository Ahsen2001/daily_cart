import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/profile_model.dart';
import '../services/auth_api_service.dart';
import '../services/profile_api_service.dart';

final profileApiServiceProvider = Provider<ProfileApiService>((ref) {
  return ProfileApiService();
});

final profileProvider = ChangeNotifierProvider<ProfileProvider>((ref) {
  return ProfileProvider(ref.watch(profileApiServiceProvider));
});

class ProfileProvider extends ChangeNotifier {
  ProfileProvider(this._apiService);

  final ProfileApiService _apiService;

  ProfileModel? user;
  String? profileImage;
  bool isLoading = false;
  String? errorMessage;

  Future<void> getProfile() async {
    await _run(() async {
      user = await _apiService.getProfile();
      profileImage = user?.profilePhoto;
    });
  }

  Future<bool> updateProfile(ProfileModel profile) async {
    return _run(() async {
      user = await _apiService.updateProfile(profile);
      profileImage = user?.profilePhoto;
    });
  }

  Future<bool> uploadProfilePhoto(String filePath) async {
    return _run(() async {
      user = await _apiService.uploadProfilePhoto(filePath);
      profileImage = user?.profilePhoto;
    });
  }

  Future<bool> changePassword({
    required String currentPassword,
    required String newPassword,
    required String confirmPassword,
  }) async {
    return _run(() async {
      await _apiService.changePassword(
        currentPassword: currentPassword,
        newPassword: newPassword,
        confirmPassword: confirmPassword,
      );
    });
  }

  Future<bool> deleteAccount(String password) {
    return _run(() => _apiService.deleteAccount(password));
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
