import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../config/app_identity.dart';
import '../models/user_model.dart';
import '../models/user_role.dart';
import '../networking/api_client.dart';
import '../routes/app_routes.dart';
import '../services/auth_api_service.dart';
import '../services/onboarding_service.dart';
import '../services/notification_service.dart';
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
  }) : _apiService = apiService,
       _storage = storage {
    ApiClient.shared.setUnauthorizedHandler(_handleUnauthorized);
  }

  final AuthApiService _apiService;
  final SecureStorageHelper _storage;

  UserModel? user;
  String? token;
  UserRole? role;
  bool isLoading = false;
  bool isInitialized = false;
  bool isSessionValidated = false;
  String? errorMessage;

  bool get isAuthenticated =>
      isSessionValidated && token != null && user != null;

  bool get requiresVerification => user?.requiresVerification == true;

  bool get requiresApproval => user?.isPendingApproval == true;

  Future<AuthActionResult> login({
    required String email,
    required String password,
  }) async {
    return _runAuthAction(() async {
      final response = await _apiService.login(
        email: email,
        password: password,
      );

      return _acceptAuthenticationResponse(response);
    });
  }

  Future<AuthActionResult> register({
    required String name,
    required String email,
    required String phone,
    required String password,
    required String passwordConfirmation,
    required UserRole role,
    Map<String, dynamic> roleData = const {},
  }) async {
    return _runAuthAction(() async {
      final response = await _apiService.register(
        name: name,
        email: email,
        phone: phone,
        password: password,
        passwordConfirmation: passwordConfirmation,
        role: role,
        roleData: roleData,
      );

      return _acceptAuthenticationResponse(response);
    });
  }

  Future<AuthActionResult> sendVerificationCode(
    VerificationChannel channel,
  ) async {
    return _runAuthAction(() async {
      final currentToken = token ?? await _storage.getToken();
      if (currentToken == null) {
        throw const ApiException('Sign in again to verify your account.');
      }

      final message = await _apiService.sendVerificationCode(
        token: currentToken,
        channel: channel,
      );

      return AuthActionResult.success(message: message);
    });
  }

  Future<AuthActionResult> verifyCode({
    required String code,
    required VerificationChannel channel,
  }) async {
    return _runAuthAction(() async {
      final currentToken = token ?? await _storage.getToken();
      if (currentToken == null) {
        throw const ApiException('Sign in again to verify your account.');
      }

      final verifiedUser = await _apiService.verifyCode(
        token: currentToken,
        code: code,
        channel: channel,
      );
      user = verifiedUser;
      role = verifiedUser.role;
      isSessionValidated = true;
      await _storage.saveUser(verifiedUser);

      if (verifiedUser.requiresVerification) {
        return AuthActionResult.verificationRequired(
          'Verification saved. Complete the remaining verification step.',
        );
      }

      if (verifiedUser.isPendingApproval) {
        return AuthActionResult.pendingApproval(
          'Verification complete. Your account is waiting for admin approval.',
        );
      }

      return AuthActionResult.success(
        message: 'Account verified successfully.',
        redirectRoute: verifiedUser.role.homeRoute,
      );
    });
  }

  Future<void> logout() async {
    final currentToken = token ?? await _storage.getToken();

    isLoading = true;
    notifyListeners();

    try {
      if (currentToken != null) {
        try {
          await NotificationService.revokeDevice();
        } catch (_) {
          // Server logout must still run and local credentials must still clear.
        }
        await _apiService.logout(currentToken);
      }
    } catch (_) {
      // Local credentials must still be removed if network logout fails.
    } finally {
      await clearToken();
      isInitialized = true;
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> checkAuthStatus() async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();

    try {
      final storedToken = await _storage.getToken();
      final expiresAt = await _storage.getTokenExpiration();

      if (storedToken == null ||
          (expiresAt != null && !expiresAt.isAfter(DateTime.now().toUtc()))) {
        await clearToken();
        return;
      }

      token = storedToken;
      final refreshedUser = await _apiService.getProfile(storedToken);
      _ensureCorrectAppRole(refreshedUser);
      user = refreshedUser;
      role = refreshedUser.role;
      isSessionValidated = true;
      await _storage.saveUser(refreshedUser);
      await _syncNotificationDevice();
    } on ApiException catch (error) {
      errorMessage = error.message;
      await clearToken();
    } catch (_) {
      errorMessage = 'Unable to validate your session. Please sign in again.';
      await clearToken();
    } finally {
      isInitialized = true;
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> refreshUser() async {
    final currentToken = token ?? await _storage.getToken();
    if (currentToken == null) {
      await clearToken();
      notifyListeners();
      return;
    }

    try {
      user = await _apiService.refreshUser(currentToken);
      role = user?.role;
      isSessionValidated = user != null;
      if (user != null) {
        await _storage.saveUser(user!);
      }
      notifyListeners();
    } on ApiException catch (error) {
      if (!error.isUnauthorized) {
        rethrow;
      }
    }
  }

  Future<String?> getToken() => _storage.getToken();

  Future<void> clearToken() async {
    token = null;
    user = null;
    role = null;
    isSessionValidated = false;
    await _storage.clearAuthData();
  }

  Future<AuthActionResult> _acceptAuthenticationResponse(
    AuthResponse response,
  ) async {
    final responseToken = response.token;
    final responseUser = response.user;

    if (!response.success || responseToken == null || responseUser == null) {
      throw const ApiException('Invalid authentication response from server.');
    }

    _ensureCorrectAppRole(responseUser);

    await _storage.saveAuthData(
      token: responseToken,
      user: responseUser,
      expiresAt: response.expiresAt,
    );
    token = responseToken;
    user = responseUser;
    role = responseUser.role;
    isSessionValidated = true;
    await _syncNotificationDevice();

    if (response.requiresVerification || responseUser.requiresVerification) {
      return AuthActionResult.verificationRequired(
        response.message.isEmpty
            ? 'Verify your email and phone to continue.'
            : response.message,
      );
    }

    if (response.requiresApproval || responseUser.isPendingApproval) {
      return AuthActionResult.pendingApproval(
        response.message.isEmpty
            ? 'Your account is waiting for admin approval.'
            : response.message,
      );
    }

    return AuthActionResult.success(
      message: response.message,
      redirectRoute: responseUser.role.homeRoute,
    );
  }

  Future<void> _handleUnauthorized() async {
    if (token == null && !isSessionValidated) {
      return;
    }

    errorMessage = 'Your session has expired. Please sign in again.';
    try {
      await NotificationService.disableLocalDevice();
    } catch (_) {
      // Session cleanup must not depend on Firebase availability.
    }
    await clearToken();
    isInitialized = true;
    notifyListeners();
  }

  void _ensureCorrectAppRole(UserModel authenticatedUser) {
    final expectedRole = UserRole.fromName(AppIdentity.flavor.name);
    if (authenticatedUser.role != expectedRole) {
      throw ApiException(
        'This ${authenticatedUser.role.label.toLowerCase()} account must be '
        'used in the DailyCart ${authenticatedUser.role.label} app.',
        statusCode: 403,
      );
    }
  }

  Future<void> _syncNotificationDevice() async {
    try {
      await NotificationService.syncDevice();
    } catch (_) {
      // Token sync is retried by startup validation and Firebase refresh events.
    }
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
}

class AuthActionResult {
  const AuthActionResult._({
    required this.isSuccess,
    required this.requiresVerification,
    required this.requiresApproval,
    required this.message,
    this.redirectRoute,
  });

  final bool isSuccess;
  final bool requiresVerification;
  final bool requiresApproval;
  final String message;
  final String? redirectRoute;

  factory AuthActionResult.success({
    required String message,
    String? redirectRoute,
  }) {
    return AuthActionResult._(
      isSuccess: true,
      requiresVerification: false,
      requiresApproval: false,
      message: message,
      redirectRoute: redirectRoute,
    );
  }

  factory AuthActionResult.verificationRequired(String message) {
    return AuthActionResult._(
      isSuccess: false,
      requiresVerification: true,
      requiresApproval: false,
      message: message,
      redirectRoute: AppRoutes.otpVerification,
    );
  }

  factory AuthActionResult.pendingApproval(String message) {
    return AuthActionResult._(
      isSuccess: false,
      requiresVerification: false,
      requiresApproval: true,
      message: message,
      redirectRoute: AppRoutes.pendingApproval,
    );
  }

  factory AuthActionResult.failure(String message) {
    return AuthActionResult._(
      isSuccess: false,
      requiresVerification: false,
      requiresApproval: false,
      message: message,
    );
  }
}
