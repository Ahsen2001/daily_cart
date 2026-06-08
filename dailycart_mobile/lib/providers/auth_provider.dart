import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../services/auth_session_service.dart';
import '../services/onboarding_service.dart';

final authSessionServiceProvider = Provider<AuthSessionService>((ref) {
  return AuthSessionService();
});

final onboardingServiceProvider = Provider<OnboardingService>((ref) {
  return OnboardingService();
});
