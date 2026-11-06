import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mobile_buyer/main.dart';
import 'package:mobile_buyer/pages/splash_screen.dart';

void main() {
  testWidgets('EcommerceApp launches with splash screen', (WidgetTester tester) async {
    await tester.pumpWidget(const EcommerceApp());
    expect(find.byType(SplashScreen), findsOneWidget);
  });

  testWidgets('HomePage renders navigation destinations', (WidgetTester tester) async {
    await tester.pumpWidget(const MaterialApp(
      home: HomePage(),
    ));
    await tester.pumpAndSettle();

    expect(find.byType(NavigationBar), findsOneWidget);
    expect(find.text('Categories'), findsOneWidget);
    expect(find.text('Cart'), findsOneWidget);
    expect(find.text('Messages'), findsOneWidget);
    expect(find.text('Account'), findsOneWidget);
  });
}
