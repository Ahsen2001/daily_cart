import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/checkout_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/delivery_time_picker.dart';

class DeliveryScheduleScreen extends ConsumerWidget {
  const DeliveryScheduleScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final checkout = ref.watch(checkoutProvider);

    return Scaffold(
      appBar: const CustomAppBar(title: 'Delivery Schedule'),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          DeliveryTimePicker(
            selectedTime: checkout.selectedDeliveryTime,
            minimumDeliveryTime: checkout.minimumDeliveryTime,
            onSelected: (value) {
              final ok = ref.read(checkoutProvider).selectDeliveryTime(value);
              if (!ok) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(ref.read(checkoutProvider).errorMessage!),
                  ),
                );
              }
            },
          ),
          const SizedBox(height: 20),
          CustomButton(
            label: 'Use This Time',
            icon: Icons.check_rounded,
            onPressed: checkout.selectedDeliveryTime == null
                ? null
                : () => context.pop(),
          ),
        ],
      ),
    );
  }
}
