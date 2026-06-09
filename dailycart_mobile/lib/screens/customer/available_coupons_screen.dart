import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../providers/coupon_provider.dart';
import '../../widgets/coupon_card.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state_widget.dart';
import '../../widgets/loading_widget.dart';

class AvailableCouponsScreen extends ConsumerStatefulWidget {
  const AvailableCouponsScreen({super.key});

  @override
  ConsumerState<AvailableCouponsScreen> createState() =>
      _AvailableCouponsScreenState();
}

class _AvailableCouponsScreenState
    extends ConsumerState<AvailableCouponsScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(couponProvider).getAvailableCoupons());
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(couponProvider);
    return Scaffold(
      appBar: const CustomAppBar(title: 'Available Coupons'),
      body: state.isLoading && state.coupons.isEmpty
          ? const LoadingWidget(message: 'Loading coupons...')
          : state.coupons.isEmpty
              ? const EmptyStateWidget(
                  title: 'No coupons available',
                  message: 'Valid coupons and offers will appear here.',
                  icon: Icons.confirmation_number_outlined,
                )
              : ListView.separated(
                  padding: const EdgeInsets.all(20),
                  itemBuilder: (context, index) {
                    final coupon = state.coupons[index];
                    return CouponCard(
                      coupon: coupon,
                      onCopy: () {
                        Clipboard.setData(ClipboardData(text: coupon.code));
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(content: Text('${coupon.code} copied.')),
                        );
                      },
                      onApply: () async {
                        final ok = await ref
                            .read(couponProvider)
                            .validateCoupon(coupon.code);
                        if (!context.mounted) {
                          return;
                        }
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(
                              ok
                                  ? 'Coupon is valid for checkout.'
                                  : ref.read(couponProvider).errorMessage ??
                                      'Invalid or expired coupon.',
                            ),
                          ),
                        );
                      },
                    );
                  },
                  separatorBuilder: (context, index) =>
                      const SizedBox(height: 14),
                  itemCount: state.coupons.length,
                ),
    );
  }
}
