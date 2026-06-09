import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../providers/support_ticket_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/ticket_reply_card.dart';

class SupportTicketDetailsScreen extends ConsumerStatefulWidget {
  const SupportTicketDetailsScreen({
    required this.ticketId,
    super.key,
  });

  final int ticketId;

  @override
  ConsumerState<SupportTicketDetailsScreen> createState() =>
      _SupportTicketDetailsScreenState();
}

class _SupportTicketDetailsScreenState
    extends ConsumerState<SupportTicketDetailsScreen> {
  final _replyController = TextEditingController();

  @override
  void initState() {
    super.initState();
    Future.microtask(
      () => ref.read(supportTicketProvider).getTicketDetails(widget.ticketId),
    );
  }

  @override
  void dispose() {
    _replyController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(supportTicketProvider);
    final ticket = state.selectedTicket;

    return Scaffold(
      appBar: CustomAppBar(
        title: 'Ticket Details',
        actions: [
          if (ticket != null && ticket.canReply)
            IconButton(
              tooltip: 'Close ticket',
              onPressed: _closeTicket,
              icon: const Icon(Icons.check_circle_outline_rounded),
            ),
        ],
      ),
      body: state.isLoading && ticket == null
          ? const LoadingWidget(message: 'Loading ticket...')
          : ticket == null
              ? const Center(child: Text('Ticket not found.'))
              : Column(
                  children: [
                    Expanded(
                      child: ListView(
                        padding: const EdgeInsets.all(20),
                        children: [
                          Text(
                            ticket.subject,
                            style: Theme.of(context)
                                .textTheme
                                .titleLarge
                                ?.copyWith(fontWeight: FontWeight.w900),
                          ),
                          const SizedBox(height: 4),
                          Text('Status: ${ticket.status}'),
                          const SizedBox(height: 16),
                          Text(ticket.message),
                          const SizedBox(height: 20),
                          ...ticket.replies.map(
                            (reply) => Padding(
                              padding: const EdgeInsets.only(bottom: 14),
                              child: TicketReplyCard(reply: reply),
                            ),
                          ),
                        ],
                      ),
                    ),
                    if (ticket.canReply)
                      SafeArea(
                        child: Padding(
                          padding: const EdgeInsets.all(12),
                          child: Row(
                            children: [
                              Expanded(
                                child: TextField(
                                  controller: _replyController,
                                  decoration: const InputDecoration(
                                    hintText: 'Write a reply',
                                  ),
                                ),
                              ),
                              IconButton(
                                tooltip: 'Send',
                                onPressed: _sendReply,
                                icon: const Icon(Icons.send_rounded),
                              ),
                            ],
                          ),
                        ),
                      ),
                  ],
                ),
    );
  }

  Future<void> _sendReply() async {
    final message = _replyController.text.trim();
    if (message.isEmpty) {
      return;
    }
    final ok = await ref.read(supportTicketProvider).replyToTicket(
          ticketId: widget.ticketId,
          message: message,
        );
    if (ok) {
      _replyController.clear();
    }
  }

  Future<void> _closeTicket() async {
    final ok = await ref.read(supportTicketProvider).closeTicket(widget.ticketId);
    if (!mounted) {
      return;
    }
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          ok
              ? 'Support ticket closed.'
              : ref.read(supportTicketProvider).errorMessage ??
                  'Unable to close ticket.',
        ),
      ),
    );
  }
}
