import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../models/payment_method_model.dart';
import '../../providers/checkout_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/payment_method_card.dart';

class PaymentMethodScreen extends ConsumerWidget {
  const PaymentMethodScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final checkout = ref.watch(checkoutProvider);

    return Scaffold(
      appBar: const CustomAppBar(title: 'Payment Method'),
      body: ListView.separated(
        padding: const EdgeInsets.all(20),
        itemBuilder: (context, index) {
          final method = PaymentMethodModel.availableMethods[index];
          return PaymentMethodCard(
            method: method,
            isSelected: checkout.selectedPaymentMethod == method.type,
            onTap: () => ref
                .read(checkoutProvider)
                .selectPaymentMethod(method.type),
          );
        },
        separatorBuilder: (context, index) => const SizedBox(height: 14),
        itemCount: PaymentMethodModel.availableMethods.length,
      ),
      bottomNavigationBar: Padding(
        padding: const EdgeInsets.all(20),
        child: SafeArea(
          child: CustomButton(
            label: 'Use Payment Method',
            icon: Icons.check_rounded,
            onPressed: () => context.pop(),
          ),
        ),
      ),
    );
  }
}
