import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'config/app_identity.dart';
import 'routes/app_router.dart';
import 'theme/light_theme.dart';
import 'widgets/connectivity_banner.dart';

class DailyCartApp extends ConsumerWidget {
  const DailyCartApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return MaterialApp.router(
      title: AppIdentity.displayName,
      debugShowCheckedModeBanner: false,
      theme: LightTheme.theme,
      routerConfig: ref.watch(appRouterProvider),
      builder: (context, child) => ConnectivityBanner(
        child: child ?? const SizedBox.shrink(),
      ),
    );
  }
}
