import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/app_info.dart';
import '../../core/theme.dart';
import '../../data/repositories/app_repositories_scope.dart';
import '../../l10n/generated/app_localizations.dart';
import '../../widgets/common.dart';

class AboutScreen extends StatelessWidget {
  const AboutScreen({super.key});

  Future<void> _resetLocalData(BuildContext context) async {
    final l10n = AppLocalizations.of(context);
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: Text(l10n.databaseResetTitle),
        content: Text(l10n.databaseResetBody),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogContext, false),
            child: Text(l10n.databaseResetCancel),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(dialogContext, true),
            child: Text(l10n.databaseResetAction),
          ),
        ],
      ),
    );
    if (confirmed != true || !context.mounted) return;
    final messenger = ScaffoldMessenger.of(context);
    try {
      await AppRepositoriesScope.of(context).resetLocalData();
      if (context.mounted) {
        messenger.showSnackBar(SnackBar(content: Text(l10n.databaseResetDone)));
      }
    } catch (error) {
      if (context.mounted) {
        messenger.showSnackBar(SnackBar(content: Text('$error')));
      }
    }
  }

  Future<void> _emailSupport(BuildContext context) async {
    final messenger = ScaffoldMessenger.of(context);
    final l10n = AppLocalizations.of(context);
    final uri = Uri(
        scheme: 'mailto',
        path: kSupportEmail,
        queryParameters: {'subject': 'Mimoo Rider support'});
    var opened = false;
    try {
      opened = await canLaunchUrl(uri) && await launchUrl(uri);
    } catch (_) {
      opened = false;
    }
    if (!opened) {
      messenger.showSnackBar(SnackBar(content: Text(l10n.aboutEmailError)));
    }
  }

  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    final l10n = AppLocalizations.of(context);
    return MScaffold(
      title: l10n.settingsAboutMimoo,
      body: ListView(padding: MS.pagePadding, children: [
        MCard(
          color: c.lowest,
          child: Column(children: [
            Container(
              width: 76,
              height: 76,
              decoration: BoxDecoration(
                  color: c.primaryContainer,
                  borderRadius: BorderRadius.circular(22)),
              child: const Icon(Icons.delivery_dining_rounded,
                  color: Colors.white, size: 42),
            ),
            const SizedBox(height: 12),
            Text('Mimoo Rider', style: ts(22, w: FontWeight.w800, c: c.text)),
            Text('${l10n.drawerVersion(kAppVersion)} · build $kAppBuild',
                style: ts(12, c: c.muted)),
            const SizedBox(height: 6),
            Text(l10n.aboutTagline,
                style: ts(13, c: c.muted, h: 1.4), textAlign: TextAlign.center),
          ]),
        ),
        const SizedBox(height: 12),
        MCard(
          color: c.lowest,
          padding: EdgeInsets.zero,
          child: Column(children: [
            ListTile(
              minVerticalPadding: MS.md,
              leading: IconDot(Icons.mail_outline,
                  size: 36, bg: c.tint, fg: c.primary),
              title: Text(l10n.aboutSupportEmail,
                  style: ts(14, w: FontWeight.w600, c: c.text)),
              subtitle: Text(kSupportEmail, style: ts(12, c: c.muted)),
              trailing: Icon(Icons.chevron_right, color: c.onSurfaceVariant),
              onTap: () => _emailSupport(context),
            ),
            Divider(height: 1, indent: 64, color: c.outlineVariant),
            ListTile(
              minVerticalPadding: MS.md,
              leading: IconDot(Icons.article_outlined,
                  size: 36, bg: c.tint, fg: c.primary),
              title: Text(l10n.aboutLicenses,
                  style: ts(14, w: FontWeight.w600, c: c.text)),
              trailing: Icon(Icons.chevron_right, color: c.onSurfaceVariant),
              onTap: () => showLicensePage(
                context: context,
                applicationName: 'Mimoo Rider',
                applicationVersion: '$kAppVersion+$kAppBuild',
                applicationLegalese: '© 2026 Mimoo Rider',
              ),
            ),
          ]),
        ),
        if (kDebugMode) ...[
          const SizedBox(height: 12),
          ListTile(
            leading:
                IconDot(Icons.restart_alt, size: 36, bg: c.tint, fg: c.coral),
            title: Text(l10n.databaseResetAction,
                style: ts(14, w: FontWeight.w600, c: c.text)),
            onTap: () => _resetLocalData(context),
          ),
        ],
        const SizedBox(height: 20),
        Text(l10n.aboutMadeFor,
            style: ts(12, c: c.muted), textAlign: TextAlign.center),
      ]),
    );
  }
}
