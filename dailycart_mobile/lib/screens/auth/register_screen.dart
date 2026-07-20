import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../config/app_identity.dart';
import '../../models/user_role.dart';
import '../../providers/auth_provider.dart';
import '../../routes/app_routes.dart';
import '../../theme/app_colors.dart';
import '../../utils/responsive.dart';
import '../../widgets/app_logo.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';
import '../../widgets/dailycart_card.dart';

class RegisterScreen extends ConsumerStatefulWidget {
  const RegisterScreen({super.key});

  @override
  ConsumerState<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends ConsumerState<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  final _storeNameController = TextEditingController();
  final _businessRegistrationController = TextEditingController();
  final _vehicleNumberController = TextEditingController();
  final _licenseNumberController = TextEditingController();
  final _addressController = TextEditingController();
  final _cityController = TextEditingController();
  final _districtController = TextEditingController();
  final _provinceController = TextEditingController();
  final _latitudeController = TextEditingController();
  final _longitudeController = TextEditingController();

  String _vehicleType = 'motorbike';

  UserRole get _role => UserRole.fromName(AppIdentity.flavor.name);

  bool get _usesHomeBase =>
      _role == UserRole.vendor || _role == UserRole.rider;

  @override
  void dispose() {
    for (final controller in [
      _nameController,
      _emailController,
      _phoneController,
      _passwordController,
      _confirmPasswordController,
      _storeNameController,
      _businessRegistrationController,
      _vehicleNumberController,
      _licenseNumberController,
      _addressController,
      _cityController,
      _districtController,
      _provinceController,
      _latitudeController,
      _longitudeController,
    ]) {
      controller.dispose();
    }
    super.dispose();
  }

  Future<void> _register() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    final roleData = <String, dynamic>{};
    if (_role == UserRole.vendor) {
      roleData.addAll({
        'store_name': _storeNameController.text.trim(),
        'business_registration_no':
            _nullIfEmpty(_businessRegistrationController.text),
      });
    }
    if (_role == UserRole.rider) {
      roleData.addAll({
        'vehicle_type': _vehicleType,
        'vehicle_number': _nullIfEmpty(_vehicleNumberController.text),
        'license_number': _nullIfEmpty(_licenseNumberController.text),
      });
    }
    if (_usesHomeBase) {
      roleData.addAll({
        'address': _addressController.text.trim(),
        'city': _cityController.text.trim(),
        'district': _districtController.text.trim(),
        'province': _provinceController.text.trim(),
        'formatted_address': _addressController.text.trim(),
        'latitude': _doubleOrNull(_latitudeController.text),
        'longitude': _doubleOrNull(_longitudeController.text),
      });
    }

    final result = await ref.read(authProvider).register(
          name: _nameController.text.trim(),
          email: _emailController.text.trim(),
          phone: _phoneController.text.trim(),
          password: _passwordController.text,
          passwordConfirmation: _confirmPasswordController.text,
          role: _role,
          roleData: roleData,
        );

    if (!mounted) {
      return;
    }

    if (result.requiresVerification) {
      context.go(AppRoutes.otpVerification);
      return;
    }
    if (result.requiresApproval) {
      context.go(AppRoutes.pendingApproval, extra: result.message);
      return;
    }
    if (result.isSuccess && result.redirectRoute != null) {
      context.go(result.redirectRoute!);
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(result.message)),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = ref.watch(authProvider);

    return Scaffold(
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: EdgeInsets.symmetric(
              horizontal: Responsive.horizontalPadding(context),
              vertical: 24,
            ),
            child: ConstrainedBox(
              constraints: BoxConstraints(
                maxWidth: Responsive.maxContentWidth(context),
              ),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Center(child: AppLogo(size: 86)),
                    const SizedBox(height: 24),
                    Text(
                      'Create ${_role.label.toLowerCase()} account',
                      style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                            color: AppColors.textColor,
                            fontWeight: FontWeight.w900,
                          ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'This registration is for ${AppIdentity.displayName}.',
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                            color: AppColors.mutedText,
                          ),
                    ),
                    const SizedBox(height: 24),
                    DailyCartCard(
                      child: Column(
                        children: [
                          CustomTextField(
                            label: 'Full Name',
                            controller: _nameController,
                            icon: Icons.person_outline_rounded,
                            textInputAction: TextInputAction.next,
                            validator: _required('Name is required.'),
                          ),
                          const SizedBox(height: 14),
                          CustomTextField(
                            label: 'Email',
                            controller: _emailController,
                            icon: Icons.mail_outline_rounded,
                            keyboardType: TextInputType.emailAddress,
                            textInputAction: TextInputAction.next,
                            validator: _validateEmail,
                          ),
                          const SizedBox(height: 14),
                          CustomTextField(
                            label: 'Phone Number',
                            controller: _phoneController,
                            icon: Icons.phone_outlined,
                            keyboardType: TextInputType.phone,
                            textInputAction: TextInputAction.next,
                            validator: _required('Phone number is required.'),
                          ),
                          if (_role == UserRole.vendor) ..._vendorFields(),
                          if (_role == UserRole.rider) ..._riderFields(),
                          if (_usesHomeBase) ..._homeBaseFields(),
                          const SizedBox(height: 14),
                          CustomTextField(
                            label: 'Password',
                            controller: _passwordController,
                            icon: Icons.lock_outline_rounded,
                            obscureText: true,
                            textInputAction: TextInputAction.next,
                            validator: _validatePassword,
                          ),
                          const SizedBox(height: 14),
                          CustomTextField(
                            label: 'Confirm Password',
                            controller: _confirmPasswordController,
                            icon: Icons.lock_reset_rounded,
                            obscureText: true,
                            validator: _validateConfirmPassword,
                          ),
                          const SizedBox(height: 22),
                          CustomButton(
                            label: 'Register ${_role.label}',
                            icon: Icons.person_add_alt_rounded,
                            isLoading: auth.isLoading,
                            onPressed: _register,
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 18),
                    Center(
                      child: TextButton(
                        onPressed: auth.isLoading ? null : () => context.pop(),
                        child: const Text('Already have an account? Sign in'),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  List<Widget> _vendorFields() {
    return [
      const SizedBox(height: 14),
      CustomTextField(
        label: 'Store Name',
        controller: _storeNameController,
        icon: Icons.storefront_outlined,
        textInputAction: TextInputAction.next,
        validator: _required('Store name is required.'),
      ),
      const SizedBox(height: 14),
      CustomTextField(
        label: 'Business Registration Number (optional)',
        controller: _businessRegistrationController,
        icon: Icons.badge_outlined,
        textInputAction: TextInputAction.next,
      ),
    ];
  }

  List<Widget> _riderFields() {
    return [
      const SizedBox(height: 14),
      DropdownButtonFormField<String>(
        initialValue: _vehicleType,
        decoration: const InputDecoration(
          labelText: 'Vehicle Type',
          prefixIcon: Icon(Icons.two_wheeler_rounded),
        ),
        items: const [
          DropdownMenuItem(value: 'bicycle', child: Text('Bicycle')),
          DropdownMenuItem(value: 'motorbike', child: Text('Motorbike')),
          DropdownMenuItem(value: 'three_wheeler', child: Text('Three wheeler')),
          DropdownMenuItem(value: 'van', child: Text('Van')),
        ],
        onChanged: (value) {
          if (value != null) {
            setState(() => _vehicleType = value);
          }
        },
      ),
      const SizedBox(height: 14),
      CustomTextField(
        label: 'Vehicle Number (optional)',
        controller: _vehicleNumberController,
        icon: Icons.pin_outlined,
        textInputAction: TextInputAction.next,
      ),
      const SizedBox(height: 14),
      CustomTextField(
        label: 'License Number (optional)',
        controller: _licenseNumberController,
        icon: Icons.credit_card_outlined,
        textInputAction: TextInputAction.next,
      ),
    ];
  }

  List<Widget> _homeBaseFields() {
    return [
      const SizedBox(height: 14),
      CustomTextField(
        label: _role == UserRole.vendor ? 'Store Address' : 'Home-base Address',
        controller: _addressController,
        icon: Icons.location_on_outlined,
        textInputAction: TextInputAction.next,
        validator: _required('Address is required.'),
      ),
      const SizedBox(height: 14),
      CustomTextField(
        label: 'City',
        controller: _cityController,
        icon: Icons.location_city_outlined,
        textInputAction: TextInputAction.next,
        validator: _required('City is required.'),
      ),
      const SizedBox(height: 14),
      CustomTextField(
        label: 'District',
        controller: _districtController,
        icon: Icons.map_outlined,
        textInputAction: TextInputAction.next,
        validator: _required('District is required.'),
      ),
      const SizedBox(height: 14),
      CustomTextField(
        label: 'Province',
        controller: _provinceController,
        icon: Icons.public_outlined,
        textInputAction: TextInputAction.next,
        validator: _required('Province is required.'),
      ),
      const SizedBox(height: 14),
      Row(
        children: [
          Expanded(
            child: CustomTextField(
              label: 'Latitude (optional)',
              controller: _latitudeController,
              keyboardType:
                  const TextInputType.numberWithOptions(decimal: true, signed: true),
              textInputAction: TextInputAction.next,
              validator: _validateLatitude,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: CustomTextField(
              label: 'Longitude (optional)',
              controller: _longitudeController,
              keyboardType:
                  const TextInputType.numberWithOptions(decimal: true, signed: true),
              textInputAction: TextInputAction.next,
              validator: _validateLongitude,
            ),
          ),
        ],
      ),
    ];
  }

  String? Function(String?) _required(String message) {
    return (value) => value == null || value.trim().isEmpty ? message : null;
  }

  String? _validateEmail(String? value) {
    final email = value?.trim() ?? '';
    if (email.isEmpty) {
      return 'Email is required.';
    }
    if (!RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$').hasMatch(email)) {
      return 'Enter a valid email address.';
    }
    return null;
  }

  String? _validatePassword(String? value) {
    if (value == null || value.isEmpty) {
      return 'Password is required.';
    }
    if (value.length < 8) {
      return 'Password must be at least 8 characters.';
    }
    return null;
  }

  String? _validateConfirmPassword(String? value) {
    if (value == null || value.isEmpty) {
      return 'Confirm password is required.';
    }
    return value == _passwordController.text ? null : 'Passwords do not match.';
  }

  String? _validateLatitude(String? value) =>
      _validateCoordinate(value, -90, 90, 'latitude');

  String? _validateLongitude(String? value) =>
      _validateCoordinate(value, -180, 180, 'longitude');

  String? _validateCoordinate(
    String? value,
    double minimum,
    double maximum,
    String label,
  ) {
    if (value == null || value.trim().isEmpty) {
      return null;
    }
    final coordinate = double.tryParse(value.trim());
    if (coordinate == null || coordinate < minimum || coordinate > maximum) {
      return 'Invalid $label.';
    }
    return null;
  }

  static String? _nullIfEmpty(String value) {
    final trimmed = value.trim();
    return trimmed.isEmpty ? null : trimmed;
  }

  static double? _doubleOrNull(String value) {
    final trimmed = value.trim();
    return trimmed.isEmpty ? null : double.tryParse(trimmed);
  }
}
