import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../core/theme.dart';

class RegistrationApprovedScreen extends StatelessWidget {
  final String fleetName, riderId, hubName; final VoidCallback onLogin;
  const RegistrationApprovedScreen({super.key, required this.fleetName, required this.riderId, required this.hubName, required this.onLogin});
  @override
  Widget build(BuildContext context) => MScaffold(body: ListView(padding: const EdgeInsets.all(16), children: [
    MCard(child: Column(children: [
      const SizedBox(height: 8),
      Container(width: 64, height: 64, decoration: const BoxDecoration(color: MC.green, shape: BoxShape.circle), child: const Icon(Icons.check, color: Colors.white, size: 38)),
      const SizedBox(height: 12), Pill('Rider ID: $riderId · Status: Active Fleet', icon: Icons.circle, fg: MC.violetDark), const SizedBox(height: 12),
      Text('Your registration was approved!', textAlign: TextAlign.center, style: ts(22, w: FontWeight.w700)), const SizedBox(height: 4),
      Text('You can now log in and start accepting jobs with $fleetName.', textAlign: TextAlign.center, style: ts(14, c: MC.muted, h: 1.4)),
    ])),
    const SizedBox(height: 12),
    InfoBanner(Icons.storefront, 'Uniform & Thermal Bag Release', body: 'Collect at $hubName. Present your Rider ID: $riderId.', bg: MC.tintHi),
    const SizedBox(height: 16), PillButton('Go to Login', icon: Icons.arrow_forward, onPressed: onLogin),
  ]));
}

class RegistrationRejectedScreen extends StatelessWidget {
  final String reason, guidance, documentLabel, supportPhone; final VoidCallback onEditAndResubmit;
  const RegistrationRejectedScreen({super.key, required this.reason, required this.onEditAndResubmit, required this.supportPhone,
    this.guidance = 'Please take a clear photo in good lighting showing all four corners of the card. Avoid glare and reflections.', this.documentLabel = "Driver's License"});
  @override
  Widget build(BuildContext context) => MScaffold(body: ListView(padding: const EdgeInsets.all(16), children: [
    MCard(color: MC.tintLow, child: Column(children: [
      Transform.rotate(angle: .05, child: const IconCircle(Icons.shield, bg: MC.errorSoft, fg: MC.coralDark, size: 64)), const SizedBox(height: 12),
      const Pill('Action required', bg: MC.coralSoft, fg: MC.coral, icon: Icons.error), const SizedBox(height: 8),
      Text('Your registration was not approved', textAlign: TextAlign.center, style: ts(22, w: FontWeight.w700)),
      const SizedBox(height: 4), Text('Our verification officers flagged an issue with your submitted documents.', textAlign: TextAlign.center, style: ts(14, c: MC.muted, h: 1.4)),
    ])),
    const SizedBox(height: 12),
    MCard(color: MC.errorSoft.withValues(alpha: .4), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Row(children: [const Icon(Icons.report_problem, size: 20, color: Color(0xFF93000A)), const SizedBox(width: 8), Text('Compliance Finding', style: ts(14, w: FontWeight.w700, c: const Color(0xFF93000A)))]),
      const SizedBox(height: 8), Container(width: double.infinity, padding: const EdgeInsets.all(12), decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12)), child: Text('Reason: $reason', style: ts(12, w: FontWeight.w600, h: 1.4))),
      const SizedBox(height: 12), Row(crossAxisAlignment: CrossAxisAlignment.start, children: [const Icon(Icons.tips_and_updates, size: 20, color: MC.coral), const SizedBox(width: 8),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text('Action Guidance', style: ts(12, w: FontWeight.w700)), Text(guidance, style: ts(12, c: MC.muted, h: 1.4))]))]),
    ])),
    const SizedBox(height: 12),
    MCard(child: Row(children: [const IconCircle(Icons.badge, bg: MC.tint, fg: MC.outline), const SizedBox(width: 12),
      Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(documentLabel, style: ts(12, w: FontWeight.w700)), Text('Re-upload required', style: ts(12, w: FontWeight.w500, c: MC.coral))])), const Icon(Icons.cloud_upload, color: MC.violetDark)])),
    const SizedBox(height: 16),
    PillButton('Edit and Resubmit', icon: Icons.edit_document, onPressed: onEditAndResubmit), const SizedBox(height: 8),
    PillButton('Contact Logistics', icon: Icons.support_agent, style: BtnStyle.outline, onPressed: () => launchUrl(Uri.parse('tel:$supportPhone'))),
  ]));
}
