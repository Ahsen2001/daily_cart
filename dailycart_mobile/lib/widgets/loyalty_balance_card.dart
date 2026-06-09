import 'package:flutter/material.dart';

import '../theme/app_colors.dart';
import '../utils/currency_formatter.dart';
import 'dailycart_card.dart';

class LoyaltyBalanceCard extends StatelessWidget {
  const LoyaltyBalanceCard({
    required this.points,
    this.onHistory,
    this.onRedeem,
    super.key,
  });

  final int points;
  final VoidCallback? onHistory;
  final VoidCallback? onRedeem;

  @override
  Widget build(BuildContext context) {
    return DailyCartCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.stars_rounded, color: AppColors.accentOrange),
          const SizedBox(height: 10),
          Text(
            '$points points',
            style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                  fontWeight: FontWeight.w900,
                ),
          ),
          Text('Value ${CurrencyFormatter.lkr(points)}'),
          const SizedBox(height: 12),
          Row(
            children: [
              if (onHistory != null)
                TextButton(onPressed: onHistory, child: const Text('History')),
              const Spacer(),
              if (onRedeem != null)
                ElevatedButton(
                  onPressed: onRedeem,
                  child: const Text('Redeem'),
                ),
            ],
          ),
        ],
      ),
    );
  }
}
