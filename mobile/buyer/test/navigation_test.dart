import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mobile_buyer/main.dart';
import 'package:mobile_buyer/pages/home/product_detail_page.dart';
import 'package:mobile_buyer/pages/landing_page.dart';
import 'package:mobile_buyer/state/auth_service.dart';
import 'package:mobile_buyer/state/cart_provider.dart';

void main() {
  setUp(() {
    CartProvider.defaultCart.clear();
  });

  tearDown(() async {
    await AuthService.instance.logout();
    CartProvider.defaultCart.clear();
  });

  testWidgets(
      'ShopPage: Tapping recommended product card navigates to ProductDetailPage and Add to Cart works',
      (WidgetTester tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.0;
    addTearDown(() {
      tester.view.resetPhysicalSize();
      tester.view.resetDevicePixelRatio();
    });

    AuthService.instance.login();

    await tester.pumpWidget(const EcommerceApp());
    await tester.pumpAndSettle();

    // In guest mode, splash screen opens LandingPage. Let's log in to go to HomePage.
    // Or pump HomePage with CartProvider root
    await tester.pumpWidget(CartProvider(
      cart: CartProvider.defaultCart,
      child: const MaterialApp(
        home: HomePage(),
      ),
    ));
    await tester.pumpAndSettle();

    final cardFinder =
        find.text('Pulse Pro AMOLED Smartwatch with Heart Rate & GPS');
    await tester.scrollUntilVisible(
      cardFinder,
      300,
      scrollable: find.byType(Scrollable).first,
    );
    expect(cardFinder, findsOneWidget);

    await tester.tap(cardFinder);
    await tester.pumpAndSettle();

    // Verify on ProductDetailPage
    expect(find.byType(ProductDetailPage), findsOneWidget);
    expect(find.text('Pulse Pro AMOLED Smartwatch with Heart Rate & GPS'),
        findsOneWidget);
    expect(find.text('Add to Cart'), findsOneWidget);

    // Initial cart should be empty
    expect(CartProvider.defaultCart.totalCount, 0);

    // Tap Add to Cart
    await tester.tap(find.text('Add to Cart'));
    await tester.pumpAndSettle();

    // Verify item was added to CartState
    expect(CartProvider.defaultCart.totalCount, 1);
    expect(find.text('Added to cart!'), findsOneWidget);
  });

  testWidgets(
      'ShopPage: Tapping flash sale card navigates to ProductDetailPage and Add to Cart works',
      (WidgetTester tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.0;
    addTearDown(() {
      tester.view.resetPhysicalSize();
      tester.view.resetDevicePixelRatio();
    });

    AuthService.instance.login();

    await tester.pumpWidget(CartProvider(
      cart: CartProvider.defaultCart,
      child: const MaterialApp(
        home: HomePage(),
      ),
    ));
    await tester.pumpAndSettle();

    final flashCardFinder = find.text('ANC Wireless Headphones');
    expect(flashCardFinder, findsOneWidget);

    await tester.tap(flashCardFinder);
    await tester.pumpAndSettle();

    // Verify on ProductDetailPage
    expect(find.byType(ProductDetailPage), findsOneWidget);
    expect(find.text('ANC Wireless Headphones'), findsOneWidget);

    // Tap Add to Cart
    await tester.tap(find.text('Add to Cart'));
    await tester.pumpAndSettle();

    expect(CartProvider.defaultCart.totalCount, 1);
    expect(find.text('Added to cart!'), findsOneWidget);
  });

  testWidgets(
      'LandingPage: Tapping flash sale card and recommended card navigates to ProductDetailPage',
      (WidgetTester tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.0;
    addTearDown(() {
      tester.view.resetPhysicalSize();
      tester.view.resetDevicePixelRatio();
    });

    await tester.pumpWidget(CartProvider(
      cart: CartProvider.defaultCart,
      child: const MaterialApp(
        home: LandingPage(),
      ),
    ));
    await tester.pumpAndSettle();

    // Tap flash card on LandingPage
    final flashCardFinder = find.text('ANC Wireless Headphones');
    expect(flashCardFinder, findsOneWidget);

    await tester.tap(flashCardFinder);
    await tester.pumpAndSettle();

    expect(find.byType(ProductDetailPage), findsOneWidget);
    expect(find.text('ANC Wireless Headphones'), findsOneWidget);

    // Pop back to LandingPage
    Navigator.of(tester.element(find.byType(ProductDetailPage))).pop();
    await tester.pumpAndSettle();

    // Tap recommended card on LandingPage
    final recCardFinder =
        find.text('Pulse Pro AMOLED Smartwatch with Heart Rate & GPS');
    await tester.scrollUntilVisible(
      recCardFinder,
      300,
      scrollable: find.byType(Scrollable).first,
    );
    expect(recCardFinder, findsOneWidget);

    await tester.tap(recCardFinder);
    await tester.pumpAndSettle();

    expect(find.byType(ProductDetailPage), findsOneWidget);
    expect(find.text('Pulse Pro AMOLED Smartwatch with Heart Rate & GPS'),
        findsOneWidget);
  });
}
