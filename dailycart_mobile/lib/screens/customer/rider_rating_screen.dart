import 'package:flutter/material.dart';

import '../../models/rider_rating_model.dart';
import '../../services/auth_api_service.dart';
import '../../services/rider_rating_api_service.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/error_widget.dart';
import '../../widgets/loading_widget.dart';

class RiderRatingScreen extends StatefulWidget {
  const RiderRatingScreen({required this.orderId, super.key});

  final int orderId;

  @override
  State<RiderRatingScreen> createState() => _RiderRatingScreenState();
}

class _RiderRatingScreenState extends State<RiderRatingScreen> {
  final _service = RiderRatingApiService();
  final _comment = TextEditingController();
  RiderRatingContext? _context;
  String? _error;
  bool _loading = true;
  bool _saving = false;
  int _rating = 5;
  final Set<String> _tags = {};

  static const _availableTags = [
    'on_time',
    'professional',
    'friendly',
    'careful_handling',
    'good_communication',
  ];

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _comment.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final value = await _service.customerRating(widget.orderId);
      if (!mounted) return;
      setState(() {
        _context = value;
        _rating = value.rating?.rating ?? 5;
        _comment.text = value.rating?.comment ?? '';
        _tags
          ..clear()
          ..addAll(value.rating?.tags ?? const []);
      });
    } on ApiException catch (error) {
      if (mounted) setState(() => _error = error.message);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _submit() async {
    setState(() => _saving = true);
    try {
      await _service.submit(
        orderId: widget.orderId,
        rating: _rating,
        comment: _comment.text.trim(),
        tags: _tags.toList(growable: false),
      );
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Thank you. Your rider rating was saved.')),
      );
      await _load();
    } on ApiException catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text(error.message)));
      }
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: const CustomAppBar(title: 'Rate Your Rider'),
      body: _loading
          ? const LoadingWidget(message: 'Loading rider rating...')
          : _error != null
              ? DailyCartErrorWidget(
                  title: 'Rating unavailable',
                  message: _error!,
                  onRetry: _load,
                )
              : ListView(
                  padding: const EdgeInsets.all(20),
                  children: [
                    Text(
                      'How was your delivery with ${_context!.riderName}?',
                      style: Theme.of(context)
                          .textTheme
                          .headlineSmall
                          ?.copyWith(fontWeight: FontWeight.w900),
                    ),
                    const SizedBox(height: 8),
                    const Text(
                      'Your feedback helps maintain a safe, reliable delivery experience.',
                    ),
                    const SizedBox(height: 20),
                    Semantics(
                      label: 'Rating $_rating out of 5',
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: List.generate(5, (index) {
                          final value = index + 1;
                          return IconButton(
                            tooltip: '$value stars',
                            iconSize: 42,
                            onPressed: _context!.canRate
                                ? () => setState(() => _rating = value)
                                : null,
                            icon: Icon(
                              value <= _rating
                                  ? Icons.star_rounded
                                  : Icons.star_border_rounded,
                              color: Colors.amber.shade700,
                            ),
                          );
                        }),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: _availableTags
                          .map((tag) => FilterChip(
                                label: Text(tag.replaceAll('_', ' ')),
                                selected: _tags.contains(tag),
                                onSelected: _context!.canRate
                                    ? (selected) => setState(() {
                                          selected
                                              ? _tags.add(tag)
                                              : _tags.remove(tag);
                                        })
                                    : null,
                              ))
                          .toList(growable: false),
                    ),
                    const SizedBox(height: 18),
                    TextField(
                      controller: _comment,
                      enabled: _context!.canRate,
                      maxLength: 2000,
                      minLines: 4,
                      maxLines: 7,
                      decoration: const InputDecoration(
                        labelText: 'Comment (optional)',
                        hintText: 'Share what went well or could be improved',
                      ),
                    ),
                    if (_context!.canRate)
                      FilledButton.icon(
                        onPressed: _saving ? null : _submit,
                        icon: const Icon(Icons.star_rounded),
                        label: Text(_saving ? 'Saving...' : 'Submit rating'),
                      )
                    else
                      const Card(
                        child: ListTile(
                          leading: Icon(Icons.check_circle_outline_rounded),
                          title: Text('Rating submitted'),
                          subtitle: Text(
                            'This order is not currently eligible for another rating.',
                          ),
                        ),
                      ),
                  ],
                ),
    );
  }
}
