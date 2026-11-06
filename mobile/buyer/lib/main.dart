import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'state/auth_service.dart';
import 'state/cart_provider.dart';
import 'state/wishlist_provider.dart';
import 'pages/splash_screen.dart';
import 'pages/home/shop_page.dart';
import 'pages/cart/cart_page.dart';
import 'pages/categories/categories_page.dart';
import 'pages/chat/chat_page.dart';
import 'pages/account/account_page.dart';
import 'pages/wishlist/wishlist_page.dart';
import 'utils/animations.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Wire CartState to AuthService BEFORE restoreSession so the cart is
  // loaded whenever a token is found in secure storage.
  AuthService.instance.onLoginCallback = CartProvider.defaultCart.onLogin;
  AuthService.instance.onLogoutCallback = CartProvider.defaultCart.onLogout;

  // Restore any persisted Sanctum token from secure storage so the user
  // stays logged in across app restarts, until logout or token expiry.
  await AuthService.instance.restoreSession();

  runApp(const EcommerceApp());
}

class EcommerceApp extends StatelessWidget {
  const EcommerceApp({super.key});

  @override
  Widget build(BuildContext context) {
    return WishlistProvider(
      wishlist: WishlistProvider.defaultWishlist,
      child: CartProvider(
        cart: CartProvider.defaultCart,
        child: MaterialApp(
          title: 'mimoo',
          debugShowCheckedModeBanner: false,
          theme: ThemeData(
            colorScheme: const ColorScheme(
              brightness: Brightness.light,
              primary: Color(0xFF34175B),
              onPrimary: Colors.white,
              primaryContainer: Color(0xFF34175B),
              onPrimaryContainer: Color(0xFF9F81CB),
              secondary: Color(0xFF6B38D4),
              onSecondary: Colors.white,
              secondaryContainer: Color(0xFF8455EF),
              onSecondaryContainer: Colors.white,
              tertiary: Color(0xFFFF3B5C),
              onTertiary: Colors.white,
              tertiaryContainer: Color(0xFF570014),
              onTertiaryContainer: Color(0xFFFF4B65),
              error: Color(0xFFBA1A1A),
              onError: Colors.white,
              errorContainer: Color(0xFFFFDAD6),
              onErrorContainer: Color(0xFF93000A),
              surface: Color(0xFFFAF8FE),
              onSurface: Color(0xFF1B1B20),
              onSurfaceVariant: Color(0xFF4A454F),
              outline: Color(0xFF7B7580),
              outlineVariant: Color(0xFFCCC4D1),
              shadow: Colors.black,
              scrim: Colors.black,
              inverseSurface: Color(0xFF303035),
              onInverseSurface: Color(0xFFF2F0F6),
              inversePrimary: Color(0xFFD7BAFF),
            ),
            textTheme: GoogleFonts.plusJakartaSansTextTheme(),
            useMaterial3: true,
            scaffoldBackgroundColor: const Color(0xFFFAF8FE),
          ),
          home: const SplashScreen(),
        ),
      ),
    );
  }
}

class HomePage extends StatefulWidget {
  const HomePage({super.key});

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  int _currentIndex = 0;

  @override
  void initState() {
    super.initState();
    // Show cart server errors in a snackbar from the root scaffold
    CartProvider.defaultCart.addListener(_onCartChanged);
  }

  @override
  void dispose() {
    CartProvider.defaultCart.removeListener(_onCartChanged);
    super.dispose();
  }

  void _onCartChanged() {
    final error = CartProvider.defaultCart.consumeError();
    if (error != null && mounted) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(error),
          backgroundColor: Colors.red.shade700,
          behavior: SnackBarBehavior.floating,
          duration: const Duration(seconds: 4),
        ));
      });
    }
  }

  void _openWishlist() {
    final wishlist = WishlistProvider.of(context);
    final cart = CartProvider.of(context);
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => WishlistProvider(
          wishlist: wishlist,
          child: CartProvider(
            cart: cart,
            child: const WishlistPage(),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final cart = CartProvider.of(context);
    final wishlist = WishlistProvider.of(context);

    return AnimatedBuilder(
      animation: Listenable.merge([cart, wishlist]),
      builder: (context, _) => Scaffold(
        body: IndexedStack(
          index: _currentIndex,
          children: const [
            ShopPage(), // 0 — Home
            CategoriesPage(), // 1 — Categories
            CartPage(), // 2 — Cart
            ChatPage(), // 3 — Messages
            AccountPage(), // 4 — Account
          ],
        ),
        bottomNavigationBar: MimooNavBar(
          currentIndex: _currentIndex,
          onTap: (i) => setState(() => _currentIndex = i),
          items: [
            MimooNavItem(
              icon: Icons.home_outlined,
              activeIcon: Icons.home,
              label: 'Home',
            ),
            MimooNavItem(
              icon: Icons.grid_view_outlined,
              activeIcon: Icons.grid_view,
              label: 'Categories',
            ),
            MimooNavItem(
              icon: Icons.shopping_bag_outlined,
              activeIcon: Icons.shopping_bag,
              label: 'Cart',
            ),
            MimooNavItem(
              icon: Icons.chat_bubble_outline,
              activeIcon: Icons.chat_bubble,
              label: 'Messages',
            ),
            MimooNavItem(
              icon: Icons.account_circle_outlined,
              activeIcon: Icons.account_circle,
              label: 'Account',
            ),
          ],
        ),
        // Wishlist FAB shown on Home and Categories tabs
        floatingActionButton: (_currentIndex == 0 || _currentIndex == 1)
            ? FloatingActionButton.small(
                onPressed: _openWishlist,
                backgroundColor: const Color(0xFF34175B),
                elevation: 3,
                child: Badge(
                  isLabelVisible: wishlist.count > 0,
                  label: Text('${wishlist.count}'),
                  backgroundColor: const Color(0xFFFF3B5C),
                  child: const Icon(Icons.favorite_border,
                      color: Colors.white, size: 18),
                ),
              )
            : null,
        floatingActionButtonLocation: FloatingActionButtonLocation.endFloat,
      ),
    );
  }
}
