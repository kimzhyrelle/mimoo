import 'dart:async';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../models/product.dart';
import '../../state/cart_provider.dart';
import '../../state/wishlist_provider.dart';
import '../../state/auth_service.dart';
import '../cart/cart_page.dart';
import '../checkout/checkout_page.dart';
import '../auth/login_page.dart';
import '../seller/seller_store_page.dart';
import '../../utils/animations.dart';

// ── Design tokens ─────────────────────────────────────────────────────────────
const _kPrimary = Color(0xFF34175B);
const _kSecondary = Color(0xFF6B38D4);
const _kFlash = Color(0xFFFF3B5C);
const _kSurface = Color(0xFFF5F4FC);
const _kCard = Color(0xFFFFFFFF);
const _kChip = Color(0xFFEFE9FD);
const _kBorder = Color(0xFFDDD8F0);
const _kText = Color(0xFF1A1035);
const _kSubtext = Color(0xFF8A84A3);
const _kGreen = Color(0xFF1DB966);

TextStyle _pjs({
  double size = 14,
  FontWeight weight = FontWeight.w400,
  Color color = _kText,
  double? height,
  TextDecoration? decoration,
  double? letterSpacing,
}) =>
    GoogleFonts.plusJakartaSans(
      fontSize: size,
      fontWeight: weight,
      color: color,
      height: height,
      decoration: decoration,
      letterSpacing: letterSpacing,
    );

// ── Main Page ─────────────────────────────────────────────────────────────────
class ProductDetailPage extends StatefulWidget {
  const ProductDetailPage({super.key, required this.product});
  final Product product;

  @override
  State<ProductDetailPage> createState() => _ProductDetailPageState();
}

class _ProductDetailPageState extends State<ProductDetailPage> {
  int _imageIndex = 0;
  final _pageCtrl = PageController();

  // ignore: unused_field, prefer_final_fields
  int _saleSeconds = 4 * 3600 + 28 * 60 + 39;
  // ignore: unused_field
  Timer? _timer;

  bool _specsExpanded = false;

  // Use the product's real image URLs; fall back to a single placeholder
  // when no images are available so the carousel always has at least one item.
  List<String> get _imageUrls {
    final imgs = widget.product.images;
    if (imgs.isNotEmpty) return imgs;
    if (widget.product.imageUrl.isNotEmpty) return [widget.product.imageUrl];
    return [];
  }

  @override
  void initState() {
    super.initState();
    // _timer intentionally not started: _buildFlashBanner is hidden until
    // a sale_end_date column is available. Keep the timer code for future use.
  }

  @override
  void dispose() {
    _timer?.cancel();
    _pageCtrl.dispose();
    super.dispose();
  }

  // ignore: unused_element
  String get _countdown {
    final h = _saleSeconds ~/ 3600;
    final m = (_saleSeconds % 3600) ~/ 60;
    final s = _saleSeconds % 60;
    return '${h.toString().padLeft(2, '0')} : ${m.toString().padLeft(2, '0')} : ${s.toString().padLeft(2, '0')}';
  }

  // ── Open variant bottom sheet ──────────────────────────────────────────────
  void _openVariantSheet({required bool buyNow}) {
    final isLoggedIn = AuthService.instance.isLoggedIn;
    if (!isLoggedIn) {
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => LoginPage(
            onLoginSuccess: () => _openVariantSheet(buyNow: buyNow),
          ),
        ),
      );
      return;
    }

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _VariantSheet(
        product: widget.product,
        buyNow: buyNow,
        onConfirm: (variantId, qty) => _onVariantConfirmed(
          variantId: variantId,
          qty: qty,
          buyNow: buyNow,
        ),
      ),
    );
  }

  void _onVariantConfirmed({
    required String? variantId,
    required int qty,
    required bool buyNow,
  }) {
    final cart = CartProvider.of(context);
    // Single API call with the exact quantity chosen in the sheet
    cart.add(widget.product, variantId: variantId, quantity: qty);

    if (buyNow) {
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => CartProvider(cart: cart, child: const CheckoutPage()),
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Added to cart!',
              style:
                  _pjs(size: 13, color: Colors.white, weight: FontWeight.w600)),
          backgroundColor: _kPrimary,
          duration: const Duration(seconds: 2),
          action: SnackBarAction(
            label: 'View Cart',
            textColor: Colors.white,
            onPressed: () => Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) =>
                    CartProvider(cart: cart, child: const CartPage()),
              ),
            ),
          ),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kSurface,
      appBar: _buildAppBar(),
      body: SingleChildScrollView(
        padding: const EdgeInsets.only(bottom: 100),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildImageCarousel(),
            _buildThumbnailRow(),
            _buildFlashBanner(),
            _buildPriceSection(),
            _buildProductInfo(),
            const Divider(color: _kBorder, height: 24),
            _buildSelectedVariantRow(),
            const Divider(color: _kBorder, height: 24),
            _buildDeliverySection(),
            const Divider(color: _kBorder, height: 24),
            _buildSellerSection(),
            const Divider(color: _kBorder, height: 24),
            _buildSpecs(),
            const Divider(color: _kBorder, height: 24),
            _buildReviews(),
            const SizedBox(height: 20),
          ],
        ),
      ),
      bottomNavigationBar: _buildBottomBar(),
    );
  }

  // ── App Bar ────────────────────────────────────────────────────────────────
  PreferredSizeWidget _buildAppBar() {
    return AppBar(
      backgroundColor: _kCard,
      elevation: 0,
      leading: IconButton(
        icon: Icon(Icons.arrow_back, color: _kText),
        onPressed: () => Navigator.pop(context),
      ),
      title: Container(
        height: 38,
        decoration: BoxDecoration(
          color: _kSurface,
          borderRadius: BorderRadius.circular(9999),
          border: Border.all(color: _kBorder),
        ),
        child: Row(
          children: [
            const SizedBox(width: 12),
            Icon(Icons.search, size: 16, color: _kSubtext),
            const SizedBox(width: 6),
            Expanded(
              child: Text('Search in store...',
                  style: _pjs(size: 12, color: _kSubtext)),
            ),
          ],
        ),
      ),
      actions: [
        Stack(
          clipBehavior: Clip.none,
          children: [
            IconButton(
              icon: Icon(Icons.shopping_bag_outlined, color: _kText),
              onPressed: () {
                final cart = CartProvider.of(context);
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) =>
                        CartProvider(cart: cart, child: const CartPage()),
                  ),
                );
              },
            ),
            Positioned(
              top: 8,
              right: 8,
              child: Container(
                width: 8,
                height: 8,
                decoration:
                    const BoxDecoration(color: _kFlash, shape: BoxShape.circle),
              ),
            ),
          ],
        ),
        IconButton(
          icon: Icon(Icons.more_vert, color: _kText),
          onPressed: () {},
        ),
      ],
    );
  }

  // ── Image Carousel ─────────────────────────────────────────────────────────
  Widget _buildImageCarousel() {
    return SizedBox(
      height: 300,
      child: Stack(
        children: [
          PageView.builder(
            controller: _pageCtrl,
            itemCount: _imageUrls.isEmpty ? 1 : _imageUrls.length,
            onPageChanged: (i) => setState(() => _imageIndex = i),
            itemBuilder: (_, i) {
              if (_imageUrls.isEmpty) {
                return Container(
                  color: _kChip,
                  child: const Center(
                    child: Icon(Icons.image_not_supported_outlined,
                        size: 60, color: _kSubtext),
                  ),
                );
              }
              return Image.network(
                _imageUrls[i],
                fit: BoxFit.cover,
                headers: const {'Accept': 'image/*'},
                errorBuilder: (_, __, ___) => Container(
                  color: _kChip,
                  child: Icon(Icons.image_outlined, size: 60, color: _kSubtext),
                ),
              );
            },
          ),
          // Top-left badges — only show sale badge when product is actually on sale
          if (widget.product.isOnSale)
            Positioned(
              top: 12,
              left: 12,
              child: (() {
                final pct = widget.product.salePrice != null
                    ? (((widget.product.price - widget.product.salePrice!) /
                                widget.product.price) *
                            100)
                        .round()
                    : 0;
                return _Badge(label: '-$pct% OFF', color: _kFlash);
              })(),
            ),
          // Top-right actions
          Positioned(
            top: 12,
            right: 12,
            child: Column(
              children: [
                _CarouselBtn(
                  icon: WishlistProvider.of(context).contains(widget.product)
                      ? Icons.favorite
                      : Icons.favorite_border,
                  color: WishlistProvider.of(context).contains(widget.product)
                      ? _kFlash
                      : _kText,
                  onTap: () {
                    if (!AuthService.instance.isLoggedIn) {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => LoginPage(
                            onLoginSuccess: () => WishlistProvider.of(context)
                                .toggle(widget.product),
                          ),
                        ),
                      );
                      return;
                    }
                    WishlistProvider.of(context).toggle(widget.product);
                  },
                ),
                const SizedBox(height: 6),
                _CarouselBtn(
                  icon: Icons.share_outlined,
                  color: _kText,
                  onTap: () {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text('Share link copied!',
                            style: _pjs(
                                size: 13,
                                color: Colors.white,
                                weight: FontWeight.w600)),
                        backgroundColor: _kPrimary,
                        duration: const Duration(seconds: 2),
                      ),
                    );
                  },
                ),
                const SizedBox(height: 6),
                _CarouselBtn(
                  icon: Icons.photo_camera_outlined,
                  color: _kText,
                  onTap: () {},
                ),
              ],
            ),
          ),
          // Page counter
          Positioned(
            bottom: 12,
            right: 12,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
              decoration: BoxDecoration(
                color: Colors.black.withValues(alpha: 0.45),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(
                  '${_imageIndex + 1}/${_imageUrls.isEmpty ? 1 : _imageUrls.length}',
                  style: _pjs(
                      size: 11, weight: FontWeight.w600, color: Colors.white)),
            ),
          ),
          // Dot indicators
          Positioned(
            bottom: 12,
            left: 12,
            child: Row(
              children: List.generate(
                _imageUrls.isEmpty ? 1 : _imageUrls.length,
                (i) => AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  width: _imageIndex == i ? 14 : 5,
                  height: 5,
                  margin: const EdgeInsets.only(right: 3),
                  decoration: BoxDecoration(
                    color: _imageIndex == i
                        ? Colors.white
                        : Colors.white.withValues(alpha: 0.5),
                    borderRadius: BorderRadius.circular(3),
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ── Thumbnail Row ──────────────────────────────────────────────────────────
  Widget _buildThumbnailRow() {
    if (_imageUrls.length <= 1) return const SizedBox.shrink();
    return Container(
      color: _kCard,
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      child: Row(
        children: List.generate(_imageUrls.length, (i) {
          final sel = _imageIndex == i;
          return GestureDetector(
            onTap: () {
              _pageCtrl.animateToPage(i,
                  duration: const Duration(milliseconds: 250),
                  curve: Curves.easeInOut);
            },
            child: Container(
              margin: const EdgeInsets.only(right: 8),
              width: 52,
              height: 52,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(8),
                border: Border.all(
                  color: sel ? _kSecondary : _kBorder,
                  width: sel ? 2 : 1,
                ),
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(7),
                child: Image.network(
                  _imageUrls[i],
                  fit: BoxFit.cover,
                  headers: const {'Accept': 'image/*'},
                  errorBuilder: (_, __, ___) => Container(
                    color: _kChip,
                    child:
                        Icon(Icons.image_outlined, size: 20, color: _kSubtext),
                  ),
                ),
              ),
            ),
          );
        }),
      ),
    );
  }

  // ── Flash Sale Banner ────────────────────────────────────────────────────────
  // Hidden: the products table has no sale_end_date, so the countdown is fake.
  // The sale badge and struck-through price in _buildPriceSection are sufficient.
  // Keep this method and the _saleSeconds / _timer / _countdown code intact
  // so it can be re-enabled once a sale_end_date column is added.
  Widget _buildFlashBanner() => const SizedBox.shrink();

  // ── Price Section ──────────────────────────────────────────────────────────
  Widget _buildPriceSection() {
    final p = widget.product;
    final effectivePrice = p.effectivePrice;
    final hasDiscount = p.isOnSale && p.salePrice != null;
    final discountPct =
        hasDiscount ? (((p.price - p.salePrice!) / p.price) * 100).round() : 0;

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 14, 16, 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text('₱${effectivePrice.toStringAsFixed(0)}',
                  style: _pjs(
                      size: 28,
                      weight: FontWeight.w800,
                      color: hasDiscount ? _kFlash : _kPrimary,
                      letterSpacing: -0.02)),
              if (hasDiscount) ...[
                const SizedBox(width: 8),
                Text('₱${p.price.toStringAsFixed(0)}',
                    style: _pjs(
                        size: 16,
                        color: _kSubtext,
                        decoration: TextDecoration.lineThrough)),
                const SizedBox(width: 8),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                  decoration: BoxDecoration(
                      color: _kFlash, borderRadius: BorderRadius.circular(5)),
                  child: Text('-$discountPct% OFF',
                      style: _pjs(
                          size: 10,
                          weight: FontWeight.w800,
                          color: Colors.white)),
                ),
              ],
            ],
          ),
          const SizedBox(height: 8),
          // Stock status chip
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
            decoration: BoxDecoration(
              color: _kChip,
              borderRadius: BorderRadius.circular(8),
              border: Border(
                  left: BorderSide(
                      color: p.isInStock ? _kSecondary : _kFlash, width: 3)),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(
                  p.isInStock
                      ? Icons.check_circle_outline
                      : Icons.remove_circle_outline,
                  size: 14,
                  color: p.isInStock ? _kSecondary : _kFlash,
                ),
                const SizedBox(width: 6),
                Text(
                  p.stockStatus.isNotEmpty
                      ? p.stockStatus
                      : (p.isInStock ? 'In stock' : 'Out of stock'),
                  style: _pjs(
                      size: 11,
                      weight: FontWeight.w600,
                      color: p.isInStock ? _kSecondary : _kFlash),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ── Product Info ───────────────────────────────────────────────────────────
  Widget _buildProductInfo() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 4, 16, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Badges row
          Row(
            children: [
              _SmallBadge(
                  label: 'MALL', icon: Icons.store_outlined, color: _kPrimary),
              const SizedBox(width: 6),
              _SmallBadge(
                  label: 'verified Authentic',
                  icon: Icons.verified_outlined,
                  color: _kGreen),
            ],
          ),
          const SizedBox(height: 8),
          // Product name + share
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Text(
                  widget.product.name.isNotEmpty
                      ? widget.product.name
                      : 'Product',
                  style: _pjs(size: 17, weight: FontWeight.w800, height: 1.35),
                ),
              ),
              const SizedBox(width: 8),
              GestureDetector(
                onTap: () {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text('Share link copied!',
                          style: _pjs(
                              size: 13,
                              color: Colors.white,
                              weight: FontWeight.w600)),
                      backgroundColor: _kPrimary,
                      duration: const Duration(seconds: 2),
                    ),
                  );
                },
                child: Padding(
                  padding: const EdgeInsets.only(top: 2),
                  child: Icon(Icons.share_outlined, size: 18, color: _kSubtext),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          // Ratings row — only shown when there are actual reviews
          if (widget.product.reviewCount > 0) ...[
            Row(
              children: [
                ...List.generate(
                    5,
                    (i) => Icon(Icons.star_rounded,
                        size: 14, color: Colors.amber.shade500)),
                const SizedBox(width: 4),
                Text(widget.product.rating.toStringAsFixed(1),
                    style: _pjs(size: 13, weight: FontWeight.w700)),
                Text(' (${widget.product.reviewCount})',
                    style: _pjs(size: 12, color: _kSubtext)),
                const SizedBox(width: 10),
                const Spacer(),
                GestureDetector(
                  onTap: () {
                    if (!AuthService.instance.isLoggedIn) {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => LoginPage(
                            onLoginSuccess: () => WishlistProvider.of(context)
                                .toggle(widget.product),
                          ),
                        ),
                      );
                      return;
                    }
                    WishlistProvider.of(context).toggle(widget.product);
                  },
                  child: Icon(
                    WishlistProvider.of(context).contains(widget.product)
                        ? Icons.favorite
                        : Icons.favorite_border,
                    size: 16,
                    color: _kFlash,
                  ),
                ),
              ],
            ),
          ] else ...[
            // No ratings yet — just show wishlist heart
            Row(
              children: [
                Text('No reviews yet', style: _pjs(size: 12, color: _kSubtext)),
                const Spacer(),
                GestureDetector(
                  onTap: () {
                    if (!AuthService.instance.isLoggedIn) {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => LoginPage(
                            onLoginSuccess: () => WishlistProvider.of(context)
                                .toggle(widget.product),
                          ),
                        ),
                      );
                      return;
                    }
                    WishlistProvider.of(context).toggle(widget.product);
                  },
                  child: Icon(
                    WishlistProvider.of(context).contains(widget.product)
                        ? Icons.favorite
                        : Icons.favorite_border,
                    size: 16,
                    color: _kFlash,
                  ),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  // ── Selected Variant Row ───────────────────────────────────────────────────
  Widget _buildSelectedVariantRow() {
    return GestureDetector(
      onTap: () => _openVariantSheet(buyNow: false),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16),
        child: Row(
          children: [
            Text('Select Variations',
                style: _pjs(size: 13, weight: FontWeight.w700)),
            const SizedBox(width: 8),
            Text('Color, Size', style: _pjs(size: 12, color: _kSubtext)),
            const Spacer(),
            GestureDetector(
              onTap: () => _showFitGuide(),
              child: Text('Size Guide',
                  style: _pjs(
                      size: 12, weight: FontWeight.w600, color: _kSecondary)),
            ),
            const SizedBox(width: 4),
            Icon(Icons.chevron_right, size: 16, color: _kSecondary),
          ],
        ),
      ),
    );
  }

  void _showFitGuide() {
    showDialog(
      context: context,
      builder: (_) => const _FitGuideDialog(),
    );
  }

  // ── Delivery Section ───────────────────────────────────────────────────────
  Widget _buildDeliverySection() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        children: [
          _InfoRow(
            icon: Icons.local_shipping_outlined,
            iconColor: _kGreen,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Free Delivery  •  orders over ₱500',
                    style: _pjs(size: 13, weight: FontWeight.w700)),
                Text('Standard Delivery: Arrives Thu, Oct 31 – Sat, Nov 2',
                    style: _pjs(size: 11, color: _kSubtext)),
              ],
            ),
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              _TrustBadge(
                  icon: Icons.verified_user_outlined, label: '100% Authentic'),
              const SizedBox(width: 10),
              _TrustBadge(icon: Icons.replay_outlined, label: '15 Days Return'),
              const SizedBox(width: 10),
              _TrustBadge(
                  icon: Icons.payments_outlined, label: 'COD Available'),
            ],
          ),
        ],
      ),
    );
  }

  // ── Seller Section ─────────────────────────────────────────────────────────
  Widget _buildSellerSection() {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: _kCard,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: _kBorder),
      ),
      child: Column(
        children: [
          Row(
            children: [
              Container(
                width: 42,
                height: 42,
                decoration: BoxDecoration(
                  color: _kChip,
                  borderRadius: BorderRadius.circular(10),
                ),
                child:
                    Icon(Icons.storefront_outlined, color: _kPrimary, size: 22),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Text('mimoo Official Apparel',
                            style: _pjs(size: 13, weight: FontWeight.w800)),
                        const SizedBox(width: 6),
                        _SmallBadge(
                            label: 'MALL', icon: Icons.store, color: _kPrimary),
                      ],
                    ),
                    Text('Flagship Streetwear Partner',
                        style: _pjs(size: 11, color: _kSubtext)),
                  ],
                ),
              ),
              OutlinedButton(
                onPressed: () => Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => const SellerStorePage(
                      sellerId: 'mimoo-official',
                      sellerName: 'mimoo Official Apparel',
                    ),
                  ),
                ),
                style: OutlinedButton.styleFrom(
                  side: const BorderSide(color: _kPrimary),
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(9999)),
                  padding:
                      const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                  minimumSize: Size.zero,
                ),
                child: Text('Visit Store',
                    style: _pjs(
                        size: 12, weight: FontWeight.w700, color: _kPrimary)),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              _SellerStat(value: '99%', label: 'Positive Rating'),
              _SellerStat(value: '< 1hr', label: 'Chat Response'),
              _SellerStat(value: '45.2k', label: 'Followers'),
            ],
          ),
        ],
      ),
    );
  }

  // ── Specs ──────────────────────────────────────────────────────────────────
  Widget _buildSpecs() {
    final specs = [
      ('SKU', 'MO-HD-420-PU'),
      ('Material / Fabric', '100% French Terry Cotton'),
      ('Fabric Weight', '420 GSM (Heavyweight)'),
      ('Fit Profile', 'Dropped Shoulder Boxy'),
      ('Treatment', 'Mineral Acid Washed'),
    ];

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Product Specifications',
              style: _pjs(size: 15, weight: FontWeight.w800)),
          const SizedBox(height: 12),
          ...specs.map((s) => Padding(
                padding: const EdgeInsets.only(bottom: 6),
                child: Row(
                  children: [
                    SizedBox(
                      width: 130,
                      child:
                          Text(s.$1, style: _pjs(size: 12, color: _kSubtext)),
                    ),
                    Expanded(
                      child: Text(s.$2,
                          style: _pjs(size: 12, weight: FontWeight.w600)),
                    ),
                  ],
                ),
              )),
          const SizedBox(height: 8),
          Text(
            'Crafted from custom-developed 420 GSM ultra-dense combed cotton, this hoodie delivers an authentic relaxed streetwear silhouette. Each garment undergoes a multi-stage mineral acid dye wash, yielding a one-of-a-kind vintage fade pattern.',
            style: _pjs(size: 12, color: _kSubtext, height: 1.6),
          ),
          const SizedBox(height: 10),
          // Size chart
          Text('Size & Measurement Guide (cm):',
              style: _pjs(size: 12, weight: FontWeight.w700)),
          const SizedBox(height: 6),
          ...[
            'Small: Chest 120 · Length 69 · Sleeve 59',
            'Medium: Chest 126 · Length 72 · Sleeve 61',
            'Large: Chest 132 · Length 74 · Sleeve 63',
            'X-Large: Chest 138 · Length 76 · Sleeve 64'
          ].map((line) => Padding(
                padding: const EdgeInsets.only(bottom: 3),
                child: Row(
                  children: [
                    Text('• ',
                        style: _pjs(
                            size: 12,
                            color: _kSecondary,
                            weight: FontWeight.w700)),
                    Expanded(
                        child: Text(line,
                            style: _pjs(size: 12, color: _kSubtext))),
                  ],
                ),
              )),
          const SizedBox(height: 10),
          // Styling & Care expandable
          GestureDetector(
            onTap: () => setState(() => _specsExpanded = !_specsExpanded),
            child: Container(
              padding: const EdgeInsets.symmetric(vertical: 12),
              decoration: const BoxDecoration(
                border: Border(top: BorderSide(color: _kBorder)),
              ),
              child: Row(
                children: [
                  Text('Care Instructions',
                      style: _pjs(
                          size: 13,
                          weight: FontWeight.w700,
                          color: _kSecondary)),
                  const Spacer(),
                  Icon(
                    _specsExpanded ? Icons.expand_less : Icons.expand_more,
                    color: _kSecondary,
                  ),
                ],
              ),
            ),
          ),
          if (_specsExpanded)
            Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: Text(
                'Machine wash cold on gentle cycle inside-out. Do not bleach. Air dry in shade to maintain rich mineral gradient.',
                style: _pjs(size: 12, color: _kSubtext, height: 1.55),
              ),
            ),
        ],
      ),
    );
  }

  // ── Reviews ────────────────────────────────────────────────────────────────
  Widget _buildReviews() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text('Customer Reviews',
                  style: _pjs(size: 15, weight: FontWeight.w800)),
              const Spacer(),
              Row(
                children: List.generate(
                    5,
                    (i) => Icon(Icons.star_rounded,
                        size: 12, color: Colors.amber.shade500)),
              ),
              const SizedBox(width: 4),
              Text('4.9 / 5',
                  style: _pjs(
                      size: 12, weight: FontWeight.w700, color: _kSecondary)),
            ],
          ),
          const SizedBox(height: 4),
          Text('(438 verified ratings)',
              style: _pjs(size: 11, color: _kSubtext)),
          const SizedBox(height: 10),
          // Filter chips
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                'All (438)',
                'With Photos (142)',
                '5 Stars (390)',
                'Fit True (310)'
              ]
                  .map((t) => Container(
                        margin: const EdgeInsets.only(right: 8),
                        padding: const EdgeInsets.symmetric(
                            horizontal: 12, vertical: 5),
                        decoration: BoxDecoration(
                          color: t.startsWith('All') ? _kPrimary : _kChip,
                          borderRadius: BorderRadius.circular(9999),
                        ),
                        child: Text(t,
                            style: _pjs(
                              size: 11,
                              weight: FontWeight.w600,
                              color: t.startsWith('All')
                                  ? Colors.white
                                  : _kSecondary,
                            )),
                      ))
                  .toList(),
            ),
          ),
          const SizedBox(height: 12),
          _ReviewCard(
            initials: 'AL',
            name: 'Alyssa L.',
            verified: true,
            timeAgo: 'Yesterday',
            variant: 'Size: M • Washed Purple',
            review:
                'The heavyweight 420 GSM cotton fleece feels super luxurious! Oversized fit falls exactly like high-end streetwear brands. Definitely worth the price! 🔥',
            imageSeeds: const ['review1', 'review2'],
          ),
          const SizedBox(height: 10),
          _ReviewCard(
            initials: 'KD',
            name: 'Kenneth D.',
            verified: true,
            timeAgo: '3 days ago',
            variant: 'Size: L • Slate Charcoal',
            review:
                'Fast delivery by mimoo Express! High quality acid wash finish and warm inside lining. Ordered Thursday and received Saturday morning.',
            imageSeeds: const [],
          ),
          const SizedBox(height: 10),
          GestureDetector(
            onTap: () => ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text('Loading all 438 reviews...',
                    style: _pjs(
                        size: 13,
                        color: Colors.white,
                        weight: FontWeight.w600)),
                backgroundColor: _kPrimary,
                duration: const Duration(seconds: 2),
              ),
            ),
            child: Container(
              padding: const EdgeInsets.symmetric(vertical: 12),
              alignment: Alignment.center,
              decoration: BoxDecoration(
                border: Border.all(color: _kBorder),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Text('See All 438 Reviews',
                  style: _pjs(
                      size: 13, weight: FontWeight.w700, color: _kSecondary)),
            ),
          ),
        ],
      ),
    );
  }

  // ── Fixed Bottom Bar ───────────────────────────────────────────────────────
  Widget _buildBottomBar() {
    return Container(
      padding: const EdgeInsets.fromLTRB(16, 10, 16, 20),
      decoration: const BoxDecoration(
        color: _kCard,
        boxShadow: [
          BoxShadow(
              color: Color(0x1A34175B), blurRadius: 14, offset: Offset(0, -4))
        ],
      ),
      child: SafeArea(
        child: Row(
          children: [
            _BottomIcon(
              icon: Icons.storefront_outlined,
              label: 'Shop',
              onTap: () => Navigator.pop(context),
            ),
            const SizedBox(width: 6),
            _BottomIcon(
              icon: Icons.chat_bubble_outline,
              label: 'Chat',
              onTap: () => ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('Opening seller chat...',
                      style: _pjs(
                          size: 13,
                          color: Colors.white,
                          weight: FontWeight.w600)),
                  backgroundColor: _kPrimary,
                  duration: const Duration(seconds: 2),
                ),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: ScaleTap(
                onTap: () => _openVariantSheet(buyNow: false),
                child: OutlinedButton(
                  onPressed: () => _openVariantSheet(buyNow: false),
                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(color: _kPrimary, width: 1.5),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12)),
                    minimumSize: const Size(0, 52),
                  ),
                  child: Text('Add to Cart',
                      style: _pjs(
                          size: 14, weight: FontWeight.w700, color: _kPrimary)),
                ),
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: ScaleTap(
                onTap: () => _openVariantSheet(buyNow: true),
                child: ElevatedButton(
                  onPressed: () => _openVariantSheet(buyNow: true),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _kPrimary,
                    foregroundColor: Colors.white,
                    elevation: 0,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12)),
                    minimumSize: const Size(0, 52),
                  ),
                  child: Text('Buy Now',
                      style: _pjs(
                          size: 14,
                          weight: FontWeight.w700,
                          color: Colors.white)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ── Variant Bottom Sheet ──────────────────────────────────────────────────────
class _VariantSheet extends StatefulWidget {
  const _VariantSheet({
    required this.product,
    required this.buyNow,
    required this.onConfirm,
  });
  final Product product;
  final bool buyNow;
  // Returns selected variantId (null for no-variant products) and quantity
  final void Function(String? variantId, int qty) onConfirm;

  @override
  State<_VariantSheet> createState() => _VariantSheetState();
}

class _VariantSheetState extends State<_VariantSheet> {
  // Selected variant index (null = no variant selected yet)
  int? _selectedVariantIndex;
  int _qty = 1;
  bool _added = false;

  Product get _p => widget.product;

  // ── Variant helpers ───────────────────────────────────────────────────────

  bool get _hasVariants =>
      _p.hasVariants && _p.variants != null && (_p.variants as List).isNotEmpty;

  List<Map<String, dynamic>> get _variants => _hasVariants
      ? (_p.variants as List)
          .map((v) => Map<String, dynamic>.from(v as Map))
          .toList()
      : [];

  List<Map<String, dynamic>> get _variantOptions => _p.variantOptions != null
      ? (_p.variantOptions as List)
          .map((o) => Map<String, dynamic>.from(o as Map))
          .toList()
      : [];

  Map<String, dynamic>? get _selectedVariant =>
      (_selectedVariantIndex != null &&
              _selectedVariantIndex! < _variants.length)
          ? _variants[_selectedVariantIndex!]
          : null;

  String? get _selectedVariantId => _selectedVariant?['id'] as String?;

  /// Stock for the currently selected variant, or product stock if no variants.
  int get _maxQty {
    if (_hasVariants && _selectedVariant != null) {
      return (_selectedVariant!['stock'] as num?)?.toInt() ?? 0;
    }
    return _p.stock;
  }

  /// Display price for the current selection.
  double get _displayPrice {
    if (_hasVariants && _selectedVariant != null) {
      final vPrice = (_selectedVariant!['price'] as num?)?.toDouble();
      if (vPrice != null && vPrice > 0) return vPrice;
    }
    return _p.effectivePrice;
  }

  /// Display image URL: variant image if available, else product main image.
  String? get _displayImageUrl {
    if (_hasVariants && _selectedVariantId != null) {
      final hasImg = _selectedVariant?['image'] != null &&
          (_selectedVariant!['image'] as String).isNotEmpty;
      if (hasImg) {
        // Use the variant-image endpoint (same base as product image endpoint)
        return 'http://127.0.0.1:8000/api/buyer/products/${_p.id}/variant-image/$_selectedVariantId';
      }
    }
    return _p.imageUrl.isNotEmpty ? _p.imageUrl : null;
  }

  // ── Stock message shown next to qty stepper ───────────────────────────────

  String? _stockMsg; // shown temporarily when user hits max

  void _incrementQty() {
    if (_qty < _maxQty) {
      setState(() {
        _qty++;
        _stockMsg = null;
      });
    } else {
      setState(() => _stockMsg = 'Only $_maxQty in stock');
    }
  }

  void _decrementQty() {
    if (_qty > 1) {
      setState(() {
        _qty--;
        _stockMsg = null;
      });
    }
  }

  void _selectVariant(int index) {
    setState(() {
      _selectedVariantIndex = index;
      // Reset qty if it now exceeds the new variant's stock
      final newMax = (_variants[index]['stock'] as num?)?.toInt() ?? 0;
      if (_qty > newMax) _qty = newMax.clamp(1, 999);
      _stockMsg = null;
    });
  }

  @override
  void initState() {
    super.initState();
    // Pre-select first in-stock variant, or first variant if none in stock
    if (_hasVariants) {
      final firstInStock =
          _variants.indexWhere((v) => ((v['stock'] as num?)?.toInt() ?? 0) > 0);
      _selectedVariantIndex = firstInStock >= 0 ? firstInStock : 0;
    }
  }

  // ── Build ─────────────────────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    final imageUrl = _displayImageUrl;
    final stockCount = _maxQty;
    final variantSelected = !_hasVariants || _selectedVariant != null;

    return Padding(
      padding:
          EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
      child: Container(
        padding: const EdgeInsets.fromLTRB(20, 6, 20, 32),
        decoration: const BoxDecoration(
          color: _kCard,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Handle
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  margin: const EdgeInsets.only(bottom: 16, top: 8),
                  decoration: BoxDecoration(
                      color: _kBorder,
                      borderRadius: BorderRadius.circular(9999)),
                ),
              ),
              // ── Product summary header ─────────────────────────────────
              Row(
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(10),
                    child: imageUrl != null
                        ? Image.network(
                            imageUrl,
                            width: 68,
                            height: 68,
                            fit: BoxFit.cover,
                            headers: const {'Accept': 'image/*'},
                            errorBuilder: (_, __, ___) => Container(
                              width: 68,
                              height: 68,
                              color: _kChip,
                              child:
                                  Icon(Icons.image_outlined, color: _kSubtext),
                            ),
                          )
                        : Container(
                            width: 68,
                            height: 68,
                            color: _kChip,
                            child: Icon(Icons.storefront_outlined,
                                color: _kSubtext),
                          ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('₱${_displayPrice.toStringAsFixed(0)}',
                            style: _pjs(
                                size: 22,
                                weight: FontWeight.w800,
                                color: _kPrimary,
                                letterSpacing: -0.02)),
                        if (_p.isOnSale)
                          Text('₱${_p.price.toStringAsFixed(0)}',
                              style: _pjs(
                                  size: 13,
                                  color: _kSubtext,
                                  decoration: TextDecoration.lineThrough)),
                        Text(
                          stockCount > 0
                              ? 'Stock: $stockCount left'
                              : 'Out of stock',
                          style: _pjs(
                              size: 11,
                              color: stockCount > 0 ? _kSubtext : _kFlash),
                        ),
                      ],
                    ),
                  ),
                  GestureDetector(
                    onTap: () => Navigator.pop(context),
                    child: Container(
                      width: 32,
                      height: 32,
                      decoration: BoxDecoration(
                          color: _kSurface, shape: BoxShape.circle),
                      child: Icon(Icons.close, size: 16, color: _kSubtext),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              Text(
                _hasVariants && _selectedVariant != null
                    ? 'Selected: ${(_selectedVariant!['combination'] as Map?)?.values.join(', ') ?? ''}'
                    : _p.name,
                style: _pjs(size: 11, color: _kSubtext),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              const Divider(color: _kBorder, height: 24),

              // ── Variant option groups ──────────────────────────────────
              if (_hasVariants) ...[
                for (final optGroup in _variantOptions) ...[
                  Row(
                    children: [
                      Text(optGroup['name'] as String? ?? 'Option',
                          style: _pjs(size: 13, weight: FontWeight.w700)),
                      const Spacer(),
                    ],
                  ),
                  const SizedBox(height: 10),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: _variants.asMap().entries.map((entry) {
                      final i = entry.key;
                      final v = entry.value;
                      final combo = v['combination'] as Map?;
                      final label = combo != null
                          ? combo.values.join(' / ')
                          : 'Option ${i + 1}';
                      final vStock = (v['stock'] as num?)?.toInt() ?? 0;
                      final sel = _selectedVariantIndex == i;
                      final oos = vStock == 0;
                      return GestureDetector(
                        onTap: oos ? null : () => _selectVariant(i),
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 14, vertical: 9),
                          decoration: BoxDecoration(
                            color: oos
                                ? _kSurface
                                : sel
                                    ? _kChip
                                    : _kSurface,
                            borderRadius: BorderRadius.circular(9999),
                            border: Border.all(
                              color: oos
                                  ? _kBorder
                                  : sel
                                      ? _kSecondary
                                      : _kBorder,
                              width: sel ? 2 : 1,
                            ),
                          ),
                          child: Text(
                            label,
                            style: _pjs(
                                size: 11,
                                weight: FontWeight.w600,
                                color: oos
                                    ? _kSubtext
                                    : sel
                                        ? _kSecondary
                                        : _kText),
                          ),
                        ),
                      );
                    }).toList(),
                  ),
                  const SizedBox(height: 16),
                ],
              ],

              // ── Quantity ───────────────────────────────────────────────
              Row(
                children: [
                  Text('Quantity',
                      style: _pjs(size: 13, weight: FontWeight.w700)),
                  if (_stockMsg != null) ...[
                    const SizedBox(width: 8),
                    Text(_stockMsg!,
                        style: _pjs(
                            size: 10, color: _kFlash, weight: FontWeight.w600)),
                  ],
                  const Spacer(),
                  _QtyBtn(
                    icon: Icons.remove,
                    onTap: _decrementQty,
                  ),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 18),
                    child: Text('$_qty',
                        style: _pjs(size: 16, weight: FontWeight.w800)),
                  ),
                  _QtyBtn(
                    icon: Icons.add,
                    onTap: stockCount > 0 ? _incrementQty : () {},
                  ),
                ],
              ),
              const SizedBox(height: 20),

              // ── CTA button ─────────────────────────────────────────────
              if (_added)
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  decoration: BoxDecoration(
                    color: _kGreen.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.check_circle, color: _kGreen, size: 18),
                      const SizedBox(width: 8),
                      Text('Added to cart successfully!',
                          style: _pjs(
                              size: 14,
                              weight: FontWeight.w700,
                              color: _kGreen)),
                    ],
                  ),
                )
              else
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: (stockCount == 0 || !variantSelected)
                        ? null
                        : () async {
                            if (!widget.buyNow) {
                              setState(() => _added = true);
                              await Future.delayed(
                                  const Duration(milliseconds: 700));
                            }
                            if (!mounted) return;
                            // Capture context before any potential unmount
                            final nav = Navigator.of(context);
                            final varId = _selectedVariantId;
                            final qty = _qty;
                            nav.pop();
                            widget.onConfirm(varId, qty);
                          },
                    icon: Icon(
                      widget.buyNow ? Icons.arrow_forward : Icons.shopping_cart,
                      size: 18,
                      color: Colors.white,
                    ),
                    label: Text(
                      widget.buyNow ? 'Buy Now' : 'Confirm Add to Cart',
                      style: _pjs(
                          size: 15,
                          weight: FontWeight.w700,
                          color: Colors.white),
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: _kPrimary,
                      elevation: 0,
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12)),
                      minimumSize: const Size(0, 52),
                    ),
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }
}

// ── Helper widgets ────────────────────────────────────────────────────────────

class _Badge extends StatelessWidget {
  const _Badge({required this.label, required this.color});
  final String label;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration:
          BoxDecoration(color: color, borderRadius: BorderRadius.circular(5)),
      child: Text(label,
          style: _pjs(size: 9, weight: FontWeight.w800, color: Colors.white)),
    );
  }
}

class _SmallBadge extends StatelessWidget {
  const _SmallBadge(
      {required this.label, required this.icon, required this.color});
  final String label;
  final IconData icon;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.10),
        borderRadius: BorderRadius.circular(5),
        border: Border.all(color: color.withValues(alpha: 0.25)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 10, color: color),
          const SizedBox(width: 3),
          Text(label,
              style: _pjs(size: 10, weight: FontWeight.w700, color: color)),
        ],
      ),
    );
  }
}

class _CarouselBtn extends StatelessWidget {
  const _CarouselBtn(
      {required this.icon, required this.color, required this.onTap});
  final IconData icon;
  final Color color;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 34,
        height: 34,
        decoration: BoxDecoration(
          color: Colors.white.withValues(alpha: 0.9),
          shape: BoxShape.circle,
          boxShadow: const [
            BoxShadow(
                color: Color(0x1A000000), blurRadius: 6, offset: Offset(0, 2))
          ],
        ),
        child: Icon(icon, size: 17, color: color),
      ),
    );
  }
}

// ignore: unused_element
class _CountBlock extends StatelessWidget {
  const _CountBlock(this.value);
  final String value;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 28,
      height: 26,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(4),
      ),
      alignment: Alignment.center,
      child: Text(value,
          style: _pjs(size: 13, weight: FontWeight.w800, color: _kPrimary)),
    );
  }
}

// ignore: unused_element
class _ColonSep extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 3),
      child: Text(':',
          style: _pjs(size: 13, weight: FontWeight.w800, color: _kFlash)),
    );
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow(
      {required this.icon, required this.iconColor, required this.child});
  final IconData icon;
  final Color iconColor;
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 16, color: iconColor),
        const SizedBox(width: 8),
        Expanded(child: child),
        Icon(Icons.chevron_right, size: 16, color: _kSubtext),
      ],
    );
  }
}

class _TrustBadge extends StatelessWidget {
  const _TrustBadge({required this.icon, required this.label});
  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 8),
        decoration: BoxDecoration(
          color: _kChip,
          borderRadius: BorderRadius.circular(8),
        ),
        child: Column(
          children: [
            Icon(icon, size: 16, color: _kSecondary),
            const SizedBox(height: 3),
            Text(label,
                textAlign: TextAlign.center,
                style:
                    _pjs(size: 9, weight: FontWeight.w700, color: _kSecondary)),
          ],
        ),
      ),
    );
  }
}

class _SellerStat extends StatelessWidget {
  const _SellerStat({required this.value, required this.label});
  final String value, label;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Column(
        children: [
          Text(value,
              style: _pjs(size: 15, weight: FontWeight.w800, color: _kPrimary)),
          Text(label,
              style: _pjs(size: 10, color: _kSubtext),
              textAlign: TextAlign.center),
        ],
      ),
    );
  }
}

class _QtyBtn extends StatelessWidget {
  const _QtyBtn({required this.icon, required this.onTap});
  final IconData icon;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 34,
        height: 34,
        decoration: BoxDecoration(
          border: Border.all(color: _kBorder),
          borderRadius: BorderRadius.circular(8),
          color: _kChip,
        ),
        child: Icon(icon, size: 15, color: _kPrimary),
      ),
    );
  }
}

class _BottomIcon extends StatelessWidget {
  const _BottomIcon(
      {required this.icon, required this.label, required this.onTap});
  final IconData icon;
  final String label;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 46,
        height: 52,
        decoration: BoxDecoration(
          border: Border.all(color: _kBorder),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 18, color: _kPrimary),
            const SizedBox(height: 2),
            Text(label,
                style:
                    _pjs(size: 9, weight: FontWeight.w600, color: _kPrimary)),
          ],
        ),
      ),
    );
  }
}

class _ReviewCard extends StatelessWidget {
  const _ReviewCard({
    required this.initials,
    required this.name,
    required this.verified,
    required this.timeAgo,
    required this.variant,
    required this.review,
    required this.imageSeeds,
  });
  final String initials, name, timeAgo, variant, review;
  final bool verified;
  final List<String> imageSeeds;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: _kCard,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: _kBorder),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              CircleAvatar(
                radius: 16,
                backgroundColor: _kChip,
                child: Text(initials,
                    style: _pjs(
                        size: 12, weight: FontWeight.w800, color: _kPrimary)),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Text(name,
                            style: _pjs(size: 13, weight: FontWeight.w700)),
                        if (verified) ...const [
                          SizedBox(width: 4),
                          Icon(Icons.verified, size: 12, color: _kGreen),
                        ],
                      ],
                    ),
                    Text(variant, style: _pjs(size: 10, color: _kSubtext)),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Row(
                    children: List.generate(
                        5,
                        (i) => Icon(Icons.star_rounded,
                            size: 12, color: Colors.amber.shade500)),
                  ),
                  Text(timeAgo, style: _pjs(size: 10, color: _kSubtext)),
                ],
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(review, style: _pjs(size: 12, color: _kText, height: 1.5)),
          if (imageSeeds.isNotEmpty) ...[
            const SizedBox(height: 8),
            Row(
              children: imageSeeds
                  .map((s) => Padding(
                        padding: const EdgeInsets.only(right: 8),
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(6),
                          child: Image.network(
                            'https://picsum.photos/seed/$s/80/80',
                            width: 56,
                            height: 56,
                            fit: BoxFit.cover,
                            errorBuilder: (_, __, ___) => Container(
                              width: 56,
                              height: 56,
                              color: _kChip,
                              child:
                                  Icon(Icons.image_outlined, color: _kSubtext),
                            ),
                          ),
                        ),
                      ))
                  .toList(),
            ),
          ],
        ],
      ),
    );
  }
}

// ── Fit Guide Dialog ──────────────────────────────────────────────────────────
class _FitGuideDialog extends StatelessWidget {
  const _FitGuideDialog();

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      title: Text('Size & Fit Guide',
          style: _pjs(size: 17, weight: FontWeight.w800)),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Table(
            border: TableBorder.all(
                color: _kBorder, borderRadius: BorderRadius.circular(6)),
            columnWidths: const {
              0: FlexColumnWidth(1),
              1: FlexColumnWidth(1.5),
              2: FlexColumnWidth(1.5),
              3: FlexColumnWidth(1.5),
            },
            children: [
              _tableRow(['Size', 'Chest', 'Length', 'Sleeve'], isHeader: true),
              _tableRow(['S', '120 cm', '69 cm', '59 cm']),
              _tableRow(['M', '126 cm', '72 cm', '61 cm']),
              _tableRow(['L', '132 cm', '74 cm', '63 cm']),
              _tableRow(['XL', '138 cm', '76 cm', '64 cm']),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            'This hoodie has an oversized fit. For a more fitted look, size down one size.',
            style: _pjs(size: 12, color: _kSubtext, height: 1.5),
          ),
        ],
      ),
      actions: [
        FilledButton(
          onPressed: () => Navigator.pop(context),
          style: FilledButton.styleFrom(
            backgroundColor: _kPrimary,
            shape:
                RoundedRectangleBorder(borderRadius: BorderRadius.circular(9)),
          ),
          child: Text('Got it',
              style:
                  _pjs(size: 14, weight: FontWeight.w700, color: Colors.white)),
        ),
      ],
    );
  }

  TableRow _tableRow(List<String> cells, {bool isHeader = false}) {
    return TableRow(
      decoration:
          isHeader ? const BoxDecoration(color: Color(0xFFEFE9FD)) : null,
      children: cells
          .map((cell) => Padding(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
                child: Text(cell,
                    style: _pjs(
                        size: 11,
                        weight: isHeader ? FontWeight.w700 : FontWeight.w400)),
              ))
          .toList(),
    );
  }
}
