import 'package:flutter/material.dart';

import '../theme/app_colors.dart';
import 'dailycart_card.dart';

class DeliveryTimePicker extends StatelessWidget {
  const DeliveryTimePicker({
    required this.selectedTime,
    required this.minimumDeliveryTime,
    required this.onSelected,
    super.key,
  });

  final DateTime? selectedTime;
  final DateTime minimumDeliveryTime;
  final ValueChanged<DateTime> onSelected;

  @override
  Widget build(BuildContext context) {
    return DailyCartCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Delivery Time',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.w900,
                ),
          ),
          const SizedBox(height: 8),
          Text(
            'Earliest delivery: ${_format(minimumDeliveryTime)}',
            style: Theme.of(context).textTheme.bodySmall?.copyWith(
                  color: AppColors.mutedText,
                ),
          ),
          const SizedBox(height: 14),
          OutlinedButton.icon(
            onPressed: () => _pickDateTime(context),
            icon: const Icon(Icons.schedule_rounded),
            label: Text(
              selectedTime == null
                  ? 'Select delivery time'
                  : _format(selectedTime!),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _pickDateTime(BuildContext context) async {
    final now = DateTime.now();
    final date = await showDatePicker(
      context: context,
      initialDate: selectedTime ?? minimumDeliveryTime,
      firstDate: DateTime(now.year, now.month, now.day),
      lastDate: now.add(const Duration(days: 30)),
    );

    if (date == null || !context.mounted) {
      return;
    }

    final time = await showTimePicker(
      context: context,
      initialTime: TimeOfDay.fromDateTime(selectedTime ?? minimumDeliveryTime),
    );

    if (time == null) {
      return;
    }

    onSelected(
      DateTime(date.year, date.month, date.day, time.hour, time.minute),
    );
  }

  String _format(DateTime value) {
    final hour = value.hour > 12
        ? value.hour - 12
        : value.hour == 0
            ? 12
            : value.hour;
    final minute = value.minute.toString().padLeft(2, '0');
    final suffix = value.hour >= 12 ? 'PM' : 'AM';
    return '${value.year}-${value.month.toString().padLeft(2, '0')}-${value.day.toString().padLeft(2, '0')} $hour:$minute $suffix';
  }
}
