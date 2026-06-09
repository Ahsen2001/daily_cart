import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/profile_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';
import '../../widgets/dailycart_card.dart';

class ChangePasswordScreen extends ConsumerStatefulWidget {
  const ChangePasswordScreen({super.key});

  @override
  ConsumerState<ChangePasswordScreen> createState() =>
      _ChangePasswordScreenState();
}

class _ChangePasswordScreenState extends ConsumerState<ChangePasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  final _currentController = TextEditingController();
  final _newController = TextEditingController();
  final _confirmController = TextEditingController();

  @override
  void dispose() {
    _currentController.dispose();
    _newController.dispose();
    _confirmController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(profileProvider);

    return Scaffold(
      appBar: const CustomAppBar(title: 'Change Password'),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(20),
          children: [
            DailyCartCard(
              child: Column(
                children: [
                  CustomTextField(
                    label: 'Current Password',
                    controller: _currentController,
                    icon: Icons.lock_outline_rounded,
                    obscureText: true,
                    validator: _required('Current password is required.'),
                  ),
                  const SizedBox(height: 12),
                  CustomTextField(
                    label: 'New Password',
                    controller: _newController,
                    icon: Icons.lock_reset_rounded,
                    obscureText: true,
                    validator: _password,
                  ),
                  const SizedBox(height: 12),
                  CustomTextField(
                    label: 'Confirm Password',
                    controller: _confirmController,
                    icon: Icons.verified_user_outlined,
                    obscureText: true,
                    validator: _confirmPassword,
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),
            CustomButton(
              label: 'Change Password',
              icon: Icons.save_outlined,
              isLoading: state.isLoading,
              onPressed: _save,
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }
    final ok = await ref.read(profileProvider).changePassword(
          currentPassword: _currentController.text,
          newPassword: _newController.text,
          confirmPassword: _confirmController.text,
        );
    if (!mounted) {
      return;
    }
    if (ok) {
      context.pop();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            ref.read(profileProvider).errorMessage ?? 'Unable to change password.',
          ),
        ),
      );
    }
  }

  String? Function(String?) _required(String message) {
    return (value) {
      if (value == null || value.isEmpty) {
        return message;
      }
      return null;
    };
  }

  String? _password(String? value) {
    if (value == null || value.length < 8) {
      return 'Password must be at least 8 characters.';
    }
    return null;
  }

  String? _confirmPassword(String? value) {
    if (value != _newController.text) {
      return 'Passwords do not match.';
    }
    return null;
  }
}
