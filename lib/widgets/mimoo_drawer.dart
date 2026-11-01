import 'package:flutter/material.dart';
import '../core/app_info.dart';
import '../core/theme.dart';
import '../data/repositories/app_repositories_scope.dart';
import '../l10n/generated/app_localizations.dart';
import 'common.dart';

/// Main navigation drawer. Items close the drawer before pushing a route so a
/// back gesture never returns to an open drawer.
class MimooDrawer extends StatelessWidget {
  const MimooDrawer({super.key});

  void _go(BuildContext context, String route) {
    final navigator = Navigator.of(context);
    navigator.pop();
    navigator.pushNamed(route);
  }

  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    final l10n = AppLocalizations.of(context);
    final repositories = AppRepositoriesScope.of(context);
    return ListenableBuilder(
      listenable: Listenable.merge([
        repositories.parcels,
        repositories.riderProfile,
        repositories.notifications,
        repositories.conversations,
      ]),
      builder: (context, _) {
        final profile = repositories.riderProfile.profile;
        final unreadMessages =
            repositories.conversations.conversations.fold<int>(
          0,
          (count, conversation) =>
              count +
              repositories.conversations
                  .messagesFor(conversation.id ?? -1)
                  .where((message) =>
                      message.sender != 'rider' && message.readAt == null)
                  .length,
        );
        final unreadNotifications = repositories.notifications.notifications
            .where((notification) => notification.readAt == null)
            .length;
        final codOnHand = repositories.parcels.codOnHand;
        return Drawer(
          backgroundColor: c.lowest,
          child: SafeArea(
            child: Column(children: [
              _header(
                context,
                c,
                l10n,
                name: profile?.name ?? '',
                fleetName: profile?.fleetName ?? '',
                hubName: profile?.hubName ?? '',
              ),
              Divider(height: 1, color: c.outlineVariant),
              Expanded(
                child: ListView(
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    children: [
                      _item(context, c, Icons.two_wheeler_rounded,
                          l10n.drawerVehicleDocuments,
                          route: '/vehicle-documents'),
                      _item(context, c, Icons.account_balance_wallet_rounded,
                          l10n.drawerCodRemittance,
                          route: '/cod-remittance',
                          badge: codOnHand > 0 ? peso(codOnHand) : null,
                          badgeColor: c.coral),
                      _item(context, c, Icons.chat_bubble_outline_rounded,
                          l10n.drawerMessages,
                          route: '/chat',
                          badge: unreadMessages > 0 ? '$unreadMessages' : null),
                      _item(context, c, Icons.notifications_none_rounded,
                          l10n.drawerNotifications,
                          route: '/notifications',
                          badge: unreadNotifications > 0
                              ? '$unreadNotifications'
                              : null),
                      Padding(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 16, vertical: 8),
                          child: Divider(height: 1, color: c.outlineVariant)),
                      _item(context, c, Icons.settings_rounded,
                          l10n.drawerSettings,
                          route: '/settings'),
                      _item(context, c, Icons.help_center_rounded,
                          l10n.drawerHelpCenter,
                          route: '/help-center'),
                    ]),
              ),
              Divider(height: 1, color: c.outlineVariant),
              Padding(
                padding:
                    const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
                child: Align(
                  alignment: Alignment.centerLeft,
                  child: Text(l10n.drawerVersion(kAppVersion),
                      style: ts(12, c: c.muted)),
                ),
              ),
            ]),
          ),
        );
      },
    );
  }

  Widget _header(BuildContext context, MimooColors c, AppLocalizations l10n,
          {required String name,
          required String fleetName,
          required String hubName}) =>
      InkWell(
        onTap: () => _go(context, '/profile'),
        child: Padding(
          padding: const EdgeInsets.fromLTRB(20, 20, 20, 18),
          child:
              Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Row(children: [
              CircleAvatar(
                  radius: 28,
                  backgroundColor: c.highest,
                  child: Icon(Icons.person, size: 30, color: c.primary)),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(children: [
                        Expanded(
                            child: Text(name,
                                style: MT.headlineMd.copyWith(color: c.text),
                                overflow: TextOverflow.ellipsis)),
                        const SizedBox(width: 4),
                        Icon(Icons.verified, size: 18, color: c.primary),
                      ]),
                      Text(fleetName,
                          style: MT.bodySm.copyWith(color: c.muted)),
                      Text(hubName, style: MT.bodySm.copyWith(color: c.muted)),
                    ]),
              ),
              Icon(Icons.chevron_right, color: c.onSurfaceVariant),
            ]),
            const SizedBox(height: 10),
            MChip(l10n.drawerActive,
                bg: c.greenSoft, fg: c.green, icon: Icons.circle),
          ]),
        ),
      );

  Widget _item(BuildContext context, MimooColors c, IconData icon, String label,
      {required String route, String? badge, Color? badgeColor}) {
    return ListTile(
      minVerticalPadding: 10,
      contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 2),
      leading: IconDot(icon, size: 38, bg: c.tint, fg: c.primary),
      title: Text(label, style: ts(14, w: FontWeight.w600, c: c.text)),
      trailing: Row(mainAxisSize: MainAxisSize.min, children: [
        if (badge != null)
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
            decoration: BoxDecoration(
                color: badgeColor ?? c.primary,
                borderRadius: BorderRadius.circular(99)),
            child:
                Text(badge, style: ts(12, w: FontWeight.w800, c: Colors.white)),
          ),
        const SizedBox(width: 4),
        Icon(Icons.chevron_right, color: c.onSurfaceVariant),
      ]),
      onTap: () => _go(context, route),
    );
  }
}
