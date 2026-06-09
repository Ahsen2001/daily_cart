import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';

import '../../providers/review_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/rating_input_widget.dart';

class AddReviewScreen extends ConsumerStatefulWidget {
  const AddReviewScreen({
    required this.orderId,
    required this.productId,
    super.key,
  });

  final int orderId;
  final int productId;

  @override
  ConsumerState<AddReviewScreen> createState() => _AddReviewScreenState();
}

class _AddReviewScreenState extends ConsumerState<AddReviewScreen> {
  final _formKey = GlobalKey<FormState>();
  final _commentController = TextEditingController();
  int _rating = 0;
  String? _imagePath;

  @override
  void dispose() {
    _commentController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(reviewProvider);

    return Scaffold(
      appBar: const CustomAppBar(title: 'Add Review'),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          DailyCartCard(
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Rate your delivered product',
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(
                          fontWeight: FontWeight.w900,
                        ),
                  ),
                  const SizedBox(height: 12),
                  RatingInputWidget(
                    rating: _rating,
                    onChanged: (value) => setState(() => _rating = value),
                  ),
                  const SizedBox(height: 14),
                  TextFormField(
                    controller: _commentController,
                    maxLines: 5,
                    decoration: const InputDecoration(
                      labelText: 'Review comment',
                      alignLabelWithHint: true,
                      prefixIcon: Icon(Icons.rate_review_outlined),
                    ),
                    validator: (value) {
                      if (_rating < 1) {
                        return 'Rating is required.';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 14),
                  OutlinedButton.icon(
                    onPressed: _pickImage,
                    icon: const Icon(Icons.image_outlined),
                    label: Text(
                      _imagePath == null ? 'Upload review image' : 'Image ready',
                    ),
                  ),
                  const SizedBox(height: 20),
                  CustomButton(
                    label: 'Submit Review',
                    isLoading: state.isLoading,
                    onPressed: _submit,
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _pickImage() async {
    final image = await ImagePicker().pickImage(
      source: ImageSource.gallery,
      imageQuality: 80,
      maxWidth: 1200,
    );
    if (image != null) {
      setState(() => _imagePath = image.path);
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }
    final ok = await ref.read(reviewProvider).addReview(
          orderId: widget.orderId,
          productId: widget.productId,
          rating: _rating,
          comment: _commentController.text.trim(),
          imagePath: _imagePath,
        );
    if (!mounted) {
      return;
    }
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          ok
              ? 'Review submitted successfully.'
              : ref.read(reviewProvider).errorMessage ??
                  'Review is allowed only after delivery.',
        ),
      ),
    );
    if (ok) {
      context.pop();
    }
  }
}
