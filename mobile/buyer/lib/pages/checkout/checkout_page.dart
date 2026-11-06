import 'dart:async';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../orders/orders_page.dart';
import '../../state/auth_service.dart';
import '../../state/cart_provider.dart';
import '../../services/order_repository.dart';
import '../../services/api_service.dart';

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
}) =>
    GoogleFonts.plusJakartaSans(
        fontSize: size,
        fontWeight: weight,
        color: color,
        height: height,
        decoration: decoration);

class CheckoutPage extends StatefulWidget {
  const CheckoutPage({super.key});

  @override
  State<CheckoutPage> createState() => _CheckoutPageState();
}

class _CheckoutPageState extends State<CheckoutPage> {
  bool _showDetails = true;
  // 0 = GCash (placeholder), 1 = COD (default, only working method)
  int _selectedPayment = 1;
  final _noteCtrl = TextEditingController();
  bool _placing = false;

  // Shipping fields — pre-filled from buyer profile, editable
  late final TextEditingController _nameCtrl;
  late final TextEditingController _addressCtrl;
  late final TextEditingController _phoneCtrl;

  // One idempotency key per checkout page instance (prevents double-tap)
  late final String _idempotencyKey;

  @override
  void initState() {
    super.initState();
    final auth = AuthService.instance;
    _nameCtrl = TextEditingController(text: auth.buyerName ?? '');
    _addressCtrl = TextEditingController(text: auth.defaultShippingAddress);
    _phoneCtrl = TextEditingController(text: auth.contactNumber ?? '');
    // Unique key for this checkout attempt — reused on retry, new on fresh open
    _idempotencyKey =
        '${auth.buyerId ?? '0'}-${DateTime.now().millisecondsSinceEpoch}';
  }

  @override
  void dispose() {
    _noteCtrl.dispose();
    _nameCtrl.dispose();
    _addressCtrl.dispose();
    _phoneCtrl.dispose();
    super.dispose();
  }

  // ── Helpers ───────────────────────────────────────────────────────────────

  String get _paymentMethod => _selectedPayment == 0 ? 'gcash' : 'cod';

  /// Total from real cart items (no shipping/voucher/VAT — matches server)
  double get _merchandiseTotal {
    final cart = CartProvider.defaultCart;
    return cart.dbItems.fold(0.0, (s, i) => s + i.lineTotal);
  }

  List<int> get _selectedCartItemIds {
    // Use all valid items in the cart
    return CartProvider.defaultCart.dbItems
        .where((i) => i.isValid)
        .map((i) => i.id)
        .toList();
  }

  // ── Place order ──────────────────────────────────────────────────────────

  Future<void> _placeOrder() async {
    // GCash guard — server also rejects it, but we catch it early
    if (_selectedPayment == 0) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(
          'GCash is not available yet. Please choose Cash on Delivery.',
          style: _pjs(size: 13, color: Colors.white),
        ),
        backgroundColor: Colors.red.shade700,
        behavior: SnackBarBehavior.floating,
        duration: const Duration(seconds: 4),
      ));
      return;
    }

    final name = _nameCtrl.text.trim();
    final address = _addressCtrl.text.trim();
    final phone = _phoneCtrl.text.trim();

    if (name.isEmpty || address.isEmpty || phone.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text('Please fill in all shipping details.',
            style: _pjs(size: 13, color: Colors.white)),
        backgroundColor: Colors.red.shade700,
        behavior: SnackBarBehavior.floating,
      ));
      return;
    }

    setState(() => _placing = true);

    try {
      final result = await OrderRepository.instance.checkout(
        paymentMethod: _paymentMethod,
        shippingName: name,
        shippingAddress: address,
        shippingPhone: phone,
        idempotencyKey: _idempotencyKey,
        cartItemIds: _selectedCartItemIds,
      );

      if (!mounted) return;

      // Refresh cart (now empty for checked-out items)
      await CartProvider.defaultCart.refresh();

      if (!mounted) return;

      // Show success dialog
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (_) => AlertDialog(
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          icon: const Icon(Icons.check_circle_outline,
              color: Color(0xFF1DB966), size: 52),
          title: Text('Order Placed! 🎉',
              style: _pjs(size: 18, weight: FontWeight.w800),
              textAlign: TextAlign.center),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                'Order ${result.orderNumber}',
                style:
                    _pjs(size: 15, weight: FontWeight.w700, color: _kPrimary),
              ),
              const SizedBox(height: 6),
              Text(
                'Total: ₱${result.total.toStringAsFixed(2)}',
                style: _pjs(size: 14, weight: FontWeight.w600),
              ),
              const SizedBox(height: 8),
              Text(
                'We\'ll notify you once it\'s on its way!',
                style: _pjs(size: 13, color: _kSubtext),
                textAlign: TextAlign.center,
              ),
            ],
          ),
          actions: [
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () {
                  Navigator.of(context).pushAndRemoveUntil(
                    MaterialPageRoute(builder: (_) => const OrdersPage()),
                    (route) => route.isFirst,
                  );
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: _kPrimary,
                  elevation: 0,
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10)),
                ),
                child: Text('View My Orders',
                    style: _pjs(
                        size: 14,
                        weight: FontWeight.w700,
                        color: Colors.white)),
              ),
            ),
          ],
        ),
      );
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(e.message, style: _pjs(size: 13, color: Colors.white)),
        backgroundColor: Colors.red.shade700,
        behavior: SnackBarBehavior.floating,
        duration: const Duration(seconds: 6),
      ));
    } catch (_) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text('Network error. Please try again.',
            style: _pjs(size: 13, color: Colors.white)),
        backgroundColor: Colors.red.shade700,
        behavior: SnackBarBehavior.floating,
      ));
    } finally {
      if (mounted) setState(() => _placing = false);
    }
  }

  // ── Build ─────────────────────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kSurface,
      appBar: AppBar(
        backgroundColor: _kCard,
        elevation: 0,
        leading: IconButton(
          icon: Icon(Icons.arrow_back, color: _kText),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text('Instant Checkout',
            style: _pjs(size: 17, weight: FontWeight.w800, color: _kText)),
      ),
      body: ListView(
        padding: const EdgeInsets.only(bottom: 100),
        children: [
          _buildTopStrip(),
          const SizedBox(height: 10),
          _buildDeliveryAddress(),
          const SizedBox(height: 10),
          _buildPackageOverview(),
          const SizedBox(height: 10),
          // ── Shipping method (hidden — no shipping fee stored) ────────────
          // _buildShippingMethod(),
          // const SizedBox(height: 10),
          _buildPaymentMethod(),
          const SizedBox(height: 10),
          _buildOrderNotes(),
          const SizedBox(height: 10),
          _buildPaymentSummary(),
          const SizedBox(height: 10),
          _buildTrustRow(),
          const SizedBox(height: 16),
        ],
      ),
      bottomNavigationBar: _buildBottomBar(),
    );
  }

  Widget _buildTopStrip() {
    return Container(
      color: _kCard,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Row(
            children: [
              const Text('🛡️', style: TextStyle(fontSize: 14)),
              const SizedBox(width: 6),
              Text('FAST & SECURE CHECKOUT',
                  style: _pjs(
                      size: 12, weight: FontWeight.w700, color: _kPrimary)),
            ],
          ),
          // 14-min countdown removed (fake) — kept as hidden comment:
          // Container(child: Text(_countdownText ...))
        ],
      ),
    );
  }

  Widget _buildDeliveryAddress() {
    return _SCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration:
                    const BoxDecoration(color: _kChip, shape: BoxShape.circle),
                child: Icon(Icons.location_on_outlined,
                    size: 18, color: _kPrimary),
              ),
              const SizedBox(width: 10),
              Text('Delivery Address',
                  style: _pjs(size: 14, weight: FontWeight.w700)),
            ],
          ),
          const SizedBox(height: 12),
          _buildTextField(_nameCtrl, 'Full Name'),
          const SizedBox(height: 8),
          _buildTextField(_phoneCtrl, 'Phone Number',
              keyboardType: TextInputType.phone),
          const SizedBox(height: 8),
          _buildTextField(_addressCtrl, 'Full Address', maxLines: 3),
        ],
      ),
    );
  }

  Widget _buildTextField(
    TextEditingController ctrl,
    String label, {
    TextInputType? keyboardType,
    int maxLines = 1,
  }) {
    return TextField(
      controller: ctrl,
      keyboardType: keyboardType,
      maxLines: maxLines,
      style: _pjs(size: 13),
      decoration: InputDecoration(
        labelText: label,
        labelStyle: _pjs(size: 11, color: _kSubtext),
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(8),
            borderSide: const BorderSide(color: _kBorder)),
        enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(8),
            borderSide: const BorderSide(color: _kBorder)),
        focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(8),
            borderSide: const BorderSide(color: _kSecondary)),
      ),
    );
  }

  Widget _buildPackageOverview() {
    final items = CartProvider.defaultCart.dbItems;
    return _SCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                  'Package Overview (${items.length} item${items.length == 1 ? '' : 's'})',
                  style: _pjs(size: 15, weight: FontWeight.w800)),
              TextButton.icon(
                onPressed: () => setState(() => _showDetails = !_showDetails),
                icon: Icon(
                    _showDetails
                        ? Icons.keyboard_arrow_up
                        : Icons.keyboard_arrow_down,
                    size: 16,
                    color: _kSecondary),
                label: Text(_showDetails ? 'Hide details' : 'Show details',
                    style: _pjs(
                        size: 12, weight: FontWeight.w600, color: _kSecondary)),
                style: TextButton.styleFrom(
                    padding: EdgeInsets.zero,
                    minimumSize: Size.zero,
                    tapTargetSize: MaterialTapTargetSize.shrinkWrap),
              ),
            ],
          ),
          if (_showDetails && items.isNotEmpty) ...[
            const SizedBox(height: 12),
            ...items.asMap().entries.map((e) {
              final item = e.value;
              final hasImg = item.imageUrl != null && item.imageUrl!.isNotEmpty;
              return Column(
                children: [
                  if (e.key > 0) const Divider(height: 16, color: _kBorder),
                  _CheckoutItemRow(
                    imageUrl: hasImg ? item.imageUrl! : '',
                    name: item.productName,
                    variation: item.variantId ?? '',
                    price: item.unitPrice,
                    originalPrice: item.unitPrice,
                    qty: item.quantity,
                  ),
                ],
              );
            }),
          ],
          if (items.isEmpty)
            Padding(
              padding: const EdgeInsets.only(top: 8),
              child: Text('No items selected.',
                  style: _pjs(size: 13, color: _kSubtext)),
            ),
        ],
      ),
    );
  }

  Widget _buildPaymentMethod() {
    final methods = [
      (
        Icons.account_balance_wallet_outlined,
        const Color(0xFF0066CC),
        'GCash',
        'Placeholder — not available yet',
        null
      ),
      (
        Icons.money_outlined,
        _kGreen,
        'Cash on Delivery (COD)',
        'Pay in cash right upon receipt',
        null
      ),
    ];

    return _SCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Payment Method',
              style: _pjs(size: 15, weight: FontWeight.w800)),
          const SizedBox(height: 12),
          ...List.generate(methods.length, (i) {
            final m = methods[i];
            final sel = _selectedPayment == i;
            return GestureDetector(
              onTap: () => setState(() => _selectedPayment = i),
              child: Container(
                margin: const EdgeInsets.only(bottom: 8),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: sel ? _kChip : _kSurface,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: sel ? _kSecondary : _kBorder),
                ),
                child: Row(
                  children: [
                    Container(
                      width: 36,
                      height: 36,
                      decoration: BoxDecoration(
                        color: m.$2.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Icon(m.$1, size: 18, color: m.$2),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(m.$3,
                              style: _pjs(size: 13, weight: FontWeight.w700)),
                          Text(m.$4, style: _pjs(size: 11, color: _kSubtext)),
                        ],
                      ),
                    ),
                    Icon(
                      sel
                          ? Icons.radio_button_checked
                          : Icons.radio_button_unchecked,
                      size: 18,
                      color: sel ? _kSecondary : _kSubtext,
                    ),
                  ],
                ),
              ),
            );
          }),
        ],
      ),
    );
  }

  Widget _buildOrderNotes() {
    return _SCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Order Notes for Courier',
              style: _pjs(size: 15, weight: FontWeight.w800)),
          const SizedBox(height: 10),
          TextField(
            controller: _noteCtrl,
            minLines: 2,
            maxLines: 4,
            style: _pjs(size: 13),
            decoration: InputDecoration(
              hintText: 'e.g. Please ring the bell, leave at door...',
              hintStyle: _pjs(size: 12, color: _kSubtext),
              contentPadding: const EdgeInsets.all(12),
              border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: _kBorder)),
              enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: _kBorder)),
              focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: _kSecondary)),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPaymentSummary() {
    final total = _merchandiseTotal;
    return _SCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Payment Summary',
              style: _pjs(size: 15, weight: FontWeight.w800)),
          const SizedBox(height: 12),
          _SRow('Merchandise Subtotal', '₱${total.toStringAsFixed(2)}'),
          // ── Hidden until shipping/voucher columns exist ────────────────
          // const SizedBox(height: 6),
          // _SRow('Shipping Subtotal', '₱80'),
          // Row(children: [Text('Shipping Discount'...), Text('-₱80')]),
          // Row(children: [Text('Mimoo Welcome Voucher'), Text('-₱150')]),
          // ────────────────────────────────────────────────────────────────
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 10),
            child: Divider(color: _kBorder),
          ),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('Total Payment',
                  style: _pjs(size: 15, weight: FontWeight.w800)),
              Text('₱${total.toStringAsFixed(2)}',
                  style:
                      _pjs(size: 20, weight: FontWeight.w800, color: _kFlash)),
            ],
          ),
          // VAT line hidden (not stored) — const SizedBox(height: 4),
          // Text('VAT inclusive', ...),
        ],
      ),
    );
  }

  Widget _buildTrustRow() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.shield_outlined, size: 14, color: _kSubtext),
          const SizedBox(width: 4),
          Text('Mimoo Buyer Guarantee · 256-Bit SSL Secured',
              style: _pjs(size: 11, color: _kSubtext)),
        ],
      ),
    );
  }

  Widget _buildBottomBar() {
    final total = _merchandiseTotal;
    return Container(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 20),
      decoration: const BoxDecoration(
        color: _kCard,
        boxShadow: [
          BoxShadow(
              color: Color(0x1A34175B), blurRadius: 12, offset: Offset(0, -4))
        ],
      ),
      child: SafeArea(
        child: Row(
          children: [
            Expanded(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Total Amount', style: _pjs(size: 12, color: _kSubtext)),
                  Text('₱${total.toStringAsFixed(2)}',
                      style: _pjs(size: 20, weight: FontWeight.w800)),
                ],
              ),
            ),
            const SizedBox(width: 12),
            ElevatedButton.icon(
              onPressed: _placing ? null : _placeOrder,
              icon: _placing
                  ? const SizedBox(
                      width: 16,
                      height: 16,
                      child: CircularProgressIndicator(
                          strokeWidth: 2, color: Colors.white))
                  : const Icon(Icons.arrow_forward,
                      size: 16, color: Colors.white),
              label: Text('Place Order',
                  style: _pjs(
                      size: 15, weight: FontWeight.w700, color: Colors.white)),
              style: ElevatedButton.styleFrom(
                backgroundColor: _kPrimary,
                disabledBackgroundColor: _kPrimary.withValues(alpha: 0.5),
                foregroundColor: Colors.white,
                elevation: 0,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
                minimumSize: const Size(0, 52),
                padding: const EdgeInsets.symmetric(horizontal: 24),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ── Shared card wrapper ───────────────────────────────────────────────────────
class _SCard extends StatelessWidget {
  const _SCard({required this.child});
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: _kCard,
        borderRadius: BorderRadius.circular(12),
        boxShadow: const [
          BoxShadow(
              color: Color(0x0D34175B), blurRadius: 8, offset: Offset(0, 2))
        ],
      ),
      child: child,
    );
  }
}

class _CheckoutItemRow extends StatelessWidget {
  const _CheckoutItemRow({
    required this.imageUrl,
    required this.name,
    required this.variation,
    required this.price,
    required this.originalPrice,
    required this.qty,
  });
  final String imageUrl, name, variation;
  final double price, originalPrice;
  final int qty;

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        ClipRRect(
          borderRadius: BorderRadius.circular(8),
          child: imageUrl.isNotEmpty
              ? Image.network(
                  imageUrl,
                  width: 60,
                  height: 60,
                  fit: BoxFit.cover,
                  headers: const {'Accept': 'image/*'},
                  errorBuilder: (_, __, ___) => Container(
                      width: 60,
                      height: 60,
                      color: _kChip,
                      child: Icon(Icons.image_outlined, color: _kSubtext)),
                )
              : Container(
                  width: 60,
                  height: 60,
                  color: _kChip,
                  child: Icon(Icons.storefront_outlined, color: _kSubtext)),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(name,
                  style: _pjs(size: 13, weight: FontWeight.w700),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis),
              if (variation.isNotEmpty)
                Text(variation, style: _pjs(size: 11, color: _kSubtext)),
              const SizedBox(height: 4),
              Text('₱${price.toStringAsFixed(0)}',
                  style: _pjs(
                      size: 14, weight: FontWeight.w800, color: _kSecondary)),
            ],
          ),
        ),
        Text('x$qty', style: _pjs(size: 13, weight: FontWeight.w600)),
      ],
    );
  }
}

class _SRow extends StatelessWidget {
  const _SRow(this.label, this.value);
  final String label, value;

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: _pjs(size: 13, weight: FontWeight.w500)),
        Text(value, style: _pjs(size: 13, weight: FontWeight.w600)),
      ],
    );
  }
}
