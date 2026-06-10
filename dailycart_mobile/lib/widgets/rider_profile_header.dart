import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';

import '../models/rider_profile_model.dart';
import '../theme/app_colors.dart';
import 'dailycart_card.dart';
import 'delivery_status_badge.dart';

class RiderProfileHeader extends StatelessWidget {
  const RiderProfileHeader({
    required this.profile,
    super.key,
  });

  final RiderProfileModel profile;

  @override
  Widget build(BuildContext context) {
    return DailyCartCard(
      child: Column(
        children: [
          CircleAvatar(
            radius: 42,
            backgroundColor: AppColors.lightBackground,
            backgroundImage: profile.profilePhoto.isEmpty
                ? null
                : CachedNetworkImageProvider(profile.profilePhoto),
            child: profile.profilePhoto.isEmpty
                ? const Icon(Icons.delivery_dining_rounded, size: 42)
                : null,
          ),
          const SizedBox(height: 12),
          Text(
            profile.name,
            style: Theme.of(context).textTheme.titleLarge?.copyWith(
                  fontWeight: FontWeight.w900,
                ),
          ),
          const SizedBox(height: 8),
          DeliveryStatusBadge(status: profile.approvalStatus),
        ],
      ),
    );
  }
}
