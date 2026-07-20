import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/vendor_provider.dart';
import '../../providers/profile_provider.dart';
import '../../providers/auth_provider.dart';
import '../../routes/app_routes.dart';
import '../../theme/app_colors.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/setting_tile.dart';
import '../../widgets/stock_status_badge.dart';

class VendorProfileScreen extends ConsumerStatefulWidget {
  const VendorProfileScreen({super.key});

  @override
  ConsumerState<VendorProfileScreen> createState() => _VendorProfileScreenState();
}

class _VendorProfileScreenState extends ConsumerState<VendorProfileScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(vendorProvider).getVendorProfile());
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(vendorProvider);
    final profile = state.profile;
    return Scaffold(
      appBar: const CustomAppBar(title: 'Vendor Profile'),
      body: state.isLoading && profile == null
          ? const LoadingWidget(message: 'Loading vendor profile...')
          : profile == null
              ? const Center(child: Text('Vendor profile not found.'))
              : ListView(
                  padding: const EdgeInsets.all(20),
                  children: [
                    DailyCartCard(
                      child: Column(
                        children: [
                          CircleAvatar(
                            radius: 42,
                            backgroundColor: AppColors.lightBackground,
                            backgroundImage: profile.shopLogo.isEmpty
                                ? null
                                : CachedNetworkImageProvider(profile.shopLogo),
                            child: profile.shopLogo.isEmpty
                                ? const Icon(Icons.storefront_rounded, size: 42)
                                : null,
                          ),
                          const SizedBox(height: 12),
                          Text(
                            profile.shopName.isEmpty
                                ? 'DailyCart Vendor'
                                : profile.shopName,
                            style: Theme.of(context)
                                .textTheme
                                .titleLarge
                                ?.copyWith(fontWeight: FontWeight.w900),
                          ),
                          const SizedBox(height: 8),
                          StockStatusBadge(status: profile.approvalStatus),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                    DailyCartCard(
                      child: Column(
                        children: [
                          _Info('Owner', profile.ownerName),
                          _Info('Email', profile.email),
                          _Info('Phone', profile.phone),
                          _Info('Address', profile.address),
                          _Info(
                            'Business Reg.',
                            profile.businessRegistrationNumber,
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                    DailyCartCard(
                      child: Column(
                        children: [
                          SettingTile(
                            icon: Icons.edit_outlined,
                            title: 'Edit Vendor Profile',
                            onTap: () => context.push(AppRoutes.vendorEditProfile),
                          ),
                          SettingTile(
                            icon: Icons.lock_outline,
                            title: 'Change Password',
                            onTap: () =>
                                context.push(AppRoutes.vendorChangePassword),
                          ),
                          SettingTile(
                            icon: Icons.reviews_outlined,
                            title: 'Vendor Reviews',
                            onTap: () => context.push(AppRoutes.vendorReviews),
                          ),
                          SettingTile(
                            icon: Icons.account_balance_wallet_outlined,
                            title: 'Wallet & Payouts',
                            onTap: () => context.push(AppRoutes.vendorWallet),
                          ),
                          SettingTile(
                            icon: Icons.currency_exchange,
                            title: 'Refunds',
                            onTap: () => context.push(AppRoutes.vendorRefunds),
                          ),
                          SettingTile(
                            icon: Icons.confirmation_number_outlined,
                            title: 'Coupons',
                            onTap: () => context.push(AppRoutes.vendorCoupons),
                          ),
                          SettingTile(
                            icon: Icons.local_offer_outlined,
                            title: 'Promotions',
                            onTap: () =>
                                context.push(AppRoutes.vendorPromotions),
                          ),
                          SettingTile(
                            icon: Icons.autorenew,
                            title: 'Subscriptions',
                            onTap: () =>
                                context.push(AppRoutes.vendorSubscriptions),
                          ),
                          SettingTile(
                            icon: Icons.event_repeat_outlined,
                            title: 'Scheduled Orders',
                            onTap: () =>
                                context.push(AppRoutes.vendorScheduledOrders),
                          ),
                          SettingTile(
                            icon: Icons.analytics_outlined,
                            title: 'Reports',
                            onTap: () => context.push(AppRoutes.vendorReports),
                          ),
                          SettingTile(
                            icon: Icons.notifications_none,
                            title: 'Notifications',
                            onTap: () =>
                                context.push(AppRoutes.vendorNotifications),
                          ),
                          SettingTile(
                            icon: Icons.support_agent,
                            title: 'Support',
                            onTap: () =>
                                context.push(AppRoutes.vendorSupportTickets),
                          ),
                          SettingTile(
                            icon: Icons.delete_forever_outlined,
                            title: 'Delete Vendor Account',
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
        title: const Text('Delete vendor account?'),
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
                'Unable to delete vendor account.',
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
            width: 112,
            child: Text(label, style: const TextStyle(color: AppColors.mutedText)),
          ),
          Expanded(child: Text(value.isEmpty ? '-' : value)),
        ],
      ),
    );
  }
}
