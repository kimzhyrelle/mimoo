import 'package:flutter/material.dart';
import '../core/theme.dart';
import '../widgets/common.dart';

class AccountTab extends StatelessWidget {
  const AccountTab({super.key});

  void _logout(BuildContext context) => showDialog(
        context: context,
        builder: (c) => AlertDialog(
          backgroundColor: MC.lowest,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: Text('Log out of Mimoo Rider?', style: MT.headlineMd),
          content: Column(mainAxisSize: MainAxisSize.min, children: [
            InfoBanner(Icons.report_problem, 'If you have a picked-up parcel you must still deliver it.', bg: MC.errorContainer.withValues(alpha: .5), fg: MC.onSurface),
          ]),
          actions: [
            TextButton(onPressed: () => Navigator.pop(c), child: const Text('Cancel')),
            FilledButton(
              style: FilledButton.styleFrom(backgroundColor: MC.tertiaryContainer),
              onPressed: () { Navigator.pop(c); Navigator.pushNamedAndRemoveUntil(context, '/login', (_) => false); },
              child: const Text('Log Out'),
            ),
          ],
        ),
      );

  @override
  Widget build(BuildContext context) {
    Widget tile(IconData i, String t, String s, {Widget? trailing, VoidCallback? onTap, bool danger = false}) => ListTile(
          onTap: onTap,
          leading: IconDot(i, size: 36, bg: danger ? MC.tertiaryContainer : MC.container, fg: danger ? Colors.white : MC.primary),
          title: Text(t, style: MT.labelLg.copyWith(color: danger ? MC.tertiary : MC.onSurface)),
          subtitle: Text(s, style: MT.bodySm),
          trailing: trailing ?? Icon(Icons.chevron_right, color: danger ? MC.tertiary : MC.onSurfaceVariant),
        );
    return ListView(padding: const EdgeInsets.all(16), children: [
      MCard(child: Row(children: [
        const CircleAvatar(radius: 32, backgroundColor: MC.highest, child: Icon(Icons.person, size: 32, color: MC.primary)),
        const SizedBox(width: 12),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [Flexible(child: Text('Pedro Jose Garcia', style: MT.headlineMd, overflow: TextOverflow.ellipsis)), const SizedBox(width: 4), const Icon(Icons.verified, size: 18, color: MC.primary)]),
          Text('Bilis Hatid Express Inc.', style: MT.bodySm),
          const SizedBox(height: 6),
          Row(children: [const MChip('Active', bg: MC.secondaryFixed, fg: Color(0xFF24005B)), const SizedBox(width: 6), const MChip('★ 4.96', bg: MC.container, fg: MC.onSurfaceVariant)]),
        ])),
      ])),
      const SizedBox(height: 12),
      MCard(padding: EdgeInsets.zero, child: Column(children: [
        tile(Icons.person_outline, 'Edit Profile', 'Update contact & address', onTap: () => _editProfile(context)),
        tile(Icons.lock_outline, 'Change Password', 'Credential security', onTap: () => _changePassword(context)),
        tile(Icons.two_wheeler, 'Vehicle & Documents', 'Plate 123ABC • OR/CR verified'),
        tile(Icons.account_balance_wallet_outlined, 'COD Remittance', 'Remit collected cash to logistics', onTap: () => Navigator.pushNamed(context, '/cod-remittance')),
        tile(Icons.notifications_none, 'Notifications', 'In-app alerts', trailing: const MChip('3 new', bg: MC.tertiaryContainer, fg: Colors.white)),
        tile(Icons.chat_bubble_outline, 'Messages', 'Seller, buyer & hub chats', onTap: () => Navigator.pushNamed(context, '/chat'), trailing: const MChip('2 new', bg: MC.primary, fg: Colors.white)),
        tile(Icons.support_agent, 'Help & Support', '24/7 Bilis Desk hotline'),
        tile(Icons.logout, 'Log Out', 'End shift & sign out', danger: true, onTap: () => _logout(context)),
      ])),
    ]);
  }

  void _editProfile(BuildContext context) => Navigator.push(context, MaterialPageRoute(builder: (_) => Scaffold(
        appBar: AppBar(title: const Text('Edit Profile'), backgroundColor: MC.header, foregroundColor: Colors.white),
        body: ListView(padding: const EdgeInsets.all(16), children: [
          MCard(color: MC.low, child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text('Verified Legal Identity', style: MT.labelLg),
            const SizedBox(height: 8),
            const _ReadOnly('Full legal name', 'Pedro Jose Garcia'),
            const _ReadOnly('Date of birth', 'Oct 24, 1995'),
            Text('Contact Logistics to change.', style: MT.bodySm),
          ])),
          const SizedBox(height: 16),
          const MField('Contact number', required: true, initial: '09189876543', keyboard: TextInputType.phone),
          const SizedBox(height: 12),
          const MField('Residential address', required: true, initial: '45 Kalayaan Ave., Brgy. Central, Quezon City'),
          const SizedBox(height: 20),
          PillButton('Save Changes', onPressed: () => Navigator.pop(context)),
        ]),
      )));

  void _changePassword(BuildContext context) => Navigator.push(context, MaterialPageRoute(builder: (_) => Scaffold(
        appBar: AppBar(title: const Text('Change Password'), backgroundColor: MC.header, foregroundColor: Colors.white),
        body: ListView(padding: const EdgeInsets.all(16), children: [
          const MField('Current password', obscure: true, required: true),
          const SizedBox(height: 12),
          const MField('New password', obscure: true, required: true),
          const SizedBox(height: 12),
          MCard(color: MC.low, child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            for (final r in ['At least 8 characters', 'Includes an uppercase letter', 'Includes a number or special character'])
              Padding(padding: const EdgeInsets.symmetric(vertical: 2), child: Row(children: [const Icon(Icons.check_circle, size: 18, color: MC.green), const SizedBox(width: 8), Text(r, style: MT.labelMd)])),
          ])),
          const SizedBox(height: 12),
          const MField('Confirm password', obscure: true, required: true),
          const SizedBox(height: 20),
          PillButton('Update Password', leading: Icons.lock_reset, trailing: null, onPressed: () => Navigator.pop(context)),
        ]),
      )));
}

class _ReadOnly extends StatelessWidget {
  final String l, v;
  const _ReadOnly(this.l, this.v);
  @override
  Widget build(BuildContext context) => Padding(padding: const EdgeInsets.only(bottom: 8), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text(l, style: MT.labelMd.copyWith(color: MC.onSurfaceVariant)),
        Container(width: double.infinity, padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12), decoration: BoxDecoration(color: MC.highest.withValues(alpha: .8), borderRadius: BorderRadius.circular(99)), child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [Text(v, style: MT.bodyMd), const Icon(Icons.lock, size: 18, color: MC.outline)])),
      ]));
}
