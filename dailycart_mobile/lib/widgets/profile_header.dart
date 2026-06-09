import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';

import '../models/profile_model.dart';
import '../theme/app_colors.dart';
import 'dailycart_card.dart';

class ProfileHeader extends StatelessWidget {
  const ProfileHeader({
    required this.profile,
    this.onPhotoTap,
    super.key,
  });

  final ProfileModel profile;
  final VoidCallback? onPhotoTap;

  @override
  Widget build(BuildContext context) {
    return DailyCartCard(
      child: Row(
        children: [
          GestureDetector(
            onTap: onPhotoTap,
            child: CircleAvatar(
              radius: 38,
              backgroundColor: AppColors.lightBackground,
              backgroundImage: profile.profilePhoto.isEmpty
                  ? null
                  : CachedNetworkImageProvider(profile.profilePhoto),
              child: profile.profilePhoto.isEmpty
                  ? const Icon(
                      Icons.person_rounded,
                      size: 42,
                      color: AppColors.primaryGreen,
                    )
                  : null,
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  profile.name,
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.w900,
                      ),
                ),
                const SizedBox(height: 4),
                Text(profile.email),
                const SizedBox(height: 4),
                Text(profile.phone),
                if (profile.joinedDate != null) ...[
                  const SizedBox(height: 4),
                  Text(
                    'Joined ${profile.joinedDate!.year}',
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: AppColors.mutedText,
                        ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}
