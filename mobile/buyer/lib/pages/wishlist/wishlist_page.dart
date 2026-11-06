import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../models/product.dart';
import '../../state/wishlist_provider.dart';
import '../../state/wishlist_state.dart';
import '../../state/cart_provider.dart';
import '../home/product_detail_page.dart';

// ── Design tokens ─────────────────────────────────────────────────────────────
const _kPrimary = Color(0xFF34175B);
const _kSecondary = Color(0xFF6B38D4);
const _kAccent = Color(0xFF8B5CF6);
const _kFlash = Color(0xFFFF3B5C);
const _kSurface = Color(0xFFF5F4FC);
const _kCard = Color(0xFFFFFFFF);
const _kChip = Color(0xFFEFE9FD);
const _kBorder = Color(0xFFDDD8F0);
const _kText = Color(0xFF1A1035);
const _kSubtext = Color(0xFF8A84A3);

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

// ── Wishlist Page ─────────────────────────────────────────────────────────────
class WishlistPage extends StatefulWidget {
  const WishlistPage({super.key});

  @override
  State<WishlistPage> createState() => _WishlistPageState();
}

class _WishlistPageState extends State<WishlistPage> {
  bool _selectMode = false;
  final Set<int> _selected = {};

  void _toggleSelectMode() {
    setState(() {
      _selectMode = !_selectMode;
      _selected.clear();
    });
  }

  void _toggleSelect(int id) {
    setState(() {
      if (_selected.contains(id)) {
        _selected.remove(id);
      } else {
        _selected.add(id);
      }
    });
  }

  void _selectAll(List<Product> items) {
    setState(() {
      if (_selected.length == items.length) {
        _selected.clear();
      } else {
        _selected.addAll(items.map((p) => p.id));
      }
    });
  }

  void _addSelectedToCart(WishlistState wishlist) {
    final cart = CartProvider.of(context);
    for (final id in _selected) {
      final product = wishlist.items.firstWhere((p) => p.id == id);
      cart.add(product);
    }
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          '${_selected.length} item${_selected.length == 1 ? '' : 's'} added to cart!',
          style: _pjs(size: 13, color: Colors.white, weight: FontWeight.w600),
        ),
        backgroundColor: _kPrimary,
        duration: const Duration(seconds: 2),
      ),
    );
    setState(() {
      _selectMode = false;
      _selected.clear();
    });
  }

  void _removeSelected(WishlistState wishlist) {
    final toRemove =
        wishlist.items.where((p) => _selected.contains(p.id)).toList();
    wishlist.removeAll(toRemove);
    setState(() {
      _selectMode = false;
      _selected.clear();
    });
  }

  @override
  Widget build(BuildContext context) {
    final wishlist = WishlistProvider.of(context);
    return AnimatedBuilder(
      animation: wishlist,
      builder: (context, _) {
        final items = wishlist.items;
        return Scaffold(
          backgroundColor: _kSurface,
          appBar: _buildAppBar(items, wishlist),
          body:
              items.isEmpty ? _buildEmptyState() : _buildGrid(items, wishlist),
          bottomNavigationBar: _selectMode && items.isNotEmpty
              ? _buildSelectBar(items, wishlist)
              : null,
        );
      },
    );
  }

  // ── AppBar ─────────────────────────────────────────────────────────────────
  PreferredSizeWidget _buildAppBar(
      List<Product> items, WishlistState wishlist) {
    return AppBar(
      backgroundColor: _kCard,
      elevation: 0,
      leading: IconButton(
        icon: Icon(Icons.arrow_back, color: _kText),
        onPressed: () => Navigator.pop(context),
      ),
      title: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('My Wishlist', style: _pjs(size: 17, weight: FontWeight.w800)),
          if (items.isNotEmpty)
            Text('${items.length} saved item${items.length == 1 ? '' : 's'}',
                style: _pjs(size: 11, color: _kSubtext)),
        ],
      ),
      actions: [
        if (items.isNotEmpty)
          TextButton(
            onPressed: _toggleSelectMode,
            child: Text(
              _selectMode ? 'Cancel' : 'Select',
              style:
                  _pjs(size: 13, weight: FontWeight.w700, color: _kSecondary),
            ),
          ),
      ],
    );
  }

  // ── Empty state ────────────────────────────────────────────────────────────
  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 96,
              height: 96,
              decoration: BoxDecoration(
                color: _kChip,
                shape: BoxShape.circle,
              ),
              child:
                  const Icon(Icons.favorite_border, size: 42, color: _kAccent),
            ),
            const SizedBox(height: 20),
            Text('Your wishlist is empty',
                style: _pjs(size: 18, weight: FontWeight.w800)),
            const SizedBox(height: 8),
            Text(
              'Tap the ♡ heart on any product to save it here for later.',
              textAlign: TextAlign.center,
              style: _pjs(size: 13, color: _kSubtext, height: 1.5),
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: () => Navigator.pop(context),
              icon: const Icon(Icons.explore_outlined,
                  size: 16, color: Colors.white),
              label: Text('Browse Products',
                  style: _pjs(
                      size: 14, weight: FontWeight.w700, color: Colors.white)),
              style: ElevatedButton.styleFrom(
                backgroundColor: _kPrimary,
                elevation: 0,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
                minimumSize: const Size(0, 50),
                padding:
                    const EdgeInsets.symmetric(horizontal: 28, vertical: 0),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ── Grid ───────────────────────────────────────────────────────────────────
  Widget _buildGrid(List<Product> items, WishlistState wishlist) {
    return GridView.builder(
      padding: EdgeInsets.fromLTRB(12, 12, 12, _selectMode ? 100 : 24),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        childAspectRatio: 0.60,
        crossAxisSpacing: 10,
        mainAxisSpacing: 10,
      ),
      itemCount: items.length,
      itemBuilder: (_, i) {
        final product = items[i];
        final isSelected = _selected.contains(product.id);
        return _WishlistCard(
          product: product,
          wishlist: wishlist,
          selectMode: _selectMode,
          isSelected: isSelected,
          onTap: () {
            if (_selectMode) {
              _toggleSelect(product.id);
            } else {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => WishlistProvider(
                    wishlist: wishlist,
                    child: CartProvider(
                      cart: CartProvider.of(context),
                      child: ProductDetailPage(product: product),
                    ),
                  ),
                ),
              );
            }
          },
          onSelectToggle: () => _toggleSelect(product.id),
          onRemove: () => wishlist.remove(product),
          onAddToCart: () => _openVariantSheet(product),
        );
      },
    );
  }

  // ── Select bottom bar ──────────────────────────────────────────────────────
  Widget _buildSelectBar(List<Product> items, WishlistState wishlist) {
    final allSelected = _selected.length == items.length;
    return Container(
      padding: const EdgeInsets.fromLTRB(16, 10, 16, 24),
      decoration: const BoxDecoration(
        color: _kCard,
        boxShadow: [
          BoxShadow(
              color: Color(0x1A34175B), blurRadius: 12, offset: Offset(0, -4)),
        ],
      ),
      child: SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Select all + count row
            Row(
              children: [
                GestureDetector(
                  onTap: () => _selectAll(items),
                  child: Row(
                    children: [
                      AnimatedContainer(
                        duration: const Duration(milliseconds: 150),
                        width: 22,
                        height: 22,
                        decoration: BoxDecoration(
                          color: allSelected ? _kPrimary : _kCard,
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(
                            color: allSelected ? _kPrimary : _kBorder,
                            width: 1.5,
                          ),
                        ),
                        child: allSelected
                            ? const Icon(Icons.check,
                                size: 14, color: Colors.white)
                            : null,
                      ),
                      const SizedBox(width: 8),
                      Text('Select All',
                          style: _pjs(
                              size: 13,
                              weight: FontWeight.w600,
                              color: _kText)),
                    ],
                  ),
                ),
                const Spacer(),
                Text(
                  '${_selected.length} selected',
                  style: _pjs(size: 13, color: _kSubtext),
                ),
              ],
            ),
            const SizedBox(height: 10),
            // Action buttons
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: _selected.isEmpty
                        ? null
                        : () => _removeSelected(wishlist),
                    icon: Icon(Icons.delete_outline,
                        size: 16,
                        color: _selected.isEmpty ? _kSubtext : _kFlash),
                    label: Text('Remove',
                        style: _pjs(
                            size: 13,
                            weight: FontWeight.w700,
                            color: _selected.isEmpty ? _kSubtext : _kFlash)),
                    style: OutlinedButton.styleFrom(
                      side: BorderSide(
                          color: _selected.isEmpty ? _kBorder : _kFlash),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10)),
                      minimumSize: const Size(0, 46),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  flex: 2,
                  child: ElevatedButton.icon(
                    onPressed: _selected.isEmpty
                        ? null
                        : () => _addSelectedToCart(wishlist),
                    icon: const Icon(Icons.shopping_cart_outlined,
                        size: 16, color: Colors.white),
                    label: Text('Add to Cart',
                        style: _pjs(
                            size: 13,
                            weight: FontWeight.w700,
                            color: Colors.white)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor:
                          _selected.isEmpty ? _kSubtext : _kPrimary,
                      elevation: 0,
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10)),
                      minimumSize: const Size(0, 46),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  // ── Open variant sheet ─────────────────────────────────────────────────────
  void _openVariantSheet(Product product) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _WishlistVariantSheet(
        product: product,
        onConfirm: (qty) {
          final cart = CartProvider.of(context);
          for (int i = 0; i < qty; i++) {
            cart.add(product);
          }
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Added to cart!',
                  style: _pjs(
                      size: 13, color: Colors.white, weight: FontWeight.w600)),
              backgroundColor: _kPrimary,
              duration: const Duration(seconds: 2),
            ),
          );
        },
      ),
    );
  }
}

// ── Wishlist Card ─────────────────────────────────────────────────────────────
class _WishlistCard extends StatelessWidget {
  const _WishlistCard({
    required this.product,
    required this.wishlist,
    required this.selectMode,
    required this.isSelected,
    required this.onTap,
    required this.onSelectToggle,
    required this.onRemove,
    required this.onAddToCart,
  });

  final Product product;
  final WishlistState wishlist;
  final bool selectMode;
  final bool isSelected;
  final VoidCallback onTap;
  final VoidCallback onSelectToggle;
  final VoidCallback onRemove;
  final VoidCallback onAddToCart;

  // Mock: treat products with id divisible by 7 as out of stock for demo
  bool get _outOfStock => product.id % 7 == 0;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        decoration: BoxDecoration(
          color: _kCard,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
            color: isSelected ? _kSecondary : _kBorder,
            width: isSelected ? 2 : 1,
          ),
          boxShadow: const [
            BoxShadow(
                color: Color(0x0834175B), blurRadius: 8, offset: Offset(0, 2)),
          ],
        ),
        child: Stack(
          children: [
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Image
                ClipRRect(
                  borderRadius:
                      const BorderRadius.vertical(top: Radius.circular(13)),
                  child: AspectRatio(
                    aspectRatio: 1,
                    child: Stack(
                      fit: StackFit.expand,
                      children: [
                        Image.network(
                          product.imageUrl.isNotEmpty
                              ? product.imageUrl
                              : 'https://picsum.photos/seed/${product.id}/300/300',
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => Container(
                            color: _kChip,
                            child: Icon(Icons.image_outlined,
                                size: 36, color: _kSubtext),
                          ),
                        ),
                        // Sold-out overlay
                        if (_outOfStock)
                          Container(
                            color: Colors.black.withValues(alpha: 0.50),
                            child: Center(
                              child: Container(
                                padding: const EdgeInsets.symmetric(
                                    horizontal: 12, vertical: 5),
                                decoration: BoxDecoration(
                                  color: _kFlash,
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text('SOLD OUT',
                                    style: _pjs(
                                        size: 11,
                                        weight: FontWeight.w800,
                                        color: Colors.white)),
                              ),
                            ),
                          ),
                      ],
                    ),
                  ),
                ),
                // Info
                Expanded(
                  child: Padding(
                    padding: const EdgeInsets.all(10),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          product.name.isNotEmpty
                              ? product.name
                              : 'Product #${product.id}',
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: _pjs(
                              size: 12, weight: FontWeight.w600, height: 1.35),
                        ),
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            Icon(Icons.star_rounded,
                                size: 12, color: Colors.amber.shade500),
                            const SizedBox(width: 2),
                            Text(product.rating.toStringAsFixed(1),
                                style: _pjs(
                                    size: 11,
                                    weight: FontWeight.w700,
                                    color: _kSecondary)),
                          ],
                        ),
                        const Spacer(),
                        Text(
                          product.price > 0
                              ? '₱${product.price.toStringAsFixed(0)}'
                              : '₱849',
                          style: _pjs(
                              size: 16,
                              weight: FontWeight.w800,
                              color: _kPrimary),
                        ),
                        const SizedBox(height: 6),
                        // Add to Cart button
                        SizedBox(
                          width: double.infinity,
                          height: 32,
                          child: ElevatedButton(
                            onPressed: _outOfStock ? null : onAddToCart,
                            style: ElevatedButton.styleFrom(
                              backgroundColor:
                                  _outOfStock ? _kBorder : _kPrimary,
                              foregroundColor: Colors.white,
                              elevation: 0,
                              shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(8)),
                              padding: EdgeInsets.zero,
                            ),
                            child: Text(
                              _outOfStock ? 'Sold Out' : 'Add to Cart',
                              style: _pjs(
                                  size: 11,
                                  weight: FontWeight.w700,
                                  color:
                                      _outOfStock ? _kSubtext : Colors.white),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),

            // Heart icon (top-right)
            if (!selectMode)
              Positioned(
                top: 8,
                right: 8,
                child: GestureDetector(
                  onTap: onRemove,
                  child: Container(
                    width: 30,
                    height: 30,
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.92),
                      shape: BoxShape.circle,
                      boxShadow: const [
                        BoxShadow(
                            color: Color(0x1A000000),
                            blurRadius: 4,
                            offset: Offset(0, 1)),
                      ],
                    ),
                    child: const Icon(Icons.favorite, size: 16, color: _kFlash),
                  ),
                ),
              ),

            // Select checkbox (top-left when in select mode)
            if (selectMode)
              Positioned(
                top: 8,
                left: 8,
                child: GestureDetector(
                  onTap: onSelectToggle,
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 150),
                    width: 24,
                    height: 24,
                    decoration: BoxDecoration(
                      color: isSelected ? _kPrimary : Colors.white,
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(
                        color: isSelected ? _kPrimary : _kBorder,
                        width: 1.5,
                      ),
                      boxShadow: const [
                        BoxShadow(
                            color: Color(0x1A000000),
                            blurRadius: 4,
                            offset: Offset(0, 1)),
                      ],
                    ),
                    child: isSelected
                        ? const Icon(Icons.check, size: 14, color: Colors.white)
                        : null,
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}

// ── Variant Sheet (lightweight, for wishlist add-to-cart) ─────────────────────
class _WishlistVariantSheet extends StatefulWidget {
  const _WishlistVariantSheet({
    required this.product,
    required this.onConfirm,
  });
  final Product product;
  final void Function(int qty) onConfirm;

  @override
  State<_WishlistVariantSheet> createState() => _WishlistVariantSheetState();
}

class _WishlistVariantSheetState extends State<_WishlistVariantSheet> {
  String _color = 'Purple';
  String _size = 'M';
  int _qty = 1;

  static const _colors = ['Purple', 'Charcoal', 'Oat Cream'];
  static const _sizes = ['S', 'M', 'L', 'XL'];

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding:
          EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
      child: Container(
        padding: const EdgeInsets.fromLTRB(20, 6, 20, 32),
        decoration: const BoxDecoration(
          color: _kCard,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
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
                    color: _kBorder, borderRadius: BorderRadius.circular(9999)),
              ),
            ),
            // Product mini header
            Row(
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(10),
                  child: Image.network(
                    widget.product.imageUrl.isNotEmpty
                        ? widget.product.imageUrl
                        : 'https://picsum.photos/seed/${widget.product.id}/80/80',
                    width: 60,
                    height: 60,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => Container(
                      width: 60,
                      height: 60,
                      color: _kChip,
                      child: Icon(Icons.image_outlined, color: _kSubtext),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        widget.product.name.isNotEmpty
                            ? widget.product.name
                            : 'Product #${widget.product.id}',
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: _pjs(size: 13, weight: FontWeight.w700),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        widget.product.price > 0
                            ? '₱${widget.product.price.toStringAsFixed(0)}'
                            : '₱849',
                        style: _pjs(
                            size: 15,
                            weight: FontWeight.w800,
                            color: _kPrimary),
                      ),
                    ],
                  ),
                ),
                GestureDetector(
                  onTap: () => Navigator.pop(context),
                  child: Container(
                    width: 30,
                    height: 30,
                    decoration:
                        BoxDecoration(color: _kSurface, shape: BoxShape.circle),
                    child: Icon(Icons.close, size: 15, color: _kSubtext),
                  ),
                ),
              ],
            ),
            const Divider(color: _kBorder, height: 24),
            // Color
            Row(
              children: [
                Text('Color', style: _pjs(size: 13, weight: FontWeight.w700)),
                const Spacer(),
                Text(_color,
                    style: _pjs(
                        size: 12, color: _kSecondary, weight: FontWeight.w600)),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: _colors.map((c) {
                final sel = _color == c;
                return Expanded(
                  child: GestureDetector(
                    onTap: () => setState(() => _color = c),
                    child: Container(
                      margin: const EdgeInsets.only(right: 8),
                      padding: const EdgeInsets.symmetric(vertical: 8),
                      decoration: BoxDecoration(
                        color: sel ? _kChip : _kSurface,
                        borderRadius: BorderRadius.circular(9999),
                        border: Border.all(
                          color: sel ? _kSecondary : _kBorder,
                          width: sel ? 2 : 1,
                        ),
                      ),
                      alignment: Alignment.center,
                      child: Text(c,
                          style: _pjs(
                              size: 11,
                              weight: FontWeight.w600,
                              color: sel ? _kSecondary : _kText)),
                    ),
                  ),
                );
              }).toList(),
            ),
            const SizedBox(height: 14),
            // Size
            Row(
              children: [
                Text('Size', style: _pjs(size: 13, weight: FontWeight.w700)),
                const Spacer(),
                Text(_size,
                    style: _pjs(
                        size: 12, color: _kSecondary, weight: FontWeight.w600)),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: _sizes.map((s) {
                final sel = _size == s;
                return Expanded(
                  child: GestureDetector(
                    onTap: () => setState(() => _size = s),
                    child: Container(
                      margin: const EdgeInsets.only(right: 8),
                      height: 38,
                      decoration: BoxDecoration(
                        color: sel ? _kPrimary : _kSurface,
                        borderRadius: BorderRadius.circular(10),
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
            const SizedBox(height: 14),
            // Quantity
            Row(
              children: [
                Text('Quantity',
                    style: _pjs(size: 13, weight: FontWeight.w700)),
                const Spacer(),
                _QtyBtn(
                  icon: Icons.remove,
                  onTap: () {
                    if (_qty > 1) setState(() => _qty--);
                  },
                ),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  child: Text('$_qty',
                      style: _pjs(size: 16, weight: FontWeight.w800)),
                ),
                _QtyBtn(
                  icon: Icons.add,
                  onTap: () {
                    if (_qty < 5) setState(() => _qty++);
                  },
                ),
              ],
            ),
            const SizedBox(height: 20),
            // Confirm
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: () {
                  Navigator.pop(context);
                  widget.onConfirm(_qty);
                },
                icon: const Icon(Icons.shopping_cart_outlined,
                    size: 18, color: Colors.white),
                label: Text('Add to Cart',
                    style: _pjs(
                        size: 15,
                        weight: FontWeight.w700,
                        color: Colors.white)),
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
    );
  }
}

// ── Qty button ────────────────────────────────────────────────────────────────
class _QtyBtn extends StatelessWidget {
  const _QtyBtn({required this.icon, required this.onTap});
  final IconData icon;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 32,
        height: 32,
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
