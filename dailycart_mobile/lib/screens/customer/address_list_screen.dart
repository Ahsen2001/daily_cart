import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../models/address_model.dart';
import '../../providers/address_provider.dart';
import '../../providers/checkout_provider.dart';
import '../../routes/app_routes.dart';
import '../../widgets/address_card.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state_widget.dart';
import '../../widgets/loading_widget.dart';

class AddressListScreen extends ConsumerStatefulWidget {
  const AddressListScreen({super.key});

  @override
  ConsumerState<AddressListScreen> createState() => _AddressListScreenState();
}

class _AddressListScreenState extends ConsumerState<AddressListScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(addressProvider).getAddresses());
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(addressProvider);
    final checkout = ref.watch(checkoutProvider);

    return Scaffold(
      appBar: CustomAppBar(
        title: 'Addresses',
        actions: [
          IconButton(
            tooltip: 'Add address',
            onPressed: () => context.push(AppRoutes.addressForm),
            icon: const Icon(Icons.add_location_alt_outlined),
          ),
        ],
      ),
      body: state.isLoading && state.addresses.isEmpty
          ? const LoadingWidget(message: 'Loading addresses...')
          : state.addresses.isEmpty
              ? const EmptyStateWidget(
                  title: 'No addresses',
                  message: 'Add a delivery address to continue checkout.',
                  icon: Icons.location_on_outlined,
                )
              : ListView.separated(
                  padding: const EdgeInsets.all(20),
                  itemBuilder: (context, index) {
                    final address = state.addresses[index];
                    return AddressCard(
                      address: address,
                      isSelected: checkout.selectedAddress?.id == address.id,
                      onSelect: () {
                        ref.read(addressProvider).selectAddress(address);
                        ref.read(checkoutProvider).selectAddress(address);
                        context.pop();
                      },
                      onEdit: () => context.push(
                        AppRoutes.addressForm,
                        extra: address,
                      ),
                      onDelete: () => _deleteAddress(address),
                      onSetDefault: () async {
                        await ref
                            .read(addressProvider)
                            .setDefaultAddress(address);
                      },
                    );
                  },
                  separatorBuilder: (context, index) =>
                      const SizedBox(height: 14),
                  itemCount: state.addresses.length,
                ),
    );
  }

  Future<void> _deleteAddress(AddressModel address) async {
    final ok = await ref.read(addressProvider).deleteAddress(address.id);
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(ok ? 'Address deleted.' : 'Unable to delete address.'),
        ),
      );
    }
  }
}
