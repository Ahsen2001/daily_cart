import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../models/checkout_response_model.dart';
import '../../routes/app_routes.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/order_placed_card.dart';

class OrderSuccessScreen extends StatelessWidget {
  const OrderSuccessScreen({
    required this.order,
    super.key,
  });

  final OrderModel order;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Center(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                OrderPlacedCard(
                  order: order,
                  title: 'Order Placed',
                  message:
                      'Your Cash on Delivery order was created successfully.',
                ),
                const SizedBox(height: 20),
                CustomButton(
                  label: 'Back to Home',
                  icon: Icons.home_rounded,
                  onPressed: () => context.go(AppRoutes.customerHome),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
