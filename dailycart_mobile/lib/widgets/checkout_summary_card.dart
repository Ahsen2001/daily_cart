import 'package:flutter/material.dart';

import '../models/cart_summary_model.dart';
import 'cart_summary_card.dart';

class CheckoutSummaryCard extends StatelessWidget {
  const CheckoutSummaryCard({
    required this.summary,
    this.action,
    super.key,
  });

  final CartSummaryModel summary;
  final Widget? action;

  @override
  Widget build(BuildContext context) {
    return CartSummaryCard(summary: summary, action: action);
  }
}
