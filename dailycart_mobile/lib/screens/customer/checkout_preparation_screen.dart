import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../providers/cart_provider.dart';
import '../../providers/coupon_provider.dart';
import '../../theme/app_colors.dart';
import '../../widgets/cart_item_card.dart';
import '../../widgets/cart_summary_card.dart';
import '../../widgets/coupon_input_widget.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/empty_cart_widget.dart';

class CheckoutPreparationScreen extends ConsumerStatefulWidget {
  const CheckoutPreparationScreen({super.key});

  @override
  ConsumerState<CheckoutPreparationScreen> createState() =>
      _CheckoutPreparationScreenState();
}

class _CheckoutPreparationScreenState
    extends ConsumerState<CheckoutPreparationScreen> {
  final _addressController = TextEditingController(
    text: 'Colombo, Sri Lanka',
  );
  bool _addressConfirmed = false;

  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(cartProvider).getCart());
  }

  @override
  void dispose() {
    _addressController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final cart = ref.watch(cartProvider);
    final coupon = ref.watch(couponProvider);

    return Scaffold(
      appBar: const CustomAppBar(title: 'Checkout Preparation'),
      body: cart.cartItems.isEmpty
          ? const EmptyCartWidget()
          : ListView(
              padding: const EdgeInsets.fromLTRB(20, 20, 20, 140),
              children: [
                _AddressCard(
                  controller: _addressController,
                  isConfirmed: _addressConfirmed,
                  onChanged: (value) {
                    setState(() => _addressConfirmed = value);
                  },
                ),
                const SizedBox(height: 14),
                CouponInputWidget(
                  appliedCoupon: coupon.appliedCoupon,
                  isLoading: coupon.isLoading,
                  onApply: _applyCoupon,
                  onRemove: () => ref.read(couponProvider).removeCoupon(),
                ),
                const SizedBox(height: 18),
                Text(
                  'Review Cart Items',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w900,
                      ),
                ),
                const SizedBox(height: 12),
                for (final item in cart.cartItems) ...[
                  CartItemCard(
                    item: item,
                    onIncrease: () =>
                        ref.read(cartProvider).increaseQuantity(item),
                    onDecrease: () =>
                        ref.read(cartProvider).decreaseQuantity(item),
                    onRemove: () => ref.read(cartProvider).removeCartItem(item.id),
                  ),
                  const SizedBox(height: 14),
                ],
              ],
            ),
      bottomSheet: cart.cartItems.isEmpty
          ? null
          : Container(
              color: AppColors.lightBackground,
              padding: const EdgeInsets.fromLTRB(20, 12, 20, 20),
              child: SafeArea(
                child: CartSummaryCard(
                  summary: cart.summary,
                  action: CustomButton(
                    label: 'Continue to Checkout',
                    icon: Icons.shopping_bag_rounded,
                    onPressed: _addressConfirmed
                        ? () {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(
                                content: Text(
                                  'Checkout prepared. Payment comes in Step 6.',
                                ),
                              ),
                            );
                          }
                        : null,
                  ),
                ),
              ),
            ),
    );
  }

  Future<void> _applyCoupon(String code) async {
    final ok = await ref.read(couponProvider).applyCoupon(code);
    final message = ok
        ? 'Coupon applied successfully.'
        : ref.read(couponProvider).errorMessage ?? 'Invalid or expired coupon.';
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(message)),
      );
    }
  }
}

class _AddressCard extends StatelessWidget {
  const _AddressCard({
    required this.controller,
    required this.isConfirmed,
    required this.onChanged,
  });

  final TextEditingController controller;
  final bool isConfirmed;
  final ValueChanged<bool> onChanged;

  @override
  Widget build(BuildContext context) {
    return DailyCartCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Delivery Address',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.w900,
                ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: controller,
            maxLines: 2,
            decoration: const InputDecoration(
              labelText: 'Address',
              prefixIcon: Icon(Icons.location_on_outlined),
            ),
          ),
          const SizedBox(height: 8),
          CheckboxListTile(
            contentPadding: EdgeInsets.zero,
            value: isConfirmed,
            activeColor: AppColors.primaryGreen,
            onChanged: (value) => onChanged(value ?? false),
            title: const Text('I confirm this delivery address'),
          ),
        ],
      ),
    );
  }
}
