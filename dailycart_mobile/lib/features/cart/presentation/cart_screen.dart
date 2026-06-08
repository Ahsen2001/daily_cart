import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../shared/widgets/dailycart_button.dart';
import '../../../shared/widgets/price_text.dart';
import '../../../shared/widgets/soft_panel.dart';

class CartScreen extends StatelessWidget {
  const CartScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Cart')),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: const [
          SoftPanel(
            child: Row(
              children: [
                Icon(Icons.shopping_basket_outlined),
                SizedBox(width: 14),
                Expanded(child: Text('Fresh Carrots x 1')),
                PriceText(650),
              ],
            ),
          ),
          SizedBox(height: 12),
          SoftPanel(
            child: Row(
              children: [
                Icon(Icons.shopping_basket_outlined),
                SizedBox(width: 14),
                Expanded(child: Text('Green Apples x 1')),
                PriceText(890),
              ],
            ),
          ),
          SizedBox(height: 20),
          SoftPanel(
            child: Row(
              children: [
                Expanded(child: Text('Total')),
                PriceText(1540),
              ],
            ),
          ),
        ],
      ),
      bottomNavigationBar: Padding(
        padding: const EdgeInsets.all(20),
        child: SafeArea(
          child: DailyCartButton(
            label: 'Checkout',
            icon: Icons.payments_rounded,
            onPressed: () => context.push('/checkout'),
          ),
        ),
      ),
    );
  }
}
