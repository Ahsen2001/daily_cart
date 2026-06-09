import 'package:flutter/material.dart';

import 'empty_state_widget.dart';

class EmptyWishlistWidget extends StatelessWidget {
  const EmptyWishlistWidget({super.key});

  @override
  Widget build(BuildContext context) {
    return const EmptyStateWidget(
      title: 'Your wishlist is empty',
      message: 'Save products you like and move them to cart later.',
      icon: Icons.favorite_border_rounded,
    );
  }
}
