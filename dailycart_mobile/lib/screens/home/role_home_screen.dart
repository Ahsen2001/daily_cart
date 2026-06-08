import 'package:flutter/material.dart';

import '../../theme/app_colors.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/dailycart_card.dart';

class RoleHomeScreen extends StatelessWidget {
  const RoleHomeScreen({
    required this.roleName,
    super.key,
  });

  final String roleName;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(
        title: '$roleName Dashboard',
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 12),
            child: Badge(
              backgroundColor: AppColors.accentOrange,
              child: IconButton(
                tooltip: 'Notifications',
                onPressed: () {},
                icon: const Icon(Icons.notifications_none_rounded),
              ),
            ),
          ),
        ],
      ),
      drawer: AppDrawer(roleName: roleName),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          DailyCartCard(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Hello, $roleName',
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                        fontWeight: FontWeight.w900,
                      ),
                ),
                const SizedBox(height: 8),
                Text(
                  'Your role-based DailyCart experience starts here.',
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                        color: AppColors.mutedText,
                      ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          const DailyCartCard(
            child: Row(
              children: [
                Icon(Icons.local_grocery_store_outlined),
                SizedBox(width: 14),
                Expanded(child: Text('Modern cards, rounded UI, and shadows')),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
