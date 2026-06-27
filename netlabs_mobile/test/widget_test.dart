import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('Smoke test', (WidgetTester tester) async {
    // Build a simple MaterialApp to verify testing environment works.
    await tester.pumpWidget(
      const MaterialApp(
        home: Scaffold(
          body: Text('Netlabs'),
        ),
      ),
    );

    // Verify that our app displays 'Netlabs'.
    expect(find.text('Netlabs'), findsOneWidget);
  });
}
