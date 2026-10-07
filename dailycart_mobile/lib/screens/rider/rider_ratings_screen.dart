import 'package:flutter/material.dart';

import '../../models/rider_rating_model.dart';
import '../../services/auth_api_service.dart';
import '../../services/rider_rating_api_service.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state_widget.dart';
import '../../widgets/error_widget.dart';
import '../../widgets/loading_widget.dart';

class RiderRatingsScreen extends StatefulWidget {
  const RiderRatingsScreen({super.key});

  @override
  State<RiderRatingsScreen> createState() => _RiderRatingsScreenState();
}

class _RiderRatingsScreenState extends State<RiderRatingsScreen> {
  final _service = RiderRatingApiService();
  RiderRatingSummary? _summary;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _error = null);
    try {
      final value = await _service.riderRatings();
      if (mounted) setState(() => _summary = value);
    } on ApiException catch (error) {
      if (mounted) setState(() => _error = error.message);
    }
  }

  Future<void> _report(RiderRatingModel rating) async {
    final controller = TextEditingController();
    final submit = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Report rating'),
        content: TextField(
          controller: controller,
          minLines: 3,
          maxLines: 6,
          decoration: const InputDecoration(
            labelText: 'Reason',
            hintText: 'Explain why an administrator should review this rating',
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Report'),
          ),
        ],
      ),
    );
    final reason = controller.text.trim();
    controller.dispose();
    if (submit != true || reason.isEmpty) return;
    try {
      await _service.report(rating.id, reason);
      await _load();
    } on ApiException catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text(error.message)));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final summary = _summary;
    return Scaffold(
      appBar: const CustomAppBar(title: 'Customer Ratings'),
      body: summary == null && _error == null
          ? const LoadingWidget(message: 'Loading ratings...')
          : _error != null && summary == null
              ? DailyCartErrorWidget(
                  title: 'Ratings unavailable',
                  message: _error!,
                  onRetry: _load,
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  child: summary!.ratings.isEmpty
                      ? ListView(
                          children: [
                            EmptyStateWidget(
                              title: 'No ratings yet',
                              message: 'Delivered-order ratings appear here.',
                              icon: Icons.star_outline_rounded,
                            ),
                          ],
                        )
                      : ListView(
                          padding: const EdgeInsets.all(20),
                          children: [
                            Card(
                              child: ListTile(
                                leading: const Icon(
                                  Icons.star_rounded,
                                  color: Colors.amber,
                                  size: 38,
                                ),
                                title: Text(
                                  summary.average.toStringAsFixed(1),
                                  style: Theme.of(context)
                                      .textTheme
                                      .headlineSmall
                                      ?.copyWith(fontWeight: FontWeight.w900),
                                ),
                                subtitle: Text('${summary.total} ratings'),
                              ),
                            ),
                            const SizedBox(height: 12),
                            for (final rating in summary.ratings) ...[
                              Card(
                                child: ListTile(
                                  title: Text(
                                    '${List.filled(rating.rating, '★').join()}  ${rating.orderNumber}',
                                  ),
                                  subtitle: Text(rating.comment.isEmpty
                                      ? rating.tags.join(', ')
                                      : rating.comment),
                                  trailing: rating.status == 'reported'
                                      ? const Chip(label: Text('Under review'))
                                      : TextButton(
                                          onPressed: () => _report(rating),
                                          child: const Text('Report'),
                                        ),
                                ),
                              ),
                              const SizedBox(height: 8),
                            ],
                          ],
                        ),
                ),
    );
  }
}
