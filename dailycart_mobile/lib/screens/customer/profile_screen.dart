import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';

import '../../providers/auth_provider.dart';
import '../../providers/profile_provider.dart';
import '../../routes/app_routes.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/profile_header.dart';
import '../../widgets/setting_tile.dart';

class ProfileScreen extends ConsumerStatefulWidget {
  const ProfileScreen({super.key});

  @override
  ConsumerState<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends ConsumerState<ProfileScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(profileProvider).getProfile());
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(profileProvider);
    final profile = state.user;

    return Scaffold(
      appBar: const CustomAppBar(title: 'Profile'),
      body: state.isLoading && profile == null
          ? const LoadingWidget(message: 'Loading profile...')
          : profile == null
              ? const Center(child: Text('Profile not found.'))
              : ListView(
                  padding: const EdgeInsets.all(20),
                  children: [
                    ProfileHeader(
                      profile: profile,
                      onPhotoTap: _pickProfilePhoto,
                    ),
                    const SizedBox(height: 16),
                    DailyCartCard(
                      child: Column(
                        children: [
                          SettingTile(
                            icon: Icons.edit_outlined,
                            title: 'Edit Profile',
                            onTap: () => context.push(AppRoutes.editProfile),
                          ),
                          SettingTile(
                            icon: Icons.lock_outline_rounded,
                            title: 'Change Password',
                            onTap: () => context.push(AppRoutes.changePassword),
                          ),
                          SettingTile(
                            icon: Icons.location_on_outlined,
                            title: 'Saved Addresses',
                            onTap: () => context.push(AppRoutes.addresses),
                          ),
                          SettingTile(
                            icon: Icons.notifications_none_rounded,
                            title: 'Notifications',
                            onTap: () => context.push(AppRoutes.notifications),
                          ),
                          SettingTile(
                            icon: Icons.privacy_tip_outlined,
                            title: 'Privacy Policy',
                            onTap: () => _placeholder('Privacy Policy'),
                          ),
                          SettingTile(
                            icon: Icons.description_outlined,
                            title: 'Terms & Conditions',
                            onTap: () => _placeholder('Terms & Conditions'),
                          ),
                          SettingTile(
                            icon: Icons.receipt_long_outlined,
                            title: 'Refund Policy',
                            onTap: () => _placeholder('Refund Policy'),
                          ),
                          SettingTile(
                            icon: Icons.logout_rounded,
                            title: 'Logout',
                            onTap: _logout,
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
    );
  }

  void _placeholder(String title) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('$title placeholder.')),
    );
  }

  Future<void> _pickProfilePhoto() async {
    final picker = ImagePicker();
    final image = await picker.pickImage(
      source: ImageSource.gallery,
      imageQuality: 80,
      maxWidth: 1024,
    );

    if (image == null) {
      return;
    }

    final success =
        await ref.read(profileProvider).uploadProfilePhoto(image.path);

    if (!mounted) {
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          success
              ? 'Profile photo updated successfully.'
              : ref.read(profileProvider).errorMessage ??
                  'Unable to update profile photo.',
        ),
      ),
    );
  }

  Future<void> _logout() async {
    await ref.read(authProvider).logout();
    if (mounted) {
      context.go(AppRoutes.login);
    }
  }
}
