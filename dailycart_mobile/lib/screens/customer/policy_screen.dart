import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:webview_flutter/webview_flutter.dart';

import '../../providers/customer_extended_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/error_widget.dart';
import '../../widgets/loading_widget.dart';

class PolicyScreen extends ConsumerStatefulWidget {
  const PolicyScreen({required this.policyKey, super.key});

  final String policyKey;

  @override
  ConsumerState<PolicyScreen> createState() => _PolicyScreenState();
}

class _PolicyScreenState extends ConsumerState<PolicyScreen> {
  WebViewController? _controller;

  @override
  void initState() {
    super.initState();
    Future.microtask(_load);
  }

  Future<void> _load() async {
    final provider = ref.read(customerExtendedProvider);
    await provider.loadPolicies();
    final policy = provider.policies
        .where((item) => item.key == widget.policyKey)
        .firstOrNull;
    if (policy != null && policy.url.isNotEmpty) {
      _controller = WebViewController()
        ..setJavaScriptMode(JavaScriptMode.unrestricted)
        ..loadRequest(Uri.parse(policy.url));
      if (mounted) setState(() {});
    }
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(customerExtendedProvider);
    final policy = state.policies
        .where((item) => item.key == widget.policyKey)
        .firstOrNull;
    return Scaffold(
      appBar: CustomAppBar(title: policy?.title ?? 'Policy'),
      body: state.isLoading
          ? const LoadingWidget(message: 'Loading policy...')
          : state.errorMessage != null
              ? DailyCartErrorWidget(
                  title: 'Unable to load policy',
                  message: state.errorMessage!,
                  onRetry: _load,
                )
              : _controller == null
                  ? const Center(child: Text('Policy is unavailable.'))
                  : WebViewWidget(controller: _controller!),
    );
  }
}
