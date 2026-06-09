import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../providers/vendor_review_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state_widget.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/rating_widget.dart';
import '../../widgets/vendor_review_card.dart';

class VendorReviewsScreen extends ConsumerStatefulWidget {
  const VendorReviewsScreen({super.key});

  @override
  ConsumerState<VendorReviewsScreen> createState() =>
      _VendorReviewsScreenState();
}

class _VendorReviewsScreenState extends ConsumerState<VendorReviewsScreen> {
  int _rating = 0;

  @override
  void initState() {
    super.initState();
    Future.microtask(_load);
  }

  Future<void> _load() {
    return ref
        .read(vendorReviewProvider)
        .getVendorReviews(rating: _rating == 0 ? null : _rating);
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(vendorReviewProvider);
    return Scaffold(
      appBar: const CustomAppBar(title: 'Vendor Reviews'),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 6),
            child: Row(
              children: [
                Text(
                  state.averageRating.toStringAsFixed(1),
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.w900,
                      ),
                ),
                const SizedBox(width: 8),
                RatingWidget(rating: state.averageRating),
                const Spacer(),
                DropdownButton<int>(
                  value: _rating,
                  items: const [
                    DropdownMenuItem(value: 0, child: Text('All')),
                    DropdownMenuItem(value: 5, child: Text('5 star')),
                    DropdownMenuItem(value: 4, child: Text('4 star')),
                    DropdownMenuItem(value: 3, child: Text('3 star')),
                    DropdownMenuItem(value: 2, child: Text('2 star')),
                    DropdownMenuItem(value: 1, child: Text('1 star')),
                  ],
                  onChanged: (value) {
                    setState(() => _rating = value ?? 0);
                    _load();
                  },
                ),
              ],
            ),
          ),
          Expanded(
            child: state.isLoading && state.reviews.isEmpty
                ? const LoadingWidget(message: 'Loading reviews...')
                : state.reviews.isEmpty
                    ? const EmptyStateWidget(
                        title: 'No reviews',
                        message: 'Customer reviews for your products appear here.',
                        icon: Icons.reviews_outlined,
                      )
                    : ListView.separated(
                        padding: const EdgeInsets.all(20),
                        itemBuilder: (context, index) =>
                            VendorReviewCard(review: state.reviews[index]),
                        separatorBuilder: (context, index) =>
                            const SizedBox(height: 14),
                        itemCount: state.reviews.length,
                      ),
          ),
        ],
      ),
    );
  }
}
