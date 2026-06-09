import 'package:flutter/material.dart';

import '../models/coupon_model.dart';
import '../theme/app_colors.dart';
import '../utils/currency_formatter.dart';
import 'dailycart_card.dart';

class CouponCard extends StatelessWidget {
  const CouponCard({
    required this.coupon,
    this.onCopy,
    this.onApply,
    super.key,
  });

  final CouponModel coupon;
  final VoidCallback? onCopy;
  final VoidCallback? onApply;

  @override
  Widget build(BuildContext context) {
    final discountText = switch (coupon.type) {
      CouponType.percentage => '${coupon.discount.toStringAsFixed(0)}% off',
      CouponType.freeDelivery => 'Free delivery',
      CouponType.fixedAmount => '${CurrencyFormatter.lkr(coupon.discount)} off',
    };

    return DailyCartCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                decoration: BoxDecoration(
                  color: AppColors.accentOrange,
                  borderRadius: BorderRadius.circular(999),
                ),
                child: Text(
                  discountText,
                  style: const TextStyle(
                    color: AppColors.white,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
              const Spacer(),
              Text(
                coupon.code,
                style: const TextStyle(fontWeight: FontWeight.w900),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            coupon.title.isEmpty ? coupon.typeLabel : coupon.title,
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.w900,
                ),
          ),
          if (coupon.description.isNotEmpty) ...[
            const SizedBox(height: 4),
            Text(coupon.description),
          ],
          if (coupon.minOrderAmount > 0) ...[
            const SizedBox(height: 6),
            Text('Minimum order ${CurrencyFormatter.lkr(coupon.minOrderAmount)}'),
          ],
          const SizedBox(height: 12),
          Row(
            children: [
              OutlinedButton.icon(
                onPressed: onCopy,
                icon: const Icon(Icons.copy_rounded),
                label: const Text('Copy'),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: ElevatedButton(
                  onPressed: onApply,
                  child: const Text('Apply'),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
