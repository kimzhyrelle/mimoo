import 'package:flutter/material.dart';
import '../../core/settings_controller.dart';
import '../../core/theme.dart';
import '../../l10n/generated/app_localizations.dart';
import '../../widgets/common.dart';

class PasskeyScreen extends StatelessWidget {
  const PasskeyScreen({super.key});

  Future<void> _create(BuildContext context, SettingsController settings) async {
    final l10n = AppLocalizations.of(context);
    final messenger = ScaffoldMessenger.of(context);
    // TODO(api): register a real WebAuthn credential; biometrics are only a
    // local placeholder until the backend ceremony exists.
    final ok = await settings.authenticateBiometric(l10n.passkeyReason);
    if (!context.mounted) return;
    if (!ok) {
      messenger.showSnackBar(SnackBar(content: Text(l10n.passkeyAuthError)));
      return;
    }
    await settings.setPasskeySetUp(true);
    if (!context.mounted) return;
    messenger.showSnackBar(SnackBar(content: Text(l10n.passkeyCreated)));
  }

  Future<void> _remove(BuildContext context, SettingsController settings) async {
    final l10n = AppLocalizations.of(context);
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) {
        final palette = MC.of(ctx);
        return AlertDialog(
          backgroundColor: palette.lowest,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: Text(l10n.passkeyRemoveTitle, style: MT.headlineMd.copyWith(color: palette.text)),
          content: Text(l10n.passkeyRemoveBody),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx, false), child: Text(l10n.commonCancel)),
            FilledButton(
              style: FilledButton.styleFrom(backgroundColor: palette.coral),
              onPressed: () => Navigator.pop(ctx, true),
              child: Text(l10n.commonRemove),
            ),
          ],
        );
      },
    );
    if (confirmed != true) return;
    // TODO(api): revoke the registered passkey on the server.
    await settings.setPasskeySetUp(false);
    if (!context.mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(l10n.passkeyRemoved)));
  }

  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    final l10n = AppLocalizations.of(context);
    final settings = SettingsScope.of(context);
    final enabled = settings.passkeySetUp;

    return MScaffold(
      title: l10n.settingsPasskey,
      body: ListView(padding: MS.pagePadding, children: [
        MCard(
          color: c.lowest,
          child: Column(children: [
            IconDot(enabled ? Icons.verified_user : Icons.fingerprint, size: 64, bg: enabled ? c.greenSoft : c.tint, fg: enabled ? c.green : c.primary),
            const SizedBox(height: 12),
            Text(enabled ? l10n.passkeyActiveTitle : l10n.passkeyInactiveTitle, style: MT.headlineMd.copyWith(color: c.text), textAlign: TextAlign.center),
            const SizedBox(height: 4),
            Text(
              enabled ? l10n.settingsPasskeySet : l10n.settingsPasskeyNotSet,
              style: ts(13, c: c.muted),
              textAlign: TextAlign.center,
            ),
          ]),
        ),
        const SizedBox(height: 12),
        MCard(
          color: c.low,
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(l10n.passkeyWhatTitle, style: ts(16, w: FontWeight.w700, c: c.text)),
            const SizedBox(height: 6),
            Text(
              l10n.passkeyWhatBody,
              style: ts(13, c: c.muted, h: 1.4),
            ),
            const SizedBox(height: 10),
            InfoBanner(Icons.info_outline, l10n.passkeyComingTitle, body: l10n.passkeyComingBody, bg: c.low, fg: c.primary),
          ]),
        ),
        const SizedBox(height: 20),
        if (!enabled)
          PillButton(l10n.passkeyCreate, leading: Icons.fingerprint, trailing: null, onPressed: () => _create(context, settings))
        else
          PillButton.danger(l10n.passkeyRemove, leading: Icons.delete_outline, trailing: null, onPressed: () => _remove(context, settings)),
      ]),
    );
  }
}
