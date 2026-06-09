import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../models/checkout_response_model.dart';
import '../../routes/app_routes.dart';
import '../../theme/app_colors.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/dailycart_card.dart';

class PaymentFailedScreen extends StatelessWidget {
  const PaymentFailedScreen({
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
            child: DailyCartCard(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(
                    Icons.error_outline_rounded,
                    color: AppColors.accentOrange,
                    size: 64,
                  ),
                  const SizedBox(height: 18),
                  Text(
                    'Payment Failed',
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(
                          fontWeight: FontWeight.w900,
                        ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    order == null
                        ? 'We could not confirm the payment status.'
                        : 'Payment status: ${order!.paymentStatus}',
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 20),
                  CustomButton(
                    label: 'Back to Checkout',
                    icon: Icons.arrow_back_rounded,
                    onPressed: () => context.go(AppRoutes.checkout),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
