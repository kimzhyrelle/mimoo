import 'package:flutter/material.dart';
import '../core/theme.dart';

class MimooAppBar extends StatelessWidget implements PreferredSizeWidget {
  final bool chatBadge;
  const MimooAppBar({super.key, this.chatBadge = true});
  @override
  Size get preferredSize => const Size.fromHeight(64);
  @override
  Widget build(BuildContext context) => AppBar(
        backgroundColor: MC.header,
        toolbarHeight: 64,
        automaticallyImplyLeading: false,
        titleSpacing: 16,
        title: Row(children: [
          Text('mimoo', style: MT.headlineLg.copyWith(color: Colors.white, fontWeight: FontWeight.w800)),
          Container(margin: const EdgeInsets.only(left: 2, top: 8), width: 8, height: 8, decoration: const BoxDecoration(color: MC.tertiaryContainer, shape: BoxShape.circle)),
          const SizedBox(width: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
            decoration: BoxDecoration(color: MC.secondaryFixed, borderRadius: BorderRadius.circular(99)),
            child: Text('RIDER', style: MT.badge.copyWith(color: const Color(0xFF24005B))),
          ),
        ]),
        actions: [
          IconButton(
            tooltip: 'Notifications',
            onPressed: () => Navigator.pushNamed(context, '/notifications'),
            icon: const Icon(Icons.notifications_none, color: Colors.white70),
          ),
          Stack(alignment: Alignment.center, children: [
            IconButton(onPressed: () => Navigator.pushNamed(context, '/chat'), icon: const Icon(Icons.chat_bubble, color: Colors.white70)),
            if (chatBadge) Positioned(top: 10, right: 10, child: Container(width: 10, height: 10, decoration: BoxDecoration(color: MC.tertiaryContainer, shape: BoxShape.circle, border: Border.all(color: MC.header, width: 2)))),
          ]),
          const Padding(padding: EdgeInsets.only(right: 12), child: CircleAvatar(radius: 16, backgroundColor: MC.primary, child: Icon(Icons.person, size: 18, color: Colors.white))),
        ],
      );
}

class MimooBottomNav extends StatelessWidget {
  final int index;
  final ValueChanged<int> onTap;
  const MimooBottomNav({super.key, required this.index, required this.onTap});
  static const _items = [
    (Icons.home_outlined, Icons.home, 'Home'),
    (Icons.local_shipping_outlined, Icons.local_shipping, 'Jobs'),
    (Icons.schedule_outlined, Icons.schedule, 'History'),
    (Icons.payments_outlined, Icons.payments, 'Earnings'),
    (Icons.account_circle_outlined, Icons.account_circle, 'Account'),
  ];
  @override
  Widget build(BuildContext context) => Container(
        decoration: BoxDecoration(color: MC.lowest.withValues(alpha: 0.95), boxShadow: [BoxShadow(color: MC.header.withValues(alpha: 0.08), blurRadius: 16, offset: const Offset(0, -4))]),
        child: SafeArea(
          top: false,
          child: SizedBox(
            height: 68,
            child: Row(mainAxisAlignment: MainAxisAlignment.spaceAround, children: [
              for (var i = 0; i < _items.length; i++)
                InkWell(
                  borderRadius: BorderRadius.circular(99),
                  onTap: () => onTap(i),
                  child: Container(
                    constraints: const BoxConstraints(minWidth: 60, minHeight: 48),
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                    decoration: BoxDecoration(color: i == index ? MC.highest : Colors.transparent, borderRadius: BorderRadius.circular(99)),
                    child: Column(mainAxisSize: MainAxisSize.min, children: [
                      Icon(i == index ? _items[i].$2 : _items[i].$1, size: 22, color: i == index ? MC.primaryContainer : MC.onSurfaceVariant),
                      Text(_items[i].$3, style: MT.labelMd.copyWith(color: i == index ? MC.primaryContainer : MC.onSurfaceVariant, fontWeight: i == index ? FontWeight.w700 : FontWeight.w600)),
                    ]),
                  ),
                ),
            ]),
          ),
        ),
      );
}
