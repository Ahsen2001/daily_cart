import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../models/profile_model.dart';
import '../../providers/profile_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';
import '../../widgets/dailycart_card.dart';

class EditProfileScreen extends ConsumerStatefulWidget {
  const EditProfileScreen({super.key});

  @override
  ConsumerState<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends ConsumerState<EditProfileScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();

  @override
  void initState() {
    super.initState();
    final profile = ref.read(profileProvider).user;
    _nameController.text = profile?.name ?? '';
    _emailController.text = profile?.email ?? '';
    _phoneController.text = profile?.phone ?? '';
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(profileProvider);
    final current = state.user;

    return Scaffold(
      appBar: const CustomAppBar(title: 'Edit Profile'),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(20),
          children: [
            DailyCartCard(
              child: Column(
                children: [
                  CustomTextField(
                    label: 'Name',
                    controller: _nameController,
                    icon: Icons.person_outline_rounded,
                    validator: _required('Name is required.'),
                  ),
                  const SizedBox(height: 12),
                  CustomTextField(
                    label: 'Email',
                    controller: _emailController,
                    icon: Icons.mail_outline_rounded,
                    keyboardType: TextInputType.emailAddress,
                    validator: _required('Email is required.'),
                  ),
                  const SizedBox(height: 12),
                  CustomTextField(
                    label: 'Phone',
                    controller: _phoneController,
                    icon: Icons.phone_outlined,
                    keyboardType: TextInputType.phone,
                    validator: _required('Phone is required.'),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),
            CustomButton(
              label: 'Save Changes',
              icon: Icons.save_outlined,
              isLoading: state.isLoading,
              onPressed: current == null ? null : () => _save(current),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _save(ProfileModel current) async {
    if (!_formKey.currentState!.validate()) {
      return;
    }
    final ok = await ref.read(profileProvider).updateProfile(
          current.copyWith(
            name: _nameController.text.trim(),
            email: _emailController.text.trim(),
            phone: _phoneController.text.trim(),
          ),
        );
    if (!mounted) {
      return;
    }
    if (ok) {
      context.pop();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            ref.read(profileProvider).errorMessage ?? 'Unable to update profile.',
          ),
        ),
      );
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
