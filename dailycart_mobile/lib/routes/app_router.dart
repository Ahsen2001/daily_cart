import 'package:go_router/go_router.dart';

import '../screens/auth/login_screen.dart';
import '../screens/auth/forgot_password_screen.dart';
import '../screens/auth/otp_verification_screen.dart';
import '../screens/auth/pending_approval_screen.dart';
import '../screens/auth/register_screen.dart';
import '../screens/customer/category_screen.dart';
import '../screens/customer/customer_home_screen.dart';
import '../screens/customer/product_details_screen.dart';
import '../screens/customer/product_list_screen.dart';
import '../screens/customer/search_screen.dart';
import '../screens/home/role_home_screen.dart';
import '../screens/onboarding/onboarding_screen.dart';
import '../screens/splash/splash_screen.dart';
import 'app_routes.dart';

final appRouter = GoRouter(
  initialLocation: AppRoutes.splash,
  routes: [
    GoRoute(
      path: AppRoutes.splash,
      name: 'splash',
      builder: (context, state) => const SplashScreen(),
    ),
    GoRoute(
      path: AppRoutes.onboarding,
      name: 'onboarding',
      builder: (context, state) => const OnboardingScreen(),
    ),
    GoRoute(
      path: AppRoutes.login,
      name: 'login',
      builder: (context, state) => const LoginScreen(),
    ),
    GoRoute(
      path: AppRoutes.register,
      name: 'register',
      builder: (context, state) => const RegisterScreen(),
    ),
    GoRoute(
      path: AppRoutes.forgotPassword,
      name: 'forgot-password',
      builder: (context, state) => const ForgotPasswordScreen(),
    ),
    GoRoute(
      path: AppRoutes.otpVerification,
      name: 'otp-verification',
      builder: (context, state) => const OtpVerificationScreen(),
    ),
    GoRoute(
      path: AppRoutes.pendingApproval,
      name: 'pending-approval',
      builder: (context, state) {
        final extra = state.extra;
        final message = extra is String
            ? extra
            : 'Your account is waiting for admin approval.';

        return PendingApprovalScreen(message: message);
      },
    ),
    GoRoute(
      path: AppRoutes.customerHome,
      name: 'customer-home',
      builder: (context, state) => const CustomerHomeScreen(),
    ),
    GoRoute(
      path: AppRoutes.vendorDashboard,
      name: 'vendor-dashboard',
      builder: (context, state) => const RoleHomeScreen(roleName: 'Vendor'),
    ),
    GoRoute(
      path: AppRoutes.riderDashboard,
      name: 'rider-dashboard',
      builder: (context, state) => const RoleHomeScreen(roleName: 'Rider'),
    ),
    GoRoute(
      path: AppRoutes.categories,
      name: 'categories',
      builder: (context, state) => const CategoryScreen(),
    ),
    GoRoute(
      path: AppRoutes.products,
      name: 'products',
      builder: (context, state) {
        final categoryId = int.tryParse(
          state.uri.queryParameters['categoryId'] ?? '',
        );
        final categoryName = state.uri.queryParameters['categoryName'];

        return ProductListScreen(
          categoryId: categoryId,
          categoryName: categoryName,
        );
      },
    ),
    GoRoute(
      path: '${AppRoutes.productDetails}/:id',
      name: 'product-details',
      builder: (context, state) {
        final productId = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
        return ProductDetailsScreen(productId: productId);
      },
    ),
    GoRoute(
      path: AppRoutes.search,
      name: 'search',
      builder: (context, state) => const SearchScreen(),
    ),
  ],
);
