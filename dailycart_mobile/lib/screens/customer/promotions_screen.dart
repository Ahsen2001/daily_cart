import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/promotion_provider.dart';
import '../../routes/app_routes.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state_widget.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/promotion_card.dart';

class PromotionsScreen extends ConsumerStatefulWidget {
  const PromotionsScreen({super.key});

  @override
  ConsumerState<PromotionsScreen> createState() => _PromotionsScreenState();
}

class _PromotionsScreenState extends ConsumerState<PromotionsScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(promotionProvider).getPromotions());
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(promotionProvider);
    return Scaffold(
      appBar: const CustomAppBar(title: 'Promotions'),
      body: state.isLoading && state.promotions.isEmpty
          ? const LoadingWidget(message: 'Loading promotions...')
          : state.promotions.isEmpty
              ? const EmptyStateWidget(
                  title: 'No promotions',
                  message: 'Flash sales and seasonal offers will appear here.',
                  icon: Icons.local_offer_outlined,
                )
              : ListView.separated(
                  padding: const EdgeInsets.all(20),
                  itemBuilder: (context, index) {
                    final promotion = state.promotions[index];
                    return PromotionCard(
                      promotion: promotion,
                      onTap: () => context.push(
                        '${AppRoutes.promotionDetails}/${promotion.id}',
                      ),
                    );
                  },
                  separatorBuilder: (context, index) =>
                      const SizedBox(height: 14),
                  itemCount: state.promotions.length,
                ),
    );
  }
}
