import 'package:firebase_core/firebase_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_dotenv/flutter_dotenv.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'app.dart';
import 'config/app_identity.dart';
import 'services/notification_service.dart';

Future<void> bootstrap(DailyCartAppFlavor flavor) async {
  WidgetsFlutterBinding.ensureInitialized();
  AppIdentity.configure(flavor);

  try {
    await dotenv.load(fileName: '.env');
  } catch (_) {
    // Local env is optional while the project is being bootstrapped.
  }

  try {
    await Firebase.initializeApp();
    await NotificationService.initialize();
  } catch (_) {
    // Firebase is configured independently for each native app identity.
  }

  runApp(const ProviderScope(child: DailyCartApp()));
}
