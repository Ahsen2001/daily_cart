import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/auth_provider.dart';
import '../../routes/app_routes.dart';
import '../../theme/app_colors.dart';
import '../../utils/responsive.dart';
import '../../widgets/app_logo.dart';
import '../../widgets/custom_button.dart';

class OnboardingScreen extends ConsumerStatefulWidget {
  const OnboardingScreen({super.key});

  @override
  ConsumerState<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends ConsumerState<OnboardingScreen> {
  final _pageController = PageController();
  int _index = 0;

  final _pages = const [
    _OnboardingPage(
      icon: Icons.delivery_dining_rounded,
      title: 'Fast grocery delivery',
      message: 'Order essentials and get them delivered quickly to your door.',
    ),
    _OnboardingPage(
      icon: Icons.eco_rounded,
      title: 'Fresh vegetables and fruits',
      message: 'Shop quality produce from trusted DailyCart vendors.',
    ),
    _OnboardingPage(
      icon: Icons.shopping_bag_rounded,
      title: 'Easy online shopping',
      message: 'Browse, checkout, pay in LKR, and track orders with ease.',
    ),
  ];

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  Future<void> _finish() async {
    await ref.read(onboardingServiceProvider).markSeen();
    if (mounted) {
      context.go(AppRoutes.login);
    }
  }

  void _next() {
    if (_index == _pages.length - 1) {
      _finish();
      return;
    }

    _pageController.nextPage(
      duration: const Duration(milliseconds: 280),
      curve: Curves.easeOutCubic,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Center(
          child: ConstrainedBox(
            constraints: BoxConstraints(
              maxWidth: Responsive.maxContentWidth(context),
            ),
            child: Padding(
              padding: EdgeInsets.symmetric(
                horizontal: Responsive.horizontalPadding(context),
                vertical: 18,
              ),
              child: Column(
                children: [
                  Row(
                    children: [
                      const AppLogo(size: 42, showShadow: false),
                      const SizedBox(width: 10),
                      Text(
                        'DailyCart',
                        style: Theme.of(context).textTheme.titleLarge?.copyWith(
                              color: AppColors.darkGreen,
                              fontWeight: FontWeight.w900,
                            ),
                      ),
                      const Spacer(),
                      TextButton(
                        onPressed: _finish,
                        child: const Text('Skip'),
                      ),
                    ],
                  ),
                  Expanded(
                    child: PageView.builder(
                      controller: _pageController,
                      onPageChanged: (value) => setState(() => _index = value),
                      itemCount: _pages.length,
                      itemBuilder: (context, index) => _pages[index],
                    ),
                  ),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      for (var i = 0; i < _pages.length; i++)
                        AnimatedContainer(
                          duration: const Duration(milliseconds: 220),
                          width: i == _index ? 24 : 8,
                          height: 8,
                          margin: const EdgeInsets.symmetric(horizontal: 4),
                          decoration: BoxDecoration(
                            color: i == _index
                                ? AppColors.primaryGreen
                                : AppColors.border,
                            borderRadius: BorderRadius.circular(99),
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 24),
                  CustomButton(
                    label: _index == _pages.length - 1 ? 'Get Started' : 'Next',
                    icon: _index == _pages.length - 1
                        ? Icons.check_rounded
                        : Icons.arrow_forward_rounded,
                    onPressed: _next,
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _OnboardingPage extends StatelessWidget {
  const _OnboardingPage({
    required this.icon,
    required this.title,
    required this.message,
  });

  final IconData icon;
  final String title;
  final String message;

  @override
  Widget build(BuildContext context) {
    return AnimatedSwitcher(
      duration: const Duration(milliseconds: 240),
      child: Column(
        key: ValueKey(title),
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            width: 170,
            height: 170,
            decoration: BoxDecoration(
              color: AppColors.white,
              borderRadius: BorderRadius.circular(42),
              boxShadow: const [
                BoxShadow(
                  color: AppColors.shadow,
                  blurRadius: 28,
                  offset: Offset(0, 14),
                ),
              ],
            ),
            child: Icon(icon, size: 82, color: AppColors.primaryGreen),
          ),
          const SizedBox(height: 34),
          Text(
            title,
            textAlign: TextAlign.center,
            style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                  fontWeight: FontWeight.w900,
                  color: AppColors.textColor,
                ),
          ),
          const SizedBox(height: 12),
          Text(
            message,
            textAlign: TextAlign.center,
            style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                  color: AppColors.mutedText,
                  height: 1.5,
                ),
          ),
        ],
      ),
    );
  }
}
