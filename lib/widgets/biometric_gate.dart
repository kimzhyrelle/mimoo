import 'dart:async';

import 'package:flutter/material.dart';
import '../core/settings_controller.dart';
import '../core/theme.dart';
import '../l10n/generated/app_localizations.dart';

/// Keeps the navigator alive underneath an opaque lock screen while the
/// "Biometric unlock" preference is enabled. Unlocks on launch and whenever
/// the app returns to the foreground.
class BiometricGate extends StatefulWidget {
  final Widget child;
  const BiometricGate({super.key, required this.child});
  @override
  State<BiometricGate> createState() => _BiometricGateState();
}

class _BiometricGateState extends State<BiometricGate> with WidgetsBindingObserver {
  bool _initialized = false;
  bool _locked = false;
  bool _authenticating = false;
  DateTime? _lastUnlock;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final settings = SettingsScope.of(context);
    if (!_initialized) {
      _initialized = true;
      _locked = settings.biometric;
      if (_locked) _scheduleAuth();
    } else if (!settings.biometric) {
      _locked = false;
    }
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state != AppLifecycleState.resumed || !_initialized || _authenticating) return;
    if (!SettingsScope.of(context).biometric) return;
    final last = _lastUnlock;
    if (last != null && DateTime.now().difference(last) < const Duration(seconds: 3)) return;
    setState(() => _locked = true);
    _scheduleAuth();
  }

  void _scheduleAuth() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted && _locked) unawaited(_authenticate());
    });
  }

  Future<void> _authenticate({bool biometricOnly = true}) async {
    if (_authenticating || !mounted) return;
    final l10n = AppLocalizations.of(context);
    final settings = SettingsScope.of(context);
    setState(() => _authenticating = true);
    final ok = await settings.authenticateBiometric(l10n.biometricLockReason, biometricOnly: biometricOnly);
    if (!mounted) return;
    setState(() {
      _authenticating = false;
      if (ok) {
        _locked = false;
        _lastUnlock = DateTime.now();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Stack(fit: StackFit.expand, children: [
      ExcludeSemantics(excluding: _locked, child: widget.child),
      if (_locked) _LockScreen(authenticating: _authenticating, onUnlock: _authenticate),
    ]);
  }
}

class _LockScreen extends StatelessWidget {
  final bool authenticating;
  final Future<void> Function({bool biometricOnly}) onUnlock;
  const _LockScreen({required this.authenticating, required this.onUnlock});

  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    final l10n = AppLocalizations.of(context);
    return Material(
      color: c.bg,
      child: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 420),
              child: Column(mainAxisSize: MainAxisSize.min, children: [
                Container(
                  width: 88,
                  height: 88,
                  decoration: BoxDecoration(color: c.primaryFixed, shape: BoxShape.circle),
                  child: Icon(Icons.fingerprint, size: 48, color: c.primary),
                ),
                const SizedBox(height: 24),
                Text(l10n.biometricLockTitle, style: MT.headlineLg.copyWith(color: c.text), textAlign: TextAlign.center),
                const SizedBox(height: 8),
                Text(l10n.biometricLockBody, style: ts(14, c: c.muted, h: 1.4), textAlign: TextAlign.center),
                const SizedBox(height: 28),
                PillButton(
                  l10n.biometricUnlock,
                  leading: Icons.fingerprint,
                  loading: authenticating,
                  onPressed: authenticating ? null : () => onUnlock(),
                ),
                const SizedBox(height: 4),
                TextButton(
                  onPressed: authenticating ? null : () => onUnlock(biometricOnly: false),
                  child: Text(l10n.biometricUsePasscode),
                ),
              ]),
            ),
          ),
        ),
      ),
    );
  }
}
