import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../models/ticket_reply_model.dart';
import '../theme/app_colors.dart';

class TicketReplyCard extends StatelessWidget {
  const TicketReplyCard({
    required this.reply,
    super.key,
  });

  final TicketReplyModel reply;

  @override
  Widget build(BuildContext context) {
    final alignment =
        reply.isCustomer ? CrossAxisAlignment.end : CrossAxisAlignment.start;
    final color = reply.isCustomer ? AppColors.primaryGreen : AppColors.white;
    final textColor = reply.isCustomer ? AppColors.white : AppColors.textColor;

    return Column(
      crossAxisAlignment: alignment,
      children: [
        Container(
          constraints: const BoxConstraints(maxWidth: 300),
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: color,
            borderRadius: BorderRadius.circular(18),
            boxShadow: const [
              BoxShadow(
                color: AppColors.shadow,
                blurRadius: 18,
                offset: Offset(0, 8),
              ),
            ],
          ),
          child: Text(reply.message, style: TextStyle(color: textColor)),
        ),
        const SizedBox(height: 4),
        Text(
          DateFormat('MMM d, h:mm a').format(reply.createdAt),
          style: Theme.of(context).textTheme.bodySmall?.copyWith(
                color: AppColors.mutedText,
              ),
        ),
      ],
    );
  }
}
