import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../models/address_model.dart';
import '../../providers/address_provider.dart';
import '../../theme/app_colors.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';
import '../../widgets/dailycart_card.dart';
import 'map_picker_screen.dart';

class AddEditAddressScreen extends ConsumerStatefulWidget {
  const AddEditAddressScreen({
    this.address,
    super.key,
  });

  final AddressModel? address;

  @override
  ConsumerState<AddEditAddressScreen> createState() =>
      _AddEditAddressScreenState();
}

class _AddEditAddressScreenState extends ConsumerState<AddEditAddressScreen> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _fullNameController;
  late final TextEditingController _phoneController;
  late final TextEditingController _line1Controller;
  late final TextEditingController _line2Controller;
  late final TextEditingController _cityController;
  late final TextEditingController _districtController;
  late final TextEditingController _postalCodeController;
  late final TextEditingController _landmarkController;
  late final TextEditingController _latitudeController;
  late final TextEditingController _longitudeController;
  bool _isDefault = false;

  bool get _isEditing => widget.address != null;

  @override
  void initState() {
    super.initState();
    final address = widget.address;
    _fullNameController = TextEditingController(text: address?.fullName ?? '');
    _phoneController = TextEditingController(text: address?.phoneNumber ?? '');
    _line1Controller = TextEditingController(text: address?.addressLine1 ?? '');
    _line2Controller = TextEditingController(text: address?.addressLine2 ?? '');
    _cityController = TextEditingController(text: address?.city ?? '');
    _districtController = TextEditingController(text: address?.district ?? '');
    _postalCodeController = TextEditingController(text: address?.postalCode ?? '');
    _landmarkController = TextEditingController(text: address?.landmark ?? '');
    _latitudeController = TextEditingController(
      text: address?.latitude?.toString() ?? '',
    );
    _longitudeController = TextEditingController(
      text: address?.longitude?.toString() ?? '',
    );
    _isDefault = address?.isDefault ?? false;
  }

  @override
  void dispose() {
    _fullNameController.dispose();
    _phoneController.dispose();
    _line1Controller.dispose();
    _line2Controller.dispose();
    _cityController.dispose();
    _districtController.dispose();
    _postalCodeController.dispose();
    _landmarkController.dispose();
    _latitudeController.dispose();
    _longitudeController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final addressState = ref.watch(addressProvider);

    return Scaffold(
      appBar: CustomAppBar(title: _isEditing ? 'Edit Address' : 'Add Address'),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(20),
          children: [
            DailyCartCard(
              child: Column(
                children: [
                  CustomTextField(
                    label: 'Full name',
                    controller: _fullNameController,
                    icon: Icons.person_outline_rounded,
                    validator: _required('Full name is required.'),
                  ),
                  const SizedBox(height: 12),
                  CustomTextField(
                    label: 'Phone number',
                    controller: _phoneController,
                    icon: Icons.phone_outlined,
                    keyboardType: TextInputType.phone,
                    validator: _required('Phone number is required.'),
                  ),
                  const SizedBox(height: 12),
                  CustomTextField(
                    label: 'Address line 1',
                    controller: _line1Controller,
                    icon: Icons.home_outlined,
                    validator: _required('Address line 1 is required.'),
                  ),
                  const SizedBox(height: 12),
                  CustomTextField(
                    label: 'Address line 2',
                    controller: _line2Controller,
                    icon: Icons.home_work_outlined,
                  ),
                  const SizedBox(height: 12),
                  CustomTextField(
                    label: 'City',
                    controller: _cityController,
                    icon: Icons.location_city_outlined,
                    validator: _required('City is required.'),
                  ),
                  const SizedBox(height: 12),
                  CustomTextField(
                    label: 'District',
                    controller: _districtController,
                    icon: Icons.map_outlined,
                    validator: _required('District is required.'),
                  ),
                  const SizedBox(height: 12),
                  CustomTextField(
                    label: 'Postal code',
                    controller: _postalCodeController,
                    icon: Icons.local_post_office_outlined,
                    keyboardType: TextInputType.number,
                  ),
                  const SizedBox(height: 12),
                  CustomTextField(
                    label: 'Landmark',
                    controller: _landmarkController,
                    icon: Icons.place_outlined,
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: CustomTextField(
                          label: 'Latitude',
                          controller: _latitudeController,
                          keyboardType: TextInputType.number,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: CustomTextField(
                          label: 'Longitude',
                          controller: _longitudeController,
                          keyboardType: TextInputType.number,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  OutlinedButton.icon(
                    onPressed: _openMapPicker,
                    icon: const Icon(Icons.map_outlined),
                    label: const Text('Choose on Google Maps'),
                  ),
                  CheckboxListTile(
                    contentPadding: EdgeInsets.zero,
                    value: _isDefault,
                    activeColor: AppColors.primaryGreen,
                    onChanged: (value) {
                      setState(() => _isDefault = value ?? false);
                    },
                    title: const Text('Set as default address'),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),
            CustomButton(
              label: _isEditing ? 'Update Address' : 'Save Address',
              icon: Icons.save_outlined,
              isLoading: addressState.isLoading,
              onPressed: _saveAddress,
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _saveAddress() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    final address = AddressModel(
      id: widget.address?.id ?? 0,
      fullName: _fullNameController.text.trim(),
      phoneNumber: _phoneController.text.trim(),
      addressLine1: _line1Controller.text.trim(),
      addressLine2: _line2Controller.text.trim(),
      city: _cityController.text.trim(),
      district: _districtController.text.trim(),
      postalCode: _postalCodeController.text.trim(),
      landmark: _landmarkController.text.trim(),
      latitude: double.tryParse(_latitudeController.text.trim()),
      longitude: double.tryParse(_longitudeController.text.trim()),
      isDefault: _isDefault,
    );

    final ok = _isEditing
        ? await ref.read(addressProvider).updateAddress(address)
        : await ref.read(addressProvider).addAddress(address);

    if (!mounted) {
      return;
    }

    if (ok) {
      context.pop();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            ref.read(addressProvider).errorMessage ?? 'Unable to save address.',
          ),
        ),
      );
    }
  }

  Future<void> _openMapPicker() async {
    final result = await Navigator.of(context).push<Map<String, Object?>>(
      MaterialPageRoute(
        builder: (_) => MapPickerScreen(
          initialLatitude: double.tryParse(_latitudeController.text),
          initialLongitude: double.tryParse(_longitudeController.text),
        ),
      ),
    );
    if (result == null || !mounted) return;
    setState(() {
      _latitudeController.text = result['latitude'].toString();
      _longitudeController.text = result['longitude'].toString();
      _setWhenPresent(_line1Controller, result['address_line_1']);
      _setWhenPresent(_cityController, result['city']);
      _setWhenPresent(_districtController, result['district']);
      _setWhenPresent(_postalCodeController, result['postal_code']);
    });
  }

  void _setWhenPresent(TextEditingController controller, Object? value) {
    final text = value?.toString().trim() ?? '';
    if (text.isNotEmpty && controller.text.trim().isEmpty) {
      controller.text = text;
    }
  }

  String? Function(String?) _required(String message) {
    return (value) {
      if (value == null || value.trim().isEmpty) {
        return message;
      }
      return null;
    };
  }
}
