import 'package:dailycart_mobile/screens/splash/splash_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('DailyCart app renders splash screen', (tester) async {
    await tester.pumpWidget(
      const ProviderScope(
        child: MaterialApp(
          home: SplashScreen(),
        ),
      ),
    );

    await tester.pump();

    expect(find.text('DailyCart'), findsOneWidget);
    expect(find.text('Fresh groceries, delivered fast'), findsOneWidget);
  });
}
