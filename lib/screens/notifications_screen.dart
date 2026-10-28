import 'package:flutter/material.dart';
import '../core/connectivity.dart';
import '../core/theme.dart';

enum NotifKind { jobOffer, parcelAssigned, remittance, weather, rescheduled, sellerReady }
class AppNotification { final NotifKind kind; final String title, body, timeAgo, tag; final bool unread;
  const AppNotification(this.kind, this.title, this.body, this.timeAgo, {this.tag = '', this.unread = false}); }

class NotificationsScreen extends StatefulWidget {
  final List<AppNotification> items; final bool? synced; final ValueChanged<AppNotification>? onTap;
  const NotificationsScreen({super.key, required this.items, this.synced, this.onTap});
  @override State<NotificationsScreen> createState() => _NotifState();
}

class _NotifState extends State<NotificationsScreen> {
  late List<AppNotification> _items = widget.items; bool get _allRead => _items.every((n) => !n.unread);
  (IconData, Color, Color) _style(NotifKind k) => switch (k) {
    NotifKind.jobOffer => (Icons.local_fire_department, MC.coralSoft, MC.coralDark),
    NotifKind.weather => (Icons.thunderstorm, const Color(0xFFFFB2B7), MC.coralDark),
    NotifKind.rescheduled => (Icons.event_repeat, MC.coralSoft, MC.coralDark),
    NotifKind.remittance => (Icons.verified, MC.tintHi, MC.violetDark),
    NotifKind.sellerReady => (Icons.storefront, MC.tint, MC.muted),
    NotifKind.parcelAssigned => (Icons.inventory_2, MC.tintHi, MC.violetDark),
  };

  @override
  Widget build(BuildContext context) => ValueListenableBuilder<bool>(
    valueListenable: ConnectivityMonitor.isOffline,
    builder: (context, offline, _) {
      final synced = widget.synced ?? !offline;
      return MScaffold(title: 'Notifications', actions: [Padding(padding: const EdgeInsets.only(right: 12), child: Pill(synced ? 'Sync OK' : 'Offline', bg: synced ? MC.tintHi : MC.coralSoft, fg: synced ? MC.violetDark : MC.coral))],
        body: _items.isEmpty ? const SystemStateView.empty() : ListView(padding: const EdgeInsets.all(16), children: [
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [Text('Recent Alerts', style: ts(18, w: FontWeight.w700)),
            TextButton(onPressed: _allRead ? null : () => setState(() => _items = [for (final n in _items) AppNotification(n.kind, n.title, n.body, n.timeAgo, tag: n.tag)]), child: Text(_allRead ? 'All Caught Up!' : 'Mark all read'))]),
          for (final n in _items) Padding(padding: const EdgeInsets.only(bottom: 8), child: InkWell(onTap: () => widget.onTap?.call(n), child: Opacity(opacity: n.unread ? 1 : .9, child: MCard(child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
            IconCircle(_style(n.kind).$1, bg: _style(n.kind).$2, fg: _style(n.kind).$3), const SizedBox(width: 12),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Row(children: [Expanded(child: Text(n.title, style: ts(14, w: FontWeight.w800))), if (n.unread) const CircleAvatar(radius: 5, backgroundColor: MC.violetDark)]),
              Text(n.body, style: ts(14, c: MC.muted, h: 1.3)), const SizedBox(height: 6),
              Row(children: [const Icon(Icons.schedule, size: 14, color: MC.muted), const SizedBox(width: 4), Text(n.timeAgo, style: ts(12, c: MC.muted)), const Spacer(), if (n.tag.isNotEmpty) Pill(n.tag)])])),
          ]))))),
        ]));
    },
  );
}

/// Full-screen system states: session expired, access denied, offline buffer, empty list.
class SystemStateView extends StatelessWidget {
  final IconData icon; final Color bg, fg; final String title, body; final String? actionLabel; final IconData? actionIcon; final VoidCallback? onAction;
  const SystemStateView({super.key, required this.icon, required this.title, required this.body, this.bg = MC.tintHi, this.fg = MC.violetDark, this.actionLabel, this.actionIcon, this.onAction});
  const SystemStateView.empty({super.key}) : icon = Icons.notifications_none, bg = MC.tintHi, fg = MC.violetDark, title = 'No notifications yet', body = 'Job offers, assignments and alerts will appear here.', actionLabel = null, actionIcon = null, onAction = null;
  factory SystemStateView.sessionExpired({VoidCallback? onLogin}) => SystemStateView(icon: Icons.lock_clock, title: 'Session expired', body: 'Please log in again to protect your delivery wallet & pending payouts.', actionLabel: 'Log In Again', actionIcon: Icons.login, onAction: onLogin);
  factory SystemStateView.accessDenied({String? zone, VoidCallback? onHome}) => SystemStateView(icon: Icons.shield, bg: MC.coralSoft, fg: MC.coralDark, title: 'Access denied', body: 'Unauthorized route or restricted dispatch hub zone${zone != null ? ' $zone' : ''}.', actionLabel: 'Back to Home', actionIcon: Icons.arrow_back, onAction: onHome);
  factory SystemStateView.offline({VoidCallback? onRetry}) => SystemStateView(icon: Icons.cloud_sync, title: 'No internet connection', body: 'Queued updates and job progress will sync automatically once reconnected.', actionLabel: 'Retry Connection', actionIcon: Icons.refresh, onAction: onRetry);
  @override
  Widget build(BuildContext context) => Center(child: Padding(padding: const EdgeInsets.all(24), child: Column(mainAxisSize: MainAxisSize.min, children: [
    IconCircle(icon, bg: bg, fg: fg, size: 72), const SizedBox(height: 16), Text(title, textAlign: TextAlign.center, style: ts(18, w: FontWeight.w700)),
    const SizedBox(height: 4), Text(body, textAlign: TextAlign.center, style: ts(14, c: MC.muted, h: 1.4)),
    if (actionLabel != null) ...[const SizedBox(height: 16), SizedBox(width: 260, child: PillButton(actionLabel!, icon: actionIcon, onPressed: onAction))]])));
}
