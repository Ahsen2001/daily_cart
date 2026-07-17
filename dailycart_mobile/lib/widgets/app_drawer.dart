import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../providers/auth_provider.dart';
import '../routes/app_routes.dart';
import '../theme/app_colors.dart';
import 'app_logo.dart';

class AppDrawer extends ConsumerWidget {
  const AppDrawer({required this.roleName, super.key});

  final String roleName;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final isVendor = roleName.toLowerCase() == 'vendor';
    final isRider = roleName.toLowerCase() == 'rider';

    return Drawer(
      backgroundColor: AppColors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.horizontal(right: Radius.circular(28)),
      ),
      child: SafeArea(
        child: Column(
          children: [
            Container(
              margin: const EdgeInsets.all(14),
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: AppColors.lightBackground,
                borderRadius: BorderRadius.circular(22),
                border: Border.all(color: AppColors.border),
              ),
              child: Row(
                children: [
                  const AppLogo(size: 54),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'DailyCart',
                          style: Theme.of(context).textTheme.titleLarge
                              ?.copyWith(
                                fontWeight: FontWeight.w900,
                                color: AppColors.darkGreen,
                              ),
                        ),
                        Text(
                          roleName,
                          style: Theme.of(context).textTheme.bodySmall
                              ?.copyWith(color: AppColors.mutedText),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: ListView(
                padding: const EdgeInsets.symmetric(horizontal: 10),
                children: [
                  Padding(
                    padding: const EdgeInsets.fromLTRB(14, 4, 14, 8),
                    child: Text(
                      'WORKSPACE',
                      style: Theme.of(context).textTheme.labelSmall?.copyWith(
                        color: AppColors.mutedText,
                        fontWeight: FontWeight.w800,
                        letterSpacing: 1.2,
                      ),
                    ),
                  ),
                  ListTile(
                    leading: const Icon(Icons.home_outlined),
                    title: const Text('Home'),
                    onTap: () {
                      Navigator.of(context).pop();
                      if (isVendor) {
                        context.go(AppRoutes.vendorDashboard);
                      } else if (isRider) {
                        context.go(AppRoutes.riderDashboard);
                      } else {
                        context.go(AppRoutes.customerHome);
                      }
                    },
                  ),
                  if (isVendor) ...[
                    ListTile(
                      leading: const Icon(Icons.inventory_2_outlined),
                      title: const Text('Products'),
                      onTap: () {
                        Navigator.of(context).pop();
                        context.push(AppRoutes.vendorProducts);
                      },
                    ),
                    ListTile(
                      leading: const Icon(Icons.receipt_long_outlined),
                      title: const Text('Orders'),
                      onTap: () {
                        Navigator.of(context).pop();
                        context.push(AppRoutes.vendorOrders);
                      },
                    ),
                    ListTile(
                      leading: const Icon(Icons.payments_outlined),
                      title: const Text('Earnings'),
                      onTap: () {
                        Navigator.of(context).pop();
                        context.push(AppRoutes.vendorEarnings);
                      },
                    ),
                    ListTile(
                      leading: const Icon(Icons.reviews_outlined),
                      title: const Text('Reviews'),
                      onTap: () {
                        Navigator.of(context).pop();
                        context.push(AppRoutes.vendorReviews);
                      },
                    ),
                    ListTile(
                      leading: const Icon(Icons.storefront_outlined),
                      title: const Text('Vendor Profile'),
                      onTap: () {
                        Navigator.of(context).pop();
                        context.push(AppRoutes.vendorProfile);
                      },
                    ),
                  ] else if (isRider) ...[
                    ListTile(
                      leading: const Icon(Icons.assignment_outlined),
                      title: const Text('Assigned Deliveries'),
                      onTap: () {
                        Navigator.of(context).pop();
                        context.push(AppRoutes.assignedDeliveries);
                      },
                    ),
                    ListTile(
                      leading: const Icon(Icons.payments_outlined),
                      title: const Text('Earnings'),
                      onTap: () {
                        Navigator.of(context).pop();
                        context.push(AppRoutes.riderEarnings);
                      },
                    ),
                    ListTile(
                      leading: const Icon(Icons.person_outline_rounded),
                      title: const Text('Rider Profile'),
                      onTap: () {
                        Navigator.of(context).pop();
                        context.push(AppRoutes.riderProfile);
                      },
                    ),
                  ] else ...[
                    ListTile(
                      leading: const Icon(Icons.receipt_long_outlined),
                      title: const Text('Orders'),
                      onTap: () {
                        Navigator.of(context).pop();
                        context.push(AppRoutes.myOrders);
                      },
                    ),
                    ListTile(
                      leading: const Icon(Icons.star_rate_rounded),
                      title: const Text('Reviews'),
                      onTap: () {
                        Navigator.of(context).pop();
                        context.push(AppRoutes.myReviews);
                      },
                    ),
                    ListTile(
                      leading: const Icon(Icons.stars_rounded),
                      title: const Text('Loyalty Points'),
                      onTap: () {
                        Navigator.of(context).pop();
                        context.push(AppRoutes.loyaltyPoints);
                      },
                    ),
                    ListTile(
                      leading: const Icon(Icons.confirmation_number_outlined),
                      title: const Text('Coupons'),
                      onTap: () {
                        Navigator.of(context).pop();
                        context.push(AppRoutes.availableCoupons);
                      },
                    ),
                    ListTile(
                      leading: const Icon(Icons.support_agent_rounded),
                      title: const Text('Support'),
                      onTap: () {
                        Navigator.of(context).pop();
                        context.push(AppRoutes.supportTickets);
                      },
                    ),
                    ListTile(
                      leading: const Icon(Icons.person_outline_rounded),
                      title: const Text('Profile'),
                      onTap: () {
                        Navigator.of(context).pop();
                        context.push(AppRoutes.profile);
                      },
                    ),
                  ],
                ],
              ),
            ),
            const Divider(),
            ListTile(
              leading: const Icon(
                Icons.logout_rounded,
                color: AppColors.danger,
              ),
              title: const Text(
                'Logout',
                style: TextStyle(color: AppColors.danger),
              ),
              onTap: () async {
                final shouldLogout = await showDialog<bool>(
                  context: context,
                  builder: (dialogContext) => AlertDialog(
                    title: const Text('Log out?'),
                    content: const Text(
                      'You will need to sign in again to access your DailyCart account.',
                    ),
                    actions: [
                      TextButton(
                        onPressed: () => Navigator.of(dialogContext).pop(false),
                        child: const Text('Cancel'),
                      ),
                      FilledButton(
                        onPressed: () => Navigator.of(dialogContext).pop(true),
                        child: const Text('Log out'),
                      ),
                    ],
                  ),
                );

                if (shouldLogout != true || !context.mounted) return;

                Navigator.of(context).pop();
                await ref.read(authProvider).logout();
                if (context.mounted) {
                  context.go(AppRoutes.login);
                }
              },
            ),
          ],
        ),
      ),
    );
  }
}
