import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../providers/promotion_provider.dart';
import '../../theme/app_colors.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/loading_widget.dart';

class PromotionDetailsScreen extends ConsumerStatefulWidget {
  const PromotionDetailsScreen({
    required this.promotionId,
    super.key,
  });

  final int promotionId;

  @override
  ConsumerState<PromotionDetailsScreen> createState() =>
      _PromotionDetailsScreenState();
}

class _PromotionDetailsScreenState
    extends ConsumerState<PromotionDetailsScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(
      () => ref.read(promotionProvider).getPromotionDetails(widget.promotionId),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(promotionProvider);
    final promotion = state.selectedPromotion;
    return Scaffold(
      appBar: const CustomAppBar(title: 'Promotion Details'),
      body: state.isLoading && promotion == null
          ? const LoadingWidget(message: 'Loading promotion...')
          : promotion == null
              ? const Center(child: Text('Promotion not found.'))
              : ListView(
                  padding: const EdgeInsets.all(20),
                  children: [
                    ClipRRect(
                      borderRadius: BorderRadius.circular(24),
                      child: promotion.image.isEmpty
                          ? Container(
                              height: 210,
                              color:
                                  AppColors.primaryGreen.withValues(alpha: 0.12),
                              child: const Icon(
                                Icons.local_offer_rounded,
                                color: AppColors.darkGreen,
                                size: 56,
                              ),
                            )
                          : CachedNetworkImage(
                              imageUrl: promotion.image,
                              height: 210,
                              fit: BoxFit.cover,
                            ),
                    ),
                    const SizedBox(height: 20),
                    Text(
                      promotion.type.replaceAll('_', ' ').toUpperCase(),
                      style: const TextStyle(
                        color: AppColors.accentOrange,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      promotion.title,
                      style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                            fontWeight: FontWeight.w900,
                          ),
                    ),
                    const SizedBox(height: 12),
                    Text(promotion.description),
                  ],
                ),
    );
  }
}
