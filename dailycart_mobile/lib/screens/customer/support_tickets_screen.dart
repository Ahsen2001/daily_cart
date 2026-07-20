import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/support_ticket_provider.dart';
import '../../config/app_identity.dart';
import '../../routes/app_routes.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state_widget.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/ticket_card.dart';

class SupportTicketsScreen extends ConsumerStatefulWidget {
  const SupportTicketsScreen({super.key});

  @override
  ConsumerState<SupportTicketsScreen> createState() =>
      _SupportTicketsScreenState();
}

class _SupportTicketsScreenState extends ConsumerState<SupportTicketsScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(supportTicketProvider).getTickets());
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(supportTicketProvider);

    return Scaffold(
      appBar: CustomAppBar(
        title: 'Support Tickets',
        actions: [
          IconButton(
            tooltip: 'Create ticket',
            onPressed: () => context.push(
              AppIdentity.isVendor
                  ? AppRoutes.vendorCreateSupportTicket
                  : AppIdentity.isRider
                      ? AppRoutes.riderCreateSupportTicket
                      : AppRoutes.createSupportTicket,
            ),
            icon: const Icon(Icons.add_rounded),
          ),
        ],
      ),
      body: state.isLoading && state.tickets.isEmpty
          ? const LoadingWidget(message: 'Loading support tickets...')
          : state.tickets.isEmpty
              ? const EmptyStateWidget(
                  title: 'No tickets',
                  message: 'Create a support ticket when you need help.',
                  icon: Icons.support_agent_rounded,
                )
              : RefreshIndicator(
                  onRefresh: () =>
                      ref.read(supportTicketProvider).getTickets(),
                  child: ListView.separated(
                    padding: const EdgeInsets.all(20),
                    itemBuilder: (context, index) {
                      final ticket = state.tickets[index];
                      return TicketCard(
                        ticket: ticket,
                        onTap: () => context.push(
                          '${AppIdentity.isVendor ? AppRoutes.vendorSupportTicketDetails : AppIdentity.isRider ? AppRoutes.riderSupportTicketDetails : AppRoutes.supportTicketDetails}/${ticket.id}',
                        ),
                      );
                    },
                    separatorBuilder: (context, index) =>
                        const SizedBox(height: 14),
                    itemCount: state.tickets.length,
                  ),
                ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push(
          AppIdentity.isVendor
              ? AppRoutes.vendorCreateSupportTicket
              : AppIdentity.isRider
                  ? AppRoutes.riderCreateSupportTicket
                  : AppRoutes.createSupportTicket,
        ),
        icon: const Icon(Icons.add_rounded),
        label: const Text('Ticket'),
      ),
    );
  }
}
