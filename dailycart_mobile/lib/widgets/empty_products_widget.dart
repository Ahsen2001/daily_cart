import 'package:flutter/material.dart';

import 'empty_state_widget.dart';

class EmptyProductsWidget extends StatelessWidget {
  const EmptyProductsWidget({super.key});

  @override
  Widget build(BuildContext context) {
    return const EmptyStateWidget(
      title: 'No products found',
      message: 'Try changing your filters or search for another item.',
      icon: Icons.shopping_basket_outlined,
    );
  }
}
