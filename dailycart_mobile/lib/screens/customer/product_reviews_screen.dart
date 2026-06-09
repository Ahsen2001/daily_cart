import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../providers/review_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state_widget.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/review_card.dart';

class ProductReviewsScreen extends ConsumerStatefulWidget {
  const ProductReviewsScreen({
    required this.productId,
    super.key,
  });

  final int productId;

  @override
  ConsumerState<ProductReviewsScreen> createState() =>
      _ProductReviewsScreenState();
}

class _ProductReviewsScreenState extends ConsumerState<ProductReviewsScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(
      () => ref.read(reviewProvider).getProductReviews(widget.productId),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(reviewProvider);
    final reviews = state.productReviews;
    final average = reviews.isEmpty
        ? 0
        : reviews.map((item) => item.rating).reduce((a, b) => a + b) /
            reviews.length;

    return Scaffold(
      appBar: const CustomAppBar(title: 'Product Reviews'),
      body: state.isLoading && reviews.isEmpty
          ? const LoadingWidget(message: 'Loading product reviews...')
          : ListView(
              padding: const EdgeInsets.all(20),
              children: [
                Text(
                  '${average.toStringAsFixed(1)} average rating',
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.w900,
                      ),
                ),
                Text('${reviews.length} reviews'),
                const SizedBox(height: 16),
                if (reviews.isEmpty)
                  const EmptyStateWidget(
                    title: 'No product reviews',
                    message: 'Customer reviews will appear here.',
                    icon: Icons.reviews_outlined,
                  )
                else
                  ...reviews.map(
                    (review) => Padding(
                      padding: const EdgeInsets.only(bottom: 14),
                      child: ReviewCard(review: review),
                    ),
                  ),
              ],
            ),
    );
  }
}
