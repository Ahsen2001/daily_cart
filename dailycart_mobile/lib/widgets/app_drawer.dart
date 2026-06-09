import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../providers/auth_provider.dart';
import '../routes/app_routes.dart';
import '../theme/app_colors.dart';
import 'app_logo.dart';

class AppDrawer extends ConsumerWidget {
  const AppDrawer({
    required this.roleName,
    super.key,
  });

  final String roleName;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final isVendor = roleName.toLowerCase() == 'vendor';

    return Drawer(
      backgroundColor: AppColors.lightBackground,
      child: SafeArea(
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.all(22),
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
                          style: Theme.of(context).textTheme.titleLarge?.copyWith(
                                fontWeight: FontWeight.w900,
                                color: AppColors.darkGreen,
                              ),
                        ),
                        Text(
                          roleName,
                          style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                color: AppColors.mutedText,
                              ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const Divider(color: AppColors.border),
            Expanded(
              child: ListView(
                padding: EdgeInsets.zero,
                children: [
                  ListTile(
                    leading: const Icon(Icons.home_outlined),
                    title: const Text('Home'),
                    onTap: () {
                      Navigator.of(context).pop();
                      if (isVendor) {
                        context.go(AppRoutes.vendorDashboard);
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
            ListTile(
              leading: const Icon(Icons.logout_rounded),
              title: const Text('Logout'),
              onTap: () async {
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
