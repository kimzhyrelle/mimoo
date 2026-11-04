import 'dart:async';

import 'package:flutter/material.dart';
import '../core/job_offer_notifications.dart';
import '../core/rider_status_controller.dart';
import '../core/theme.dart';
import '../l10n/generated/app_localizations.dart';

class MimooAppBar extends StatelessWidget implements PreferredSizeWidget {
  final int unreadMessages;
  final int unreadNotifications;
  const MimooAppBar({
    super.key,
    this.unreadMessages = 0,
    this.unreadNotifications = 0,
  });
  @override
  Size get preferredSize => const Size.fromHeight(64);
  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return AppBar(
      backgroundColor: MC.header,
      flexibleSpace: const DecoratedBox(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [MC.violetDark, MC.violet],
          ),
        ),
      ),
      toolbarHeight: 64,
      automaticallyImplyLeading: false,
      leading: Builder(
        builder: (context) => IconButton(
          tooltip: MaterialLocalizations.of(context).openAppDrawerTooltip,
          onPressed: () => Scaffold.of(context).openDrawer(),
          icon: const Icon(Icons.menu, color: Colors.white, size: 26),
        ),
      ),
      titleSpacing: 8,
      title: Row(children: [
        Text('mimoo',
            style: MT.headlineLg
                .copyWith(color: Colors.white, fontWeight: FontWeight.w800)),
        Container(
            margin: const EdgeInsets.only(left: 2, top: 8),
            width: 8,
            height: 8,
            decoration: const BoxDecoration(
                color: MC.tertiaryContainer, shape: BoxShape.circle)),
      ]),
      actions: [
        const _RiderStatusSwitch(),
        _iconAction(context,
            icon: Icons.chat_bubble_outline_rounded,
            tooltip: l10n.drawerMessages,
            route: '/chat',
            badge: unreadMessages),
        _iconAction(context,
            icon: Icons.notifications_none_rounded,
            tooltip: l10n.drawerNotifications,
            route: '/notifications',
            badge: unreadNotifications),
        const SizedBox(width: 8),
      ],
    );
  }

  Widget _iconAction(BuildContext context,
          {required IconData icon,
          required String tooltip,
          required String route,
          required int badge}) =>
      Stack(alignment: Alignment.center, children: [
        IconButton(
          tooltip: tooltip,
          onPressed: () => Navigator.pushNamed(context, route),
          icon: Icon(icon, color: Colors.white70),
        ),
        if (badge > 0)
          Positioned(
            top: 8,
            right: 8,
            child: Container(
              constraints: const BoxConstraints(minWidth: 18, minHeight: 18),
              padding: const EdgeInsets.symmetric(horizontal: 4),
              decoration: BoxDecoration(
                  color: MC.tertiaryContainer,
                  borderRadius: BorderRadius.circular(99),
                  border: Border.all(color: MC.header, width: 1.5)),
              child: Center(
                child: Text(badge > 9 ? '9+' : '$badge',
                    style: ts(12, w: FontWeight.w800, c: MC.header)),
              ),
            ),
          ),
      ]);
}

class _RiderStatusSwitch extends StatelessWidget {
  const _RiderStatusSwitch();

  @override
  Widget build(BuildContext context) {
    final status = RiderStatusScope.of(context);
    final l10n = AppLocalizations.of(context);
    final online = status.isOnline;
    final label = status.isSuspended
        ? l10n.riderStatusSuspended
        : online
            ? l10n.riderStatusTooltipOnline
            : l10n.riderStatusTooltipOffline;
    return Semantics(
      button: true,
      toggled: online,
      enabled: !status.isSuspended && !status.isUpdatingStatus,
      label: label,
      child: Tooltip(
        message: label,
        child: InkWell(
          onTap: status.isSuspended || status.isUpdatingStatus
              ? null
              : () => toggleRiderStatus(context, status, !online),
          borderRadius: BorderRadius.circular(24),
          child: SizedBox(
            width: 48,
            height: 48,
            child: Center(
              child: Row(mainAxisSize: MainAxisSize.min, children: [
                AnimatedContainer(
                  duration: const Duration(milliseconds: 180),
                  width: 6,
                  height: 6,
                  decoration: BoxDecoration(
                    color: online ? MC.greenSoft : Colors.white38,
                    shape: BoxShape.circle,
                  ),
                ),
                const SizedBox(width: 4),
                AnimatedContainer(
                  duration: const Duration(milliseconds: 180),
                  width: 36,
                  height: 20,
                  padding: const EdgeInsets.all(2),
                  decoration: BoxDecoration(
                    color: online ? MC.green : Colors.white24,
                    borderRadius: BorderRadius.circular(99),
                  ),
                  child: AnimatedAlign(
                    duration: const Duration(milliseconds: 180),
                    alignment:
                        online ? Alignment.centerRight : Alignment.centerLeft,
                    child: Container(
                      width: 16,
                      height: 16,
                      decoration: const BoxDecoration(
                          color: Colors.white, shape: BoxShape.circle),
                    ),
                  ),
                ),
              ]),
            ),
          ),
        ),
      ),
    );
  }
}

Future<void> toggleRiderStatus(
    BuildContext context, RiderStatusController status, bool value) async {
  final l10n = AppLocalizations.of(context);
  final messenger = ScaffoldMessenger.of(context);

  if (value) {
    if (status.isSuspended) {
      messenger
          .showSnackBar(SnackBar(content: Text(l10n.riderStatusSuspended)));
      return;
    }
    bool ok;
    try {
      ok = await status.requestOnline(context);
    } on Exception catch (error) {
      if (!context.mounted) return;
      messenger.showSnackBar(SnackBar(content: Text('$error')));
      return;
    }
    if (!context.mounted || !ok) return;
    unawaited(JobOfferNotifications.requestPermission());
    messenger.showSnackBar(SnackBar(
      content: Text(l10n.riderStatusOnline),
      duration: const Duration(seconds: 2),
    ));
    return;
  }

  if (status.heldParcels == 0) {
    try {
      await status.goOffline();
    } on Exception catch (error) {
      if (!context.mounted) return;
      messenger.showSnackBar(SnackBar(content: Text('$error')));
      return;
    }
    if (!context.mounted) return;
    messenger.showSnackBar(SnackBar(
      content: Text(l10n.riderStatusOffline),
      duration: const Duration(seconds: 2),
    ));
    return;
  }

  final held = status.heldParcels;
  final palette = MC.of(context);
  final stay = await showDialog<bool>(
    context: context,
    builder: (c) => Dialog(
      backgroundColor: palette.lowest,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          IconCircle(Icons.inventory_2,
              size: 64, bg: palette.tint, fg: palette.primary),
          const SizedBox(height: 12),
          Text(l10n.riderStatusHeldTitle(held),
              style: ts(18, w: FontWeight.w700, c: palette.text),
              textAlign: TextAlign.center),
          const SizedBox(height: 8),
          Text(l10n.riderStatusHeldBody(held),
              style: ts(14, c: palette.muted, h: 1.4),
              textAlign: TextAlign.center),
          const SizedBox(height: 16),
          PillButton(l10n.riderStatusStayOnline,
              onPressed: () => Navigator.pop(c, true)),
          const SizedBox(height: 8),
          TextButton.icon(
            onPressed: () => Navigator.pop(c, false),
            icon: Icon(Icons.power_settings_new, color: palette.coral),
            label: Text(l10n.riderStatusGoOffline,
                style: ts(14, w: FontWeight.w700, c: palette.coral)),
          ),
        ]),
      ),
    ),
  );
  if (!context.mounted || stay == true || stay == null) return;
  try {
    await status.goOffline();
  } on Exception catch (error) {
    if (!context.mounted) return;
    messenger.showSnackBar(SnackBar(content: Text('$error')));
    return;
  }
  if (!context.mounted) return;
  messenger.showSnackBar(SnackBar(
    content: Text(l10n.riderStatusOffline),
    duration: const Duration(seconds: 2),
  ));
}

class MimooBottomNav extends StatelessWidget {
  final int index;
  final ValueChanged<int> onTap;
  final VoidCallback onScan;
  const MimooBottomNav(
      {super.key,
      required this.index,
      required this.onTap,
      required this.onScan});
  static const _items = [
    (Icons.home_outlined, Icons.home),
    (Icons.local_shipping_outlined, Icons.local_shipping),
    (Icons.payments_outlined, Icons.payments),
    (Icons.schedule_outlined, Icons.schedule),
  ];
  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    final l10n = AppLocalizations.of(context);
    return Container(
      decoration: BoxDecoration(color: c.lowest, boxShadow: [
        BoxShadow(
            color: MC.header.withValues(alpha: 0.08),
            blurRadius: 16,
            offset: const Offset(0, -4))
      ]),
      child: SafeArea(
        top: false,
        child: SizedBox(
          height: 76,
          child: Stack(
            clipBehavior: Clip.none,
            alignment: Alignment.topCenter,
            children: [
              Row(children: [
                Expanded(child: _navItem(c, 0, l10n.navHome)),
                Expanded(child: _navItem(c, 1, l10n.navJobs)),
                const Spacer(),
                Expanded(child: _navItem(c, 2, l10n.navEarnings)),
                Expanded(child: _navItem(c, 3, l10n.navHistory)),
              ]),
              Positioned(
                top: -16,
                left: 0,
                right: 0,
                child: Center(child: _scanButton(c, l10n.navScan)),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _navItem(MimooColors c, int i, String label) {
    final selected = i == index;
    final fg = selected ? c.primary : c.onSurfaceVariant;
    return InkWell(
      borderRadius: BorderRadius.circular(16),
      onTap: () => onTap(i),
      child: ConstrainedBox(
        constraints: const BoxConstraints(minWidth: 56, minHeight: 48),
        child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            mainAxisSize: MainAxisSize.min,
            children: [
              AnimatedContainer(
                duration: const Duration(milliseconds: 180),
                padding:
                    const EdgeInsets.symmetric(horizontal: 16, vertical: 5),
                decoration: BoxDecoration(
                    color: selected
                        ? c.primaryContainer.withValues(alpha: 0.14)
                        : Colors.transparent,
                    borderRadius: BorderRadius.circular(99)),
                child: Icon(selected ? _items[i].$2 : _items[i].$1,
                    size: 24, color: fg),
              ),
              const SizedBox(height: 2),
              Text(label,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: ts(12,
                      w: selected ? FontWeight.w700 : FontWeight.w600, c: fg)),
            ]),
      ),
    );
  }

  Widget _scanButton(MimooColors c, String label) =>
      Column(mainAxisSize: MainAxisSize.min, children: [
        InkWell(
          customBorder: const CircleBorder(),
          onTap: onScan,
          child: Container(
            width: 72,
            height: 72,
            decoration: BoxDecoration(
              color: c.lowest,
              shape: BoxShape.circle,
              boxShadow: [
                BoxShadow(
                    color: MC.header.withValues(alpha: 0.12),
                    blurRadius: 14,
                    offset: const Offset(0, 4))
              ],
            ),
            child: Center(
              child: Container(
                width: 64,
                height: 64,
                decoration: BoxDecoration(
                  color: c.primaryContainer,
                  shape: BoxShape.circle,
                  boxShadow: [
                    BoxShadow(
                        color: c.primaryContainer.withValues(alpha: 0.35),
                        blurRadius: 10,
                        offset: const Offset(0, 4))
                  ],
                ),
                child: const Icon(Icons.qr_code_scanner,
                    color: Colors.white, size: 30),
              ),
            ),
          ),
        ),
        const SizedBox(height: 2),
        Text(label, style: ts(12, w: FontWeight.w700, c: c.primary)),
      ]);
}
