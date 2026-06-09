import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../models/review_model.dart';
import '../../providers/review_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state_widget.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/rating_input_widget.dart';
import '../../widgets/review_card.dart';

class MyReviewsScreen extends ConsumerStatefulWidget {
  const MyReviewsScreen({super.key});

  @override
  ConsumerState<MyReviewsScreen> createState() => _MyReviewsScreenState();
}

class _MyReviewsScreenState extends ConsumerState<MyReviewsScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(reviewProvider).getMyReviews());
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(reviewProvider);

    return Scaffold(
      appBar: const CustomAppBar(title: 'My Reviews'),
      body: state.isLoading && state.reviews.isEmpty
          ? const LoadingWidget(message: 'Loading reviews...')
          : state.reviews.isEmpty
              ? const EmptyStateWidget(
                  title: 'No reviews yet',
                  message: 'Reviews for delivered orders will appear here.',
                  icon: Icons.star_border_rounded,
                )
              : RefreshIndicator(
                  onRefresh: () => ref.read(reviewProvider).getMyReviews(),
                  child: ListView.separated(
                    padding: const EdgeInsets.all(20),
                    itemBuilder: (context, index) {
                      final review = state.reviews[index];
                      return ReviewCard(
                        review: review,
                        onEdit: review.canEdit
                            ? () => _showEditReviewDialog(review)
                            : null,
                        onDelete: review.canDelete
                            ? () => ref
                                .read(reviewProvider)
                                .deleteReview(review.id)
                            : null,
                      );
                    },
                    separatorBuilder: (context, index) =>
                        const SizedBox(height: 14),
                    itemCount: state.reviews.length,
                  ),
                ),
    );
  }

  Future<void> _showEditReviewDialog(ReviewModel review) async {
    final controller = TextEditingController(text: review.comment);
    var rating = review.rating;

    await showDialog<void>(
      context: context,
      builder: (dialogContext) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              title: const Text('Edit Review'),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  RatingInputWidget(
                    rating: rating,
                    onChanged: (value) =>
                        setDialogState(() => rating = value),
                  ),
                  TextField(
                    controller: controller,
                    maxLines: 4,
                    decoration: const InputDecoration(
                      labelText: 'Review comment',
                    ),
                  ),
                ],
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.of(dialogContext).pop(),
                  child: const Text('Cancel'),
                ),
                ElevatedButton(
                  onPressed: () async {
                    final ok = await ref.read(reviewProvider).updateReview(
                          reviewId: review.id,
                          rating: rating,
                          comment: controller.text.trim(),
                        );
                    if (!dialogContext.mounted) {
                      return;
                    }
                    Navigator.of(dialogContext).pop();
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text(
                          ok
                              ? 'Review updated.'
                              : ref.read(reviewProvider).errorMessage ??
                                  'Unable to update review.',
                        ),
                      ),
                    );
                  },
                  child: const Text('Save'),
                ),
              ],
            );
          },
        );
      },
    );

    controller.dispose();
  }
}
