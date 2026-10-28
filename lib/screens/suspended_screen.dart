import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../core/theme.dart';

class SuspendedScreen extends StatelessWidget {
  final String fleetName, reason, dateLabel, hotline, coordinatorName, coordinatorHub, coordinatorSms; final VoidCallback onLogout;
  const SuspendedScreen({super.key, required this.fleetName, required this.reason, required this.dateLabel, required this.hotline,
    required this.coordinatorName, required this.coordinatorHub, required this.coordinatorSms, required this.onLogout});

  Future<void> _open(String uri) => launchUrl(Uri.parse(uri));

  @override
  Widget build(BuildContext context) => MScaffold(
        title: 'mimoo  •  RIDER',
        body: ListView(padding: const EdgeInsets.all(16), children: [
          Container(padding: const EdgeInsets.all(20), decoration: BoxDecoration(color: MC.errorSoft.withValues(alpha: .6), borderRadius: BorderRadius.circular(20)),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
                const IconCircle(Icons.gpp_maybe, bg: Colors.white, fg: MC.coralDark, size: 48), const SizedBox(width: 12),
                Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text('ACCOUNT NOTICE', style: ts(11, w: FontWeight.w800, c: MC.coralDark)),
                  Text('Your account has been suspended by $fleetName', style: ts(18, w: FontWeight.w700, c: const Color(0xFF93000A), h: 1.3)),
                ])),
              ]),
              const SizedBox(height: 12),
              Text('You cannot go Online or receive jobs. Contact your logistics company dispatch supervisor to resolve this issue.', style: ts(14, h: 1.4)),
              const SizedBox(height: 12),
              Container(padding: const EdgeInsets.all(10), decoration: BoxDecoration(color: Colors.white70, borderRadius: BorderRadius.circular(12)),
                child: Row(children: [const Icon(Icons.event_busy, size: 18, color: MC.coralDark), const SizedBox(width: 8),
                  Expanded(child: Text.rich(TextSpan(style: ts(12, c: MC.muted), children: [
                    TextSpan(text: 'Reason: ', style: ts(12, w: FontWeight.w700)), TextSpan(text: '$reason  •  '),
                    TextSpan(text: 'Date: ', style: ts(12, w: FontWeight.w700)), TextSpan(text: dateLabel)])))])),
            ])),
          const SizedBox(height: 12),
          MCard(child: Column(children: [
            Row(children: [
              const Icon(Icons.lock, size: 18, color: MC.outline), const SizedBox(width: 6),
              Expanded(child: Text('STATUS: OFFLINE (LOCKED)', style: ts(14, w: FontWeight.w700, c: MC.outline))),
              Opacity(opacity: .6, child: Container(width: 56, height: 32, padding: const EdgeInsets.all(4),
                decoration: BoxDecoration(color: MC.tintHi, borderRadius: BorderRadius.circular(99)),
                child: const CircleAvatar(backgroundColor: MC.outline, child: Icon(Icons.close, size: 12, color: Colors.white)))),
            ]),
            const SizedBox(height: 6),
            Text('Switching to Online is disabled during account suspension.', style: ts(12, c: MC.muted)),
            const SizedBox(height: 10),
            Container(padding: const EdgeInsets.all(10), decoration: BoxDecoration(color: MC.tintLow, borderRadius: BorderRadius.circular(8)),
              child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                Text('Dispatcher Clearance Status', style: ts(12, w: FontWeight.w600, c: MC.muted)),
                Text('ACTION REQUIRED', style: ts(11, w: FontWeight.w800, c: MC.coral))])),
          ])),
          const SizedBox(height: 12),
          MCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text('Assistance & Fleet Office', style: ts(18, w: FontWeight.w700)), const SizedBox(height: 12),
            _tile(Icons.phone, 'Logistics Dispatch Hotline', hotline, MC.violet, Colors.white, () => _open('tel:$hotline')),
            const SizedBox(height: 8),
            _tile(Icons.supervisor_account, 'Fleet Coordinator', '$coordinatorName\n$coordinatorHub', MC.tintHi, MC.muted, () => _open('sms:$coordinatorSms')),
          ])),
          const SizedBox(height: 12),
          const InfoBanner(Icons.info, 'Bring your documents', body: "Have your Professional Driver's License and Barangay Clearance ready when visiting or calling the hub office.", bg: MC.tintHi),
          const SizedBox(height: 16),
          PillButton('Log Out', icon: Icons.logout, style: BtnStyle.outline, onPressed: onLogout),
        ]));

  Widget _tile(IconData i, String k, String v, Color bg, Color fg, VoidCallback tap) => InkWell(
      onTap: tap, borderRadius: BorderRadius.circular(12),
      child: Container(padding: const EdgeInsets.all(12), decoration: BoxDecoration(color: MC.tintLow, borderRadius: BorderRadius.circular(12)),
        child: Row(children: [IconCircle(i, bg: bg, fg: fg), const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(k, style: ts(12, c: MC.muted)), Text(v, style: ts(14, w: FontWeight.w700))])),
          const Icon(Icons.chevron_right, color: MC.violetDark)])));
}
