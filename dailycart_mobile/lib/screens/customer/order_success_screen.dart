import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../models/checkout_response_model.dart';
import '../../providers/payment_provider.dart';
import '../../routes/app_routes.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/order_placed_card.dart';

class OrderSuccessScreen extends ConsumerWidget {
  const OrderSuccessScreen({
    required this.orders,
    this.payHere = false,
    super.key,
  });

  final List<OrderModel> orders;
  final bool payHere;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Scaffold(
      body: SafeArea(
        child: Center(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Flexible(
                  child: ListView.separated(
                    shrinkWrap: true,
                    itemCount: orders.length,
                    separatorBuilder: (_, _) => const SizedBox(height: 12),
                    itemBuilder: (context, index) {
                      final order = orders[index];
                      return Column(
                        children: [
                          OrderPlacedCard(
                            order: order,
                            title: orders.length > 1
                                ? 'Vendor Order ${index + 1}'
                                : 'Order Placed',
                            message: payHere
                                ? 'Complete payment for this vendor order.'
                                : 'Your Cash on Delivery order was created successfully.',
                          ),
                          if (payHere && !order.isPaid)
                            TextButton.icon(
                              onPressed: () => _pay(context, ref, order),
                              icon: const Icon(Icons.payment),
                              label: const Text('Pay this order'),
                            ),
                        ],
                      );
                    },
                  ),
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

  Future<void> _pay(
    BuildContext context,
    WidgetRef ref,
    OrderModel order,
  ) async {
    final payment = ref.read(paymentProvider);
    final ready = await payment.getPaymentUrl(order.id);
    if (!context.mounted) return;
    if (!ready || payment.paymentUrl == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(payment.errorMessage ?? 'Payment URL is unavailable.'),
        ),
      );
      return;
    }
    context.push(
      AppRoutes.payHereWebView,
      extra: {'orderId': order.id, 'paymentUrl': payment.paymentUrl!},
    );
  }
}
