import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../models/loyalty_point_model.dart';
import '../theme/app_colors.dart';
import '../utils/currency_formatter.dart';
import 'dailycart_card.dart';

class LoyaltyHistoryCard extends StatelessWidget {
  const LoyaltyHistoryCard({
    required this.item,
    super.key,
  });

  final LoyaltyPointModel item;

  @override
  Widget build(BuildContext context) {
    final positive = item.type == 'earned' || item.type == 'adjusted';
    return DailyCartCard(
      child: Row(
        children: [
          CircleAvatar(
            backgroundColor: (positive
                    ? AppColors.primaryGreen
                    : AppColors.accentOrange)
                .withValues(alpha: 0.14),
            child: Icon(
              positive ? Icons.add_rounded : Icons.remove_rounded,
              color: positive ? AppColors.darkGreen : AppColors.accentOrange,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item.type.replaceAll('_', ' ').toUpperCase(),
                  style: const TextStyle(fontWeight: FontWeight.w900),
                ),
                Text(
                  item.description,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                Text(
                  DateFormat('MMM d, yyyy').format(item.createdAt),
                  style: const TextStyle(color: AppColors.mutedText),
                ),
              ],
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                '${positive ? '+' : '-'}${item.points}',
                style: TextStyle(
                  color: positive ? AppColors.darkGreen : AppColors.accentOrange,
                  fontWeight: FontWeight.w900,
                ),
              ),
              Text(CurrencyFormatter.lkr(item.value)),
            ],
          ),
        ],
      ),
    );
  }
}
