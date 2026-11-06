import 'dart:async';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../home/product_detail_page.dart';
import '../../models/product.dart';
import 'flash_deals_screen.dart';
import 'category_product_list_screen.dart';

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
const _kOrange = Color(0xFFFF6B00);
const _kGreen = Color(0xFF1DB966);

TextStyle _pjs({
  double size = 14,
  FontWeight weight = FontWeight.w400,
  Color color = _kText,
  double? height,
  TextDecoration? decoration,
}) =>
    GoogleFonts.plusJakartaSans(
      fontSize: size,
      fontWeight: weight,
      color: color,
      height: height,
      decoration: decoration,
    );

// ── Data models ───────────────────────────────────────────────────────────────
class _SellerCategory {
  const _SellerCategory({
    required this.id,
    required this.name,
    required this.count,
    required this.imageSeed,
  });
  final String id;
  final String name;
  final String imageSeed;
  final int count;
}

class StoreProduct {
  const StoreProduct({
    required this.id,
    required this.name,
    required this.price,
    required this.originalPrice,
    required this.imageSeed,
    required this.rating,
    required this.sold,
    required this.discount,
    this.isFulfilled = false,
    this.badge = '',
  });
  final String id;
  final String name;
  final double price;
  final double originalPrice;
  final String imageSeed;
  final double rating;
  final String sold;
  final int discount;
  final bool isFulfilled;
  final String badge;
}

class StoreReview {
  const StoreReview({
    required this.reviewer,
    required this.rating,
    required this.comment,
    required this.date,
    required this.product,
    this.avatarSeed = 'user1',
    this.imageSeed = '',
    this.hasVideo = false,
    this.productBadges = const [],
  });
  final String reviewer;
  final int rating;
  final String comment;
  final String date;
  final String product;
  final String avatarSeed;
  final String imageSeed;
  final bool hasVideo;
  final List<String> productBadges;
}

class SellerInfo {
  const SellerInfo({
    required this.id,
    required this.name,
    required this.badge,
    required this.tagline,
    required this.avatarSeed,
    required this.bannerSeed,
    required this.rating,
    required this.followers,
    required this.chatResponse,
    required this.positiveRating,
    required this.totalProducts,
    required this.joinedDate,
    required this.categories,
    required this.products,
    required this.reviews,
    required this.sellerCategories,
    this.isFollowed = false,
  });

  final String id;
  final String name;
  final String badge;
  final String tagline;
  final String avatarSeed;
  final String bannerSeed;
  final double rating;
  final String followers;
  final String chatResponse;
  final String positiveRating;
  final int totalProducts;
  final String joinedDate;
  final List<String> categories;
  final List<StoreProduct> products;
  final List<StoreReview> reviews;
  final List<_SellerCategory> sellerCategories;
  final bool isFollowed;
}

// ── Sample data ───────────────────────────────────────────────────────────────
final Map<String, SellerInfo> _sellerData = {
  'geeso-official': SellerInfo(
    id: 'geeso-official',
    name: 'GEESO',
    badge: 'PREFERRED',
    tagline: '182.5K Followers • 1.5K Videos',
    avatarSeed: 'geeso-avatar',
    bannerSeed: 'geeso-banner',
    rating: 4.8,
    followers: '182.5K',
    chatResponse: '< 1hr',
    positiveRating: '98%',
    totalProducts: 366,
    joinedDate: 'Jan 2020',
    categories: ['All', 'Hardware', 'Home', 'Kitchen', 'Sports'],
    sellerCategories: [
      _SellerCategory(
          id: 'hardware',
          name: 'Hardware & Tools',
          count: 27,
          imageSeed: 'hardware'),
      _SellerCategory(
          id: 'home', name: 'Home & Living', count: 169, imageSeed: 'home'),
      _SellerCategory(
          id: 'kitchen',
          name: 'Kitchen & Bathroom',
          count: 73,
          imageSeed: 'kitchen'),
      _SellerCategory(
          id: 'toys', name: 'Toys & Stationery', count: 11, imageSeed: 'toys'),
      _SellerCategory(
          id: 'beauty',
          name: 'Beauty & Personal Care',
          count: 28,
          imageSeed: 'beauty'),
      _SellerCategory(
          id: '3c', name: '3C Digital Accessories', count: 37, imageSeed: '3c'),
      _SellerCategory(
          id: 'massage',
          name: 'Massage & Health',
          count: 5,
          imageSeed: 'massage'),
      _SellerCategory(
          id: 'sports',
          name: 'Sports & Outdoor',
          count: 52,
          imageSeed: 'sports'),
    ],
    reviews: [
      StoreReview(
        reviewer: 'Abby R.',
        rating: 5,
        comment:
            'Super fast shipping and legit products. Will buy again! The quality is amazing and packaging is perfect.',
        date: 'Oct 18, 2026',
        product: 'Soft Makeup Brush Set',
        avatarSeed: 'reviewer1',
        imageSeed: 'brushset',
        hasVideo: false,
        productBadges: ['SULIT', 'ADD-ON'],
      ),
      StoreReview(
        reviewer: 'Claire M.',
        rating: 5,
        comment:
            'Packaged really well. Exactly as described. Great value for money!',
        date: 'Oct 12, 2026',
        product: '2Pcs Blind Spot Mirrors',
        avatarSeed: 'reviewer2',
        imageSeed: 'mirror1',
        hasVideo: true,
        productBadges: ['DEAL'],
      ),
      StoreReview(
        reviewer: 'Dana P.',
        rating: 4,
        comment:
            'Good quality but arrived a day late. Still happy with purchase overall.',
        date: 'Oct 5, 2026',
        product: 'GEESO 10/7/5 Pcs Brush Set',
        avatarSeed: 'reviewer3',
        imageSeed: 'brushset2',
        hasVideo: false,
        productBadges: ['ADD-ON', 'DEAL'],
      ),
      StoreReview(
        reviewer: 'Marco T.',
        rating: 5,
        comment:
            'Best product from GEESO. Very true to description and fast delivery.',
        date: 'Oct 3, 2026',
        product: 'HD Setting Powder',
        avatarSeed: 'reviewer4',
        imageSeed: 'settingpowder',
        hasVideo: true,
        productBadges: ['SULIT'],
      ),
    ],
    products: [
      StoreProduct(
        id: 'p1',
        name: 'GEESO 10/7/5 Pcs Soft Makeup Brush Set',
        price: 68,
        originalPrice: 195,
        imageSeed: 'brushset',
        rating: 4.9,
        sold: '3.2k sold',
        discount: 65,
        isFulfilled: true,
        badge: 'FLASH',
      ),
      StoreProduct(
        id: 'p2',
        name: 'GEESO 2Pcs Blind Spot Mirrors 2" HD Glass',
        price: 38,
        originalPrice: 111,
        imageSeed: 'mirror1',
        rating: 4.8,
        sold: '1.8k sold',
        discount: 66,
        isFulfilled: true,
        badge: 'FLASH',
      ),
      StoreProduct(
        id: 'p3',
        name: 'Velvet Matte Long-lasting Moisture Lip Tint',
        price: 129,
        originalPrice: 199,
        imageSeed: 'liptint',
        rating: 4.9,
        sold: '1.2k sold',
        discount: 35,
      ),
      StoreProduct(
        id: 'p4',
        name: '2-in-1 Waterproof Precision Liquid Eyeliner',
        price: 89,
        originalPrice: 145,
        imageSeed: 'eyeliner',
        rating: 4.7,
        sold: '856 sold',
        discount: 38,
      ),
      StoreProduct(
        id: 'p5',
        name: 'HD Setting Powder Translucent Matte Finish',
        price: 175,
        originalPrice: 299,
        imageSeed: 'settingpowder',
        rating: 4.8,
        sold: '2.1k sold',
        discount: 41,
      ),
      StoreProduct(
        id: 'p6',
        name: 'Hydrating Glow Serum with Niacinamide',
        price: 249,
        originalPrice: 420,
        imageSeed: 'serum1',
        rating: 4.9,
        sold: '980 sold',
        discount: 40,
      ),
    ],
  ),
  'mimoo-official': SellerInfo(
    id: 'mimoo-official',
    name: 'mimoo Official Apparel',
    badge: 'MALL',
    tagline: 'Flagship Streetwear Partner',
    avatarSeed: 'mimoo-avatar',
    bannerSeed: 'mimoo-banner',
    rating: 4.9,
    followers: '45.2K',
    chatResponse: '< 1hr',
    positiveRating: '99%',
    totalProducts: 134,
    joinedDate: 'Mar 2021',
    categories: ['All', 'Hoodies', 'Tees', 'Accessories', 'Limited'],
    sellerCategories: [
      _SellerCategory(
          id: 'hoodies', name: 'Hoodies', count: 42, imageSeed: 'hoodie1'),
      _SellerCategory(
          id: 'tees', name: 'Graphic Tees', count: 55, imageSeed: 'tee1'),
      _SellerCategory(
          id: 'bottoms', name: 'Bottoms', count: 28, imageSeed: 'pants1'),
      _SellerCategory(
          id: 'accessories',
          name: 'Accessories',
          count: 9,
          imageSeed: 'accessories'),
    ],
    reviews: [
      StoreReview(
        reviewer: 'Marco T.',
        rating: 5,
        comment:
            'Best quality hoodie I\'ve ever bought online. Very true to size.',
        date: 'Oct 20, 2026',
        product: 'Acid-Wash Vintage Hoodie',
        avatarSeed: 'reviewer4',
        imageSeed: 'hoodie1',
        hasVideo: false,
        productBadges: ['SULIT'],
      ),
      StoreReview(
        reviewer: 'Jana L.',
        rating: 5,
        comment: 'Arrived in 2 days. Packaging is premium. Legit seller!',
        date: 'Oct 14, 2026',
        product: 'Oversized Drop-shoulder Tee',
        avatarSeed: 'reviewer5',
        imageSeed: 'tee1',
        hasVideo: true,
        productBadges: ['ADD-ON', 'DEAL'],
      ),
    ],
    products: [
      StoreProduct(
        id: 'm1',
        name: 'mimoo Studio Acid-Wash Heavyweight Vintage Hoodie',
        price: 849,
        originalPrice: 1540,
        imageSeed: 'hoodie1',
        rating: 4.9,
        sold: '1.2k sold',
        discount: 45,
        badge: 'FLASH',
      ),
      StoreProduct(
        id: 'm2',
        name: 'mimoo Oversized Drop-shoulder Graphic Tee',
        price: 459,
        originalPrice: 799,
        imageSeed: 'tee1',
        rating: 4.8,
        sold: '890 sold',
        discount: 43,
      ),
      StoreProduct(
        id: 'm3',
        name: 'mimoo Cargo Wide-leg Trousers',
        price: 699,
        originalPrice: 1100,
        imageSeed: 'pants1',
        rating: 4.7,
        sold: '620 sold',
        discount: 36,
      ),
      StoreProduct(
        id: 'm4',
        name: 'mimoo Fleece-lined Zip Jacket',
        price: 1099,
        originalPrice: 1799,
        imageSeed: 'jacket1',
        rating: 4.9,
        sold: '445 sold',
        discount: 38,
      ),
    ],
  ),
};

SellerInfo _fallbackSeller(String sellerId, String sellerName) {
  return SellerInfo(
    id: sellerId,
    name: sellerName.isNotEmpty ? sellerName : 'Store',
    badge: '',
    tagline: 'Official Store',
    avatarSeed: sellerId,
    bannerSeed: '$sellerId-banner',
    rating: 4.5,
    followers: '10K',
    chatResponse: '< 2hr',
    positiveRating: '95%',
    totalProducts: 50,
    joinedDate: '2022',
    categories: ['All', 'Products'],
    sellerCategories: [
      _SellerCategory(
          id: 'all', name: 'All Products', count: 50, imageSeed: sellerId),
    ],
    reviews: [],
    products: [
      StoreProduct(
        id: 'f1',
        name: 'Featured Product',
        price: 299,
        originalPrice: 499,
        imageSeed: sellerId,
        rating: 4.5,
        sold: '100+ sold',
        discount: 40,
      ),
    ],
  );
}

// ── Helper: build product from store product ──────────────────────────────────
Product storeProductToProduct(StoreProduct p) => Product(
      id: p.id.hashCode,
      name: p.name,
      price: p.price,
      imageUrl: 'https://picsum.photos/seed/${p.imageSeed}/300/300',
      category: 'Store',
      rating: p.rating,
      reviewCount: 0,
    );

// ═════════════════════════════════════════════════════════════════════════════
// SELLER STORE PAGE
// ═════════════════════════════════════════════════════════════════════════════
class SellerStorePage extends StatefulWidget {
  const SellerStorePage({
    super.key,
    required this.sellerId,
    this.sellerName = '',
  });

  final String sellerId;
  final String sellerName;

  @override
  State<SellerStorePage> createState() => _SellerStorePageState();
}

class _SellerStorePageState extends State<SellerStorePage>
    with SingleTickerProviderStateMixin {
  late TabController _tabCtrl;
  late SellerInfo _seller;
  bool _isFollowed = false;

  int _saleSeconds = 1 * 3600 + 26 * 60 + 45;
  Timer? _timer;

  static const _tabs = ['Shop', 'Products', 'Reviews', 'Categories'];

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: _tabs.length, vsync: this);
    _seller = _sellerData[widget.sellerId] ??
        _fallbackSeller(widget.sellerId, widget.sellerName);
    _isFollowed = _seller.isFollowed;

    _timer = Timer.periodic(const Duration(seconds: 1), (_) {
      if (_saleSeconds > 0) {
        setState(() => _saleSeconds--);
      } else {
        _timer?.cancel();
      }
    });
  }

  @override
  void dispose() {
    _tabCtrl.dispose();
    _timer?.cancel();
    super.dispose();
  }

  String get _countdown {
    final h = _saleSeconds ~/ 3600;
    final m = (_saleSeconds % 3600) ~/ 60;
    final s = _saleSeconds % 60;
    return '${h.toString().padLeft(2, '0')}:${m.toString().padLeft(2, '0')}:${s.toString().padLeft(2, '0')}';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kSurface,
      appBar: _buildAppBar(),
      body: Column(
        children: [
          // Non-scrolling: profile header
          _buildProfileHeader(),
          // Non-scrolling: chat button
          _buildChatButton(),
          // Non-scrolling: tab bar
          _buildTabBar(),
          // Scrolling: tab content
          Expanded(
            child: TabBarView(
              controller: _tabCtrl,
              children: [
                _ShopTab(seller: _seller, countdown: _countdown),
                _ProductsTab(seller: _seller),
                _ReviewsTab(seller: _seller),
                _CategoriesTab(seller: _seller),
              ],
            ),
          ),
        ],
      ),
    );
  }

  PreferredSizeWidget _buildAppBar() {
    return AppBar(
      backgroundColor: _kCard,
      elevation: 0,
      leading: IconButton(
        icon: const Icon(Icons.arrow_back, color: _kText),
        onPressed: () => Navigator.pop(context),
      ),
      titleSpacing: 0,
      title: Container(
        height: 36,
        margin: const EdgeInsets.symmetric(horizontal: 4),
        decoration: BoxDecoration(
          color: _kSurface,
          borderRadius: BorderRadius.circular(9999),
          border: Border.all(color: _kBorder),
        ),
        child: Row(
          children: [
            const SizedBox(width: 10),
            const Icon(Icons.search, size: 15, color: _kSubtext),
            const SizedBox(width: 6),
            Text('Search in shop...', style: _pjs(size: 12, color: _kSubtext)),
          ],
        ),
      ),
      actions: [
        IconButton(
          icon: const Icon(Icons.more_vert, color: _kText),
          onPressed: () {},
        ),
        Padding(
          padding: const EdgeInsets.only(right: 10),
          child: CircleAvatar(
            radius: 16,
            backgroundColor: _kPrimary,
            child: const Icon(Icons.person, size: 17, color: Colors.white),
          ),
        ),
      ],
    );
  }

  Widget _buildProfileHeader() {
    return Container(
      color: _kCard,
      padding: const EdgeInsets.fromLTRB(14, 12, 14, 0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          // Avatar
          Container(
            width: 52,
            height: 52,
            decoration: BoxDecoration(
              color: _kChip,
              shape: BoxShape.circle,
              border: Border.all(color: _kBorder, width: 1.5),
            ),
            child: ClipOval(
              child: Image.network(
                'https://picsum.photos/seed/${_seller.avatarSeed}/100/100',
                fit: BoxFit.cover,
                errorBuilder: (_, __, ___) => const Icon(
                    Icons.storefront_outlined,
                    size: 26,
                    color: _kPrimary),
              ),
            ),
          ),
          const SizedBox(width: 10),
          // Name + badge + stats
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Flexible(
                      child: Text(
                        _seller.name,
                        style: _pjs(size: 15, weight: FontWeight.w800),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    if (_seller.badge.isNotEmpty) ...[
                      const SizedBox(width: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 7, vertical: 2),
                        decoration: BoxDecoration(
                          color: _kChip,
                          borderRadius: BorderRadius.circular(5),
                          border: Border.all(color: _kSecondary),
                        ),
                        child: Text(
                          _seller.badge,
                          style: _pjs(
                              size: 10,
                              weight: FontWeight.w700,
                              color: _kSecondary),
                        ),
                      ),
                    ],
                  ],
                ),
                const SizedBox(height: 3),
                Row(
                  children: [
                    Icon(Icons.star_rounded,
                        size: 13, color: Colors.amber.shade500),
                    const SizedBox(width: 3),
                    Text(
                      '${_seller.rating}',
                      style: _pjs(size: 12, weight: FontWeight.w700),
                    ),
                    Flexible(
                      child: Text(
                        '  •  ${_seller.tagline}',
                        style: _pjs(size: 11, color: _kSubtext),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          // Follow button
          ElevatedButton(
            onPressed: () => setState(() => _isFollowed = !_isFollowed),
            style: ElevatedButton.styleFrom(
              backgroundColor: _isFollowed ? _kSurface : _kPrimary,
              foregroundColor: _isFollowed ? _kPrimary : Colors.white,
              elevation: 0,
              side: BorderSide(color: _isFollowed ? _kBorder : _kPrimary),
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(9999)),
              minimumSize: const Size(0, 34),
              padding: const EdgeInsets.symmetric(horizontal: 18),
            ),
            child: Text(
              _isFollowed ? 'Following' : 'Follow',
              style: _pjs(
                size: 13,
                weight: FontWeight.w700,
                color: _isFollowed ? _kPrimary : Colors.white,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildChatButton() {
    return Container(
      color: _kCard,
      padding: const EdgeInsets.fromLTRB(14, 10, 14, 10),
      child: SizedBox(
        width: double.infinity,
        height: 42,
        child: ElevatedButton.icon(
          onPressed: () {},
          icon: const Icon(Icons.chat_bubble_outline,
              size: 16, color: Colors.white),
          label: Text(
            'Chat with Seller',
            style: _pjs(size: 14, weight: FontWeight.w700, color: Colors.white),
          ),
          style: ElevatedButton.styleFrom(
            backgroundColor: _kPrimary,
            elevation: 0,
            shape:
                RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          ),
        ),
      ),
    );
  }

  Widget _buildTabBar() {
    return Container(
      decoration: const BoxDecoration(
        color: _kCard,
        border: Border(bottom: BorderSide(color: _kBorder)),
      ),
      child: TabBar(
        controller: _tabCtrl,
        labelColor: _kPrimary,
        unselectedLabelColor: _kSubtext,
        indicatorColor: _kPrimary,
        indicatorWeight: 2.5,
        labelStyle: _pjs(size: 13, weight: FontWeight.w700),
        unselectedLabelStyle: _pjs(size: 13),
        isScrollable: false,
        tabs: _tabs.map((t) => Tab(text: t)).toList(),
      ),
    );
  }
}

// ═════════════════════════════════════════════════════════════════════════════
// SHOP TAB
// ═════════════════════════════════════════════════════════════════════════════
class _ShopTab extends StatelessWidget {
  const _ShopTab({required this.seller, required this.countdown});
  final SellerInfo seller;
  final String countdown;

  @override
  Widget build(BuildContext context) {
    final flashProducts =
        seller.products.where((p) => p.badge == 'FLASH').toList();
    final allProducts = seller.products;

    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (flashProducts.isNotEmpty) ...[
            _buildFlashDealsSection(context, flashProducts),
            const SizedBox(height: 8),
          ],
          _buildVoucherBanner(context),
          const SizedBox(height: 8),
          _buildRecommendedSection(context, allProducts),
          const SizedBox(height: 24),
        ],
      ),
    );
  }

  Widget _buildFlashDealsSection(
      BuildContext context, List<StoreProduct> products) {
    final parts = countdown.split(':');
    return Container(
      color: _kCard,
      padding: const EdgeInsets.fromLTRB(12, 12, 12, 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: _kFlash,
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.flash_on, size: 12, color: Colors.white),
                    Text(
                      'FLASH\nDEALS',
                      textAlign: TextAlign.center,
                      style: _pjs(
                          size: 7,
                          weight: FontWeight.w800,
                          color: Colors.white,
                          height: 1.2),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 10),
              // Countdown blocks
              if (parts.length == 3) ...[
                _CountBlock(parts[0]),
                _ColonSep(),
                _CountBlock(parts[1]),
                _ColonSep(),
                _CountBlock(parts[2]),
              ],
              const Spacer(),
              GestureDetector(
                onTap: () => Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => FlashDealsScreen(seller: seller),
                  ),
                ),
                child: Row(
                  children: [
                    Text('See More',
                        style: _pjs(
                            size: 12,
                            weight: FontWeight.w700,
                            color: _kSecondary)),
                    const Icon(Icons.chevron_right,
                        size: 14, color: _kSecondary),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          ...products.map((p) => _FlashProductRow(product: p)),
        ],
      ),
    );
  }

  Widget _buildVoucherBanner(BuildContext context) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 12),
      decoration: BoxDecoration(
        gradient: const LinearGradient(colors: [_kPrimary, _kSecondary]),
        borderRadius: BorderRadius.circular(12),
      ),
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  '${seller.name.toUpperCase()} OFFICIAL',
                  style: _pjs(
                      size: 12, weight: FontWeight.w700, color: Colors.white),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: _kFlash,
                  borderRadius: BorderRadius.circular(5),
                ),
                child: Text('LIMITED TIME',
                    style: _pjs(
                        size: 9, weight: FontWeight.w800, color: Colors.white)),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text('MEGA DISCOUNT VOUCHERS',
              style:
                  _pjs(size: 16, weight: FontWeight.w800, color: Colors.white)),
          Text('Stack with shop coupons for maximum savings today',
              style: _pjs(size: 11, color: Colors.white70, height: 1.4)),
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: Colors.white.withValues(alpha: 0.25)),
            ),
            child: Row(
              children: [
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: _kFlash,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text('25%',
                          style: _pjs(
                              size: 13,
                              weight: FontWeight.w800,
                              color: Colors.white)),
                      Text('OFF',
                          style: _pjs(
                              size: 9,
                              weight: FontWeight.w800,
                              color: Colors.white)),
                    ],
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Storewide Discount',
                          style: _pjs(
                              size: 13,
                              weight: FontWeight.w700,
                              color: Colors.white)),
                      Text('Min. spend ₱499  •  Capped at ₱150',
                          style: _pjs(size: 11, color: Colors.white70)),
                    ],
                  ),
                ),
                ElevatedButton(
                  onPressed: () => _showVoucherModal(context),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _kFlash,
                    foregroundColor: Colors.white,
                    elevation: 0,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(9999)),
                    minimumSize: const Size(0, 32),
                    padding: const EdgeInsets.symmetric(horizontal: 14),
                  ),
                  child: Text('Claim',
                      style: _pjs(
                          size: 12,
                          weight: FontWeight.w700,
                          color: Colors.white)),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  void _showVoucherModal(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => const _VoucherClaimModal(
        voucherTitle: '25% OFF Storewide',
        voucherDetails:
            'Min. spend ₱499  •  Capped at ₱150  •  Valid until Oct 31',
      ),
    );
  }

  Widget _buildRecommendedSection(
      BuildContext context, List<StoreProduct> products) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 0, 16, 10),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('Recommended For You',
                  style: _pjs(size: 14, weight: FontWeight.w800)),
              GestureDetector(
                onTap: () {},
                child: Text('View All',
                    style: _pjs(
                        size: 13, weight: FontWeight.w600, color: _kSecondary)),
              ),
            ],
          ),
        ),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 12),
          child: GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: products.length,
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              crossAxisSpacing: 10,
              mainAxisSpacing: 10,
              childAspectRatio: 0.68,
            ),
            itemBuilder: (ctx, i) => _StoreProductCard(product: products[i]),
          ),
        ),
      ],
    );
  }
}

// ── Flash product row ─────────────────────────────────────────────────────────
class _FlashProductRow extends StatelessWidget {
  const _FlashProductRow({required this.product});
  final StoreProduct product;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: GestureDetector(
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) =>
                ProductDetailPage(product: storeProductToProduct(product)),
          ),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Stack(
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: Image.network(
                    'https://picsum.photos/seed/${product.imageSeed}/200/200',
                    width: 90,
                    height: 90,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) =>
                        Container(width: 90, height: 90, color: _kChip),
                  ),
                ),
                Positioned(
                  top: 5,
                  left: 5,
                  child: Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                    decoration: BoxDecoration(
                      color: _kOrange,
                      borderRadius: BorderRadius.circular(3),
                    ),
                    child: Text('3 DAYS',
                        style: _pjs(
                            size: 8,
                            weight: FontWeight.w800,
                            color: Colors.white)),
                  ),
                ),
                Positioned(
                  bottom: 5,
                  left: 5,
                  child: Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                    decoration: BoxDecoration(
                      color: _kFlash,
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Text('-${product.discount}%',
                        style: _pjs(
                            size: 9,
                            weight: FontWeight.w800,
                            color: Colors.white)),
                  ),
                ),
              ],
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (product.isFulfilled)
                    Container(
                      margin: const EdgeInsets.only(bottom: 3),
                      padding: const EdgeInsets.symmetric(
                          horizontal: 5, vertical: 2),
                      decoration: BoxDecoration(
                        color: _kChip,
                        borderRadius: BorderRadius.circular(3),
                      ),
                      child: Text('FULFILLED',
                          style: _pjs(
                              size: 8,
                              weight: FontWeight.w800,
                              color: _kSecondary)),
                    ),
                  Text(product.name,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: _pjs(size: 12, height: 1.4)),
                  const SizedBox(height: 3),
                  Row(
                    children: [
                      Icon(Icons.star_rounded,
                          size: 11, color: Colors.amber.shade500),
                      Text(' (${product.rating})',
                          style: _pjs(size: 11, color: _kSubtext)),
                    ],
                  ),
                  const SizedBox(height: 3),
                  Row(
                    children: [
                      Text('₱${product.price.toStringAsFixed(0)}',
                          style: _pjs(
                              size: 16,
                              weight: FontWeight.w800,
                              color: _kText)),
                      const SizedBox(width: 6),
                      Text(
                        '₱${product.originalPrice.toStringAsFixed(0)}',
                        style: _pjs(
                          size: 12,
                          color: _kSubtext,
                          decoration: TextDecoration.lineThrough,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  // Selling fast progress bar
                  Stack(
                    children: [
                      Container(
                        height: 5,
                        decoration: BoxDecoration(
                          color: _kBorder,
                          borderRadius: BorderRadius.circular(3),
                        ),
                      ),
                      FractionallySizedBox(
                        widthFactor: 0.75,
                        child: Container(
                          height: 5,
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: [_kSecondary, _kFlash],
                            ),
                            borderRadius: BorderRadius.circular(3),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 5, vertical: 1),
                        decoration: BoxDecoration(
                          color: _kFlash.withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(3),
                        ),
                        child: Text('SELLING FAST',
                            style: _pjs(
                                size: 8,
                                weight: FontWeight.w800,
                                color: _kFlash)),
                      ),
                      const Spacer(),
                      GestureDetector(
                        onTap: () => Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => ProductDetailPage(
                                product: storeProductToProduct(product)),
                          ),
                        ),
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: _kPrimary,
                            borderRadius: BorderRadius.circular(9),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.flash_on,
                                  size: 11, color: Colors.white),
                              Text('Buy',
                                  style: _pjs(
                                      size: 11,
                                      weight: FontWeight.w700,
                                      color: Colors.white)),
                            ],
                          ),
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

// ═════════════════════════════════════════════════════════════════════════════
// PRODUCTS TAB
// ═════════════════════════════════════════════════════════════════════════════
class _ProductsTab extends StatefulWidget {
  const _ProductsTab({required this.seller});
  final SellerInfo seller;

  @override
  State<_ProductsTab> createState() => _ProductsTabState();
}

class _ProductsTabState extends State<_ProductsTab> {
  String _sortOrder = 'Popular';

  static const _sortOptions = ['Popular', 'Latest', 'Top Sales', 'Price ↕'];

  List<StoreProduct> get _sortedProducts {
    final list = List<StoreProduct>.from(widget.seller.products);
    switch (_sortOrder) {
      case 'Latest':
        return list.reversed.toList();
      case 'Top Sales':
        list.sort((a, b) => b.sold.compareTo(a.sold));
        return list;
      case 'Price ↕':
        list.sort((a, b) => a.price.compareTo(b.price));
        return list;
      default: // Popular
        list.sort((a, b) => b.rating.compareTo(a.rating));
        return list;
    }
  }

  void _showVoucherModal(BuildContext context, String title, String details) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) =>
          _VoucherClaimModal(voucherTitle: title, voucherDetails: details),
    );
  }

  void _showAddOnModal(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _AddOnDealModal(seller: widget.seller),
    );
  }

  @override
  Widget build(BuildContext context) {
    final products = _sortedProducts;
    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Sort filter row
          Container(
            color: _kCard,
            padding: const EdgeInsets.fromLTRB(12, 10, 12, 10),
            child: Row(
              children: _sortOptions.map((opt) {
                final selected = opt == _sortOrder;
                return Expanded(
                  child: GestureDetector(
                    onTap: () => setState(() => _sortOrder = opt),
                    child: Column(
                      children: [
                        Text(
                          opt,
                          style: _pjs(
                            size: 12,
                            weight:
                                selected ? FontWeight.w700 : FontWeight.w400,
                            color: selected ? _kSecondary : _kSubtext,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 4),
                        if (selected)
                          Container(
                            width: 6,
                            height: 6,
                            decoration: const BoxDecoration(
                              color: _kSecondary,
                              shape: BoxShape.circle,
                            ),
                          )
                        else
                          const SizedBox(height: 6),
                      ],
                    ),
                  ),
                );
              }).toList(),
            ),
          ),
          // Voucher mini-banners
          SizedBox(
            height: 52,
            child: ListView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              children: [
                _VoucherChip(
                  label: 'VOUCHER ₱20 OFF min ₱199',
                  onClaim: () => _showVoucherModal(
                    context,
                    '₱20 OFF Voucher',
                    'Min. spend ₱199  •  No cap  •  Valid until Oct 31',
                  ),
                ),
                const SizedBox(width: 8),
                _VoucherChip(
                  label: 'FREE SHIPPING 0 Min. Spend',
                  onClaim: () => _showVoucherModal(
                    context,
                    'Free Shipping Voucher',
                    '₱0 minimum spend  •  Capped at ₱50  •  Valid until Oct 31',
                  ),
                ),
              ],
            ),
          ),
          // Product grid
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12),
            child: GridView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: products.length,
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 2,
                crossAxisSpacing: 10,
                mainAxisSpacing: 10,
                childAspectRatio: 0.60,
              ),
              itemBuilder: (ctx, i) => _EnhancedProductCard(
                product: products[i],
                storeName: widget.seller.name,
                onAddOnTap: () => _showAddOnModal(context),
              ),
            ),
          ),
          // Footer bar
          Container(
            color: _kCard,
            margin: const EdgeInsets.only(top: 12),
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            child: Row(
              children: [
                const Icon(Icons.verified_user_outlined,
                    size: 14, color: _kGreen),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    '100% Authentic Products guaranteed by ${widget.seller.name} Official Store',
                    style: _pjs(size: 11, color: _kSubtext),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
        ],
      ),
    );
  }
}

// ── Voucher chip ──────────────────────────────────────────────────────────────
class _VoucherChip extends StatelessWidget {
  const _VoucherChip({required this.label, required this.onClaim});
  final String label;
  final VoidCallback onClaim;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: _kChip,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: _kBorder),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.confirmation_number_outlined,
              size: 13, color: _kSecondary),
          const SizedBox(width: 6),
          Text(label,
              style: _pjs(size: 11, weight: FontWeight.w600, color: _kText)),
          const SizedBox(width: 8),
          GestureDetector(
            onTap: onClaim,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
              decoration: BoxDecoration(
                color: _kSecondary,
                borderRadius: BorderRadius.circular(9999),
              ),
              child: Text('Claim',
                  style: _pjs(
                      size: 10, weight: FontWeight.w700, color: Colors.white)),
            ),
          ),
        ],
      ),
    );
  }
}

// ── Enhanced product card (for Products tab) ──────────────────────────────────
class _EnhancedProductCard extends StatelessWidget {
  const _EnhancedProductCard({
    required this.product,
    required this.storeName,
    required this.onAddOnTap,
  });
  final StoreProduct product;
  final String storeName;
  final VoidCallback onAddOnTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () => Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) =>
              ProductDetailPage(product: storeProductToProduct(product)),
        ),
      ),
      child: Container(
        decoration: BoxDecoration(
          color: _kCard,
          borderRadius: BorderRadius.circular(12),
          boxShadow: const [
            BoxShadow(
                color: Color(0x0D34175B), blurRadius: 6, offset: Offset(0, 2)),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image
            Stack(
              children: [
                ClipRRect(
                  borderRadius:
                      const BorderRadius.vertical(top: Radius.circular(12)),
                  child: AspectRatio(
                    aspectRatio: 1,
                    child: Image.network(
                      'https://picsum.photos/seed/${product.imageSeed}/300/300',
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => Container(
                        color: _kChip,
                        child: const Icon(Icons.image_outlined,
                            size: 36, color: _kSubtext),
                      ),
                    ),
                  ),
                ),
                // Store badge top-left
                Positioned(
                  top: 6,
                  left: 6,
                  child: Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                    decoration: BoxDecoration(
                      color: _kPrimary.withValues(alpha: 0.85),
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Text(storeName.toUpperCase(),
                        style: _pjs(
                            size: 7,
                            weight: FontWeight.w800,
                            color: Colors.white)),
                  ),
                ),
                // Wishlist heart top-right
                Positioned(
                  top: 6,
                  right: 6,
                  child: Container(
                    width: 26,
                    height: 26,
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.9),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.favorite_border,
                        size: 14, color: _kFlash),
                  ),
                ),
              ],
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(8, 6, 8, 8),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // SPayLater / MEGA SALE badges
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 4, vertical: 2),
                        decoration: BoxDecoration(
                          color: _kChip,
                          borderRadius: BorderRadius.circular(3),
                          border: Border.all(color: _kBorder),
                        ),
                        child: Text('SPayLater 0%',
                            style: _pjs(
                                size: 7,
                                weight: FontWeight.w700,
                                color: _kSecondary)),
                      ),
                      const SizedBox(width: 3),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 4, vertical: 2),
                        decoration: BoxDecoration(
                          color: _kFlash.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(3),
                        ),
                        child: Text('MEGA SALE',
                            style: _pjs(
                                size: 7,
                                weight: FontWeight.w700,
                                color: _kFlash)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 3),
                  // Fulfilled + Preferred badges
                  if (product.isFulfilled)
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 4, vertical: 1),
                          decoration: BoxDecoration(
                            color: _kChip,
                            borderRadius: BorderRadius.circular(3),
                          ),
                          child: Text('✓ Fulfilled',
                              style: _pjs(
                                  size: 7,
                                  weight: FontWeight.w700,
                                  color: _kSecondary)),
                        ),
                        const SizedBox(width: 3),
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 4, vertical: 1),
                          decoration: BoxDecoration(
                            color: _kChip,
                            borderRadius: BorderRadius.circular(3),
                            border: Border.all(color: _kSecondary),
                          ),
                          child: Text('Preferred',
                              style: _pjs(
                                  size: 7,
                                  weight: FontWeight.w700,
                                  color: _kSecondary)),
                        ),
                      ],
                    ),
                  const SizedBox(height: 4),
                  Text(product.name,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style:
                          _pjs(size: 12, weight: FontWeight.w600, height: 1.3)),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      Text('₱${product.price.toStringAsFixed(0)}',
                          style: _pjs(
                              size: 14,
                              weight: FontWeight.w800,
                              color: _kText)),
                      const SizedBox(width: 4),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 4, vertical: 1),
                        decoration: BoxDecoration(
                          color: _kFlash,
                          borderRadius: BorderRadius.circular(3),
                        ),
                        child: Text('-${product.discount}%',
                            style: _pjs(
                                size: 8,
                                weight: FontWeight.w800,
                                color: Colors.white)),
                      ),
                      const SizedBox(width: 3),
                      const Icon(Icons.verified, size: 11, color: _kGreen),
                    ],
                  ),
                  const SizedBox(height: 3),
                  // Add-on Deal link
                  GestureDetector(
                    onTap: onAddOnTap,
                    child: Text('Add-on Deal',
                        style: _pjs(
                            size: 10,
                            weight: FontWeight.w600,
                            color: _kSecondary,
                            decoration: TextDecoration.underline)),
                  ),
                  const SizedBox(height: 3),
                  Row(
                    children: [
                      Icon(Icons.star_rounded,
                          size: 11, color: Colors.amber.shade500),
                      Text(' ${product.rating}',
                          style: _pjs(size: 11, weight: FontWeight.w600)),
                      Text('  ${product.sold}',
                          style: _pjs(size: 10, color: _kSubtext)),
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

// ═════════════════════════════════════════════════════════════════════════════
// REVIEWS TAB
// ═════════════════════════════════════════════════════════════════════════════
class _ReviewsTab extends StatefulWidget {
  const _ReviewsTab({required this.seller});
  final SellerInfo seller;

  @override
  State<_ReviewsTab> createState() => _ReviewsTabState();
}

class _ReviewsTabState extends State<_ReviewsTab> {
  String _selectedFilter = 'All (1.5K)';
  final Map<int, bool> _expanded = {};

  static const _filterChips = ['All (1.5K)', 'With Videos', 'With Media', '5★'];

  void _showAddToCartModal(BuildContext context, StoreReview review) {
    // Find a matching product or use first
    final product =
        widget.seller.products.isNotEmpty ? widget.seller.products.first : null;
    if (product == null) return;
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _AddToCartModal(product: product),
    );
  }

  @override
  Widget build(BuildContext context) {
    final reviews = widget.seller.reviews;
    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Filter chips
          Container(
            color: _kCard,
            padding: const EdgeInsets.fromLTRB(12, 10, 12, 10),
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: _filterChips.map((chip) {
                  final selected = chip == _selectedFilter;
                  return Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: GestureDetector(
                      onTap: () => setState(() => _selectedFilter = chip),
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 14, vertical: 7),
                        decoration: BoxDecoration(
                          color: selected ? _kPrimary : _kSurface,
                          borderRadius: BorderRadius.circular(9999),
                          border: Border.all(
                              color: selected ? _kPrimary : _kBorder),
                        ),
                        child: Text(chip,
                            style: _pjs(
                                size: 12,
                                weight: FontWeight.w600,
                                color: selected ? Colors.white : _kText)),
                      ),
                    ),
                  );
                }).toList(),
              ),
            ),
          ),
          // Rating summary row
          Container(
            color: _kCard,
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
            child: Row(
              children: [
                const Icon(Icons.shield_outlined, size: 16, color: _kGreen),
                const SizedBox(width: 6),
                Expanded(
                  child: Text('100% Genuine Buyer Feedback',
                      style: _pjs(
                          size: 12, weight: FontWeight.w600, color: _kGreen)),
                ),
                Text(
                  '${widget.seller.rating} / 5.0 Rating',
                  style:
                      _pjs(size: 12, weight: FontWeight.w700, color: _kPrimary),
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),
          // Review cards grid
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12),
            child: GridView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: reviews.length,
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 2,
                crossAxisSpacing: 10,
                mainAxisSpacing: 10,
                childAspectRatio: 0.75,
              ),
              itemBuilder: (ctx, i) {
                final review = reviews[i];
                final isExpanded = _expanded[i] ?? false;
                return _ReviewCard(
                  review: review,
                  isExpanded: isExpanded,
                  onToggleExpand: () =>
                      setState(() => _expanded[i] = !isExpanded),
                  onProductTap: () {
                    final product = widget.seller.products.isNotEmpty
                        ? widget.seller.products.first
                        : null;
                    if (product == null) return;
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => ProductDetailPage(
                            product: storeProductToProduct(product)),
                      ),
                    );
                  },
                  onCartTap: () => _showAddToCartModal(context, review),
                );
              },
            ),
          ),
          const SizedBox(height: 16),
          // Footer section
          Container(
            color: _kCard,
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                const Icon(Icons.videocam_outlined, size: 32, color: _kPrimary),
                const SizedBox(height: 8),
                Text('Watch More Community Unboxings',
                    style: _pjs(size: 14, weight: FontWeight.w800),
                    textAlign: TextAlign.center),
                const SizedBox(height: 4),
                Text('Over 1,500 buyers uploaded clips for this shop',
                    style: _pjs(size: 12, color: _kSubtext),
                    textAlign: TextAlign.center),
                const SizedBox(height: 14),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () {},
                    style: ElevatedButton.styleFrom(
                      backgroundColor: _kPrimary,
                      elevation: 0,
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10)),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text('Load More Reviews',
                            style: _pjs(
                                size: 13,
                                weight: FontWeight.w700,
                                color: Colors.white)),
                        const Icon(Icons.keyboard_arrow_down,
                            size: 18, color: Colors.white),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
        ],
      ),
    );
  }
}

class _ReviewCard extends StatelessWidget {
  const _ReviewCard({
    required this.review,
    required this.isExpanded,
    required this.onToggleExpand,
    required this.onProductTap,
    required this.onCartTap,
  });

  final StoreReview review;
  final bool isExpanded;
  final VoidCallback onToggleExpand;
  final VoidCallback onProductTap;
  final VoidCallback onCartTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onToggleExpand,
      child: Container(
        decoration: BoxDecoration(
          color: _kCard,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: _kBorder),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Verified buyer chip
            Padding(
              padding: const EdgeInsets.fromLTRB(8, 8, 8, 4),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
                decoration: BoxDecoration(
                  color: _kGreen.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(4),
                ),
                child: Text('✓ Verified Buyer',
                    style:
                        _pjs(size: 9, weight: FontWeight.w700, color: _kGreen)),
              ),
            ),
            // Product image
            if (review.imageSeed.isNotEmpty)
              GestureDetector(
                onTap: onProductTap,
                child: Stack(
                  children: [
                    AspectRatio(
                      aspectRatio: 1.6,
                      child: ClipRRect(
                        borderRadius: const BorderRadius.vertical(
                            top: Radius.circular(0)),
                        child: Image.network(
                          'https://picsum.photos/seed/${review.imageSeed}/200/200',
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) =>
                              Container(color: _kChip),
                        ),
                      ),
                    ),
                    if (review.hasVideo)
                      Positioned.fill(
                        child: Container(
                          color: Colors.black.withValues(alpha: 0.3),
                          child: const Center(
                            child: Icon(Icons.play_circle_outline,
                                color: Colors.white, size: 32),
                          ),
                        ),
                      ),
                  ],
                ),
              ),
            Padding(
              padding: const EdgeInsets.fromLTRB(8, 6, 8, 8),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Stars
                  Row(
                    children: List.generate(
                      5,
                      (i) => Icon(
                        i < review.rating
                            ? Icons.star_rounded
                            : Icons.star_outline_rounded,
                        size: 12,
                        color: Colors.amber.shade500,
                      ),
                    ),
                  ),
                  const SizedBox(height: 4),
                  // Review text
                  Text(
                    review.comment,
                    maxLines: isExpanded ? null : 2,
                    overflow: isExpanded
                        ? TextOverflow.visible
                        : TextOverflow.ellipsis,
                    style: _pjs(size: 11, height: 1.4),
                  ),
                  const SizedBox(height: 4),
                  // Product name
                  Text(review.product,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: _pjs(size: 10, color: _kSubtext)),
                  // Badges
                  if (review.productBadges.isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Wrap(
                      spacing: 4,
                      runSpacing: 2,
                      children: review.productBadges
                          .map((b) => Container(
                                padding: const EdgeInsets.symmetric(
                                    horizontal: 4, vertical: 1),
                                decoration: BoxDecoration(
                                  color: _kChip,
                                  borderRadius: BorderRadius.circular(3),
                                  border: Border.all(color: _kBorder),
                                ),
                                child: Text(b,
                                    style: _pjs(
                                        size: 8,
                                        weight: FontWeight.w700,
                                        color: _kSecondary)),
                              ))
                          .toList(),
                    ),
                  ],
                  // Cart icon
                  Align(
                    alignment: Alignment.centerRight,
                    child: GestureDetector(
                      onTap: onCartTap,
                      child: Container(
                        width: 28,
                        height: 28,
                        decoration: BoxDecoration(
                          color: _kPrimary,
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(Icons.add_shopping_cart,
                            size: 14, color: Colors.white),
                      ),
                    ),
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

// ═════════════════════════════════════════════════════════════════════════════
// CATEGORIES TAB
// ═════════════════════════════════════════════════════════════════════════════
class _CategoriesTab extends StatelessWidget {
  const _CategoriesTab({required this.seller});
  final SellerInfo seller;

  @override
  Widget build(BuildContext context) {
    final cats = seller.sellerCategories;
    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header banner
          Container(
            decoration: const BoxDecoration(
              gradient: LinearGradient(colors: [_kPrimary, _kSecondary]),
            ),
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 16),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('SELLER COLLECTIONS',
                          style: _pjs(
                              size: 10,
                              weight: FontWeight.w800,
                              color: Colors.white70,
                              height: 1)),
                      const SizedBox(height: 4),
                      Text('Explore by Category',
                          style: _pjs(
                              size: 18,
                              weight: FontWeight.w800,
                              color: Colors.white)),
                      const SizedBox(height: 4),
                      Text(
                          '${seller.totalProducts} products across ${cats.length} categories',
                          style: _pjs(size: 12, color: Colors.white70)),
                    ],
                  ),
                ),
                const Icon(Icons.grid_view_rounded,
                    color: Colors.white70, size: 28),
              ],
            ),
          ),
          // Category count + guarantee row
          Container(
            color: _kCard,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            child: Row(
              children: [
                Text('${cats.length} Categories',
                    style: _pjs(size: 13, weight: FontWeight.w700)),
                const Spacer(),
                const Icon(Icons.verified_user_outlined,
                    size: 14, color: _kGreen),
                const SizedBox(width: 4),
                Text('All In-Stock Guarantee',
                    style: _pjs(
                        size: 12, weight: FontWeight.w600, color: _kGreen)),
              ],
            ),
          ),
          const Divider(height: 1, color: _kBorder),
          // Category list
          Container(
            color: _kCard,
            child: Column(
              children: [
                ...cats.asMap().entries.map((entry) {
                  final i = entry.key;
                  final cat = entry.value;
                  return Column(
                    children: [
                      if (i > 0)
                        const Divider(height: 1, color: _kBorder, indent: 64),
                      _CategoryRow(
                        category: cat,
                        onTap: () => Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => CategoryProductListScreen(
                              seller: seller,
                              categoryId: cat.id,
                              categoryName: cat.name,
                            ),
                          ),
                        ),
                      ),
                    ],
                  );
                }),
                const Divider(height: 1, color: _kBorder),
                // "All Products" row
                Container(
                  color: _kChip,
                  padding:
                      const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  child: Row(
                    children: [
                      Container(
                        width: 40,
                        height: 40,
                        decoration: BoxDecoration(
                          color: _kPrimary,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Icon(Icons.storefront_outlined,
                            size: 20, color: Colors.white),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          'All Products (${seller.totalProducts})',
                          style: _pjs(size: 14, weight: FontWeight.w700),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      OutlinedButton(
                        onPressed: () => Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => CategoryProductListScreen(
                              seller: seller,
                              categoryId: 'all',
                              categoryName: 'All Products',
                            ),
                          ),
                        ),
                        style: OutlinedButton.styleFrom(
                          side: const BorderSide(color: _kSecondary),
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(9999)),
                          padding: const EdgeInsets.symmetric(
                              horizontal: 14, vertical: 6),
                          minimumSize: Size.zero,
                        ),
                        child: Text('View All',
                            style: _pjs(
                                size: 12,
                                weight: FontWeight.w700,
                                color: _kSecondary)),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          // Footer trust badges
          Container(
            color: _kSurface,
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Expanded(
                  child: Row(
                    children: [
                      const Icon(Icons.local_shipping_outlined,
                          size: 14, color: _kGreen),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          'Express Delivery\nShips within 24 hours',
                          style: _pjs(size: 11, color: _kSubtext, height: 1.4),
                        ),
                      ),
                    ],
                  ),
                ),
                Container(width: 1, height: 32, color: _kBorder),
                Expanded(
                  child: Row(
                    children: [
                      const SizedBox(width: 12),
                      const Icon(Icons.verified_user_outlined,
                          size: 14, color: _kGreen),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          '100% Authentic\nDirect from ${seller.name}',
                          style: _pjs(size: 11, color: _kSubtext, height: 1.4),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
        ],
      ),
    );
  }
}

class _CategoryRow extends StatelessWidget {
  const _CategoryRow({required this.category, required this.onTap});
  final _SellerCategory category;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        child: Row(
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: Image.network(
                'https://picsum.photos/seed/${category.imageSeed}/80/80',
                width: 40,
                height: 40,
                fit: BoxFit.cover,
                errorBuilder: (_, __, ___) => Container(
                  width: 40,
                  height: 40,
                  color: _kChip,
                  child: const Icon(Icons.category_outlined,
                      size: 20, color: _kSecondary),
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                '${category.name} (${category.count})',
                style: _pjs(size: 13, weight: FontWeight.w600),
                overflow: TextOverflow.ellipsis,
              ),
            ),
            const Icon(Icons.chevron_right, size: 18, color: _kSubtext),
          ],
        ),
      ),
    );
  }
}

// ── Store product card ────────────────────────────────────────────────────────
class _StoreProductCard extends StatelessWidget {
  const _StoreProductCard({required this.product});
  final StoreProduct product;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () => Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) =>
              ProductDetailPage(product: storeProductToProduct(product)),
        ),
      ),
      child: Container(
        decoration: BoxDecoration(
          color: _kCard,
          borderRadius: BorderRadius.circular(12),
          boxShadow: const [
            BoxShadow(
                color: Color(0x0D34175B), blurRadius: 6, offset: Offset(0, 2)),
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
                      'https://picsum.photos/seed/${product.imageSeed}/300/300',
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => Container(
                        color: _kChip,
                        child: const Icon(Icons.image_outlined,
                            size: 36, color: _kSubtext),
                      ),
                    ),
                  ),
                ),
                if (product.discount > 0)
                  Positioned(
                    top: 6,
                    left: 6,
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 5, vertical: 2),
                      decoration: BoxDecoration(
                        color: _kFlash,
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text('-${product.discount}%',
                          style: _pjs(
                              size: 9,
                              weight: FontWeight.w800,
                              color: Colors.white)),
                    ),
                  ),
                Positioned(
                  top: 6,
                  right: 6,
                  child: Container(
                    width: 26,
                    height: 26,
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.9),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.favorite_border,
                        size: 14, color: _kFlash),
                  ),
                ),
                Positioned(
                  bottom: 6,
                  right: 6,
                  child: Container(
                    width: 30,
                    height: 30,
                    decoration: const BoxDecoration(
                      color: _kPrimary,
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.add_shopping_cart,
                        size: 15, color: Colors.white),
                  ),
                ),
              ],
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(8, 6, 8, 8),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(product.name,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style:
                          _pjs(size: 12, weight: FontWeight.w600, height: 1.3)),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      Icon(Icons.star_rounded,
                          size: 11, color: Colors.amber.shade500),
                      Text(' ${product.rating}',
                          style: _pjs(size: 11, weight: FontWeight.w600)),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text('₱${product.price.toStringAsFixed(0)}',
                      style: _pjs(
                          size: 14, weight: FontWeight.w800, color: _kText)),
                  Text(product.sold, style: _pjs(size: 10, color: _kSubtext)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ═════════════════════════════════════════════════════════════════════════════
// COUNTDOWN WIDGETS
// ═════════════════════════════════════════════════════════════════════════════
class _CountBlock extends StatelessWidget {
  const _CountBlock(this.value);
  final String value;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 36,
      height: 28,
      decoration: BoxDecoration(
        color: _kPrimary,
        borderRadius: BorderRadius.circular(5),
      ),
      alignment: Alignment.center,
      child: Text(value,
          style: _pjs(size: 13, weight: FontWeight.w800, color: Colors.white)),
    );
  }
}

class _ColonSep extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 3),
      child: Text(':',
          style: _pjs(size: 15, weight: FontWeight.w800, color: _kPrimary)),
    );
  }
}

// ═════════════════════════════════════════════════════════════════════════════
// MODALS
// ═════════════════════════════════════════════════════════════════════════════

// ── Voucher Claim Modal ───────────────────────────────────────────────────────
class _VoucherClaimModal extends StatelessWidget {
  const _VoucherClaimModal({
    required this.voucherTitle,
    required this.voucherDetails,
  });
  final String voucherTitle;
  final String voucherDetails;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: _kCard,
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Drag handle
          Container(
            width: 36,
            height: 4,
            decoration: BoxDecoration(
              color: _kBorder,
              borderRadius: BorderRadius.circular(2),
            ),
          ),
          const SizedBox(height: 20),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
            decoration: BoxDecoration(
              color: _kChip,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: _kBorder),
            ),
            child: Row(
              children: [
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                  decoration: BoxDecoration(
                    color: _kSecondary,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(Icons.confirmation_number_outlined,
                      color: Colors.white, size: 20),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(voucherTitle,
                          style: _pjs(size: 14, weight: FontWeight.w800)),
                      const SizedBox(height: 4),
                      Text(voucherDetails,
                          style: _pjs(size: 11, color: _kSubtext, height: 1.4)),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: () => Navigator.pop(context),
              style: ElevatedButton.styleFrom(
                backgroundColor: _kSecondary,
                elevation: 0,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
                minimumSize: const Size(0, 48),
              ),
              child: Text('Claim Voucher',
                  style: _pjs(
                      size: 15, weight: FontWeight.w700, color: Colors.white)),
            ),
          ),
          const SizedBox(height: 10),
          GestureDetector(
            onTap: () => Navigator.pop(context),
            child: Text('Maybe Later', style: _pjs(size: 13, color: _kSubtext)),
          ),
        ],
      ),
    );
  }
}

// ── Add-On Deal Modal ─────────────────────────────────────────────────────────
class _AddOnDealModal extends StatelessWidget {
  const _AddOnDealModal({required this.seller});
  final SellerInfo seller;

  @override
  Widget build(BuildContext context) {
    final items = seller.products.take(3).toList();
    return Container(
      decoration: const BoxDecoration(
        color: _kCard,
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 36,
            height: 4,
            decoration: BoxDecoration(
              color: _kBorder,
              borderRadius: BorderRadius.circular(2),
            ),
          ),
          const SizedBox(height: 16),
          Text('Add-on Deal', style: _pjs(size: 16, weight: FontWeight.w800)),
          Text('Bundle with these items for extra savings',
              style: _pjs(size: 12, color: _kSubtext)),
          const SizedBox(height: 16),
          ...items.map((p) => Padding(
                padding: const EdgeInsets.only(bottom: 10),
                child: Row(
                  children: [
                    ClipRRect(
                      borderRadius: BorderRadius.circular(8),
                      child: Image.network(
                        'https://picsum.photos/seed/${p.imageSeed}/80/80',
                        width: 52,
                        height: 52,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) =>
                            Container(width: 52, height: 52, color: _kChip),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(p.name,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: _pjs(size: 12, weight: FontWeight.w600)),
                          Text('₱${p.price.toStringAsFixed(0)}',
                              style: _pjs(
                                  size: 13,
                                  weight: FontWeight.w800,
                                  color: _kFlash)),
                        ],
                      ),
                    ),
                    GestureDetector(
                      onTap: () {},
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: _kChip,
                          borderRadius: BorderRadius.circular(9999),
                          border: Border.all(color: _kSecondary),
                        ),
                        child: Text('+ Add',
                            style: _pjs(
                                size: 11,
                                weight: FontWeight.w700,
                                color: _kSecondary)),
                      ),
                    ),
                  ],
                ),
              )),
          const SizedBox(height: 10),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: () => Navigator.pop(context),
              style: ElevatedButton.styleFrom(
                backgroundColor: _kSecondary,
                elevation: 0,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
                minimumSize: const Size(0, 48),
              ),
              child: Text('Confirm Bundle',
                  style: _pjs(
                      size: 15, weight: FontWeight.w700, color: Colors.white)),
            ),
          ),
        ],
      ),
    );
  }
}

// ── Add to Cart Modal ─────────────────────────────────────────────────────────
class _AddToCartModal extends StatefulWidget {
  const _AddToCartModal({required this.product});
  final StoreProduct product;

  @override
  State<_AddToCartModal> createState() => _AddToCartModalState();
}

class _AddToCartModalState extends State<_AddToCartModal> {
  int _qty = 1;
  String _selectedColor = 'Purple';
  String _selectedSize = 'M';

  static const _colors = ['Purple', 'Black', 'White'];
  static const _sizes = ['S', 'M', 'L', 'XL'];

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: _kCard,
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 36,
            height: 4,
            decoration: BoxDecoration(
              color: _kBorder,
              borderRadius: BorderRadius.circular(2),
            ),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(10),
                child: Image.network(
                  'https://picsum.photos/seed/${widget.product.imageSeed}/160/160',
                  width: 72,
                  height: 72,
                  fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) =>
                      Container(width: 72, height: 72, color: _kChip),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(widget.product.name,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: _pjs(size: 13, weight: FontWeight.w700)),
                    const SizedBox(height: 4),
                    Text('₱${widget.product.price.toStringAsFixed(0)}',
                        style: _pjs(
                            size: 18, weight: FontWeight.w800, color: _kFlash)),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          // Color selector
          Align(
            alignment: Alignment.centerLeft,
            child:
                Text('Color', style: _pjs(size: 13, weight: FontWeight.w700)),
          ),
          const SizedBox(height: 8),
          Row(
            children: _colors.map((c) {
              final sel = c == _selectedColor;
              return Padding(
                padding: const EdgeInsets.only(right: 8),
                child: GestureDetector(
                  onTap: () => setState(() => _selectedColor = c),
                  child: Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
                    decoration: BoxDecoration(
                      color: sel ? _kPrimary : _kSurface,
                      borderRadius: BorderRadius.circular(9999),
                      border: Border.all(color: sel ? _kPrimary : _kBorder),
                    ),
                    child: Text(c,
                        style: _pjs(
                            size: 12,
                            weight: FontWeight.w600,
                            color: sel ? Colors.white : _kText)),
                  ),
                ),
              );
            }).toList(),
          ),
          const SizedBox(height: 12),
          // Size selector
          Align(
            alignment: Alignment.centerLeft,
            child: Text('Size', style: _pjs(size: 13, weight: FontWeight.w700)),
          ),
          const SizedBox(height: 8),
          Row(
            children: _sizes.map((s) {
              final sel = s == _selectedSize;
              return Padding(
                padding: const EdgeInsets.only(right: 8),
                child: GestureDetector(
                  onTap: () => setState(() => _selectedSize = s),
                  child: Container(
                    width: 44,
                    height: 36,
                    decoration: BoxDecoration(
                      color: sel ? _kPrimary : _kSurface,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: sel ? _kPrimary : _kBorder),
                    ),
                    alignment: Alignment.center,
                    child: Text(s,
                        style: _pjs(
                            size: 13,
                            weight: FontWeight.w700,
                            color: sel ? Colors.white : _kText)),
                  ),
                ),
              );
            }).toList(),
          ),
          const SizedBox(height: 12),
          // Quantity
          Row(
            children: [
              Text('Quantity', style: _pjs(size: 13, weight: FontWeight.w700)),
              const Spacer(),
              GestureDetector(
                onTap: () {
                  if (_qty > 1) setState(() => _qty--);
                },
                child: Container(
                  width: 32,
                  height: 32,
                  decoration: BoxDecoration(
                    color: _kSurface,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: _kBorder),
                  ),
                  alignment: Alignment.center,
                  child: const Icon(Icons.remove, size: 16, color: _kText),
                ),
              ),
              Container(
                width: 40,
                alignment: Alignment.center,
                child: Text('$_qty',
                    style: _pjs(size: 16, weight: FontWeight.w800)),
              ),
              GestureDetector(
                onTap: () => setState(() => _qty++),
                child: Container(
                  width: 32,
                  height: 32,
                  decoration: BoxDecoration(
                    color: _kSurface,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: _kBorder),
                  ),
                  alignment: Alignment.center,
                  child: const Icon(Icons.add, size: 16, color: _kText),
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: () => Navigator.pop(context),
              style: ElevatedButton.styleFrom(
                backgroundColor: _kSecondary,
                elevation: 0,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
                minimumSize: const Size(0, 48),
              ),
              child: Text('Add to Cart',
                  style: _pjs(
                      size: 15, weight: FontWeight.w700, color: Colors.white)),
            ),
          ),
        ],
      ),
    );
  }
}
