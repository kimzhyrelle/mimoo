import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import '../core/job_offer_notifications.dart';
import '../core/theme.dart';
import '../data/mock_data.dart';
import '../widgets/common.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});
  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  bool _hide = true;
  AccountStatus _status = mockAccountStatus;

  void _login() {
    switch (_status) {
      case AccountStatus.active:
        Navigator.pushReplacementNamed(context, '/home');
      case AccountStatus.suspended:
        Navigator.pushNamed(context, '/suspended');
      case AccountStatus.pending:
        Navigator.pushNamed(context, '/pending');
      case AccountStatus.rejected:
        Navigator.pushNamed(context, '/registration/rejected');
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        body: SafeArea(
          child: ListView(padding: const EdgeInsets.all(16), children: [
            // Brand header
            Container(
              padding: const EdgeInsets.symmetric(vertical: 28),
              decoration: BoxDecoration(color: MC.header, borderRadius: const BorderRadius.vertical(bottom: Radius.circular(12))),
              child: Column(children: [
                Row(mainAxisAlignment: MainAxisAlignment.center, children: [
                  Text('mimoo', style: MT.headlineXl.copyWith(color: Colors.white)),
                  Container(margin: const EdgeInsets.only(left: 3, top: 8), width: 8, height: 8, decoration: const BoxDecoration(color: Color(0xFFFF6B6B), shape: BoxShape.circle)),
                  const SizedBox(width: 8),
                  const MChip('Rider', bg: Color(0xFFE9D5FF), fg: Color(0xFF581C87)),
                ]),
                const SizedBox(height: 12),
                Text('Metro Manila Logistics Grid • Operational', style: MT.bodySm.copyWith(color: const Color(0xFFF4EEFF))),
              ]),
            ),
            const SizedBox(height: 16),
            MCard(
              radius: 20,
              padding: const EdgeInsets.all(20),
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Row(children: [
                  Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Text('Welcome Back, Rider', style: MT.headlineLg),
                    Text('Sign in to check dispatch feeds & start your shift', style: MT.bodyMd.copyWith(color: MC.onSurfaceVariant)),
                  ])),
                  const IconDot(Icons.two_wheeler, size: 48),
                ]),
                const SizedBox(height: 16),
                const MField('Email Address', prefix: Icons.mail_outline, keyboard: TextInputType.emailAddress, initial: 'pedrojose.garcia@example.com'),
                const SizedBox(height: 16),
                MField('Password', prefix: Icons.lock_outline, obscure: _hide, initial: 'password123'),
                Align(
                  alignment: Alignment.centerRight,
                  child: TextButton(onPressed: () => setState(() => _hide = !_hide), child: Text(_hide ? 'Show password' : 'Hide password', style: MT.labelMd.copyWith(color: MC.primary))),
                ),
                const SizedBox(height: 4),
                PillButton('Log In', onPressed: _login),
                const SizedBox(height: 12),
                // ERP flow: riders can self-register, then wait for Logistics approval
                OutlinedButton.icon(
                  onPressed: () => Navigator.pushNamed(context, '/register'),
                  icon: const Icon(Icons.person_add_alt),
                  label: const Text('Create Rider Account'),
                  style: OutlinedButton.styleFrom(minimumSize: const Size.fromHeight(52), shape: const StadiumBorder(), foregroundColor: MC.primary, side: const BorderSide(color: MC.primaryContainer)),
                ),
              ]),
            ),
            const SizedBox(height: 16),
            MCard(
              color: MC.container,
              child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
                const Icon(Icons.info, color: MC.primaryContainer, size: 20),
                const SizedBox(width: 10),
                Expanded(child: Text.rich(TextSpan(style: MT.bodySm.copyWith(color: MC.onSurface), children: const [
                  TextSpan(text: 'New rider? ', style: TextStyle(fontWeight: FontWeight.w700)),
                  TextSpan(text: 'Register and wait for approval from your logistics company.'),
                ]))),
              ]),
            ),
            if (kDebugMode) ...[
              const SizedBox(height: 16),
              MCard(
                color: MC.low,
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text('DEBUG TOOLS', style: MT.badge.copyWith(color: MC.onSurfaceVariant)),
                  const SizedBox(height: 4),
                  Row(children: [
                    Expanded(child: Text('Mock login status', style: MT.labelMd)),
                    DropdownButton<AccountStatus>(
                      value: _status,
                      items: [for (final status in AccountStatus.values) DropdownMenuItem(value: status, child: Text(status.name))],
                      onChanged: (value) { if (value != null) setState(() => _status = value); },
                    ),
                  ]),
                  Wrap(spacing: 8, runSpacing: 4, children: [
                    TextButton(onPressed: () => Navigator.pushNamed(context, '/activation/set-password'), child: const Text('Activation')),
                    TextButton(onPressed: () => Navigator.pushNamed(context, '/activation/link-expired'), child: const Text('Link Expired')),
                    TextButton(onPressed: () => Navigator.pushNamed(context, '/registration/approved'), child: const Text('Approved')),
                    TextButton(onPressed: () => Navigator.pushNamed(context, '/registration/rejected'), child: const Text('Rejected')),
                    TextButton(onPressed: () => Navigator.pushNamed(context, '/suspended'), child: const Text('Suspended')),
                    TextButton(
                      onPressed: () => JobOfferNotifications.showJobOffer(
                        offerId: 'OFFER-1001',
                        area: 'Commonwealth, QC',
                        payout: '₱30.00',
                        cod: '₱500.00',
                        km: '7.4',
                      ),
                      child: const Text('Test Notification'),
                    ),
                  ]),
                ]),
              ),
            ],
          ]),
        ),
      );
}

