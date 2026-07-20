import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/auth_provider.dart';
import '../../routes/app_routes.dart';
import '../../theme/app_colors.dart';
import '../../utils/responsive.dart';
import '../../widgets/app_logo.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/dailycart_card.dart';

class PendingApprovalScreen extends ConsumerWidget {
  const PendingApprovalScreen({
    required this.message,
    super.key,
  });

  final String message;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
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
                children: [
                  const AppLogo(size: 96),
                  const SizedBox(height: 24),
                  DailyCartCard(
                    child: Column(
                      children: [
                        Container(
                          width: 68,
                          height: 68,
                          decoration: BoxDecoration(
                            color: AppColors.accentOrange.withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(22),
                          ),
                          child: const Icon(
                            Icons.hourglass_top_rounded,
                            color: AppColors.accentOrange,
                            size: 36,
                          ),
                        ),
                        const SizedBox(height: 18),
                        Text(
                          'Pending Approval',
                          textAlign: TextAlign.center,
                          style:
                              Theme.of(context).textTheme.titleLarge?.copyWith(
                                    fontWeight: FontWeight.w900,
                                  ),
                        ),
                        const SizedBox(height: 10),
                        Text(
                          message,
                          textAlign: TextAlign.center,
                          style:
                              Theme.of(context).textTheme.bodyMedium?.copyWith(
                                    color: AppColors.mutedText,
                                    height: 1.5,
                                  ),
                        ),
                        const SizedBox(height: 24),
                        CustomButton(
                          label: 'Sign Out',
                          icon: Icons.logout_rounded,
                          onPressed: () async {
                            await ref.read(authProvider).logout();
                            if (context.mounted) {
                              context.go(AppRoutes.login);
                            }
                          },
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
    );
  }
}
