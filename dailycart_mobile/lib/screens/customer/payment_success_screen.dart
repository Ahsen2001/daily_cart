import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../models/checkout_response_model.dart';
import '../../routes/app_routes.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/order_placed_card.dart';

class PaymentSuccessScreen extends StatelessWidget {
  const PaymentSuccessScreen({
    this.order,
    super.key,
  });

  final OrderModel? order;

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
                  order: order ?? const OrderModel(
                    id: 0,
                    orderNumber: '',
                    status: 'confirmed',
                    paymentStatus: 'paid',
                    grandTotal: 0,
                  ),
                  title: 'Payment Successful',
                  message: 'Your PayHere payment was completed successfully.',
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
