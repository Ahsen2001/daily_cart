import 'package:flutter/material.dart';

import '../theme/app_colors.dart';
import 'app_logo.dart';

class AppDrawer extends StatelessWidget {
  const AppDrawer({
    required this.roleName,
    super.key,
  });

  final String roleName;

  @override
  Widget build(BuildContext context) {
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
            ListTile(
              leading: const Icon(Icons.home_outlined),
              title: const Text('Home'),
              onTap: () => Navigator.of(context).pop(),
            ),
            ListTile(
              leading: const Icon(Icons.receipt_long_outlined),
              title: const Text('Orders'),
              onTap: () => Navigator.of(context).pop(),
            ),
            ListTile(
              leading: const Icon(Icons.person_outline_rounded),
              title: const Text('Profile'),
              onTap: () => Navigator.of(context).pop(),
            ),
          ],
        ),
      ),
    );
  }
}
