import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/rider_provider.dart';
import '../../providers/profile_provider.dart';
import '../../providers/auth_provider.dart';
import '../../routes/app_routes.dart';
import '../../theme/app_colors.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/rider_profile_header.dart';
import '../../widgets/setting_tile.dart';

class RiderProfileScreen extends ConsumerStatefulWidget {
  const RiderProfileScreen({super.key});

  @override
  ConsumerState<RiderProfileScreen> createState() => _RiderProfileScreenState();
}

class _RiderProfileScreenState extends ConsumerState<RiderProfileScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(riderProvider).getRiderProfile());
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(riderProvider);
    final profile = state.profile;
    return Scaffold(
      appBar: const CustomAppBar(title: 'Rider Profile'),
      body: state.isLoading && profile == null
          ? const LoadingWidget(message: 'Loading rider profile...')
          : profile == null
              ? const Center(child: Text('Rider profile not found.'))
              : ListView(
                  padding: const EdgeInsets.all(20),
                  children: [
                    RiderProfileHeader(profile: profile),
                    const SizedBox(height: 16),
                    DailyCartCard(
                      child: Column(
                        children: [
                          _Info('Email', profile.email),
                          _Info('Phone', profile.phone),
                          _Info('Vehicle Type', profile.vehicleType),
                          _Info('Vehicle Number', profile.vehicleNumber),
                          _Info('License Number', profile.licenseNumber),
                          _Info(
                            'Availability',
                            profile.availabilityStatus.replaceAll('_', ' '),
                          ),
                          _Info('Home Base', profile.address),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                    DailyCartCard(
                      child: Column(
                        children: [
                          SettingTile(
                            icon: Icons.edit_outlined,
                            title: 'Edit Rider Profile',
                            onTap: () => context.push(AppRoutes.editRiderProfile),
                          ),
                          SettingTile(
                            icon: Icons.payments_outlined,
                            title: 'Rider Earnings',
                            onTap: () => context.push(AppRoutes.riderEarnings),
                          ),
                          SettingTile(
                            icon: Icons.analytics_outlined,
                            title: 'Rider Reports',
                            onTap: () => context.push(AppRoutes.riderReports),
                          ),
                          SettingTile(
                            icon: Icons.notifications_none,
                            title: 'Notifications',
                            onTap: () =>
                                context.push(AppRoutes.riderNotifications),
                          ),
                          SettingTile(
                            icon: Icons.support_agent,
                            title: 'Support',
                            onTap: () =>
                                context.push(AppRoutes.riderSupportTickets),
                          ),
                          SettingTile(
                            icon: Icons.lock_outline,
                            title: 'Change Password',
                            onTap: () =>
                                context.push(AppRoutes.riderChangePassword),
                          ),
                          SettingTile(
                            icon: Icons.delete_forever_outlined,
                            title: 'Delete Rider Account',
                            onTap: _deleteAccount,
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
    );
  }

  Future<void> _deleteAccount() async {
    final password = TextEditingController();
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete rider account?'),
        content: TextField(
          controller: password,
          obscureText: true,
          decoration: const InputDecoration(labelText: 'Confirm password'),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Delete'),
          ),
        ],
      ),
    );
    final value = password.text;
    password.dispose();
    if (confirmed != true || value.isEmpty) return;
    final ok = await ref.read(profileProvider).deleteAccount(value);
    if (!mounted) return;
    if (ok) {
      await ref.read(authProvider).clearToken();
      if (mounted) context.go(AppRoutes.login);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            ref.read(profileProvider).errorMessage ??
                'Unable to delete rider account.',
          ),
        ),
      );
    }
  }
}

class _Info extends StatelessWidget {
  const _Info(this.label, this.value);

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 128,
            child: Text(label, style: const TextStyle(color: AppColors.mutedText)),
          ),
          Expanded(child: Text(value.isEmpty ? '-' : value)),
        ],
      ),
    );
  }
}
