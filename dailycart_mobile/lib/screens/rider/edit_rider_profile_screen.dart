import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../models/rider_profile_model.dart';
import '../../providers/rider_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/loading_widget.dart';

class EditRiderProfileScreen extends ConsumerStatefulWidget {
  const EditRiderProfileScreen({super.key});

  @override
  ConsumerState<EditRiderProfileScreen> createState() =>
      _EditRiderProfileScreenState();
}

class _EditRiderProfileScreenState
    extends ConsumerState<EditRiderProfileScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _vehicleTypeController = TextEditingController();
  final _vehicleNumberController = TextEditingController();
  final _licenseController = TextEditingController();
  bool _filled = false;

  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(riderProvider).getRiderProfile());
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _vehicleTypeController.dispose();
    _vehicleNumberController.dispose();
    _licenseController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(riderProvider);
    final profile = state.profile;
    if (profile != null && !_filled) {
      _filled = true;
      _nameController.text = profile.name;
      _emailController.text = profile.email;
      _phoneController.text = profile.phone;
      _vehicleTypeController.text = profile.vehicleType;
      _vehicleNumberController.text = profile.vehicleNumber;
      _licenseController.text = profile.licenseNumber;
    }

    return Scaffold(
      appBar: const CustomAppBar(title: 'Edit Rider Profile'),
      body: state.isLoading && profile == null
          ? const LoadingWidget(message: 'Loading rider profile...')
          : profile == null
              ? const Center(child: Text('Rider profile not found.'))
              : ListView(
                  padding: const EdgeInsets.all(20),
                  children: [
                    DailyCartCard(
                      child: Form(
                        key: _formKey,
                        child: Column(
                          children: [
                            CustomTextField(
                              label: 'Rider Name',
                              controller: _nameController,
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
                              label: 'Vehicle Type',
                              controller: _vehicleTypeController,
                              icon: Icons.two_wheeler_outlined,
                            ),
                            const SizedBox(height: 12),
                            CustomTextField(
                              label: 'Vehicle Number',
                              controller: _vehicleNumberController,
                              icon: Icons.pin_outlined,
                            ),
                            const SizedBox(height: 12),
                            CustomTextField(
                              label: 'License Number',
                              controller: _licenseController,
                              icon: Icons.badge_outlined,
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 20),
                    CustomButton(
                      label: 'Save Rider Profile',
                      isLoading: state.isLoading,
                      onPressed: () => _save(profile),
                    ),
                  ],
                ),
    );
  }

  Future<void> _save(RiderProfileModel profile) async {
    if (!_formKey.currentState!.validate()) return;
    final updated = RiderProfileModel(
      id: profile.id,
      name: _nameController.text.trim(),
      email: _emailController.text.trim(),
      phone: _phoneController.text.trim(),
      vehicleType: _vehicleTypeController.text.trim(),
      vehicleNumber: _vehicleNumberController.text.trim(),
      licenseNumber: _licenseController.text.trim(),
      approvalStatus: profile.approvalStatus,
      profilePhoto: profile.profilePhoto,
    );
    final ok = await ref.read(riderProvider).updateRiderProfile(updated);
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(ok ? 'Rider profile updated.' : 'Unable to update profile.')),
    );
    if (ok) context.pop();
  }

  static String? _required(String? value) {
    return value == null || value.trim().isEmpty ? 'This field is required.' : null;
  }
}
