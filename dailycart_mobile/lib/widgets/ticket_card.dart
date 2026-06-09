import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../models/support_ticket_model.dart';
import '../theme/app_colors.dart';
import 'dailycart_card.dart';

class TicketCard extends StatelessWidget {
  const TicketCard({
    required this.ticket,
    required this.onTap,
    super.key,
  });

  final SupportTicketModel ticket;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(22),
      onTap: onTap,
      child: DailyCartCard(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    ticket.subject,
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          fontWeight: FontWeight.w900,
                        ),
                  ),
                ),
                _Badge(label: ticket.status, color: AppColors.primaryGreen),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              ticket.ticketNumber.isEmpty ? '#${ticket.id}' : ticket.ticketNumber,
              style: const TextStyle(color: AppColors.mutedText),
            ),
            const SizedBox(height: 10),
            Row(
              children: [
                _Badge(label: ticket.priority, color: AppColors.accentOrange),
                const Spacer(),
                Text(
                  DateFormat('MMM d, yyyy').format(ticket.createdAt),
                  style: Theme.of(context).textTheme.bodySmall,
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _Badge extends StatelessWidget {
  const _Badge({required this.label, required this.color});

  final String label;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.14),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        label.replaceAll('_', ' ').toUpperCase(),
        style: TextStyle(
          color: color,
          fontSize: 11,
          fontWeight: FontWeight.w900,
        ),
      ),
    );
  }
}
