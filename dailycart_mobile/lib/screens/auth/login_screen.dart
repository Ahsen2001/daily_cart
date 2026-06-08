import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../models/user_role.dart';
import '../../providers/auth_provider.dart';
import '../../routes/app_routes.dart';
import '../../theme/app_colors.dart';
import '../../utils/responsive.dart';
import '../../widgets/app_logo.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';
import '../../widgets/dailycart_card.dart';

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  UserRole _selectedRole = UserRole.customer;
  bool _isLoading = false;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _signIn() async {
    setState(() => _isLoading = true);
    await Future<void>.delayed(const Duration(milliseconds: 500));
    await ref.read(authSessionServiceProvider).saveSession(
          token: 'local-ui-foundation-token',
          role: _selectedRole,
        );

    if (mounted) {
      context.go(_selectedRole.homeRoute);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: EdgeInsets.symmetric(
              horizontal: Responsive.horizontalPadding(context),
              vertical: 24,
            ),
            child: ConstrainedBox(
              constraints: BoxConstraints(
                maxWidth: Responsive.maxContentWidth(context),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Center(child: AppLogo(size: 96)),
                  const SizedBox(height: 24),
                  Text(
                    'Welcome back',
                    style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                          color: AppColors.textColor,
                          fontWeight: FontWeight.w900,
                        ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Sign in to continue shopping with DailyCart.',
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          color: AppColors.mutedText,
                        ),
                  ),
                  const SizedBox(height: 24),
                  DailyCartCard(
                    child: Column(
                      children: [
                        CustomTextField(
                          label: 'Email',
                          controller: _emailController,
                          icon: Icons.mail_outline_rounded,
                          keyboardType: TextInputType.emailAddress,
                          textInputAction: TextInputAction.next,
                        ),
                        const SizedBox(height: 14),
                        CustomTextField(
                          label: 'Password',
                          controller: _passwordController,
                          icon: Icons.lock_outline_rounded,
                          obscureText: true,
                        ),
                        const SizedBox(height: 18),
                        _RoleSelector(
                          selectedRole: _selectedRole,
                          onChanged: (role) {
                            setState(() => _selectedRole = role);
                          },
                        ),
                        const SizedBox(height: 22),
                        CustomButton(
                          label: 'Sign in',
                          icon: Icons.arrow_forward_rounded,
                          isLoading: _isLoading,
                          onPressed: _signIn,
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 18),
                  Center(
                    child: TextButton(
                      onPressed: () => context.push(AppRoutes.register),
                      child: const Text('Create a new account'),
                    ),
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

class _RoleSelector extends StatelessWidget {
  const _RoleSelector({
    required this.selectedRole,
    required this.onChanged,
  });

  final UserRole selectedRole;
  final ValueChanged<UserRole> onChanged;

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: SegmentedButton<UserRole>(
        segments: [
          for (final role in UserRole.values)
            ButtonSegment<UserRole>(
              value: role,
              label: Text(role.label),
            ),
        ],
        selected: {selectedRole},
        onSelectionChanged: (value) => onChanged(value.first),
        style: SegmentedButton.styleFrom(
          selectedBackgroundColor: AppColors.primaryGreen,
          selectedForegroundColor: AppColors.white,
          foregroundColor: AppColors.textColor,
        ),
      ),
    );
  }
}
