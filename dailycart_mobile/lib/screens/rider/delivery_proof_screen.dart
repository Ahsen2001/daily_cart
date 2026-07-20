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
    this.replaceExisting = false,
    super.key,
  });

  final int deliveryId;
  final bool replaceExisting;

  @override
  ConsumerState<DeliveryProofScreen> createState() =>
      _DeliveryProofScreenState();
}

class _DeliveryProofScreenState extends ConsumerState<DeliveryProofScreen> {
  final _noteController = TextEditingController();
  String? _proofImagePath;
  String? _signatureImagePath;

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
                  onPressed: () async {
                    await showModalBottomSheet<void>(
                      context: context,
                      builder: (context) => Padding(
                        padding: const EdgeInsets.all(20),
                        child: ProofImagePicker(
                          imagePath: _signatureImagePath,
                          onChanged: (path) {
                            setState(() => _signatureImagePath = path);
                            Navigator.pop(context);
                          },
                        ),
                      ),
                    );
                  },
                  icon: const Icon(Icons.draw_outlined),
                  label: Text(
                    _signatureImagePath == null
                        ? 'Add optional signature image'
                        : 'Signature selected',
                  ),
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
            label: widget.replaceExisting
                ? 'Replace Delivery Proof'
                : 'Save Proof and Mark Delivered',
            icon: Icons.check_circle_outline_rounded,
            isLoading: state.isLoading,
            onPressed: _proofImagePath == null ? null : _submit,
          ),
        ],
      ),
    );
  }

  Future<void> _submit() async {
    final provider = ref.read(riderDeliveryProvider);
    final ok = widget.replaceExisting
        ? await provider.replaceProof(
            deliveryId: widget.deliveryId,
            proofImagePath: _proofImagePath ?? '',
            signatureImagePath: _signatureImagePath,
            note: _noteController.text.trim(),
          )
        : await provider.markDelivered(
            deliveryId: widget.deliveryId,
            proofImagePath: _proofImagePath ?? '',
            signatureImagePath: _signatureImagePath,
            note: _noteController.text.trim(),
          );
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          ok
              ? widget.replaceExisting
                  ? 'Delivery proof replaced.'
                  : 'Delivery completed.'
              : 'Unable to save proof.',
        ),
      ),
    );
    if (ok) context.pop();
  }
}
