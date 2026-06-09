import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:webview_flutter/webview_flutter.dart';

import '../../providers/payment_provider.dart';
import '../../routes/app_routes.dart';

class PayHereWebViewScreen extends ConsumerStatefulWidget {
  const PayHereWebViewScreen({
    required this.orderId,
    required this.paymentUrl,
    super.key,
  });

  final int orderId;
  final String paymentUrl;

  @override
  ConsumerState<PayHereWebViewScreen> createState() =>
      _PayHereWebViewScreenState();
}

class _PayHereWebViewScreenState extends ConsumerState<PayHereWebViewScreen> {
  late final WebViewController _controller;
  int _progress = 0;

  @override
  void initState() {
    super.initState();
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(
        NavigationDelegate(
          onProgress: (progress) => setState(() => _progress = progress),
          onNavigationRequest: (request) {
            final url = request.url;
            if (url.contains('/payment/success')) {
              _finishPayment(successHint: true);
              return NavigationDecision.prevent;
            }
            if (url.contains('/payment/cancel') ||
                url.contains('/payment/failed')) {
              _finishPayment(successHint: false);
              return NavigationDecision.prevent;
            }
            return NavigationDecision.navigate;
          },
        ),
      )
      ..loadRequest(Uri.parse(widget.paymentUrl));
  }

  Future<void> _finishPayment({required bool successHint}) async {
    final order = await ref
        .read(paymentProvider)
        .checkPaymentStatus(widget.orderId);

    if (!mounted) {
      return;
    }

    if (successHint && order?.isPaid == true) {
      context.go(AppRoutes.paymentSuccess, extra: order);
      return;
    }

    context.go(AppRoutes.paymentFailed, extra: order);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('PayHere Payment')),
      body: Column(
        children: [
          if (_progress < 100) LinearProgressIndicator(value: _progress / 100),
          Expanded(child: WebViewWidget(controller: _controller)),
        ],
      ),
    );
  }
}
