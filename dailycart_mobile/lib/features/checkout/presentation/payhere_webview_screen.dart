import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';

class PayHereWebViewScreen extends StatefulWidget {
  const PayHereWebViewScreen({
    required this.checkoutUrl,
    super.key,
  });

  final String checkoutUrl;

  @override
  State<PayHereWebViewScreen> createState() => _PayHereWebViewScreenState();
}

class _PayHereWebViewScreenState extends State<PayHereWebViewScreen> {
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
            if (url.contains('/success') || url.contains('/failed')) {
              Navigator.of(context).pop();
            }
            return NavigationDecision.navigate;
          },
        ),
      )
      ..loadRequest(Uri.parse(widget.checkoutUrl));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('PayHere')),
      body: Column(
        children: [
          if (_progress < 100) LinearProgressIndicator(value: _progress / 100),
          Expanded(child: WebViewWidget(controller: _controller)),
        ],
      ),
    );
  }
}
