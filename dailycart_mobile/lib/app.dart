import 'package:flutter/material.dart';

import 'config/app_identity.dart';
import 'routes/app_router.dart';
import 'theme/light_theme.dart';

class DailyCartApp extends StatelessWidget {
  const DailyCartApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp.router(
      title: AppIdentity.displayName,
      debugShowCheckedModeBanner: false,
      theme: LightTheme.theme,
      routerConfig: appRouter,
    );
  }
}
