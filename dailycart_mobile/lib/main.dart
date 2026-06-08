import 'package:firebase_core/firebase_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_dotenv/flutter_dotenv.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'app/app.dart';
import 'features/notifications/data/notification_service.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  await dotenv.load(fileName: '.env', isOptional: true);

  try {
    await Firebase.initializeApp();
    await NotificationService.initialize();
  } catch (_) {
    // Firebase is configured after Android/iOS platform files are generated.
  }

  runApp(const ProviderScope(child: DailyCartApp()));
}
