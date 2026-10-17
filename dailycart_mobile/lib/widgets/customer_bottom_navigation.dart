import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../routes/app_routes.dart';

/// The route-backed navigation shared by the customer's five primary screens.
///
/// Each screen supplies its own index, so the selected destination always
/// reflects the visible route instead of stale state left on the home screen.
class CustomerBottomNavigation extends StatelessWidget {
  const CustomerBottomNavigation({
    super.key,
    required this.currentIndex,
  });

  final int currentIndex;

  static const _routes = <String>[
    AppRoutes.customerHome,
    AppRoutes.categories,
    AppRoutes.wishlist,
    AppRoutes.myOrders,
    AppRoutes.profile,
  ];

  @override
  Widget build(BuildContext context) {
    return NavigationBar(
      selectedIndex: currentIndex,
      onDestinationSelected: (index) {
        if (index == currentIndex) return;
        context.go(_routes[index]);
      },
      destinations: const [
        NavigationDestination(
          icon: Icon(Icons.home_outlined),
          selectedIcon: Icon(Icons.home_rounded),
          label: 'Home',
        ),
        NavigationDestination(
          icon: Icon(Icons.category_outlined),
          selectedIcon: Icon(Icons.category_rounded),
          label: 'Shop',
        ),
        NavigationDestination(
          icon: Icon(Icons.favorite_border_rounded),
          selectedIcon: Icon(Icons.favorite_rounded),
          label: 'Wishlist',
        ),
        NavigationDestination(
          icon: Icon(Icons.receipt_long_outlined),
          selectedIcon: Icon(Icons.receipt_long_rounded),
          label: 'Orders',
        ),
        NavigationDestination(
          icon: Icon(Icons.person_outline_rounded),
          selectedIcon: Icon(Icons.person_rounded),
          label: 'Profile',
        ),
      ],
    );
  }
}
