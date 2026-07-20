import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/support_ticket_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';
import '../../widgets/dailycart_card.dart';

class CreateSupportTicketScreen extends ConsumerStatefulWidget {
  const CreateSupportTicketScreen({super.key});

  @override
  ConsumerState<CreateSupportTicketScreen> createState() =>
      _CreateSupportTicketScreenState();
}

class _CreateSupportTicketScreenState
    extends ConsumerState<CreateSupportTicketScreen> {
  final _formKey = GlobalKey<FormState>();
  final _subjectController = TextEditingController();
  final _messageController = TextEditingController();
  final _orderIdController = TextEditingController();
  String _priority = 'medium';

  @override
  void dispose() {
    _subjectController.dispose();
    _messageController.dispose();
    _orderIdController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(supportTicketProvider);

    return Scaffold(
      appBar: const CustomAppBar(title: 'Create Ticket'),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          DailyCartCard(
            child: Form(
              key: _formKey,
              child: Column(
                children: [
                  CustomTextField(
                    label: 'Subject',
                    controller: _subjectController,
                    icon: Icons.subject_rounded,
                    validator: (value) => value == null || value.trim().isEmpty
                        ? 'Subject is required.'
                        : null,
                  ),
                  const SizedBox(height: 14),
                  TextFormField(
                    controller: _messageController,
                    maxLines: 5,
                    decoration: const InputDecoration(
                      labelText: 'Message',
                      alignLabelWithHint: true,
                      prefixIcon: Icon(Icons.message_outlined),
                    ),
                    validator: (value) => value == null || value.trim().isEmpty
                        ? 'Message is required.'
                        : null,
                  ),
                  const SizedBox(height: 14),
                  CustomTextField(
                    label: 'Order ID optional',
                    controller: _orderIdController,
                    icon: Icons.receipt_long_outlined,
                    keyboardType: TextInputType.number,
                  ),
                  const SizedBox(height: 14),
                  DropdownButtonFormField<String>(
                    initialValue: _priority,
                    decoration: const InputDecoration(
                      labelText: 'Priority',
                      prefixIcon: Icon(Icons.priority_high_rounded),
                    ),
                    items: const [
                      DropdownMenuItem(value: 'low', child: Text('Low')),
                      DropdownMenuItem(value: 'medium', child: Text('Medium')),
                      DropdownMenuItem(value: 'high', child: Text('High')),
                      DropdownMenuItem(value: 'urgent', child: Text('Urgent')),
                    ],
                    onChanged: (value) =>
                        setState(() => _priority = value ?? 'medium'),
                  ),
                  const SizedBox(height: 12),
                  OutlinedButton.icon(
                    onPressed: () {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                          content: Text('Attachment upload placeholder.'),
                        ),
                      );
                    },
                    icon: const Icon(Icons.attach_file_rounded),
                    label: const Text('Attachment placeholder'),
                  ),
                  const SizedBox(height: 20),
                  CustomButton(
                    label: 'Create Ticket',
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

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }
    final orderId = int.tryParse(_orderIdController.text.trim());
    final ok = await ref
        .read(supportTicketProvider)
        .createTicket(
          subject: _subjectController.text.trim(),
          message: _messageController.text.trim(),
          priority: _priority,
          orderId: orderId,
        );
    if (!mounted) {
      return;
    }
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          ok
              ? 'Support ticket created.'
              : ref.read(supportTicketProvider).errorMessage ??
                    'Unable to create ticket.',
        ),
      ),
    );
    if (ok) {
      context.pop();
    }
  }
}
