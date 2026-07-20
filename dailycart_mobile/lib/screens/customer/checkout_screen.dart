import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../models/payment_method_model.dart';
import '../../providers/cart_provider.dart';
import '../../providers/checkout_provider.dart';
import '../../providers/coupon_provider.dart';
import '../../routes/app_routes.dart';
import '../../theme/app_colors.dart';
import '../../widgets/checkout_summary_card.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/empty_cart_widget.dart';

class CheckoutScreen extends ConsumerStatefulWidget {
  const CheckoutScreen({super.key});

  @override
  ConsumerState<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends ConsumerState<CheckoutScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(cartProvider).getCart());
  }

  @override
  Widget build(BuildContext context) {
    final cart = ref.watch(cartProvider);
    final checkout = ref.watch(checkoutProvider);
    final coupon = ref.watch(couponProvider);

    return Scaffold(
      appBar: const CustomAppBar(title: 'Checkout'),
      body: cart.cartItems.isEmpty
          ? const EmptyCartWidget()
          : ListView(
              padding: const EdgeInsets.fromLTRB(20, 20, 20, 150),
              children: [
                const _CheckoutProgress(),
                const SizedBox(height: 18),
                _StepCard(
                  title: 'Delivery Address',
                  value: checkout.selectedAddress?.displayAddress ??
                      'Select delivery address',
                  icon: Icons.location_on_outlined,
                  onTap: () => context.push(AppRoutes.addresses),
                ),
                const SizedBox(height: 12),
                _StepCard(
                  title: 'Delivery Time',
                  value: checkout.selectedDeliveryTime == null
                      ? 'Select delivery time'
                      : checkout.selectedDeliveryTime!.toString(),
                  icon: Icons.schedule_outlined,
                  onTap: () => context.push(AppRoutes.deliverySchedule),
                ),
                const SizedBox(height: 12),
                _StepCard(
                  title: 'Payment Method',
                  value: checkout.selectedPaymentMethod.title,
                  icon: Icons.payments_outlined,
                  onTap: () => context.push(AppRoutes.paymentMethod),
                ),
                const SizedBox(height: 18),
                Text(
                  'Cart Items',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w900,
                      ),
                ),
                const SizedBox(height: 8),
                for (final item in cart.cartItems)
                  Padding(
                    padding: const EdgeInsets.only(bottom: 8),
                    child: Text('${item.quantity} x ${item.name}'),
                  ),
              ],
            ),
      bottomSheet: cart.cartItems.isEmpty
          ? null
          : Container(
              color: AppColors.lightBackground,
              padding: const EdgeInsets.fromLTRB(20, 12, 20, 20),
              child: SafeArea(
                child: CheckoutSummaryCard(
                  summary: checkout.quote?.summary ?? cart.summary,
                  action: CustomButton(
                    label: 'Place Order',
                    icon: Icons.shopping_bag_rounded,
                    isLoading: checkout.isLoading,
                    onPressed: () => _placeOrder(coupon.appliedCoupon?.code),
                  ),
                ),
              ),
            ),
    );
  }

  Future<void> _placeOrder(String? couponCode) async {
    final checkout = ref.read(checkoutProvider);
    final ok = await checkout.createOrder(couponCode: couponCode);

    if (!mounted) {
      return;
    }

    if (!ok) {
      _showMessage(checkout.errorMessage ?? 'Unable to place order.');
      return;
    }

    final order = checkout.order;
    if (order == null) {
      _showMessage('Order response is missing.');
      return;
    }

    switch (checkout.selectedPaymentMethod) {
      case PaymentMethodType.cashOnDelivery:
        context.go(
          AppRoutes.orderSuccess,
          extra: {'orders': checkout.orders, 'payHere': false},
        );
      case PaymentMethodType.payHere:
        context.go(
          AppRoutes.orderSuccess,
          extra: {'orders': checkout.orders, 'payHere': true},
        );
      case PaymentMethodType.bankTransfer:
      case PaymentMethodType.wallet:
        _showMessage('${checkout.selectedPaymentMethod.title} placeholder.');
    }
  }

  void _showMessage(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }
}

class _CheckoutProgress extends StatelessWidget {
  const _CheckoutProgress();

  @override
  Widget build(BuildContext context) {
    const steps = ['Cart', 'Address', 'Schedule', 'Payment', 'Confirm'];
    return Row(
      children: [
        for (var index = 0; index < steps.length; index++) ...[
          Expanded(
            child: Column(
              children: [
                CircleAvatar(
                  radius: 14,
                  backgroundColor: AppColors.primaryGreen,
                  child: Text(
                    '${index + 1}',
                    style: const TextStyle(
                      color: AppColors.white,
                      fontSize: 12,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  steps[index],
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: Theme.of(context).textTheme.labelSmall,
                ),
              ],
            ),
          ),
          if (index != steps.length - 1)
            Container(width: 12, height: 2, color: AppColors.border),
        ],
      ],
    );
  }
}

class _StepCard extends StatelessWidget {
  const _StepCard({
    required this.title,
    required this.value,
    required this.icon,
    required this.onTap,
  });

  final String title;
  final String value;
  final IconData icon;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(22),
      onTap: onTap,
      child: DailyCartCard(
        child: Row(
          children: [
            Icon(icon, color: AppColors.darkGreen),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: Theme.of(context).textTheme.titleSmall?.copyWith(
                          fontWeight: FontWeight.w900,
                        ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    value,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: AppColors.mutedText,
                        ),
                  ),
                ],
              ),
            ),
            const Icon(Icons.chevron_right_rounded),
          ],
        ),
      ),
    );
  }
}
