import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../providers/promotion_provider.dart';
import '../../config/app_config.dart';
import '../../models/promotion_model.dart';
import '../../services/app_share_service.dart';
import '../../theme/app_colors.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/loading_widget.dart';

class PromotionDetailsScreen extends ConsumerStatefulWidget {
  const PromotionDetailsScreen({required this.promotionId, super.key});

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
      appBar: CustomAppBar(
        title: 'Promotion Details',
        actions: [
          IconButton(
            tooltip: 'Share offer',
            onPressed: promotion == null ? null : () => _share(promotion),
            icon: const Icon(Icons.share_outlined),
          ),
        ],
      ),
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
                          color: AppColors.primaryGreen.withValues(alpha: 0.12),
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
                const SizedBox(height: 22),
                Row(
                  children: [
                    Expanded(
                      child: FilledButton.icon(
                        onPressed: () => _share(promotion),
                        icon: const Icon(Icons.share_outlined),
                        label: const Text('Share Offer'),
                      ),
                    ),
                    const SizedBox(width: 10),
                    IconButton.filledTonal(
                      tooltip: 'Copy offer link',
                      onPressed: () => _copyLink(promotion),
                      icon: const Icon(Icons.link_rounded),
                    ),
                  ],
                ),
              ],
            ),
    );
  }

  String _offerLink(PromotionModel promotion) {
    if (promotion.productSlug.isNotEmpty) {
      return '${AppConfig.webBaseUrl}/products/${promotion.productSlug}?promotion=${promotion.id}';
    }
    return '${AppConfig.webBaseUrl}/offers';
  }

  Future<void> _share(PromotionModel promotion) {
    final discount = promotion.discountText.isEmpty
        ? ''
        : '${promotion.discountText}\n';
    return AppShareService.shareText(
      context,
      subject: '${promotion.title} — DailyCart',
      text:
          '${promotion.title}\n$discount${promotion.description}\n${_offerLink(promotion)}',
    );
  }

  Future<void> _copyLink(PromotionModel promotion) async {
    await AppShareService.copyLink(_offerLink(promotion));
    if (!mounted) return;
    ScaffoldMessenger.of(
      context,
    ).showSnackBar(const SnackBar(content: Text('Offer link copied.')));
  }
}
