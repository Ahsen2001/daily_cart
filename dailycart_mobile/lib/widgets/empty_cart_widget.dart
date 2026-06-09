import 'package:flutter/material.dart';

import 'empty_state_widget.dart';

class EmptyCartWidget extends StatelessWidget {
  const EmptyCartWidget({super.key});

  @override
  Widget build(BuildContext context) {
    return const EmptyStateWidget(
      title: 'Your cart is empty',
      message: 'Add approved and active products to prepare your order.',
      icon: Icons.shopping_cart_outlined,
    );
  }
}
