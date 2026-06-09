import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/vendor_provider.dart';
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
                            icon: Icons.reviews_outlined,
                            title: 'Vendor Reviews',
                            onTap: () => context.push(AppRoutes.vendorReviews),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
    );
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
