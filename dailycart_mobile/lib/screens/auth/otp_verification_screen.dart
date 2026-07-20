import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/auth_provider.dart';
import '../../routes/app_routes.dart';
import '../../services/auth_api_service.dart';
import '../../theme/app_colors.dart';
import '../../utils/responsive.dart';
import '../../widgets/app_logo.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';
import '../../widgets/dailycart_card.dart';

class OtpVerificationScreen extends ConsumerStatefulWidget {
  const OtpVerificationScreen({super.key});

  @override
  ConsumerState<OtpVerificationScreen> createState() =>
      _OtpVerificationScreenState();
}

class _OtpVerificationScreenState extends ConsumerState<OtpVerificationScreen> {
  final _formKey = GlobalKey<FormState>();
  final _otpController = TextEditingController();
  VerificationChannel? _selectedChannel;

  VerificationChannel _channelFor(AuthProvider auth) {
    if (_selectedChannel case final selected?) {
      return selected;
    }
    return auth.user?.isEmailVerified == true
        ? VerificationChannel.phone
        : VerificationChannel.email;
  }

  @override
  void dispose() {
    _otpController.dispose();
    super.dispose();
  }

  Future<void> _verifyOtp() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    final auth = ref.read(authProvider);
    final channel = _channelFor(auth);
    final result = await auth.verifyCode(
      code: _otpController.text.trim(),
      channel: channel,
    );
    if (!mounted) {
      return;
    }

    if (result.requiresVerification) {
      _otpController.clear();
      setState(() {
        _selectedChannel = auth.user?.isEmailVerified == true
            ? VerificationChannel.phone
            : VerificationChannel.email;
      });
      _showMessage(result.message);
      return;
    }
    if (result.requiresApproval) {
      context.go(AppRoutes.pendingApproval, extra: result.message);
      return;
    }
    if (result.isSuccess && result.redirectRoute != null) {
      context.go(result.redirectRoute!);
      return;
    }
    _showMessage(result.message);
  }

  Future<void> _resend() async {
    final auth = ref.read(authProvider);
    final result = await auth.sendVerificationCode(_channelFor(auth));
    if (mounted) {
      _showMessage(result.message);
    }
  }

  void _showMessage(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = ref.watch(authProvider);
    final channel = _channelFor(auth);
    final destination = channel == VerificationChannel.email
        ? auth.user?.email ?? 'your email'
        : auth.user?.phone ?? 'your phone';

    return Scaffold(
      appBar: AppBar(
        actions: [
          TextButton(
            onPressed: auth.isLoading
                ? null
                : () async {
                    await ref.read(authProvider).logout();
                    if (context.mounted) {
                      context.go(AppRoutes.login);
                    }
                  },
            child: const Text('Sign out'),
          ),
        ],
      ),
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
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Center(child: AppLogo(size: 84)),
                    const SizedBox(height: 24),
                    Text(
                      '${channel == VerificationChannel.email ? 'Email' : 'Phone'} Verification',
                      style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                            color: AppColors.textColor,
                            fontWeight: FontWeight.w900,
                          ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Enter the 6-digit code sent to $destination.',
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                            color: AppColors.mutedText,
                          ),
                    ),
                    const SizedBox(height: 24),
                    DailyCartCard(
                      child: Column(
                        children: [
                          CustomTextField(
                            label: '6-digit OTP',
                            controller: _otpController,
                            icon: Icons.password_rounded,
                            keyboardType: TextInputType.number,
                            maxLength: 6,
                            validator: _validateOtp,
                          ),
                          const SizedBox(height: 22),
                          CustomButton(
                            label: 'Verify ${channel == VerificationChannel.email ? 'Email' : 'Phone'}',
                            icon: Icons.verified_rounded,
                            isLoading: auth.isLoading,
                            onPressed: _verifyOtp,
                          ),
                          const SizedBox(height: 10),
                          TextButton.icon(
                            onPressed: auth.isLoading ? null : _resend,
                            icon: const Icon(Icons.refresh_rounded),
                            label: const Text('Resend code'),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  String? _validateOtp(String? value) {
    final otp = value?.trim() ?? '';
    if (!RegExp(r'^\d{6}$').hasMatch(otp)) {
      return 'Enter a valid 6-digit OTP.';
    }
    return null;
  }
}
