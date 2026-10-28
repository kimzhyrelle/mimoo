import 'package:flutter/material.dart';
import '../core/theme.dart';

/// 1. Set password (activation link landing)
class SetPasswordScreen extends StatefulWidget {
  final String riderId, fleetName; final DateTime expiresAt;
  final Future<void> Function(String password) onActivate;
  const SetPasswordScreen({super.key, required this.riderId, required this.fleetName, required this.expiresAt, required this.onActivate});
  @override State<SetPasswordScreen> createState() => _SetPasswordState();
}

class _SetPasswordState extends State<SetPasswordScreen> {
  final _pw = TextEditingController(), _cf = TextEditingController();
  bool _show = false, _busy = false;
  static final _rules = <(String, bool Function(String))>[
    ('8+ characters', (s) => s.length >= 8),
    ('Uppercase letter (A-Z)', (s) => RegExp('[A-Z]').hasMatch(s)),
    ('Lowercase letter (a-z)', (s) => RegExp('[a-z]').hasMatch(s)),
    ('At least one number (0-9)', (s) => RegExp('[0-9]').hasMatch(s)),
    ('Special character (!@#\$%^&*)', (s) => RegExp(r'[!@#$%^&*]').hasMatch(s)),
  ];

  @override
  void dispose() { _pw.dispose(); _cf.dispose(); super.dispose(); }

  String get _left {
    final d = widget.expiresAt.difference(DateTime.now());
    return d.isNegative ? 'Expired' : 'Expires in ${d.inHours}h ${d.inMinutes % 60}m';
  }

  InputDecoration _dec(bool error, {Widget? suffix}) => InputDecoration(
      filled: true, fillColor: error ? MC.errorSoft.withValues(alpha: .4) : MC.tintLow, suffixIcon: suffix,
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(99), borderSide: BorderSide.none),
      contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14));

  @override
  Widget build(BuildContext context) {
    final passed = _rules.where((r) => r.$2(_pw.text)).length;
    final allOk = passed == _rules.length;
    final mismatch = _cf.text.isNotEmpty && _cf.text != _pw.text;
    final canSubmit = allOk && _cf.text == _pw.text && !_busy;
    final eye = IconButton(icon: Icon(_show ? Icons.visibility_off : Icons.visibility), onPressed: () => setState(() => _show = !_show));
    return MScaffold(
      body: ListView(padding: const EdgeInsets.all(16), children: [
        Row(children: [
          const IconCircle(Icons.two_wheeler, bg: MC.violetDark, fg: Colors.white, size: 32), const SizedBox(width: 8),
          Text('Mimoo Rider', style: ts(18, w: FontWeight.w700, c: MC.violetDark)), const Spacer(),
          Pill(_left, icon: Icons.schedule),
        ]),
        const SizedBox(height: 16),
        MCard(child: Row(children: [
          const IconCircle(Icons.person, size: 48), const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Pill(widget.fleetName), const SizedBox(height: 4),
            Text('Rider ID ${widget.riderId}', style: ts(18, w: FontWeight.w700)),
          ])),
          const Icon(Icons.verified, color: MC.violet),
        ])),
        const SizedBox(height: 20),
        Text('Activate your rider account', style: ts(28, w: FontWeight.w800, h: 1.2)),
        const SizedBox(height: 4),
        Text('Set a secure password for your rider account (valid for 24 hours from invitation).', style: ts(14, c: MC.muted, h: 1.4)),
        const SizedBox(height: 20),
        MCard(padding: const EdgeInsets.all(20), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
            Text('Create Password', style: ts(14, w: FontWeight.w700)),
            Text(passed <= 2 ? 'Weak' : passed < 5 ? 'Fair' : 'Strong', style: ts(12, w: FontWeight.w600, c: allOk ? MC.violetDark : MC.coral)),
          ]),
          const SizedBox(height: 8),
          TextField(controller: _pw, obscureText: !_show, onChanged: (_) => setState(() {}), decoration: _dec(false, suffix: eye)),
          const SizedBox(height: 12),
          Container(padding: const EdgeInsets.all(12), decoration: BoxDecoration(color: MC.tintLow, borderRadius: BorderRadius.circular(12)),
            child: Column(children: [for (final r in _rules) Padding(padding: const EdgeInsets.symmetric(vertical: 3), child: Row(children: [
              Icon(r.$2(_pw.text) ? Icons.check_circle : Icons.radio_button_unchecked, size: 18, color: r.$2(_pw.text) ? MC.green : MC.outline),
              const SizedBox(width: 8), Text(r.$1, style: ts(12, w: FontWeight.w500)),
            ]))])),
          const SizedBox(height: 16),
          Text('Confirm Password', style: ts(14, w: FontWeight.w700)), const SizedBox(height: 8),
          TextField(controller: _cf, obscureText: !_show, onChanged: (_) => setState(() {}), decoration: _dec(mismatch)),
          if (mismatch) ...[const SizedBox(height: 8), const InfoBanner(Icons.error, 'Passwords do not match', body: 'Please re-enter your password to match exactly.', bg: MC.errorSoft, fg: MC.coral)],
        ])),
        const SizedBox(height: 12),
        const InfoBanner(Icons.lock_reset, 'Account Protection', body: 'This login will protect your delivery wallet & dispatch profile.'),
        const SizedBox(height: 16),
        PillButton('Activate Account', icon: Icons.arrow_forward, loading: _busy, onPressed: canSubmit ? () async {
          setState(() => _busy = true);
          try { await widget.onActivate(_pw.text); } finally { if (mounted) setState(() => _busy = false); }
        } : null),
      ]));
  }
}

/// 2. Link expired / already used
class LinkExpiredScreen extends StatefulWidget {
  final String fleetName, email, phone; final VoidCallback onBackToLogin; final Future<void> Function() onRequestResend;
  const LinkExpiredScreen({super.key, required this.fleetName, required this.email, required this.phone, required this.onBackToLogin, required this.onRequestResend});
  @override State<LinkExpiredScreen> createState() => _LinkExpiredState();
}

class _LinkExpiredState extends State<LinkExpiredScreen> {
  bool _busy = false, _sent = false;
  @override
  Widget build(BuildContext context) => MScaffold(
        body: ListView(padding: const EdgeInsets.all(16), children: [
          const SizedBox(height: 16),
          const Center(child: IconCircle(Icons.link_off, bg: MC.coralSoft, fg: MC.coralDark, size: 88)),
          const SizedBox(height: 12),
          const Center(child: Pill('Link inactive', bg: MC.coralSoft, fg: MC.coralDark, icon: Icons.timer_off)),
          const SizedBox(height: 16),
          Text('This activation link has expired or was already used', textAlign: TextAlign.center, style: ts(22, w: FontWeight.w700, h: 1.25)),
          const SizedBox(height: 8),
          Text('For security reasons, activation links expire after 24 hours. Ask your logistics company to resend the activation email.', textAlign: TextAlign.center, style: ts(14, c: MC.muted, h: 1.4)),
          const SizedBox(height: 20),
          MCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text('ASSIGNED FLEET', style: ts(12, w: FontWeight.w600, c: MC.muted)), const SizedBox(height: 6),
            Text(widget.fleetName, style: ts(18, w: FontWeight.w700)), const SizedBox(height: 10),
            Row(children: [const Icon(Icons.mail, size: 16, color: MC.violetDark), const SizedBox(width: 8), Text(widget.email, style: ts(14, w: FontWeight.w600, c: MC.violetDark))]),
            const SizedBox(height: 6),
            Row(children: [const Icon(Icons.call, size: 16, color: MC.violetDark), const SizedBox(width: 8), Text(widget.phone, style: ts(14, w: FontWeight.w600, c: MC.violetDark))]),
          ])),
          const SizedBox(height: 20),
          PillButton('Back to Login', icon: Icons.arrow_forward, onPressed: widget.onBackToLogin),
          const SizedBox(height: 8),
          PillButton(_sent ? 'Request Submitted' : 'Request Resend from Logistics', style: BtnStyle.outline,
              icon: _sent ? Icons.mark_email_read : Icons.forward_to_inbox, loading: _busy,
              onPressed: _sent ? null : () async {
                setState(() => _busy = true);
                await widget.onRequestResend();
                if (!context.mounted) return;
                setState(() { _busy = false; _sent = true; });
                ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Resend request dispatched to hub')));
              }),
        ]));
}

/// 3. Activation success
class ActivationSuccessScreen extends StatelessWidget {
  final String riderName, fleetName, vehicle, hub, referenceId; final VoidCallback onLogin;
  const ActivationSuccessScreen({super.key, required this.riderName, required this.fleetName, required this.vehicle, required this.hub, required this.referenceId, required this.onLogin});
  @override
  Widget build(BuildContext context) => MScaffold(
        body: ListView(padding: const EdgeInsets.all(16), children: [
          const SizedBox(height: 16),
          Center(child: Container(width: 80, height: 80, decoration: const BoxDecoration(color: MC.green, shape: BoxShape.circle),
              child: const Icon(Icons.check, color: Colors.white, size: 44))),
          const SizedBox(height: 12),
          const Center(child: Pill('Rider onboarding verified', icon: Icons.verified)),
          const SizedBox(height: 12),
          Text('Account activated', textAlign: TextAlign.center, style: ts(28, w: FontWeight.w800)),
          const SizedBox(height: 4),
          Text('Log in to start accepting jobs with $fleetName and earning on the Mimoo platform.', textAlign: TextAlign.center, style: ts(14, c: MC.muted, h: 1.4)),
          const SizedBox(height: 20),
          MCard(child: Column(children: [
            Row(children: [const IconCircle(Icons.person, size: 48), const SizedBox(width: 12),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text('ACTIVATED RIDER', style: ts(11, w: FontWeight.w600, c: MC.muted)), Text(riderName, style: ts(18, w: FontWeight.w700))])),
              const Pill('Ready')]),
            const SizedBox(height: 12),
            _row(Icons.local_shipping, 'Assigned Fleet', fleetName),
            _row(Icons.two_wheeler, 'Vehicle', vehicle),
            _row(Icons.hub, 'Hub', hub),
            const SizedBox(height: 8),
            Row(children: [const Icon(Icons.check_circle, size: 16, color: MC.green), const SizedBox(width: 6),
              Expanded(child: Text('Driver ID & NBI clearance approved', style: ts(12, c: MC.muted))), Text(referenceId, style: ts(12, w: FontWeight.w600, c: MC.violetDark))]),
          ])),
          const SizedBox(height: 20),
          PillButton('Go to Login', icon: Icons.arrow_forward, onPressed: onLogin),
        ]));
  Widget _row(IconData i, String k, String v) => Container(
      margin: const EdgeInsets.only(bottom: 8), padding: const EdgeInsets.all(8),
      decoration: BoxDecoration(color: MC.tintLow, borderRadius: BorderRadius.circular(8)),
      child: Row(children: [IconCircle(i, size: 32), const SizedBox(width: 10),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(k, style: ts(12, c: MC.muted)), Text(v, style: ts(14, w: FontWeight.w700))]))]));
}
