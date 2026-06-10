import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/rider_delivery_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/proof_image_picker.dart';

class DeliveryProofScreen extends ConsumerStatefulWidget {
  const DeliveryProofScreen({
    required this.deliveryId,
    super.key,
  });

  final int deliveryId;

  @override
  ConsumerState<DeliveryProofScreen> createState() =>
      _DeliveryProofScreenState();
}

class _DeliveryProofScreenState extends ConsumerState<DeliveryProofScreen> {
  final _noteController = TextEditingController();
  String? _proofImagePath;

  @override
  void dispose() {
    _noteController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(riderDeliveryProvider);
    return Scaffold(
      appBar: const CustomAppBar(title: 'Delivery Proof'),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          DailyCartCard(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                ProofImagePicker(
                  imagePath: _proofImagePath,
                  onChanged: (path) => setState(() => _proofImagePath = path),
                ),
                const SizedBox(height: 12),
                OutlinedButton.icon(
                  onPressed: () {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Customer signature placeholder.'),
                      ),
                    );
                  },
                  icon: const Icon(Icons.draw_outlined),
                  label: const Text('Signature placeholder'),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _noteController,
                  maxLines: 4,
                  decoration: const InputDecoration(
                    labelText: 'Delivery note',
                    alignLabelWithHint: true,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),
          CustomButton(
            label: 'Save Proof and Mark Delivered',
            icon: Icons.check_circle_outline_rounded,
            isLoading: state.isLoading,
            onPressed: _proofImagePath == null ? null : _submit,
          ),
        ],
      ),
    );
  }

  Future<void> _submit() async {
    final ok = await ref.read(riderDeliveryProvider).markDelivered(
          deliveryId: widget.deliveryId,
          proofImagePath: _proofImagePath ?? '',
          note: _noteController.text.trim(),
        );
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(ok ? 'Delivery completed.' : 'Unable to save proof.')),
    );
    if (ok) context.pop();
  }
}
