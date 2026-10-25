import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../routes/app_routes.dart';

class VendorBottomNavigation extends StatelessWidget {
  const VendorBottomNavigation({required this.currentIndex, super.key});

  final int currentIndex;

  static const _routes = [
    AppRoutes.vendorDashboard,
    AppRoutes.vendorProducts,
    AppRoutes.vendorOrders,
    AppRoutes.vendorEarnings,
    AppRoutes.vendorProfile,
  ];

  @override
  Widget build(BuildContext context) {
    return NavigationBar(
      selectedIndex: currentIndex,
      onDestinationSelected: (index) {
        if (index != currentIndex) context.go(_routes[index]);
      },
      destinations: const [
        NavigationDestination(
          icon: Icon(Icons.dashboard_outlined),
          selectedIcon: Icon(Icons.dashboard_rounded),
          label: 'Home',
        ),
        NavigationDestination(
          icon: Icon(Icons.inventory_2_outlined),
          selectedIcon: Icon(Icons.inventory_2_rounded),
          label: 'Products',
        ),
        NavigationDestination(
          icon: Icon(Icons.receipt_long_outlined),
          selectedIcon: Icon(Icons.receipt_long_rounded),
          label: 'Orders',
        ),
        NavigationDestination(
          icon: Icon(Icons.payments_outlined),
          selectedIcon: Icon(Icons.payments_rounded),
          label: 'Earnings',
        ),
        NavigationDestination(
          icon: Icon(Icons.storefront_outlined),
          selectedIcon: Icon(Icons.storefront_rounded),
          label: 'Profile',
        ),
      ],
    );
  }
}
