import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/user_model.dart';
import '../models/user_role.dart';
import '../services/auth_api_service.dart';
import '../services/onboarding_service.dart';
import '../utils/secure_storage_helper.dart';

final authApiServiceProvider = Provider<AuthApiService>((ref) {
  return AuthApiService();
});

final secureStorageHelperProvider = Provider<SecureStorageHelper>((ref) {
  return SecureStorageHelper();
});

final authProvider = ChangeNotifierProvider<AuthProvider>((ref) {
  return AuthProvider(
    apiService: ref.watch(authApiServiceProvider),
    storage: ref.watch(secureStorageHelperProvider),
  );
});

final onboardingServiceProvider = Provider<OnboardingService>((ref) {
  return OnboardingService();
});

class AuthProvider extends ChangeNotifier {
  AuthProvider({
    required AuthApiService apiService,
    required SecureStorageHelper storage,
  })  : _apiService = apiService,
        _storage = storage;

  final AuthApiService _apiService;
  final SecureStorageHelper _storage;

  UserModel? user;
  String? token;
  UserRole? role;
  bool isLoading = false;
  String? errorMessage;

  bool get isAuthenticated => token != null && user != null;

  Future<AuthActionResult> login({
    required String email,
    required String password,
  }) async {
    return _runAuthAction(() async {
      final response = await _apiService.login(
        email: email,
        password: password,
      );

      if (response.requiresApproval && response.user != null) {
        user = response.user;
        role = response.user!.role;
        return AuthActionResult.pendingApproval(response.message);
      }

      final responseToken = response.token;
      final responseUser = response.user;

      if (responseToken == null || responseUser == null) {
        throw const ApiException('Invalid login response from server.');
      }

      await saveToken(responseToken);
      await _storage.saveUser(responseUser);

      token = responseToken;
      user = responseUser;
      role = responseUser.role;

      return AuthActionResult.success(
        message: response.message,
        redirectRoute: responseUser.role.homeRoute,
      );
    });
  }

  Future<AuthActionResult> register({
    required String name,
    required String email,
    required String phone,
    required String password,
    required String passwordConfirmation,
    required UserRole role,
  }) async {
    return _runAuthAction(() async {
      final response = await _apiService.register(
        name: name,
        email: email,
        phone: phone,
        password: password,
        passwordConfirmation: passwordConfirmation,
        role: role,
      );

      final responseUser = response.user;
      final responseToken = response.token;

      if (role == UserRole.vendor || role == UserRole.rider) {
        user = responseUser;
        this.role = role;
        return AuthActionResult.pendingApproval(
          role == UserRole.vendor
              ? 'Your vendor account is waiting for admin approval.'
              : 'Your rider account is waiting for admin approval.',
        );
      }

      if (responseToken != null && responseUser != null) {
        await _storage.saveAuthData(token: responseToken, user: responseUser);
        token = responseToken;
        user = responseUser;
        this.role = responseUser.role;

        return AuthActionResult.success(
          message: response.message.isEmpty
              ? 'Account created successfully.'
              : response.message,
          redirectRoute: responseUser.role.homeRoute,
        );
      }

      return AuthActionResult.success(
        message: response.message.isEmpty
            ? 'Account created successfully.'
            : response.message,
        redirectRoute: '/login',
      );
    });
  }

  Future<void> logout() async {
    final currentToken = token ?? await getToken();

    isLoading = true;
    notifyListeners();

    try {
      if (currentToken != null) {
        await _apiService.logout(currentToken);
      }
    } catch (_) {
      // Always clear the local session even if the network logout fails.
    } finally {
      await clearToken();
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> checkAuthStatus() async {
    isLoading = true;
    notifyListeners();

    token = await getToken();
    user = await _storage.getUser();
    role = user?.role ?? await _storage.getRole();

    isLoading = false;
    notifyListeners();
  }

  Future<void> refreshUser() async {
    final currentToken = token ?? await getToken();
    if (currentToken == null) {
      return;
    }

    user = await _apiService.refreshUser(currentToken);
    role = user?.role;
    if (user != null) {
      await _storage.saveUser(user!);
    }
    notifyListeners();
  }

  Future<void> saveToken(String value) {
    return _storage.saveToken(value);
  }

  Future<String?> getToken() {
    return _storage.getToken();
  }

  Future<void> clearToken() async {
    token = null;
    user = null;
    role = null;
    errorMessage = null;
    await _storage.clearAuthData();
  }

  Future<AuthActionResult> _runAuthAction(
    Future<AuthActionResult> Function() action,
  ) async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();

    try {
      return await action();
    } on ApiException catch (error) {
      if (_isPendingApprovalMessage(error.message)) {
        errorMessage = null;
        return AuthActionResult.pendingApproval(error.message);
      }

      errorMessage = error.message;
      return AuthActionResult.failure(error.message);
    } catch (_) {
      errorMessage = 'Something went wrong. Please try again.';
      return AuthActionResult.failure(errorMessage!);
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  bool _isPendingApprovalMessage(String message) {
    final normalized = message.toLowerCase();
    return normalized.contains('pending approval') ||
        normalized.contains('waiting for admin approval') ||
        normalized.contains('awaiting approval');
  }
}

class AuthActionResult {
  const AuthActionResult._({
    required this.isSuccess,
    required this.requiresApproval,
    required this.message,
    this.redirectRoute,
  });

  final bool isSuccess;
  final bool requiresApproval;
  final String message;
  final String? redirectRoute;

  factory AuthActionResult.success({
    required String message,
    String? redirectRoute,
  }) {
    return AuthActionResult._(
      isSuccess: true,
      requiresApproval: false,
      message: message,
      redirectRoute: redirectRoute,
    );
  }

  factory AuthActionResult.pendingApproval(String message) {
    return AuthActionResult._(
      isSuccess: false,
      requiresApproval: true,
      message: message,
    );
  }

  factory AuthActionResult.failure(String message) {
    return AuthActionResult._(
      isSuccess: false,
      requiresApproval: false,
      message: message,
    );
  }
}
