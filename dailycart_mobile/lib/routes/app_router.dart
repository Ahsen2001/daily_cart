import 'package:go_router/go_router.dart';

import '../screens/auth/login_screen.dart';
import '../screens/auth/register_screen.dart';
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
      path: AppRoutes.customer,
      name: 'customer',
      builder: (context, state) => const RoleHomeScreen(roleName: 'Customer'),
    ),
    GoRoute(
      path: AppRoutes.vendor,
      name: 'vendor',
      builder: (context, state) => const RoleHomeScreen(roleName: 'Vendor'),
    ),
    GoRoute(
      path: AppRoutes.rider,
      name: 'rider',
      builder: (context, state) => const RoleHomeScreen(roleName: 'Rider'),
    ),
  ],
);
