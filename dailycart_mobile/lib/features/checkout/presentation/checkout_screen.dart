import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../core/constants/app_colors.dart';
import '../../../shared/widgets/dailycart_button.dart';
import '../../../shared/widgets/price_text.dart';
import '../../../shared/widgets/soft_panel.dart';
import 'payhere_webview_screen.dart';

class CheckoutScreen extends StatelessWidget {
  const CheckoutScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Checkout')),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          SoftPanel(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Delivery address',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w800,
                      ),
                ),
                const SizedBox(height: 8),
                const Text('Colombo, Sri Lanka'),
                const SizedBox(height: 12),
                OutlinedButton.icon(
                  onPressed: () => context.push('/address-map'),
                  icon: const Icon(Icons.map_outlined),
                  label: const Text('Choose on map'),
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),
          const SoftPanel(
            child: Row(
              children: [
                Expanded(child: Text('Payable amount')),
                PriceText(1540),
              ],
            ),
          ),
          const SizedBox(height: 14),
          Text(
            'Currency: LKR only',
            style: Theme.of(context).textTheme.bodySmall?.copyWith(
                  color: AppColors.mutedText,
                ),
          ),
        ],
      ),
      bottomNavigationBar: Padding(
        padding: const EdgeInsets.all(20),
        child: SafeArea(
          child: DailyCartButton(
            label: 'Pay with PayHere',
            icon: Icons.lock_rounded,
            onPressed: () {
              Navigator.of(context).push(
                MaterialPageRoute<void>(
                  builder: (_) => const PayHereWebViewScreen(
                    checkoutUrl: 'http://10.0.2.2:8000/customer/payments/1/payhere',
                  ),
                ),
              );
            },
          ),
        ),
      ),
    );
  }
}
