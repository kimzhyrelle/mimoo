import 'package:flutter/material.dart';
import '../core/theme.dart';
import '../widgets/common.dart';

class DeliveredScreen extends StatelessWidget {
  final String? photoPath;
  const DeliveredScreen({super.key, this.photoPath});
  @override
  Widget build(BuildContext context) => Scaffold(
        body: SafeArea(child: ListView(padding: const EdgeInsets.all(16), children: [
          const SizedBox(height: 24),
          Center(child: Container(width: 80, height: 80, decoration: BoxDecoration(color: MC.lowest, shape: BoxShape.circle, boxShadow: [BoxShadow(color: MC.primaryFixed, blurRadius: 24)]), child: const Icon(Icons.check_circle, size: 44, color: MC.green))),
          const SizedBox(height: 16),
          Center(child: Text('Delivered at 2:15 PM', style: MT.headlineLg)),
          Center(child: Text('Handover completed · TRK-0012345', style: MT.bodySm)),
          const SizedBox(height: 12),
          Center(child: Container(padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8), decoration: BoxDecoration(color: MC.greenBg, borderRadius: BorderRadius.circular(99)), child: Text('+₱50.00 Delivery Pay', style: MT.labelLg.copyWith(color: const Color(0xFF166534))))),
          const SizedBox(height: 20),
          MCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text('Delivery Summary', style: MT.labelLg),
            const SizedBox(height: 8),
            Text('Juan Miguel D.', style: MT.headlineMd),
            Text('45 Kalayaan Ave., Brgy. Central, Quezon City', style: MT.bodySm),
            const SizedBox(height: 12),
            MCard(color: MC.high, child: Row(children: [const IconDot(Icons.account_balance_wallet, bg: MC.primaryFixed), const SizedBox(width: 10), Expanded(child: Text('COD Collected\nTotal on hand: ₱1,800.00', style: MT.labelMd)), Text('₱500.00', style: MT.currency.copyWith(color: MC.primary))])),
          ])),
          const SizedBox(height: 20),
          PillButton('Back to Jobs', onPressed: () => Navigator.pushNamedAndRemoveUntil(context, '/home', (_) => false)),
          const SizedBox(height: 8),
          PillButton.secondary('View COD Balance', leading: Icons.account_balance, onPressed: () => Navigator.pushNamed(context, '/cod-remittance')),
        ])),
      );
}
