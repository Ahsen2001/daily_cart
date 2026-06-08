import 'package:flutter/material.dart';

import 'router.dart';
import 'theme.dart';

class DailyCartApp extends StatelessWidget {
  const DailyCartApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp.router(
      title: 'DailyCart',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.light,
      routerConfig: appRouter,
    );
  }
}
