import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';

import '../models/store_model.dart';
import '../theme/app_colors.dart';

class StoreCard extends StatelessWidget {
  const StoreCard({required this.store, required this.onTap, super.key});
  final StoreModel store;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: AppColors.white,
      borderRadius: BorderRadius.circular(22),
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            SizedBox(
              height: 126,
              child: Stack(
                fit: StackFit.expand,
                children: [
                  _NetworkImage(url: store.coverImage),
                  if (store.isFeatured)
                    Positioned(
                      right: 10,
                      top: 10,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 9,
                          vertical: 5,
                        ),
                        decoration: BoxDecoration(
                          color: AppColors.accentOrange,
                          borderRadius: BorderRadius.circular(99),
                        ),
                        child: const Text(
                          'Featured',
                          style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w800,
                            fontSize: 11,
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(14),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(14),
                    child: SizedBox(
                      width: 52,
                      height: 52,
                      child: _NetworkImage(url: store.logo),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          store.name,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontWeight: FontWeight.w900,
                            fontSize: 16,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          store.deliveryEstimate.isEmpty
                              ? 'Delivery estimate available'
                              : store.deliveryEstimate,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            color: AppColors.mutedText,
                            fontSize: 12,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          '${store.followersCount} followers',
                          style: const TextStyle(
                            color: AppColors.darkGreen,
                            fontWeight: FontWeight.w700,
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _NetworkImage extends StatelessWidget {
  const _NetworkImage({required this.url});
  final String url;

  @override
  Widget build(BuildContext context) {
    if (url.isEmpty) {
      return const ColoredBox(
        color: AppColors.lightBackground,
        child: Icon(
          Icons.storefront_rounded,
          size: 44,
          color: AppColors.primaryGreen,
        ),
      );
    }
    return CachedNetworkImage(
      imageUrl: url,
      fit: BoxFit.cover,
      memCacheWidth: 640,
      placeholder: (_, _) =>
          const ColoredBox(color: AppColors.lightBackground),
      errorWidget: (_, _, _) => const ColoredBox(
        color: AppColors.lightBackground,
        child: Icon(Icons.storefront_rounded, color: AppColors.primaryGreen),
      ),
    );
  }
}
