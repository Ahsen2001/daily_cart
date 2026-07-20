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
                            icon: Icons.star_rate_rounded,
                            title: 'My Reviews',
                            onTap: () => context.push(AppRoutes.myReviews),
                          ),
                          SettingTile(
                            icon: Icons.stars_rounded,
                            title: 'Loyalty Points',
                            onTap: () => context.push(AppRoutes.loyaltyPoints),
                          ),
                          SettingTile(
                            icon: Icons.support_agent_rounded,
                            title: 'Support Tickets',
                            onTap: () => context.push(AppRoutes.supportTickets),
                          ),
                          SettingTile(
                            icon: Icons.confirmation_number_outlined,
                            title: 'Available Coupons',
                            onTap: () => context.push(AppRoutes.availableCoupons),
                          ),
                          SettingTile(
                            icon: Icons.local_offer_outlined,
                            title: 'Promotions',
                            onTap: () => context.push(AppRoutes.promotions),
                          ),
                          SettingTile(
                            icon: Icons.account_balance_wallet_outlined,
                            title: 'Wallet',
                            onTap: () => context.push(AppRoutes.wallet),
                          ),
                          SettingTile(
                            icon: Icons.currency_exchange,
                            title: 'Refunds',
                            onTap: () => context.push(AppRoutes.refunds),
                          ),
                          SettingTile(
                            icon: Icons.autorenew,
                            title: 'Subscriptions',
                            onTap: () => context.push(AppRoutes.subscriptions),
                          ),
                          SettingTile(
                            icon: Icons.event_repeat_outlined,
                            title: 'Scheduled Orders',
                            onTap: () => context.push(AppRoutes.scheduledOrders),
                          ),
                          SettingTile(
                            icon: Icons.privacy_tip_outlined,
                            title: 'Privacy Policy',
                            onTap: () =>
                                context.push('${AppRoutes.policy}/privacy'),
                          ),
                          SettingTile(
                            icon: Icons.description_outlined,
                            title: 'Terms & Conditions',
                            onTap: () =>
                                context.push('${AppRoutes.policy}/terms'),
                          ),
                          SettingTile(
                            icon: Icons.receipt_long_outlined,
                            title: 'Refund Policy',
                            onTap: () =>
                                context.push('${AppRoutes.policy}/refund'),
                          ),
                          SettingTile(
                            icon: Icons.delete_forever_outlined,
                            title: 'Delete Account',
                            onTap: _deleteAccount,
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

  Future<void> _deleteAccount() async {
    final password = TextEditingController();
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete account?'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              'This permanently deletes your DailyCart account and cannot be undone.',
            ),
            const SizedBox(height: 12),
            TextField(
              controller: password,
              obscureText: true,
              decoration:
                  const InputDecoration(labelText: 'Confirm your password'),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Keep account'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Delete'),
          ),
        ],
      ),
    );
    if (confirmed != true || password.text.isEmpty) {
      password.dispose();
      return;
    }
    final value = password.text;
    password.dispose();
    final ok = await ref.read(profileProvider).deleteAccount(value);
    if (!mounted) return;
    if (!ok) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            ref.read(profileProvider).errorMessage ??
                'Unable to delete account.',
          ),
        ),
      );
      return;
    }
    await ref.read(authProvider).clearToken();
    if (mounted) context.go(AppRoutes.login);
  }
}
