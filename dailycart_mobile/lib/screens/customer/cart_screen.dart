import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/cart_provider.dart';
import '../../providers/coupon_provider.dart';
import '../../routes/app_routes.dart';
import '../../theme/app_colors.dart';
import '../../widgets/cart_item_card.dart';
import '../../widgets/cart_summary_card.dart';
import '../../widgets/coupon_input_widget.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/empty_cart_widget.dart';
import '../../widgets/loading_widget.dart';

class CartScreen extends ConsumerStatefulWidget {
  const CartScreen({super.key});

  @override
  ConsumerState<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends ConsumerState<CartScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(cartProvider).getCart());
  }

  @override
  Widget build(BuildContext context) {
    final cart = ref.watch(cartProvider);
    final coupon = ref.watch(couponProvider);

    return Scaffold(
      appBar: CustomAppBar(
        title: 'Cart (${cart.cartCount})',
        actions: [
          IconButton(
            tooltip: 'Clear cart',
            onPressed: cart.cartItems.isEmpty ? null : _clearCart,
            icon: const Icon(Icons.delete_sweep_outlined),
          ),
        ],
      ),
      body: cart.isLoading && cart.cartItems.isEmpty
          ? const LoadingWidget(message: 'Loading cart...')
          : cart.cartItems.isEmpty
              ? const EmptyCartWidget()
              : RefreshIndicator(
                  onRefresh: () => ref.read(cartProvider).getCart(),
                  child: ListView.separated(
                    padding: const EdgeInsets.fromLTRB(20, 20, 20, 180),
                    itemBuilder: (context, index) {
                      final item = cart.cartItems[index];
                      return CartItemCard(
                        item: item,
                        onIncrease: () async {
                          final ok = await ref
                              .read(cartProvider)
                              .increaseQuantity(item);
                          _showCartMessage(ok);
                        },
                        onDecrease: () async {
                          final ok = await ref
                              .read(cartProvider)
                              .decreaseQuantity(item);
                          _showCartMessage(ok);
                        },
                        onRemove: () async {
                          final ok = await ref
                              .read(cartProvider)
                              .removeCartItem(item.id);
                          _showCartMessage(ok);
                        },
                      );
                    },
                    separatorBuilder: (context, index) =>
                        const SizedBox(height: 14),
                    itemCount: cart.cartItems.length,
                  ),
                ),
      bottomSheet: cart.cartItems.isEmpty
          ? null
          : Container(
              color: AppColors.lightBackground,
              padding: const EdgeInsets.fromLTRB(20, 12, 20, 20),
              child: SafeArea(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    CouponInputWidget(
                      appliedCoupon: coupon.appliedCoupon,
                      isLoading: coupon.isLoading,
                      onApply: _applyCoupon,
                      onRemove: () => ref.read(couponProvider).removeCoupon(),
                    ),
                    const SizedBox(height: 12),
                    CartSummaryCard(
                      summary: cart.summary,
                      action: CustomButton(
                        label: 'Prepare Checkout',
                        icon: Icons.arrow_forward_rounded,
                        onPressed: () => context.push(
                          AppRoutes.checkoutPreparation,
                        ),
                      ),
                    ),
                  ],
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

  Future<void> _clearCart() async {
    final ok = await ref.read(cartProvider).clearCart();
    _showCartMessage(ok);
  }

  void _showCartMessage(bool ok) {
    final message =
        ok ? 'Cart updated.' : ref.read(cartProvider).errorMessage ?? 'Error.';
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }
}
