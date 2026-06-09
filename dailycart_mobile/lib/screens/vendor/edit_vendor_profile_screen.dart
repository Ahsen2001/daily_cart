import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../models/vendor_profile_model.dart';
import '../../providers/vendor_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/loading_widget.dart';

class EditVendorProfileScreen extends ConsumerStatefulWidget {
  const EditVendorProfileScreen({super.key});

  @override
  ConsumerState<EditVendorProfileScreen> createState() =>
      _EditVendorProfileScreenState();
}

class _EditVendorProfileScreenState
    extends ConsumerState<EditVendorProfileScreen> {
  final _formKey = GlobalKey<FormState>();
  final _shopController = TextEditingController();
  final _ownerController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _addressController = TextEditingController();
  final _registrationController = TextEditingController();
  bool _filled = false;

  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(vendorProvider).getVendorProfile());
  }

  @override
  void dispose() {
    _shopController.dispose();
    _ownerController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _addressController.dispose();
    _registrationController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(vendorProvider);
    final profile = state.profile;
    if (profile != null && !_filled) {
      _filled = true;
      _shopController.text = profile.shopName;
      _ownerController.text = profile.ownerName;
      _emailController.text = profile.email;
      _phoneController.text = profile.phone;
      _addressController.text = profile.address;
      _registrationController.text = profile.businessRegistrationNumber;
    }

    return Scaffold(
      appBar: const CustomAppBar(title: 'Edit Vendor Profile'),
      body: state.isLoading && profile == null
          ? const LoadingWidget(message: 'Loading vendor profile...')
          : profile == null
              ? const Center(child: Text('Vendor profile not found.'))
              : ListView(
                  padding: const EdgeInsets.all(20),
                  children: [
                    DailyCartCard(
                      child: Form(
                        key: _formKey,
                        child: Column(
                          children: [
                            CustomTextField(
                              label: 'Shop Name',
                              controller: _shopController,
                              icon: Icons.storefront_outlined,
                              validator: _required,
                            ),
                            const SizedBox(height: 12),
                            CustomTextField(
                              label: 'Owner Name',
                              controller: _ownerController,
                              icon: Icons.person_outline_rounded,
                              validator: _required,
                            ),
                            const SizedBox(height: 12),
                            CustomTextField(
                              label: 'Email',
                              controller: _emailController,
                              icon: Icons.email_outlined,
                              keyboardType: TextInputType.emailAddress,
                              validator: _required,
                            ),
                            const SizedBox(height: 12),
                            CustomTextField(
                              label: 'Phone',
                              controller: _phoneController,
                              icon: Icons.phone_outlined,
                              validator: _required,
                            ),
                            const SizedBox(height: 12),
                            CustomTextField(
                              label: 'Address',
                              controller: _addressController,
                              icon: Icons.location_on_outlined,
                            ),
                            const SizedBox(height: 12),
                            CustomTextField(
                              label: 'Business Registration Number',
                              controller: _registrationController,
                              icon: Icons.badge_outlined,
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 20),
                    CustomButton(
                      label: 'Save Vendor Profile',
                      isLoading: state.isLoading,
                      onPressed: () => _save(profile),
                    ),
                  ],
                ),
    );
  }

  Future<void> _save(VendorProfileModel profile) async {
    if (!_formKey.currentState!.validate()) {
      return;
    }
    final updated = VendorProfileModel(
      id: profile.id,
      shopName: _shopController.text.trim(),
      ownerName: _ownerController.text.trim(),
      email: _emailController.text.trim(),
      phone: _phoneController.text.trim(),
      address: _addressController.text.trim(),
      businessRegistrationNumber: _registrationController.text.trim(),
      approvalStatus: profile.approvalStatus,
      shopLogo: profile.shopLogo,
    );
    final ok = await ref.read(vendorProvider).updateVendorProfile(updated);
    if (!mounted) {
      return;
    }
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(ok ? 'Vendor profile updated.' : 'Unable to update profile.')),
    );
    if (ok) {
      context.pop();
    }
  }

  static String? _required(String? value) {
    return value == null || value.trim().isEmpty ? 'This field is required.' : null;
  }
}
