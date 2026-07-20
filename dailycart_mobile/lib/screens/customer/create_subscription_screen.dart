import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../models/product_model.dart';
import '../../providers/checkout_provider.dart';
import '../../providers/customer_extended_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';
import '../../widgets/dailycart_card.dart';

class CreateSubscriptionScreen extends ConsumerStatefulWidget {
  const CreateSubscriptionScreen({
    required this.product,
    this.variant,
    super.key,
  });

  final ProductModel product;
  final ProductVariantModel? variant;

  @override
  ConsumerState<CreateSubscriptionScreen> createState() =>
      _CreateSubscriptionScreenState();
}

class _CreateSubscriptionScreenState
    extends ConsumerState<CreateSubscriptionScreen> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _quantity;
  late final TextEditingController _address;
  late final TextEditingController _startDate;
  late final TextEditingController _time;
  final _notes = TextEditingController();
  String _frequency = 'weekly';

  @override
  void initState() {
    super.initState();
    _quantity = TextEditingController(text: '1');
    _address = TextEditingController(
      text: ref.read(checkoutProvider).selectedAddress?.displayAddress ?? '',
    );
    _startDate = TextEditingController(
      text: DateTime.now()
          .add(const Duration(days: 1))
          .toIso8601String()
          .split('T')
          .first,
    );
    _time = TextEditingController(text: '09:00');
  }

  @override
  void dispose() {
    _quantity.dispose();
    _address.dispose();
    _startDate.dispose();
    _time.dispose();
    _notes.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(customerExtendedProvider);
    return Scaffold(
      appBar: const CustomAppBar(title: 'Create Subscription'),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(20),
          children: [
            DailyCartCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    widget.product.name,
                    style: Theme.of(context)
                        .textTheme
                        .titleLarge
                        ?.copyWith(fontWeight: FontWeight.w900),
                  ),
                  if (widget.variant != null)
                    Text('Variant: ${widget.variant!.name}'),
                  const SizedBox(height: 16),
                  DropdownButtonFormField<String>(
                    initialValue: _frequency,
                    decoration: const InputDecoration(labelText: 'Frequency'),
                    items: const [
                      DropdownMenuItem(value: 'daily', child: Text('Daily')),
                      DropdownMenuItem(value: 'weekly', child: Text('Weekly')),
                      DropdownMenuItem(value: 'monthly', child: Text('Monthly')),
                    ],
                    onChanged: (value) =>
                        setState(() => _frequency = value ?? 'weekly'),
                  ),
                  const SizedBox(height: 12),
                  CustomTextField(
                    label: 'Quantity',
                    controller: _quantity,
                    keyboardType: TextInputType.number,
                    validator: _required,
                  ),
                  const SizedBox(height: 12),
                  CustomTextField(
                    label: 'Delivery address',
                    controller: _address,
                    maxLines: 3,
                    validator: _required,
                  ),
                  const SizedBox(height: 12),
                  CustomTextField(
                    label: 'Start date (YYYY-MM-DD)',
                    controller: _startDate,
                    validator: _required,
                  ),
                  const SizedBox(height: 12),
                  CustomTextField(
                    label: 'Preferred time (HH:mm)',
                    controller: _time,
                    validator: _required,
                  ),
                  const SizedBox(height: 12),
                  CustomTextField(
                    label: 'Notes',
                    controller: _notes,
                    maxLines: 3,
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),
            CustomButton(
              label: 'Start Subscription',
              icon: Icons.autorenew,
              isLoading: state.isLoading,
              onPressed: _submit,
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    final ok = await ref.read(customerExtendedProvider).createSubscription({
      'product_id': widget.product.id,
      if (widget.variant != null) 'product_variant_id': widget.variant!.id,
      'frequency': _frequency,
      'quantity': int.tryParse(_quantity.text) ?? 1,
      'delivery_address': _address.text.trim(),
      'preferred_delivery_time': _time.text.trim(),
      'start_date': _startDate.text.trim(),
      'payment_method': 'cash_on_delivery',
      if (_notes.text.trim().isNotEmpty) 'notes': _notes.text.trim(),
    });
    if (!mounted) return;
    if (ok) {
      Navigator.of(context).pop();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            ref.read(customerExtendedProvider).errorMessage ??
                'Unable to create subscription.',
          ),
        ),
      );
    }
  }

  String? _required(String? value) {
    return value == null || value.trim().isEmpty ? 'Required.' : null;
  }
}
