import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

// ── Color tokens ──────────────────────────────────────────────────────────────
const _kPrimary = Color(0xFF34175B);
const _kSecondary = Color(0xFF6B38D4);
const _kFlash = Color(0xFFFF3B5C);
const _kGreen = Color(0xFF1DB966);
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
}) =>
    GoogleFonts.plusJakartaSans(
        fontSize: size, fontWeight: weight, color: color, height: height);

// ── Notification types ────────────────────────────────────────────────────────
enum _NType { order, promo, account, delivery, system }

class _NotifItem {
  const _NotifItem({
    required this.id,
    required this.type,
    required this.title,
    required this.body,
    required this.time,
    this.isRead = false,
  });
  final String id;
  final _NType type;
  final String title;
  final String body;
  final String time;
  final bool isRead;

  _NotifItem copyWith({bool? isRead}) => _NotifItem(
        id: id,
        type: type,
        title: title,
        body: body,
        time: time,
        isRead: isRead ?? this.isRead,
      );
}

// ── Sample data ────────────────────────────────────────────────────────────────
final _sampleNotifs = <_NotifItem>[
  _NotifItem(
    id: 'n1',
    type: _NType.delivery,
    title: 'Your order is out for delivery!',
    body: 'Rider Mark V. is on the way. Order #MM-89241029 will arrive today.',
    time: '2 min ago',
    isRead: false,
  ),
  _NotifItem(
    id: 'n2',
    type: _NType.order,
    title: 'Order confirmed',
    body: 'mimoo Acid-Wash Hoodie × 1 has been confirmed by the seller.',
    time: '1h ago',
    isRead: false,
  ),
  _NotifItem(
    id: 'n3',
    type: _NType.promo,
    title: '⚡ Flash Sale starts in 30 minutes!',
    body:
        'Up to 70% off on selected items. Add to cart now before stocks run out.',
    time: '2h ago',
    isRead: false,
  ),
  _NotifItem(
    id: 'n4',
    type: _NType.order,
    title: 'Seller is preparing your order',
    body: 'StyleTrend Boutique has started packing your Lilac Knit Cardigan.',
    time: '3h ago',
    isRead: true,
  ),
  _NotifItem(
    id: 'n5',
    type: _NType.promo,
    title: 'You have a ₱150 voucher waiting!',
    body: 'Your Welcome Voucher expires in 2 days. Use it before it\'s gone.',
    time: 'Yesterday',
    isRead: true,
  ),
  _NotifItem(
    id: 'n6',
    type: _NType.account,
    title: 'Email verified successfully',
    body: 'Your mimoo account is now fully active. Start shopping!',
    time: 'Yesterday',
    isRead: true,
  ),
  _NotifItem(
    id: 'n7',
    type: _NType.order,
    title: 'Rate your purchase',
    body: 'How was your mimoo Retro Runner Sneakers? Share your experience.',
    time: '2 days ago',
    isRead: true,
  ),
  _NotifItem(
    id: 'n8',
    type: _NType.system,
    title: 'App updated to v2.4',
    body:
        'Improved search, faster checkout, and new chat features. Tap to see what\'s new.',
    time: '3 days ago',
    isRead: true,
  ),
];

// ── Page ───────────────────────────────────────────────────────────────────────
class NotificationsPage extends StatefulWidget {
  const NotificationsPage({super.key});

  @override
  State<NotificationsPage> createState() => _NotificationsPageState();
}

class _NotificationsPageState extends State<NotificationsPage> {
  late final List<_NotifItem> _notifs;
  String _filter = 'All';
  final _filters = ['All', 'Orders', 'Promos', 'Account'];

  @override
  void initState() {
    super.initState();
    _notifs = List.of(_sampleNotifs);
  }

  int get _unreadCount => _notifs.where((n) => !n.isRead).length;

  List<_NotifItem> get _filtered {
    if (_filter == 'Orders') {
      return _notifs
          .where((n) => n.type == _NType.order || n.type == _NType.delivery)
          .toList();
    }
    if (_filter == 'Promos') {
      return _notifs.where((n) => n.type == _NType.promo).toList();
    }
    if (_filter == 'Account') {
      return _notifs
          .where((n) => n.type == _NType.account || n.type == _NType.system)
          .toList();
    }
    return _notifs;
  }

  void _markAllRead() => setState(() {
        for (var i = 0; i < _notifs.length; i++) {
          _notifs[i] = _notifs[i].copyWith(isRead: true);
        }
      });

  void _markRead(String id) {
    final idx = _notifs.indexWhere((n) => n.id == id);
    if (idx != -1 && !_notifs[idx].isRead) {
      setState(() => _notifs[idx] = _notifs[idx].copyWith(isRead: true));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kSurface,
      appBar: _buildAppBar(),
      body: Column(
        children: [
          _buildFilterRow(),
          Expanded(
            child: _filtered.isEmpty
                ? _buildEmpty()
                : ListView.separated(
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    itemCount: _filtered.length,
                    separatorBuilder: (_, __) => const Divider(
                        height: 1, color: _kBorder, indent: 72, endIndent: 16),
                    itemBuilder: (_, i) => _NotifTile(
                      item: _filtered[i],
                      onTap: () => _markRead(_filtered[i].id),
                    ),
                  ),
          ),
        ],
      ),
    );
  }

  AppBar _buildAppBar() {
    return AppBar(
      backgroundColor: _kPrimary,
      elevation: 0,
      iconTheme: const IconThemeData(color: Colors.white),
      title: Text('Notifications',
          style: _pjs(size: 17, weight: FontWeight.w800, color: Colors.white)),
      actions: [
        if (_unreadCount > 0)
          TextButton(
            onPressed: _markAllRead,
            child: Text('Mark all read',
                style: _pjs(
                    size: 13,
                    weight: FontWeight.w600,
                    color: Colors.white.withValues(alpha: 0.85))),
          ),
        const SizedBox(width: 4),
      ],
    );
  }

  Widget _buildFilterRow() {
    return Container(
      color: _kCard,
      padding: const EdgeInsets.fromLTRB(16, 10, 16, 10),
      child: Row(
        children: _filters.map((f) {
          final sel = _filter == f;
          return Padding(
            padding: const EdgeInsets.only(right: 8),
            child: GestureDetector(
              onTap: () => setState(() => _filter = f),
              child: Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
                decoration: BoxDecoration(
                  color: sel ? _kPrimary : _kSurface,
                  borderRadius: BorderRadius.circular(9999),
                ),
                child: Text(f,
                    style: _pjs(
                        size: 13,
                        weight: FontWeight.w600,
                        color: sel ? Colors.white : _kText)),
              ),
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildEmpty() {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.notifications_none_outlined, size: 64, color: _kBorder),
          const SizedBox(height: 12),
          Text('No notifications',
              style: _pjs(size: 16, weight: FontWeight.w700, color: _kSubtext)),
          const SizedBox(height: 6),
          Text('You\'re all caught up!',
              style: _pjs(size: 13, color: _kSubtext)),
        ],
      ),
    );
  }
}

// ── Notification Tile ─────────────────────────────────────────────────────────
class _NotifTile extends StatelessWidget {
  const _NotifTile({required this.item, required this.onTap});
  final _NotifItem item;
  final VoidCallback onTap;

  IconData get _icon {
    switch (item.type) {
      case _NType.order:
        return Icons.shopping_bag_outlined;
      case _NType.delivery:
        return Icons.delivery_dining_outlined;
      case _NType.promo:
        return Icons.local_offer_outlined;
      case _NType.account:
        return Icons.person_outline;
      case _NType.system:
        return Icons.info_outline;
    }
  }

  Color get _iconBg {
    switch (item.type) {
      case _NType.order:
        return _kChip;
      case _NType.delivery:
        return const Color(0xFFE8F8EF);
      case _NType.promo:
        return const Color(0xFFFFF0F3);
      case _NType.account:
        return _kChip;
      case _NType.system:
        return const Color(0xFFF0F4FF);
    }
  }

  Color get _iconColor {
    switch (item.type) {
      case _NType.order:
        return _kSecondary;
      case _NType.delivery:
        return _kGreen;
      case _NType.promo:
        return _kFlash;
      case _NType.account:
        return _kPrimary;
      case _NType.system:
        return _kSecondary;
    }
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        color: item.isRead ? _kCard : _kChip.withValues(alpha: 0.4),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Icon badge
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: _iconBg,
                shape: BoxShape.circle,
              ),
              child: Icon(_icon, size: 20, color: _iconColor),
            ),
            const SizedBox(width: 12),
            // Content
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(item.title,
                            style: _pjs(
                                size: 13,
                                weight: item.isRead
                                    ? FontWeight.w500
                                    : FontWeight.w700)),
                      ),
                      const SizedBox(width: 8),
                      Text(item.time, style: _pjs(size: 10, color: _kSubtext)),
                    ],
                  ),
                  const SizedBox(height: 3),
                  Text(item.body,
                      style: _pjs(size: 12, color: _kSubtext, height: 1.4),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis),
                ],
              ),
            ),
            // Unread dot
            if (!item.isRead) ...[
              const SizedBox(width: 8),
              Container(
                width: 8,
                height: 8,
                margin: const EdgeInsets.only(top: 4),
                decoration: const BoxDecoration(
                    color: _kSecondary, shape: BoxShape.circle),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
