import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../providers/vendor_product_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';
import '../../widgets/dailycart_card.dart';

class InventoryScreen extends ConsumerStatefulWidget {
  const InventoryScreen({
    required this.productId,
    super.key,
  });

  final int productId;

  @override
  ConsumerState<InventoryScreen> createState() => _InventoryScreenState();
}

class _InventoryScreenState extends ConsumerState<InventoryScreen> {
  final _stockController = TextEditingController();
  final _expiryController = TextEditingController();

  @override
  void dispose() {
    _stockController.dispose();
    _expiryController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(vendorProductProvider);
    return Scaffold(
      appBar: const CustomAppBar(title: 'Inventory'),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          DailyCartCard(
            child: Column(
              children: [
                CustomTextField(
                  label: 'Stock Quantity',
                  controller: _stockController,
                  icon: Icons.inventory_outlined,
                  keyboardType: TextInputType.number,
                ),
                const SizedBox(height: 12),
                CustomTextField(
                  label: 'Expiry Date YYYY-MM-DD',
                  controller: _expiryController,
                  icon: Icons.event_outlined,
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),
          CustomButton(
            label: 'Update Inventory',
            isLoading: state.isLoading,
            onPressed: _save,
          ),
        ],
      ),
    );
  }

  Future<void> _save() async {
    final ok = await ref.read(vendorProductProvider).updateInventory(
          productId: widget.productId,
          stockQuantity: int.tryParse(_stockController.text.trim()) ?? 0,
          expiryDate: DateTime.tryParse(_expiryController.text.trim()),
        );
    if (!mounted) {
      return;
    }
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(ok ? 'Inventory updated.' : 'Unable to update inventory.')),
    );
  }
}
