import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

// ── Color tokens ─────────────────────────────────────────────────────────────
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

// ── Data models ───────────────────────────────────────────────────────────────
class _Conversation {
  const _Conversation({
    required this.id,
    required this.name,
    required this.avatar,
    required this.type,
    required this.lastMessage,
    required this.time,
    this.unread = 0,
    this.sub,
    this.isLive = false,
    this.liveLabel,
    this.tracking,
  });
  final String id, name, avatar, type, lastMessage, time;
  final int unread;
  final String? sub, liveLabel, tracking;
  final bool isLive;
}

const _convos = [
  _Conversation(
    id: 'rider1',
    name: 'Mark V.',
    avatar: 'MV',
    type: 'Courier',
    lastMessage: 'Order #MM-9821: Hi ma\'am! I have arrived...',
    time: '2m ago',
    unread: 1,
    sub: 'Tap to see pin and parking location',
    isLive: true,
    liveLabel: 'Lobby Gate',
  ),
  _Conversation(
    id: 'seller1',
    name: 'mimoo Official Flags...',
    avatar: 'm',
    type: 'MALL',
    lastMessage: 'Your Acid-Wash Hoodie has been packed ...',
    time: '15m ago',
    unread: 2,
    tracking: 'PH-EX9930219',
  ),
  _Conversation(
    id: 'seller2',
    name: 'StyleTrend Boutique',
    avatar: 'ST',
    type: '',
    lastMessage: 'Hello Jane! The lilac knit cardigan is back in ...',
    time: '1h ago',
    sub: 'Claim 10% voucher inside',
  ),
  _Conversation(
    id: 'support',
    name: 'mimoo Custome...',
    avatar: 'mC',
    type: 'SUPPORT',
    lastMessage: 'Ticket #TK-4819: Your refund request for ₱3...',
    time: 'Yesterday',
    sub: 'Resolved • Rate experience',
  ),
  _Conversation(
    id: 'seller3',
    name: 'TechZone Direct',
    avatar: 'TZ',
    type: 'Merchant',
    lastMessage: 'Inquiry on Wireless Noise-canceling Earbud...',
    time: '2d ago',
    sub: 'Product link attached',
  ),
];

// ── Chat Page (Messages List) ─────────────────────────────────────────────────
class ChatPage extends StatefulWidget {
  const ChatPage({super.key});

  @override
  State<ChatPage> createState() => _ChatPageState();
}

class _ChatPageState extends State<ChatPage> {
  String _filter = 'All';
  final _searchCtrl = TextEditingController();

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kSurface,
      body: SafeArea(
        child: Column(
          children: [
            _buildHeader(),
            _buildFilterChips(),
            _buildLiveBanner(),
            Expanded(child: _buildConvoList()),
          ],
        ),
      ),
    );
  }

  // ── Header ──────────────────────────────────────────────────────────────────
  Widget _buildHeader() {
    return Container(
      color: _kCard,
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
      child: Row(
        children: [
          Text('mimoo',
              style: _pjs(size: 22, weight: FontWeight.w800, color: _kPrimary)),
          const SizedBox(width: 12),
          // Search pill
          Expanded(
            child: Container(
              height: 36,
              decoration: BoxDecoration(
                color: _kChip,
                borderRadius: BorderRadius.circular(9999),
              ),
              child: Row(
                children: [
                  const SizedBox(width: 10),
                  Icon(Icons.search, size: 16, color: _kSubtext),
                  const SizedBox(width: 6),
                  Text('Find in Mimoo...',
                      style: _pjs(size: 12, color: _kSubtext)),
                ],
              ),
            ),
          ),
          const SizedBox(width: 10),
          // Bell
          Stack(
            children: [
              Icon(Icons.notifications_none_outlined, color: _kText, size: 24),
              Positioned(
                right: 0,
                top: 0,
                child: Container(
                  width: 8,
                  height: 8,
                  decoration: const BoxDecoration(
                      color: _kFlash, shape: BoxShape.circle),
                ),
              ),
            ],
          ),
          const SizedBox(width: 12),
          CircleAvatar(
            radius: 16,
            backgroundColor: _kChip,
            child: Icon(Icons.person, size: 18, color: _kPrimary),
          ),
        ],
      ),
    );
  }

  // ── Filter chips ─────────────────────────────────────────────────────────────
  Widget _buildFilterChips() {
    final filters = [
      ('All', 3, true),
      ('Riders', 0, false),
      ('Sellers', 0, false),
      ('Support', 0, false),
    ];
    return Container(
      color: _kCard,
      padding: const EdgeInsets.fromLTRB(16, 0, 16, 10),
      child: Row(
        children: filters.map((f) {
          final selected = _filter == f.$1;
          return Padding(
            padding: const EdgeInsets.only(right: 8),
            child: GestureDetector(
              onTap: () => setState(() => _filter = f.$1),
              child: Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
                decoration: BoxDecoration(
                  color: selected ? _kPrimary : _kSurface,
                  borderRadius: BorderRadius.circular(9999),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(f.$1,
                        style: _pjs(
                            size: 13,
                            weight: FontWeight.w600,
                            color: selected ? Colors.white : _kText)),
                    if (f.$2 > 0) ...[
                      const SizedBox(width: 5),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 5, vertical: 1),
                        decoration: BoxDecoration(
                          color: selected
                              ? Colors.white.withValues(alpha: 0.25)
                              : _kSecondary,
                          borderRadius: BorderRadius.circular(9999),
                        ),
                        child: Text('${f.$2}',
                            style: _pjs(
                                size: 10,
                                weight: FontWeight.w700,
                                color: selected ? Colors.white : Colors.white)),
                      ),
                    ],
                  ],
                ),
              ),
            ),
          );
        }).toList(),
      ),
    );
  }

  // ── Live delivery banner ──────────────────────────────────────────────────────
  Widget _buildLiveBanner() {
    return Container(
      margin: const EdgeInsets.fromLTRB(16, 0, 16, 8),
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
              Container(
                width: 8,
                height: 8,
                decoration:
                    const BoxDecoration(color: _kFlash, shape: BoxShape.circle),
              ),
              const SizedBox(width: 6),
              Text('LIVE DELIVERY',
                  style:
                      _pjs(size: 11, weight: FontWeight.w800, color: _kFlash)),
              Text(' • Order #MM-9821',
                  style: _pjs(size: 11, color: _kSubtext)),
              const Spacer(),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: _kChip,
                  borderRadius: BorderRadius.circular(9999),
                ),
                child: Row(
                  children: [
                    Icon(Icons.send, size: 10, color: _kSecondary),
                    const SizedBox(width: 4),
                    Text('Lobby Gate',
                        style: _pjs(
                            size: 11,
                            weight: FontWeight.w700,
                            color: _kSecondary)),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              ClipOval(
                child: Image.network(
                  'https://picsum.photos/seed/rider/60/60',
                  width: 44,
                  height: 44,
                  fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) => CircleAvatar(
                    radius: 22,
                    backgroundColor: _kChip,
                    child: Icon(Icons.delivery_dining, color: _kPrimary),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Text('Rider Mark V.',
                            style: _pjs(size: 14, weight: FontWeight.w700)),
                        const SizedBox(width: 6),
                        Icon(Icons.star_rounded,
                            size: 13, color: Colors.amber.shade500),
                        Text(' 4.9',
                            style: _pjs(size: 12, weight: FontWeight.w600)),
                      ],
                    ),
                    Text(
                        '"Arrived at building gate, ready to hand off parcel."',
                        style: _pjs(size: 11, color: _kSubtext, height: 1.4)),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: () {},
                  icon: Icon(Icons.phone_outlined, size: 14, color: _kPrimary),
                  label: Text('Direct Call',
                      style: _pjs(
                          size: 12, weight: FontWeight.w700, color: _kPrimary)),
                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(color: _kBorder),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10)),
                    minimumSize: const Size(0, 38),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: () => _openChat(context, _convos[0]),
                  icon: Icon(Icons.chat_bubble_outline,
                      size: 14, color: Colors.white),
                  label: Text('Reply Now',
                      style: _pjs(
                          size: 12,
                          weight: FontWeight.w700,
                          color: Colors.white)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _kPrimary,
                    elevation: 0,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10)),
                    minimumSize: const Size(0, 38),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // ── Conversation list ─────────────────────────────────────────────────────────
  Widget _buildConvoList() {
    return ListView.separated(
      padding: EdgeInsets.zero,
      itemCount: _convos.length,
      separatorBuilder: (_, __) =>
          const Divider(height: 1, color: _kBorder, indent: 72),
      itemBuilder: (ctx, i) => _ConvoTile(
        convo: _convos[i],
        onTap: () => _openChat(ctx, _convos[i]),
      ),
    );
  }

  void _openChat(BuildContext ctx, _Conversation convo) {
    Navigator.push(
        ctx, MaterialPageRoute(builder: (_) => _ChatDetailPage(convo: convo)));
  }
}

// ── Conversation Tile ─────────────────────────────────────────────────────────
class _ConvoTile extends StatelessWidget {
  const _ConvoTile({required this.convo, required this.onTap});
  final _Conversation convo;
  final VoidCallback onTap;

  Color get _badgeColor {
    if (convo.type == 'MALL') return _kSecondary;
    if (convo.type == 'Courier') return _kGreen;
    if (convo.type == 'SUPPORT') return Colors.orange;
    if (convo.type == 'Merchant') return _kSubtext;
    return _kSubtext;
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        color: _kCard,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Avatar
            Stack(
              children: [
                CircleAvatar(
                  radius: 24,
                  backgroundColor: _kChip,
                  child: Text(convo.avatar.substring(0, 1),
                      style: _pjs(
                          size: 16, weight: FontWeight.w700, color: _kPrimary)),
                ),
                if (convo.type == 'Courier')
                  Positioned(
                    bottom: 0,
                    right: 0,
                    child: Container(
                      width: 16,
                      height: 16,
                      decoration: BoxDecoration(
                          color: _kGreen,
                          shape: BoxShape.circle,
                          border: Border.all(color: Colors.white, width: 1.5)),
                      child: Icon(Icons.delivery_dining,
                          size: 9, color: Colors.white),
                    ),
                  ),
              ],
            ),
            const SizedBox(width: 12),
            // Content
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Text(convo.name,
                          style: _pjs(size: 14, weight: FontWeight.w700)),
                      if (convo.type.isNotEmpty) ...[
                        const SizedBox(width: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 5, vertical: 2),
                          decoration: BoxDecoration(
                            color: _badgeColor.withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Text(convo.type,
                              style: _pjs(
                                  size: 9,
                                  weight: FontWeight.w700,
                                  color: _badgeColor)),
                        ),
                      ],
                      const Spacer(),
                      Text(convo.time, style: _pjs(size: 11, color: _kSubtext)),
                    ],
                  ),
                  const SizedBox(height: 3),
                  Text(convo.lastMessage,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: _pjs(size: 12, color: _kSubtext)),
                  if (convo.sub != null) ...[
                    const SizedBox(height: 2),
                    Text(convo.sub!,
                        style: _pjs(
                            size: 11,
                            color: _kSecondary,
                            weight: FontWeight.w600)),
                  ],
                  if (convo.tracking != null) ...[
                    const SizedBox(height: 3),
                    Row(
                      children: [
                        Icon(Icons.local_shipping_outlined,
                            size: 11, color: _kSecondary),
                        const SizedBox(width: 3),
                        Text('Tracking: ${convo.tracking}',
                            style: _pjs(
                                size: 11,
                                color: _kSecondary,
                                weight: FontWeight.w600)),
                      ],
                    ),
                  ],
                ],
              ),
            ),
            if (convo.unread > 0) ...[
              const SizedBox(width: 8),
              Container(
                width: 20,
                height: 20,
                decoration:
                    const BoxDecoration(color: _kFlash, shape: BoxShape.circle),
                alignment: Alignment.center,
                child: Text('${convo.unread}',
                    style: _pjs(
                        size: 11,
                        weight: FontWeight.w800,
                        color: Colors.white)),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

// ── Chat Detail Page ──────────────────────────────────────────────────────────
class _ChatDetailPage extends StatefulWidget {
  const _ChatDetailPage({required this.convo});
  final _Conversation convo;

  @override
  State<_ChatDetailPage> createState() => _ChatDetailPageState();
}

class _ChatDetailPageState extends State<_ChatDetailPage> {
  final _msgCtrl = TextEditingController();
  final _scrollCtrl = ScrollController();

  final List<_Msg> _messages = [
    _Msg(text: 'Today, 2:18 PM', isSystem: true, time: ''),
    _Msg(
        text:
            'Messages are encrypted & monitored for safety and delivery coordination.',
        isSystem: true,
        time: ''),
    _Msg(
      text:
          'Good afternoon ma\'am Jane! I\'ve picked up your order from Manila Tech Hub and I\'m currently on Dahlia St. Estimated arrival in 10 mins.',
      isMe: false,
      time: '2:20 PM',
    ),
    _Msg(
      text:
          'Thanks Mark! Please ring bell twice or leave with the lobby security if needed.',
      isMe: true,
      time: '2:22 PM',
      isRead: true,
    ),
    _Msg(
      text: 'Hi ma\'am! I have arrived at the lobby gate with your package.',
      isMe: false,
      time: '2:28 PM',
      hasImage: true,
      imageUrl: 'https://picsum.photos/seed/package/300/200',
      imageLabel: 'Lobby Gate Drop-off',
      hasGeotag: true,
    ),
    _Msg(
      text: 'Coming down to the lobby now, thanks!',
      isMe: true,
      time: 'Just now',
      isSent: true,
    ),
  ];

  @override
  void dispose() {
    _msgCtrl.dispose();
    _scrollCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kSurface,
      appBar: _buildAppBar(),
      body: Column(
        children: [
          _buildOrderBanner(),
          Expanded(child: _buildMessages()),
          _buildQuickReplies(),
          _buildInputBar(),
        ],
      ),
    );
  }

  AppBar _buildAppBar() {
    return AppBar(
      backgroundColor: _kCard,
      elevation: 0,
      leading: IconButton(
        icon: Icon(Icons.arrow_back, color: _kText),
        onPressed: () => Navigator.pop(context),
      ),
      title: Row(
        children: [
          CircleAvatar(
            radius: 18,
            backgroundColor: _kChip,
            child: Icon(Icons.delivery_dining, size: 18, color: _kPrimary),
          ),
          const SizedBox(width: 8),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('Active Chat',
                  style:
                      _pjs(size: 15, weight: FontWeight.w800, color: _kText)),
              Row(
                children: [
                  Container(
                    width: 7,
                    height: 7,
                    decoration: const BoxDecoration(
                        color: _kGreen, shape: BoxShape.circle),
                  ),
                  const SizedBox(width: 4),
                  Text('Online • mimoo',
                      style: _pjs(size: 11, color: _kSubtext)),
                ],
              ),
            ],
          ),
        ],
      ),
      actions: [
        IconButton(
            icon: Icon(Icons.phone_outlined, color: _kText), onPressed: () {}),
        IconButton(
            icon: Icon(Icons.more_vert, color: _kText), onPressed: () {}),
        Padding(
          padding: const EdgeInsets.only(right: 12),
          child: CircleAvatar(
            radius: 16,
            backgroundColor: _kChip,
            child: Icon(Icons.person, size: 18, color: _kPrimary),
          ),
        ),
      ],
    );
  }

  Widget _buildOrderBanner() {
    return Container(
      margin: const EdgeInsets.all(12),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
            colors: [Color(0xFF2D0F52), Color(0xFF4A1C8A)]),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        children: [
          const Text('⚡', style: TextStyle(fontSize: 14)),
          const SizedBox(width: 6),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(6),
            ),
            child: Text('Arriving at Gate',
                style: _pjs(
                    size: 11, weight: FontWeight.w700, color: Colors.white)),
          ),
          const SizedBox(width: 8),
          Text('Order #MM-9821', style: _pjs(size: 12, color: Colors.white70)),
          const Spacer(),
          Icon(Icons.keyboard_arrow_up, color: Colors.white70, size: 18),
        ],
      ),
    );
  }

  Widget _buildMessages() {
    return ListView.builder(
      controller: _scrollCtrl,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      itemCount: _messages.length,
      itemBuilder: (_, i) => _buildMessage(_messages[i]),
    );
  }

  Widget _buildMessage(_Msg msg) {
    if (msg.isSystem) {
      return Padding(
        padding: const EdgeInsets.symmetric(vertical: 8),
        child: Center(
          child: Text(msg.text,
              textAlign: TextAlign.center,
              style: _pjs(size: 11, color: _kSubtext, height: 1.5)),
        ),
      );
    }

    final isMe = msg.isMe;
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        mainAxisAlignment:
            isMe ? MainAxisAlignment.end : MainAxisAlignment.start,
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          if (!isMe) ...[
            CircleAvatar(
              radius: 16,
              backgroundColor: _kChip,
              child: Icon(Icons.delivery_dining, size: 14, color: _kPrimary),
            ),
            const SizedBox(width: 8),
          ],
          Flexible(
            child: Column(
              crossAxisAlignment:
                  isMe ? CrossAxisAlignment.end : CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: isMe ? _kPrimary : _kCard,
                    borderRadius: BorderRadius.only(
                      topLeft: const Radius.circular(16),
                      topRight: const Radius.circular(16),
                      bottomLeft: Radius.circular(isMe ? 16 : 4),
                      bottomRight: Radius.circular(isMe ? 4 : 16),
                    ),
                    boxShadow: [
                      BoxShadow(
                          color: Colors.black.withValues(alpha: 0.06),
                          blurRadius: 6,
                          offset: const Offset(0, 2)),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(msg.text,
                          style: _pjs(
                              size: 13,
                              color: isMe ? Colors.white : _kText,
                              height: 1.5)),
                      if (msg.hasImage) ...[
                        const SizedBox(height: 8),
                        ClipRRect(
                          borderRadius: BorderRadius.circular(8),
                          child: Image.network(
                            msg.imageUrl!,
                            width: 220,
                            fit: BoxFit.cover,
                            errorBuilder: (_, __, ___) => Container(
                                width: 220, height: 120, color: _kChip),
                          ),
                        ),
                        if (msg.imageLabel != null) ...[
                          const SizedBox(height: 4),
                          Row(
                            children: [
                              Icon(Icons.location_on,
                                  size: 12, color: _kSubtext),
                              const SizedBox(width: 3),
                              Text(msg.imageLabel!,
                                  style: _pjs(size: 11, color: _kSubtext)),
                            ],
                          ),
                        ],
                        if (msg.hasGeotag) ...[
                          const SizedBox(height: 6),
                          Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: _kGreen.withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(Icons.verified, size: 12, color: _kGreen),
                                const SizedBox(width: 4),
                                Text('Geotag Confirmed',
                                    style: _pjs(
                                        size: 11,
                                        weight: FontWeight.w700,
                                        color: _kGreen)),
                                const SizedBox(width: 8),
                                Text('Tap to view',
                                    style: _pjs(
                                        size: 11,
                                        color: _kSecondary,
                                        decoration: TextDecoration.underline)),
                              ],
                            ),
                          ),
                        ],
                      ],
                    ],
                  ),
                ),
                const SizedBox(height: 3),
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(msg.time, style: _pjs(size: 10, color: _kSubtext)),
                    if (isMe) ...[
                      const SizedBox(width: 4),
                      if (msg.isRead)
                        Text(' • Read',
                            style: _pjs(
                                size: 10,
                                color: _kSecondary,
                                weight: FontWeight.w600))
                      else if (msg.isSent)
                        Icon(Icons.check, size: 12, color: _kSubtext),
                    ],
                  ],
                ),
              ],
            ),
          ),
          if (isMe) const SizedBox(width: 4),
        ],
      ),
    );
  }

  Widget _buildQuickReplies() {
    final replies = [
      '👋 On my way down',
      '⏳ Please wait 2 mins',
      '🔑 Leave at...'
    ];
    return Container(
      height: 40,
      color: _kCard,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        itemCount: replies.length,
        separatorBuilder: (_, __) => const SizedBox(width: 8),
        itemBuilder: (_, i) => Container(
          padding: const EdgeInsets.symmetric(horizontal: 12),
          decoration: BoxDecoration(
            color: _kChip,
            borderRadius: BorderRadius.circular(9999),
            border: Border.all(color: _kBorder),
          ),
          alignment: Alignment.center,
          child: Text(replies[i], style: _pjs(size: 12, color: _kText)),
        ),
      ),
    );
  }

  Widget _buildInputBar() {
    return Container(
      color: _kCard,
      padding: const EdgeInsets.fromLTRB(12, 8, 12, 20),
      child: Row(
        children: [
          IconButton(
            icon: Icon(Icons.add, color: _kSubtext, size: 24),
            onPressed: () {},
            padding: EdgeInsets.zero,
            constraints: const BoxConstraints(),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: _kSurface,
                borderRadius: BorderRadius.circular(9999),
                border: Border.all(color: _kBorder),
              ),
              child: Text('Type a message...',
                  style: _pjs(size: 13, color: _kSubtext)),
            ),
          ),
          const SizedBox(width: 8),
          IconButton(
            icon: Icon(Icons.camera_alt_outlined, color: _kSubtext, size: 22),
            onPressed: () {},
            padding: EdgeInsets.zero,
            constraints: const BoxConstraints(),
          ),
          const SizedBox(width: 8),
          Container(
            width: 40,
            height: 40,
            decoration:
                const BoxDecoration(color: _kPrimary, shape: BoxShape.circle),
            child: const Icon(Icons.mic, color: Colors.white, size: 20),
          ),
        ],
      ),
    );
  }
}

// ── Message model ─────────────────────────────────────────────────────────────
class _Msg {
  const _Msg({
    required this.text,
    this.isMe = false,
    this.isSystem = false,
    required this.time,
    this.isRead = false,
    this.isSent = false,
    this.hasImage = false,
    this.imageUrl,
    this.imageLabel,
    this.hasGeotag = false,
  });
  final String text, time;
  final bool isMe, isSystem, isRead, isSent, hasImage, hasGeotag;
  final String? imageUrl, imageLabel;
}
