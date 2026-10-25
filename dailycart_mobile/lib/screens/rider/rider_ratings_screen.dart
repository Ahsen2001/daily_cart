import 'package:flutter/material.dart';

import '../../models/rider_rating_model.dart';
import '../../services/auth_api_service.dart';
import '../../services/rider_rating_api_service.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state_widget.dart';
import '../../widgets/error_widget.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/dailycart_card.dart';

class RiderRatingsScreen extends StatefulWidget {
  const RiderRatingsScreen({super.key});

  @override
  State<RiderRatingsScreen> createState() => _RiderRatingsScreenState();
}

class _RiderRatingsScreenState extends State<RiderRatingsScreen> {
  final _service = RiderRatingApiService();
  RiderRatingSummary? _summary;
  String? _error;
  bool _reporting = false;

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
          maxLength: 1000,
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
    setState(() => _reporting = true);
    try {
      await _service.report(rating.id, reason);
      await _load();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Rating sent for administrator review.'),
          ),
        );
      }
    } on ApiException catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text(error.message)));
      }
    } finally {
      if (mounted) setState(() => _reporting = false);
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
                        DailyCartCard(
                          child: Column(
                            children: [
                              Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceAround,
                                children: [
                                  _RatingStat(
                                    icon: Icons.star_rounded,
                                    label: 'Average',
                                    value: summary.average.toStringAsFixed(1),
                                  ),
                                  _RatingStat(
                                    icon: Icons.reviews_outlined,
                                    label: 'Visible',
                                    value: '${summary.total}',
                                  ),
                                  _RatingStat(
                                    icon: Icons.workspace_premium_outlined,
                                    label: '5 star',
                                    value: '${summary.fiveStarCount}',
                                  ),
                                  _RatingStat(
                                    icon: Icons.flag_outlined,
                                    label: 'Review',
                                    value: '${summary.reported}',
                                  ),
                                ],
                              ),
                              const SizedBox(height: 16),
                              for (var score = 5; score >= 1; score--)
                                Padding(
                                  padding: const EdgeInsets.symmetric(
                                    vertical: 3,
                                  ),
                                  child: Row(
                                    children: [
                                      SizedBox(
                                        width: 28,
                                        child: Text('$score★'),
                                      ),
                                      const SizedBox(width: 8),
                                      Expanded(
                                        child: LinearProgressIndicator(
                                          value: summary.total == 0
                                              ? 0
                                              : (summary.distribution[score] ??
                                                        0) /
                                                    summary.total,
                                          minHeight: 7,
                                          borderRadius: BorderRadius.circular(
                                            20,
                                          ),
                                        ),
                                      ),
                                      SizedBox(
                                        width: 30,
                                        child: Text(
                                          '${summary.distribution[score] ?? 0}',
                                          textAlign: TextAlign.end,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 12),
                        for (final rating in summary.ratings) ...[
                          DailyCartCard(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Expanded(
                                      child: Text(
                                        '${List.filled(rating.rating, '★').join()}  ${rating.orderNumber}',
                                        style: const TextStyle(
                                          fontWeight: FontWeight.w800,
                                        ),
                                      ),
                                    ),
                                    if (rating.status == 'reported')
                                      const Chip(label: Text('Under review')),
                                  ],
                                ),
                                if (rating.comment.isNotEmpty) ...[
                                  const SizedBox(height: 8),
                                  Text(rating.comment),
                                ],
                                if (rating.tags.isNotEmpty) ...[
                                  const SizedBox(height: 8),
                                  Wrap(
                                    spacing: 6,
                                    runSpacing: 6,
                                    children: rating.tags
                                        .map((tag) => Chip(label: Text(tag)))
                                        .toList(),
                                  ),
                                ],
                                if (rating.status == 'visible' &&
                                    rating.comment.isNotEmpty)
                                  Align(
                                    alignment: Alignment.centerRight,
                                    child: TextButton.icon(
                                      onPressed: _reporting
                                          ? null
                                          : () => _report(rating),
                                      icon: const Icon(Icons.flag_outlined),
                                      label: const Text('Report'),
                                    ),
                                  ),
                              ],
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

class _RatingStat extends StatelessWidget {
  const _RatingStat({
    required this.icon,
    required this.label,
    required this.value,
  });
  final IconData icon;
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) => Expanded(
    child: Column(
      children: [
        Icon(icon, color: Colors.amber.shade700),
        const SizedBox(height: 4),
        Text(value, style: const TextStyle(fontWeight: FontWeight.w900)),
        Text(label, style: Theme.of(context).textTheme.labelSmall),
      ],
    ),
  );
}
