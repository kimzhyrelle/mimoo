import 'dart:async';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'auth/login_page.dart';
import 'auth/register_page.dart';
import '../models/product.dart';
import 'home/product_detail_page.dart';
import '../state/cart_provider.dart';
import '../state/wishlist_provider.dart';

// ─────────────────────────────────────────────────────────────────────────────
// Shared color tokens (duplicated here so landing_page is self-contained)
// ─────────────────────────────────────────────────────────────────────────────
class _C {
  static const primaryContainer = Color(0xFF34175B);
  static const secondary = Color(0xFF6B38D4);
  static const secondaryBright = Color(0xFF8455EF);
  static const flash = Color(0xFFFF3B5C);
  static const surface = Color(0xFFFAF8FE);
  static const card = Color(0xFFFFFFFF);
  static const chip = Color(0xFFEFE9FD);
  static const onSurface = Color(0xFF1B1B20);
  static const onVariant = Color(0xFF4A454F);
  static const outline = Color(0xFF7B7580);
  static const outlineVariant = Color(0xFFCCC4D1);
  static const star = Color(0xFF8455EF);
}

// ─────────────────────────────────────────────────────────────────────────────
// Data models
// ─────────────────────────────────────────────────────────────────────────────
class _Banner {
  const _Banner({
    required this.tag,
    required this.title,
    required this.subtitle,
    required this.cta,
    required this.ctaIcon,
    required this.imageUrl,
    required this.gradient,
  });
  final String tag, title, subtitle, cta;
  final IconData ctaIcon;
  final String imageUrl;
  final List<Color> gradient;
}

class _Flash {
  const _Flash({
    required this.name,
    required this.price,
    required this.original,
    required this.discount,
    required this.claimed,
    required this.imageUrl,
  });
  final String name, imageUrl;
  final double price, original;
  final int discount, claimed;
}

class _Rec {
  const _Rec({
    required this.name,
    required this.price,
    required this.original,
    required this.rating,
    required this.reviews,
    required this.badge,
    required this.shipping,
    required this.imageUrl,
  });
  final String name, reviews, badge, shipping, imageUrl;
  final double price, original, rating;

  Product get asProduct => Product(
        id: name.hashCode.abs(),
        name: name,
        price: price,
        imageUrl: imageUrl,
        category: badge,
        rating: rating,
      );
}

class _Cat {
  const _Cat({required this.icon, required this.label});
  final IconData icon;
  final String label;
}

// ─────────────────────────────────────────────────────────────────────────────
// Static data
// ─────────────────────────────────────────────────────────────────────────────
const _banners = [
  _Banner(
    tag: '✨ New Drop',
    title: 'Seoul Spring',
    subtitle: 'Fresh Urban Aesthetics\n& Linen Essentials.',
    cta: 'Explore Now',
    ctaIcon: Icons.bolt,
    imageUrl: 'https://picsum.photos/seed/banner1/200/200',
    gradient: [Color(0xFF34175B), Color(0xFF6B38D4)],
  ),
  _Banner(
    tag: '🔥 Super Payday Sale',
    title: 'Up to 70% Off',
    subtitle: 'Shop from trusted local sellers,\nall in one place.',
    cta: 'Claim Voucher',
    ctaIcon: Icons.arrow_forward,
    imageUrl: 'https://picsum.photos/seed/banner2/200/200',
    gradient: [Color(0xFF1E0042), Color(0xFF34175B)],
  ),
  _Banner(
    tag: '⚡ Flash Tech',
    title: 'Up to 60% Off',
    subtitle: 'Smart Home &\nNoise Cancelling Audio.',
    cta: 'Shop Audio',
    ctaIcon: Icons.headphones,
    imageUrl: 'https://picsum.photos/seed/banner3/200/200',
    gradient: [Color(0xFF570014), Color(0xFF34175B)],
  ),
];

const _flashItems = [
  _Flash(
      name: 'ANC Wireless Headphones',
      price: 49.99,
      original: 119.00,
      discount: 58,
      claimed: 82,
      imageUrl: 'https://picsum.photos/seed/headphones/300/300'),
  _Flash(
      name: 'Galaxy Ultra 5G',
      price: 699.00,
      original: 899.00,
      discount: 22,
      claimed: 65,
      imageUrl: 'https://picsum.photos/seed/phone/300/300'),
  _Flash(
      name: 'Ceramic Desk Lamp',
      price: 24.50,
      original: 48.00,
      discount: 49,
      claimed: 91,
      imageUrl: 'https://picsum.photos/seed/lamp/300/300'),
  _Flash(
      name: 'Everyday Vegan Tote',
      price: 32.00,
      original: 65.00,
      discount: 50,
      claimed: 74,
      imageUrl: 'https://picsum.photos/seed/tote/300/300'),
];

const _recItems = [
  _Rec(
      name: 'Pulse Pro AMOLED Smartwatch with Heart Rate & GPS',
      price: 78.00,
      original: 110.00,
      rating: 4.9,
      reviews: '1.4k',
      badge: 'Trusted Seller',
      shipping: 'Free Shipping',
      imageUrl: 'https://picsum.photos/seed/watch2/300/300'),
  _Rec(
      name: 'Breeze Pure French Linen Vacation Resort Shirt',
      price: 39.50,
      original: 55.00,
      rating: 4.8,
      reviews: '890',
      badge: 'Local Maker',
      shipping: 'Free Shipping',
      imageUrl: 'https://picsum.photos/seed/shirt/300/300'),
  _Rec(
      name: 'Indoor Calathea & Fluted Rib Ceramic Planter Set',
      price: 18.90,
      original: 28.00,
      rating: 4.9,
      reviews: '630',
      badge: 'Trusted Seller',
      shipping: 'Next-Day Delivery',
      imageUrl: 'https://picsum.photos/seed/plant/300/300'),
  _Rec(
      name: 'Aura 360° Waterproof Portable Bass Speaker',
      price: 54.00,
      original: 79.00,
      rating: 4.9,
      reviews: '2.1k',
      badge: 'Official Store',
      shipping: 'Free Shipping',
      imageUrl: 'https://picsum.photos/seed/speaker/300/300'),
  _Rec(
      name: 'Cloud Velvet Long-Wear Tint Trio Set',
      price: 22.00,
      original: 35.00,
      rating: 4.7,
      reviews: '3.4k',
      badge: 'Best Seller',
      shipping: 'Gift Included',
      imageUrl: 'https://picsum.photos/seed/makeup/300/300'),
  _Rec(
      name: 'Slim RFID-Blocking Full Grain Cardholder',
      price: 27.00,
      original: 42.00,
      rating: 4.9,
      reviews: '512',
      badge: 'Trusted Seller',
      shipping: 'Free Shipping',
      imageUrl: 'https://picsum.photos/seed/wallet/300/300'),
];

const _cats = [
  _Cat(icon: Icons.star_outline_rounded, label: 'All'),
  _Cat(icon: Icons.pets_outlined, label: 'Pet Supplies'),
  _Cat(icon: Icons.devices_outlined, label: 'Electronics'),
  _Cat(icon: Icons.woman_outlined, label: "Women's"),
  _Cat(icon: Icons.man_outlined, label: "Men's"),
  _Cat(icon: Icons.child_friendly_outlined, label: 'Kids & Baby'),
  _Cat(icon: Icons.yard_outlined, label: 'Home & Garden'),
  _Cat(icon: Icons.directions_run_outlined, label: 'Sports'),
  _Cat(icon: Icons.health_and_safety_outlined, label: 'Health'),
  _Cat(icon: Icons.menu_book_outlined, label: 'Books'),
  _Cat(icon: Icons.restaurant_outlined, label: 'Food'),
  _Cat(icon: Icons.chair_outlined, label: 'Furniture'),
  _Cat(icon: Icons.diamond_outlined, label: 'Jewelry'),
];

// ─────────────────────────────────────────────────────────────────────────────
// Landing Page
// ─────────────────────────────────────────────────────────────────────────────
class LandingPage extends StatefulWidget {
  const LandingPage({super.key});

  @override
  State<LandingPage> createState() => _LandingPageState();
}

class _LandingPageState extends State<LandingPage> {
  final _searchCtrl = TextEditingController();
  late PageController _bannerPageCtrl;
  int _bannerIdx = 0;
  Timer? _bannerTimer;

  @override
  void initState() {
    super.initState();
    _bannerPageCtrl = PageController(viewportFraction: 1.0);
    _startAutoScroll();
  }

  void _startAutoScroll() {
    _bannerTimer?.cancel();
    _bannerTimer = Timer.periodic(const Duration(seconds: 4), (_) {
      if (!mounted || !_bannerPageCtrl.hasClients) return;
      final next = (_bannerIdx + 1) % _banners.length;
      _bannerPageCtrl.animateToPage(
        next,
        duration: const Duration(milliseconds: 500),
        curve: Curves.easeInOut,
      );
    });
  }

  @override
  void dispose() {
    _bannerTimer?.cancel();
    _searchCtrl.dispose();
    _bannerPageCtrl.dispose();
    super.dispose();
  }

  // Shows modal bottom sheet prompting login/signup for gated actions
  void _showAuthPrompt(BuildContext context, String action) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (_) => _AuthPromptSheet(action: action),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _C.surface,
      body: SafeArea(
        bottom: false,
        child: CustomScrollView(
          slivers: [
            // ── Guest Header (search + Log In + Sign Up) ──────────────────
            SliverToBoxAdapter(child: _buildGuestHeader()),

            // ── Hero Banner (wide rectangular) ────────────────────────────
            SliverToBoxAdapter(
              child: SizedBox(
                height: 210,
                child: PageView.builder(
                  controller: _bannerPageCtrl,
                  itemCount: _banners.length,
                  onPageChanged: (i) => setState(() => _bannerIdx = i),
                  itemBuilder: (_, i) => _BannerCard(
                    banner: _banners[i],
                    index: i,
                    currentIndex: _bannerIdx,
                    total: _banners.length,
                  ),
                ),
              ),
            ),

            // ── Categories ────────────────────────────────────────────────
            SliverToBoxAdapter(child: _buildCategories()),

            // ── Quick Chips ───────────────────────────────────────────────
            SliverToBoxAdapter(child: _buildQuickChips()),

            // ── Flash Sale ────────────────────────────────────────────────
            SliverToBoxAdapter(child: _buildFlashSale()),

            // ── Recommended ───────────────────────────────────────────────
            SliverToBoxAdapter(child: _buildRecommendedHeader()),
            _buildRecommendedGrid(),

            // ── Footer ────────────────────────────────────────────────────
            const SliverToBoxAdapter(
              child: Padding(
                padding: EdgeInsets.symmetric(vertical: 24),
                child: Center(
                  child: Text(
                    'Loading more discovery gems...',
                    style: TextStyle(color: _C.onVariant, fontSize: 12),
                  ),
                ),
              ),
            ),
            const SliverToBoxAdapter(child: SizedBox(height: 32)),
          ],
        ),
      ),
    );
  }

  // ── Guest Header (ONE search bar + Log In + Sign Up + Avatar) ─────────────

  Widget _buildGuestHeader() {
    return Container(
      color: _C.surface,
      padding: const EdgeInsets.fromLTRB(12, 10, 12, 10),
      child: Row(
        children: [
          // ── Functional search bar ──────────────────────────────────────
          Expanded(
            child: Container(
              height: 42,
              decoration: BoxDecoration(
                color: _C.chip,
                borderRadius: BorderRadius.circular(9999),
              ),
              child: Row(
                children: [
                  const SizedBox(width: 12),
                  const Icon(Icons.search, size: 16, color: _C.outline),
                  const SizedBox(width: 6),
                  Expanded(
                    child: TextField(
                      controller: _searchCtrl,
                      style: GoogleFonts.plusJakartaSans(
                          fontSize: 13, color: _C.onSurface),
                      decoration: InputDecoration(
                        hintText: 'Search mimoo catalog & deals...',
                        hintStyle: GoogleFonts.plusJakartaSans(
                            fontSize: 12, color: _C.outline),
                        border: InputBorder.none,
                        isDense: true,
                        contentPadding: EdgeInsets.zero,
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                ],
              ),
            ),
          ),
          const SizedBox(width: 10),

          // ── Log In ─────────────────────────────────────────────────────
          GestureDetector(
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const LoginPage()),
            ),
            child: Text(
              'Log In',
              style: GoogleFonts.plusJakartaSans(
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  color: _C.secondary),
            ),
          ),
          const SizedBox(width: 10),

          // ── Sign Up pill ───────────────────────────────────────────────
          GestureDetector(
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const RegisterPage()),
            ),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
              decoration: BoxDecoration(
                color: _C.primaryContainer,
                borderRadius: BorderRadius.circular(9999),
              ),
              child: Text(
                'Sign Up',
                style: GoogleFonts.plusJakartaSans(
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                    color: Colors.white),
              ),
            ),
          ),
          const SizedBox(width: 8),

          // ── Avatar ─────────────────────────────────────────────────────
          Container(
            width: 36,
            height: 36,
            decoration: const BoxDecoration(
              color: _C.primaryContainer,
              shape: BoxShape.circle,
            ),
            child: const Icon(Icons.person, color: Colors.white, size: 18),
          ),
        ],
      ),
    );
  }

  // ── Categories ───────────────────────────────────────────────────────────────

  Widget _buildCategories() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(12, 16, 12, 4),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('Curated Categories',
                  style: GoogleFonts.plusJakartaSans(
                      fontSize: 15,
                      fontWeight: FontWeight.w800,
                      color: _C.onSurface)),
              Text('See all',
                  style: GoogleFonts.plusJakartaSans(
                      fontSize: 13,
                      color: _C.secondary,
                      fontWeight: FontWeight.w600)),
            ],
          ),
          const SizedBox(height: 12),
          SizedBox(
            height: 38,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: _cats.length,
              separatorBuilder: (_, __) => const SizedBox(width: 8),
              itemBuilder: (_, i) => _CatChip(
                cat: _cats[i],
                isSelected: i == 0,
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ── Quick Chips ──────────────────────────────────────────────────────────────

  Widget _buildQuickChips() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
      child: Row(
        children: const [
          _QuickChip(
              icon: '⚡', label: 'Flash Sale', tag: 'HOT', tagColor: _C.flash),
          SizedBox(width: 8),
          _QuickChip(
              icon: '✨', label: 'New In', tag: 'DAILY', tagColor: _C.secondary),
          SizedBox(width: 8),
          _QuickChip(
              icon: '🎟️',
              label: 'Vouchers',
              tag: '-\$15 OFF',
              tagColor: _C.secondaryBright),
          SizedBox(width: 8),
          _QuickChip(
              icon: '🔥',
              label: 'Top Picks',
              tag: 'TREND',
              tagColor: _C.secondary),
        ],
      ),
    );
  }

  // ── Flash Sale ───────────────────────────────────────────────────────────────

  Widget _buildFlashSale() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(12, 16, 12, 4),
      child: Column(
        children: [
          Row(
            children: [
              const Icon(Icons.bolt, color: _C.flash, size: 18),
              const SizedBox(width: 4),
              Text('Flash Sale',
                  style: GoogleFonts.plusJakartaSans(
                      fontSize: 15,
                      fontWeight: FontWeight.w800,
                      color: _C.onSurface)),
              const SizedBox(width: 8),
              const _CountdownTimer(),
              const Spacer(),
              Text('See All',
                  style: GoogleFonts.plusJakartaSans(
                      fontSize: 12,
                      color: _C.secondary,
                      fontWeight: FontWeight.w600)),
              const Icon(Icons.arrow_forward, size: 14, color: _C.secondary),
            ],
          ),
          const SizedBox(height: 10),
          SizedBox(
            height: 220,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: _flashItems.length,
              separatorBuilder: (_, __) => const SizedBox(width: 8),
              itemBuilder: (ctx, i) => _FlashCard(
                product: _flashItems[i],
                onAddToCart: () => _showAuthPrompt(ctx, 'add items to cart'),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ── Recommended Header ───────────────────────────────────────────────────────

  Widget _buildRecommendedHeader() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(12, 20, 12, 10),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text('Recommended For You',
              style: GoogleFonts.plusJakartaSans(
                  fontSize: 15,
                  fontWeight: FontWeight.w800,
                  color: _C.onSurface)),
          Row(
            children: [
              Text('Popular',
                  style: GoogleFonts.plusJakartaSans(
                      fontSize: 12,
                      color: _C.secondary,
                      fontWeight: FontWeight.w600)),
              const SizedBox(width: 4),
              const Icon(Icons.tune, size: 14, color: _C.secondary),
            ],
          ),
        ],
      ),
    );
  }

  // ── Recommended Grid ─────────────────────────────────────────────────────────

  Widget _buildRecommendedGrid() {
    return SliverPadding(
      padding: const EdgeInsets.symmetric(horizontal: 12),
      sliver: SliverGrid(
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 2,
          childAspectRatio: 0.54,
          crossAxisSpacing: 8,
          mainAxisSpacing: 8,
        ),
        delegate: SliverChildBuilderDelegate(
          (ctx, i) => _RecCard(
            product: _recItems[i],
            onAddToCart: () => _showAuthPrompt(ctx, 'add items to cart'),
            onWishlist: () {
              final wishlist = WishlistProvider.of(ctx);
              wishlist.toggle(_recItems[i].asProduct);
            },
          ),
          childCount: _recItems.length,
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Auth Prompt Bottom Sheet
// ─────────────────────────────────────────────────────────────────────────────
class _AuthPromptSheet extends StatelessWidget {
  const _AuthPromptSheet({required this.action});
  final String action;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.fromLTRB(12, 0, 12, 24),
      decoration: BoxDecoration(
        color: _C.card,
        borderRadius: BorderRadius.circular(20),
        boxShadow: const [
          BoxShadow(
              color: Color(0x2034175B), blurRadius: 24, offset: Offset(0, -4)),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.fromLTRB(24, 28, 24, 24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Handle
            Container(
              width: 40,
              height: 4,
              margin: const EdgeInsets.only(bottom: 20),
              decoration: BoxDecoration(
                color: _C.outlineVariant,
                borderRadius: BorderRadius.circular(9999),
              ),
            ),
            // Icon
            Container(
              width: 60,
              height: 60,
              decoration: BoxDecoration(
                color: _C.chip,
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.lock_outline,
                  color: _C.secondaryBright, size: 28),
            ),
            const SizedBox(height: 16),
            Text(
              'Sign in to continue',
              style: GoogleFonts.plusJakartaSans(
                fontSize: 18,
                fontWeight: FontWeight.w800,
                color: _C.onSurface,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'You need an account to $action.\nJoin mimoo — it\'s free!',
              textAlign: TextAlign.center,
              style: GoogleFonts.plusJakartaSans(
                fontSize: 13,
                color: _C.onVariant,
                height: 1.5,
              ),
            ),
            const SizedBox(height: 24),
            // Log In button
            SizedBox(
              width: double.infinity,
              child: FilledButton(
                onPressed: () {
                  Navigator.pop(context);
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const LoginPage()),
                  );
                },
                style: FilledButton.styleFrom(
                  backgroundColor: _C.primaryContainer,
                  minimumSize: const Size.fromHeight(48),
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(9999)),
                ),
                child: Text('Log In',
                    style: GoogleFonts.plusJakartaSans(
                        fontSize: 14,
                        fontWeight: FontWeight.w700,
                        color: Colors.white)),
              ),
            ),
            const SizedBox(height: 10),
            // Sign Up button
            SizedBox(
              width: double.infinity,
              child: OutlinedButton(
                onPressed: () {
                  Navigator.pop(context);
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const RegisterPage()),
                  );
                },
                style: OutlinedButton.styleFrom(
                  minimumSize: const Size.fromHeight(48),
                  side: const BorderSide(color: _C.secondaryBright, width: 1.5),
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(9999)),
                ),
                child: Text('Create an Account',
                    style: GoogleFonts.plusJakartaSans(
                        fontSize: 14,
                        fontWeight: FontWeight.w700,
                        color: _C.secondaryBright)),
              ),
            ),
            const SizedBox(height: 10),
            // Continue browsing
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: Text('Continue Browsing',
                  style: GoogleFonts.plusJakartaSans(
                      fontSize: 13, color: _C.onVariant)),
            ),
          ],
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Banner Card — wide rectangular, left text / right image
// ─────────────────────────────────────────────────────────────────────────────
class _BannerCard extends StatelessWidget {
  const _BannerCard({
    required this.banner,
    required this.index,
    required this.currentIndex,
    required this.total,
  });
  final _Banner banner;
  final int index;
  final int currentIndex;
  final int total;

  @override
  Widget build(BuildContext context) {
    final screenW = MediaQuery.of(context).size.width;

    return Container(
      height: 210,
      width: screenW,
      margin: const EdgeInsets.fromLTRB(12, 12, 12, 0),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: banner.gradient,
          begin: Alignment.centerLeft,
          end: Alignment.centerRight,
        ),
        borderRadius: BorderRadius.circular(18),
        boxShadow: const [
          BoxShadow(
              color: Color(0x2034175B), blurRadius: 16, offset: Offset(0, 6)),
        ],
      ),
      child: Stack(
        clipBehavior: Clip.antiAlias,
        children: [
          // ── Right image panel ──────────────────────────────────────────
          Positioned(
            right: 0,
            top: 0,
            bottom: 0,
            width: screenW * 0.42,
            child: ClipRRect(
              borderRadius:
                  const BorderRadius.horizontal(right: Radius.circular(18)),
              child: Stack(
                fit: StackFit.expand,
                children: [
                  Image.network(
                    banner.imageUrl,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) =>
                        Container(color: Colors.white.withValues(alpha: 0.08)),
                  ),
                  // subtle dark overlay on left edge of image for blending
                  Container(
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [
                          banner.gradient.first,
                          Colors.transparent,
                        ],
                        begin: Alignment.centerLeft,
                        end: Alignment.centerRight,
                        stops: const [0.0, 0.45],
                      ),
                    ),
                  ),
                  // Discount badge bottom-right
                  Positioned(
                    bottom: 12,
                    right: 12,
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 10, vertical: 5),
                      decoration: BoxDecoration(
                        color: _C.flash,
                        borderRadius: BorderRadius.circular(9999),
                        boxShadow: const [
                          BoxShadow(
                              color: Color(0x47FF3B5C),
                              blurRadius: 8,
                              offset: Offset(0, 3)),
                        ],
                      ),
                      child: Text(
                        '-70%',
                        style: GoogleFonts.plusJakartaSans(
                            fontSize: 13,
                            fontWeight: FontWeight.w800,
                            color: Colors.white),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),

          // ── Left content ───────────────────────────────────────────────
          Positioned(
            left: 0,
            top: 0,
            bottom: 0,
            width: screenW * 0.60,
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 14, 8, 40),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Top row: tag chip + "Up to X Off" label
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(9999),
                          border: Border.all(
                              color: Colors.white.withValues(alpha: 0.3),
                              width: 0.8),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Container(
                              width: 6,
                              height: 6,
                              decoration: const BoxDecoration(
                                  color: _C.flash, shape: BoxShape.circle),
                            ),
                            const SizedBox(width: 4),
                            Text(
                              banner.tag.toUpperCase(),
                              style: GoogleFonts.plusJakartaSans(
                                  fontSize: 9,
                                  fontWeight: FontWeight.w800,
                                  color: Colors.white,
                                  letterSpacing: 0.5),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),

                  // Big headline
                  Text(
                    banner.title,
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 22,
                      fontWeight: FontWeight.w800,
                      color: Colors.white,
                      height: 1.15,
                      letterSpacing: -0.5,
                    ),
                    maxLines: 3,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 4),

                  // Subtitle
                  Text(
                    banner.subtitle,
                    style: GoogleFonts.plusJakartaSans(
                        fontSize: 11, color: Colors.white70, height: 1.4),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const Spacer(),

                  // CTA button
                  GestureDetector(
                    onTap: () {},
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 16, vertical: 9),
                      decoration: BoxDecoration(
                        color: _C.secondary,
                        borderRadius: BorderRadius.circular(9999),
                        boxShadow: const [
                          BoxShadow(
                              color: Color(0x406B38D4),
                              blurRadius: 10,
                              offset: Offset(0, 4)),
                        ],
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            banner.cta,
                            style: GoogleFonts.plusJakartaSans(
                                fontSize: 12,
                                fontWeight: FontWeight.w700,
                                color: Colors.white),
                          ),
                          const SizedBox(width: 5),
                          Icon(banner.ctaIcon, size: 13, color: Colors.white),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),

          // ── Bottom: dot indicators + Verified chip ─────────────────────
          Positioned(
            bottom: 10,
            left: 16,
            child: Row(
              children: [
                // Dot indicators
                ...List.generate(total, (i) {
                  final active = i == currentIndex;
                  return AnimatedContainer(
                    duration: const Duration(milliseconds: 250),
                    margin: const EdgeInsets.only(right: 4),
                    width: active ? 18 : 6,
                    height: 6,
                    decoration: BoxDecoration(
                      color:
                          Colors.white.withValues(alpha: active ? 1.0 : 0.35),
                      borderRadius: BorderRadius.circular(9999),
                    ),
                  );
                }),
                const SizedBox(width: 10),
                // Verified local stores chip
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(9999),
                    border: Border.all(
                        color: Colors.white.withValues(alpha: 0.25),
                        width: 0.8),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.verified_outlined,
                          size: 10, color: Colors.white70),
                      const SizedBox(width: 4),
                      Text(
                        'Verified Local Stores  ${currentIndex + 1}/$total',
                        style: GoogleFonts.plusJakartaSans(
                            fontSize: 9,
                            color: Colors.white70,
                            fontWeight: FontWeight.w600),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Category Chip
// ─────────────────────────────────────────────────────────────────────────────
class _CatChip extends StatelessWidget {
  const _CatChip({required this.cat, this.isSelected = false});
  final _Cat cat;
  final bool isSelected;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {},
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        padding: const EdgeInsets.symmetric(horizontal: 12),
        decoration: BoxDecoration(
          color: isSelected ? _C.primaryContainer : _C.card,
          borderRadius: BorderRadius.circular(9999),
          border: Border.all(
            color: isSelected ? _C.primaryContainer : _C.outlineVariant,
            width: 1.2,
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              cat.icon,
              size: 15,
              color: isSelected ? Colors.white : _C.secondary,
            ),
            const SizedBox(width: 5),
            Text(
              cat.label,
              style: GoogleFonts.plusJakartaSans(
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: isSelected ? Colors.white : _C.onSurface,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Quick Chip
// ─────────────────────────────────────────────────────────────────────────────
class _QuickChip extends StatelessWidget {
  const _QuickChip({
    required this.icon,
    required this.label,
    required this.tag,
    required this.tagColor,
  });
  final String icon, label, tag;
  final Color tagColor;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 7),
        decoration: BoxDecoration(
          color: _C.card,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: _C.outlineVariant),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(icon, style: const TextStyle(fontSize: 16)),
            const SizedBox(height: 2),
            Text(label,
                style: GoogleFonts.plusJakartaSans(
                    fontSize: 10,
                    fontWeight: FontWeight.w700,
                    color: _C.onSurface)),
            Container(
              margin: const EdgeInsets.only(top: 2),
              padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
              decoration: BoxDecoration(
                color: tagColor.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(4),
              ),
              child: Text(tag,
                  style: GoogleFonts.plusJakartaSans(
                      fontSize: 8,
                      fontWeight: FontWeight.w800,
                      color: tagColor)),
            ),
          ],
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Countdown Timer
// ─────────────────────────────────────────────────────────────────────────────
class _CountdownTimer extends StatefulWidget {
  const _CountdownTimer();

  @override
  State<_CountdownTimer> createState() => _CountdownTimerState();
}

class _CountdownTimerState extends State<_CountdownTimer> {
  int _secs = 2 * 3600 + 45 * 60 + 18;
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _timer = Timer.periodic(const Duration(seconds: 1), (_) {
      if (mounted && _secs > 0) {
        setState(() => _secs--);
      }
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final h = _secs ~/ 3600;
    final m = (_secs % 3600) ~/ 60;
    final s = _secs % 60;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: _C.primaryContainer,
        borderRadius: BorderRadius.circular(6),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          _TB(h.toString().padLeft(2, '0')),
          _sep,
          _TB(m.toString().padLeft(2, '0')),
          _sep,
          _TB(s.toString().padLeft(2, '0')),
        ],
      ),
    );
  }

  static final _sep = Text(' : ',
      style: GoogleFonts.plusJakartaSans(
          fontSize: 11, fontWeight: FontWeight.w800, color: _C.flash));
}

class _TB extends StatelessWidget {
  const _TB(this.value);
  final String value;

  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
        decoration: BoxDecoration(
            color: Colors.white, borderRadius: BorderRadius.circular(3)),
        child: Text(value,
            style: GoogleFonts.plusJakartaSans(
                fontSize: 11,
                fontWeight: FontWeight.w800,
                color: _C.primaryContainer)),
      );
}

// ─────────────────────────────────────────────────────────────────────────────
// Flash Card
// ─────────────────────────────────────────────────────────────────────────────
class _FlashCard extends StatelessWidget {
  const _FlashCard({required this.product, required this.onAddToCart});
  final _Flash product;
  final VoidCallback onAddToCart;

  Product get _asProduct => Product(
        id: product.name.hashCode,
        name: product.name,
        price: product.price,
        imageUrl: product.imageUrl,
        category: 'Flash Sale',
        rating: 4.8,
        reviewCount: product.claimed * 10,
      );

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        final cart = CartProvider.of(context);
        final wishlist = WishlistProvider.of(context);
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => WishlistProvider(
              wishlist: wishlist,
              child: CartProvider(
                cart: cart,
                child: ProductDetailPage(product: _asProduct),
              ),
            ),
          ),
        );
      },
      child: Container(
        width: 130,
        decoration: BoxDecoration(
          color: _C.card,
          borderRadius: BorderRadius.circular(12),
          boxShadow: const [
            BoxShadow(
                color: Color(0x0D34175B), blurRadius: 8, offset: Offset(0, 2)),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Stack(
              children: [
                ClipRRect(
                  borderRadius:
                      const BorderRadius.vertical(top: Radius.circular(12)),
                  child: Image.network(
                    product.imageUrl,
                    width: 130,
                    height: 110,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) =>
                        Container(width: 130, height: 110, color: _C.chip),
                  ),
                ),
                Positioned(
                  top: 6,
                  left: 6,
                  child: Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                    decoration: BoxDecoration(
                        color: _C.flash,
                        borderRadius: BorderRadius.circular(4)),
                    child: Text('-${product.discount}%',
                        style: GoogleFonts.plusJakartaSans(
                            fontSize: 10,
                            fontWeight: FontWeight.w800,
                            color: Colors.white)),
                  ),
                ),
              ],
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(8, 6, 8, 6),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(product.name,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: GoogleFonts.plusJakartaSans(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: _C.onSurface,
                          height: 1.3)),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      Flexible(
                        child: Text('\$${product.price.toStringAsFixed(2)}',
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: GoogleFonts.plusJakartaSans(
                                fontSize: 13,
                                fontWeight: FontWeight.w800,
                                color: _C.flash)),
                      ),
                      const SizedBox(width: 4),
                      Text('\$${product.original.toStringAsFixed(0)}',
                          style: GoogleFonts.plusJakartaSans(
                              fontSize: 10,
                              color: _C.outline,
                              decoration: TextDecoration.lineThrough)),
                    ],
                  ),
                  const SizedBox(height: 6),
                  ClipRRect(
                    borderRadius: BorderRadius.circular(9999),
                    child: LinearProgressIndicator(
                      value: product.claimed / 100,
                      backgroundColor: _C.chip,
                      color: _C.flash,
                      minHeight: 4,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Flexible(
                        child: Text('${product.claimed}% Claimed',
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: GoogleFonts.plusJakartaSans(
                                fontSize: 9,
                                color: _C.onVariant,
                                fontWeight: FontWeight.w600)),
                      ),
                      GestureDetector(
                        onTap: onAddToCart,
                        child: Container(
                          width: 22,
                          height: 22,
                          decoration: BoxDecoration(
                              color: _C.primaryContainer,
                              shape: BoxShape.circle),
                          child: const Icon(Icons.add,
                              size: 13, color: Colors.white),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Recommended Card
// ─────────────────────────────────────────────────────────────────────────────
class _RecCard extends StatelessWidget {
  const _RecCard({
    required this.product,
    required this.onAddToCart,
    required this.onWishlist,
  });
  final _Rec product;
  final VoidCallback onAddToCart;
  final VoidCallback onWishlist;

  @override
  Widget build(BuildContext context) {
    final wishlist = WishlistProvider.of(context);
    final wished = wishlist.contains(product.asProduct);
    final p = Product(
      id: product.name.hashCode,
      name: product.name,
      price: product.price,
      imageUrl: product.imageUrl,
      category: product.badge,
      rating: product.rating,
      reviewCount: 0,
    );

    return GestureDetector(
      onTap: () {
        final cart = CartProvider.of(context);
        final wishlist = WishlistProvider.of(context);
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => WishlistProvider(
              wishlist: wishlist,
              child: CartProvider(
                cart: cart,
                child: ProductDetailPage(product: p),
              ),
            ),
          ),
        );
      },
      child: Container(
        decoration: BoxDecoration(
          color: _C.card,
          borderRadius: BorderRadius.circular(12),
          boxShadow: const [
            BoxShadow(
                color: Color(0x0D34175B), blurRadius: 8, offset: Offset(0, 2)),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Stack(
              children: [
                ClipRRect(
                  borderRadius:
                      const BorderRadius.vertical(top: Radius.circular(12)),
                  child: AspectRatio(
                    aspectRatio: 1,
                    child: Image.network(
                      product.imageUrl,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => Container(color: _C.chip),
                    ),
                  ),
                ),
                Positioned(
                  top: 6,
                  right: 6,
                  child: GestureDetector(
                    onTap: onWishlist,
                    child: Container(
                      width: 28,
                      height: 28,
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.9),
                        shape: BoxShape.circle,
                      ),
                      child: Icon(
                          wished ? Icons.favorite : Icons.favorite_border,
                          size: 15,
                          color: wished ? const Color(0xFFFF3B5C) : _C.outline),
                    ),
                  ),
                ),
              ],
            ),
            Expanded(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(8, 6, 8, 8),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 5, vertical: 2),
                      decoration: BoxDecoration(
                          color: _C.chip,
                          borderRadius: BorderRadius.circular(4)),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.verified,
                              size: 9, color: _C.secondary),
                          const SizedBox(width: 2),
                          Text(product.badge,
                              style: GoogleFonts.plusJakartaSans(
                                  fontSize: 8,
                                  fontWeight: FontWeight.w700,
                                  color: _C.secondary)),
                        ],
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(product.name,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: GoogleFonts.plusJakartaSans(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: _C.onSurface,
                            height: 1.3)),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        const Icon(Icons.star_rounded,
                            size: 11, color: _C.star),
                        const SizedBox(width: 2),
                        Text('${product.rating} (${product.reviews})',
                            style: GoogleFonts.plusJakartaSans(
                                fontSize: 9, color: _C.onVariant)),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('\$${product.price.toStringAsFixed(2)}',
                                style: GoogleFonts.plusJakartaSans(
                                    fontSize: 15,
                                    fontWeight: FontWeight.w800,
                                    color: _C.primaryContainer,
                                    letterSpacing: -0.3)),
                            Text('\$${product.original.toStringAsFixed(0)}',
                                style: GoogleFonts.plusJakartaSans(
                                    fontSize: 10,
                                    color: _C.outline,
                                    decoration: TextDecoration.lineThrough)),
                          ],
                        ),
                        GestureDetector(
                          onTap: onAddToCart,
                          child: Container(
                            width: 28,
                            height: 28,
                            decoration: const BoxDecoration(
                                color: _C.primaryContainer,
                                shape: BoxShape.circle),
                            child: const Icon(Icons.add,
                                size: 16, color: Colors.white),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Text(product.shipping,
                        style: GoogleFonts.plusJakartaSans(
                            fontSize: 9,
                            fontWeight: FontWeight.w600,
                            color: _C.secondary)),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
