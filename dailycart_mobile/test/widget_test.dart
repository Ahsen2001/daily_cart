import 'package:dailycart_mobile/app.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

void main() {
  testWidgets('DailyCart app renders splash screen', (tester) async {
    await tester.pumpWidget(
      const ProviderScope(
        child: DailyCartApp(),
      ),
    );

    await tester.pump();

    expect(find.text('DailyCart'), findsOneWidget);
    expect(find.text('Fresh groceries, delivered fast'), findsOneWidget);
  });
}
