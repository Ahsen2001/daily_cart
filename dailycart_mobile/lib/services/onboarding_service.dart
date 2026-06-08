import 'package:shared_preferences/shared_preferences.dart';

class OnboardingService {
  static const _key = 'dailycart_onboarding_seen';

  Future<bool> get hasSeenOnboarding async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getBool(_key) ?? false;
  }

  Future<void> markSeen() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_key, true);
  }
}
