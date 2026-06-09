import 'package:flutter/material.dart';

import '../models/coupon_model.dart';
import '../theme/app_colors.dart';
import 'custom_button.dart';
import 'dailycart_card.dart';

class CouponInputWidget extends StatefulWidget {
  const CouponInputWidget({
    required this.appliedCoupon,
    required this.isLoading,
    required this.onApply,
    required this.onRemove,
    super.key,
  });

  final CouponModel? appliedCoupon;
  final bool isLoading;
  final ValueChanged<String> onApply;
  final VoidCallback onRemove;

  @override
  State<CouponInputWidget> createState() => _CouponInputWidgetState();
}

class _CouponInputWidgetState extends State<CouponInputWidget> {
  final _controller = TextEditingController();

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final coupon = widget.appliedCoupon;

    return DailyCartCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Coupon',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.w900,
                ),
          ),
          const SizedBox(height: 12),
          if (coupon == null) ...[
            TextField(
              controller: _controller,
              textCapitalization: TextCapitalization.characters,
              decoration: const InputDecoration(
                labelText: 'Coupon Code',
                prefixIcon: Icon(Icons.local_offer_outlined),
              ),
            ),
            const SizedBox(height: 12),
            CustomButton(
              label: 'Apply Coupon',
              icon: Icons.check_rounded,
              isLoading: widget.isLoading,
              onPressed: () => widget.onApply(_controller.text),
            ),
          ] else ...[
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppColors.accentOrange.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Row(
                children: [
                  const Icon(
                    Icons.local_offer_rounded,
                    color: AppColors.accentOrange,
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      '${coupon.code} applied',
                      style: const TextStyle(fontWeight: FontWeight.w800),
                    ),
                  ),
                  TextButton(
                    onPressed: widget.onRemove,
                    child: const Text('Remove'),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }
}
