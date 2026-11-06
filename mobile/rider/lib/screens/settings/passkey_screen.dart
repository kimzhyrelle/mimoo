import 'package:flutter/material.dart';
import '../../core/settings_controller.dart';
import '../../core/theme.dart';
import '../../l10n/generated/app_localizations.dart';
import '../../widgets/common.dart';

/// Passkey screen — local biometric credential.
/// When enabled, the rider can authenticate with fingerprint/face instead
/// of typing their password. The credential is stored purely on-device via
/// the OS biometric stack (local_auth); no server ceremony is involved.
class PasskeyScreen extends StatefulWidget {
  const PasskeyScreen({super.key});
  @override
  State<PasskeyScreen> createState() => _PasskeyScreenState();
}

class _PasskeyScreenState extends State<PasskeyScreen> {
  bool _busy = false;

  Future<void> _create(BuildContext context, SettingsController settings) async {
    final l10n = AppLocalizations.of(context);
    final messenger = ScaffoldMessenger.of(context);
    if (_busy) return;

    // Require a successful biometric check before enabling.
    final ok = await settings.authenticateBiometric(l10n.passkeyReason);
    if (!context.mounted) return;
    if (!ok) {
      messenger.showSnackBar(SnackBar(content: Text(l10n.passkeyAuthError)));
      return;
    }

    setState(() => _busy = true);
    try {
      await settings.setPasskeySetUp(true);
      if (!context.mounted) return;
      messenger.showSnackBar(SnackBar(content: Text(l10n.passkeyCreated)));
    } catch (e) {
      if (!context.mounted) return;
      messenger.showSnackBar(SnackBar(content: Text('Could not enable passkey: $e')));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
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
    if (confirmed != true || !context.mounted) return;

    setState(() => _busy = true);
    try {
      await settings.setPasskeySetUp(false);
      if (!context.mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(l10n.passkeyRemoved)));
    } catch (e) {
      if (!context.mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Could not remove passkey: $e')));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
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
            IconDot(
              enabled ? Icons.verified_user : Icons.fingerprint,
              size: 64,
              bg: enabled ? c.greenSoft : c.tint,
              fg: enabled ? c.green : c.primary,
            ),
            const SizedBox(height: 12),
            Text(
              enabled ? l10n.passkeyActiveTitle : l10n.passkeyInactiveTitle,
              style: MT.headlineMd.copyWith(color: c.text),
              textAlign: TextAlign.center,
            ),
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
            Text(l10n.passkeyWhatBody, style: ts(13, c: c.muted, h: 1.4)),
            const SizedBox(height: 10),
            InfoBanner(
              Icons.phone_android,
              'Device-only biometric credential — stored on this device, no server required.',
              bg: c.low,
              fg: c.primary,
            ),
          ]),
        ),
        const SizedBox(height: 20),
        if (!enabled)
          PillButton(
            l10n.passkeyCreate,
            leading: Icons.fingerprint,
            trailing: null,
            onPressed: _busy ? null : () => _create(context, settings),
          )
        else
          PillButton.danger(
            l10n.passkeyRemove,
            leading: Icons.delete_outline,
            trailing: null,
            onPressed: _busy ? null : () => _remove(context, settings),
          ),
      ]),
    );
  }
}

/// Passkey sign-in helper — local biometric only.
/// Returns silently on success; throws if biometrics are unavailable or fail.
Future<void> signInWithPasskey({
  required SettingsController settings,
  required String localAuthReason,
}) async {
  final ok = await settings.authenticateBiometric(localAuthReason);
  if (!ok) throw Exception('Biometric authentication failed.');
}
