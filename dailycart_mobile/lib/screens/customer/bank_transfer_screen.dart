import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';

import '../../models/bank_transfer_model.dart';
import '../../models/checkout_response_model.dart';
import '../../providers/payment_provider.dart';
import '../../routes/app_routes.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/error_widget.dart';
import '../../widgets/loading_widget.dart';

class BankTransferScreen extends ConsumerStatefulWidget {
  const BankTransferScreen({required this.orders, super.key});

  final List<OrderModel> orders;

  @override
  ConsumerState<BankTransferScreen> createState() => _BankTransferScreenState();
}

class _BankTransferScreenState extends ConsumerState<BankTransferScreen> {
  final _picker = ImagePicker();

  @override
  void initState() {
    super.initState();
    Future.microtask(() async {
      for (final order in widget.orders) {
        await ref.read(paymentProvider).getBankTransfer(order.id);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(paymentProvider);
    return Scaffold(
      appBar: const CustomAppBar(title: 'Bank Transfer'),
      body: state.isLoading && state.bankTransfers.isEmpty
          ? const LoadingWidget(message: 'Loading bank instructions...')
          : RefreshIndicator(
              onRefresh: () async {
                for (final order in widget.orders) {
                  await ref.read(paymentProvider).getBankTransfer(order.id);
                }
              },
              child: ListView(
                padding: const EdgeInsets.all(20),
                children: [
                  const DailyCartCard(
                    child: Text(
                      'Transfer the exact amount for each vendor order, then upload a clear photo of its payment slip.',
                    ),
                  ),
                  const SizedBox(height: 14),
                  for (final order in widget.orders) ...[
                    if (state.bankTransfers[order.id] case final transfer?)
                      _TransferCard(
                        order: order,
                        transfer: transfer,
                        isUploading: state.isLoading,
                        onUpload: () => _upload(order.id),
                      )
                    else
                      DailyCartErrorWidget(
                        title: 'Order ${order.orderNumber}',
                        message:
                            state.errorMessage ??
                            'Bank transfer details are unavailable.',
                        onRetry: () =>
                            ref.read(paymentProvider).getBankTransfer(order.id),
                      ),
                    const SizedBox(height: 14),
                  ],
                  FilledButton.icon(
                    onPressed: () => context.go(AppRoutes.myOrders),
                    icon: const Icon(Icons.receipt_long_rounded),
                    label: const Text('View my orders'),
                  ),
                ],
              ),
            ),
    );
  }

  Future<void> _upload(int orderId) async {
    final image = await _picker.pickImage(
      source: ImageSource.gallery,
      imageQuality: 90,
    );
    if (image == null) return;
    final transfer = await ref
        .read(paymentProvider)
        .uploadBankTransferSlip(orderId, image.path);
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          transfer == null
              ? ref.read(paymentProvider).errorMessage ?? 'Upload failed.'
              : 'Payment slip uploaded for verification.',
        ),
      ),
    );
  }
}

class _TransferCard extends StatelessWidget {
  const _TransferCard({
    required this.order,
    required this.transfer,
    required this.isUploading,
    required this.onUpload,
  });

  final OrderModel order;
  final BankTransferModel transfer;
  final bool isUploading;
  final VoidCallback onUpload;

  @override
  Widget build(BuildContext context) {
    return DailyCartCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Order ${order.orderNumber}',
            style: Theme.of(
              context,
            ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w900),
          ),
          const SizedBox(height: 12),
          _row('Amount', CurrencyFormatter.lkr(transfer.expectedAmount)),
          _row('Reference', transfer.referenceNumber),
          _row('Bank', transfer.bankName),
          _row('Account holder', transfer.accountHolder),
          _row('Account number', transfer.accountNumber),
          _row('Branch', transfer.branch),
          _row('Status', transfer.status.replaceAll('_', ' ')),
          if (transfer.lastRejectionReason?.isNotEmpty == true) ...[
            const SizedBox(height: 8),
            Text(
              'Rejected: ${transfer.lastRejectionReason}',
              style: TextStyle(color: Theme.of(context).colorScheme.error),
            ),
          ],
          if (transfer.slips.isNotEmpty) ...[
            const SizedBox(height: 8),
            Text('${transfer.slips.length} slip(s) submitted'),
          ],
          const SizedBox(height: 14),
          SizedBox(
            width: double.infinity,
            child: FilledButton.icon(
              onPressed: isUploading ? null : onUpload,
              icon: const Icon(Icons.upload_file_rounded),
              label: Text(
                transfer.requiresSlip ? 'Upload payment slip' : 'Replace slip',
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _row(String label, String value) => Padding(
    padding: const EdgeInsets.only(bottom: 7),
    child: Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(width: 112, child: Text(label)),
        Expanded(
          child: SelectableText(
            value.isEmpty ? '-' : value,
            style: const TextStyle(fontWeight: FontWeight.w700),
          ),
        ),
      ],
    ),
  );
}
