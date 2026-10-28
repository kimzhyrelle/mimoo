import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../core/theme.dart';
import '../data/mock_data.dart';
import '../widgets/common.dart';
import 'pickup_detail_screen.dart';

class DeliveryDetailScreen extends StatelessWidget {
  const DeliveryDetailScreen({super.key});

  Future<void> _callBuyer(BuildContext context) async {
    final messenger = ScaffoldMessenger.of(context);
    final uri = Uri.parse('tel:${mockHubParcel.phone}');
    var opened = false;
    try {
      opened = await canLaunchUrl(uri) && await launchUrl(uri);
    } catch (_) {
      opened = false;
    }
    if (!opened && context.mounted) {
      messenger.showSnackBar(const SnackBar(content: Text("Couldn't start the call")));
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(
          backgroundColor: MC.header, foregroundColor: Colors.white,
          title: Text('TRK-0012345', style: MT.labelLg.copyWith(color: Colors.white)),
          actions: const [Padding(padding: EdgeInsets.only(right: 12), child: MChip('Live dispatch synced', bg: MC.container, fg: MC.primary))],
        ),
        body: ListView(padding: const EdgeInsets.all(16), children: [
          MCard(child: Column(children: [
            Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [Text('DELIVERY LIFECYCLE', style: MT.badge.copyWith(color: MC.onSurfaceVariant)), Text('Step 4 of 5', style: MT.badge.copyWith(color: MC.primary))]),
            const SizedBox(height: 12),
            Row(children: [
              for (final s in [('Assigned', 1), ('Picked Up', 1), ('At Hub', 1), ('Out for Delivery', 2), ('Delivered', 0)])
                Expanded(child: Column(children: [
                  CircleAvatar(radius: 12, backgroundColor: s.$2 == 0 ? MC.highest : (s.$2 == 2 ? MC.primaryContainer : MC.primary), child: Icon(s.$2 == 1 ? Icons.check : (s.$2 == 2 ? Icons.sports_motorsports : Icons.home), size: 14, color: s.$2 == 0 ? MC.onSurfaceVariant : Colors.white)),
                  const SizedBox(height: 4),
                  Text(s.$1, style: MT.badge.copyWith(fontSize: 9, color: s.$2 == 2 ? MC.primaryContainer : MC.onSurfaceVariant), textAlign: TextAlign.center),
                ])),
            ]),
          ])),
          const SizedBox(height: 12),
          MCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Row(children: [
              const CircleAvatar(radius: 24, backgroundColor: MC.highest, child: Icon(Icons.person, color: MC.primary)),
              const SizedBox(width: 12),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text('Juan Miguel D.', style: MT.headlineMd), Text('Verified Mimoo Buyer · 48 Orders', style: MT.bodySm)])),
              const MChip('Doorstep', bg: MC.secondaryFixed, fg: Color(0xFF24005B)),
            ]),
            const SizedBox(height: 12),
            Row(children: [
              Expanded(child: OutlinedButton.icon(onPressed: () => _callBuyer(context), icon: const Icon(Icons.call, size: 18), label: const Text('Call'), style: OutlinedButton.styleFrom(shape: const StadiumBorder(), minimumSize: const Size.fromHeight(44), foregroundColor: MC.primary, side: BorderSide.none, backgroundColor: MC.container))),
              const SizedBox(width: 8),
              Expanded(child: FilledButton.icon(onPressed: () => Navigator.pushNamed(context, '/chat/thread'), icon: const Icon(Icons.chat, size: 18), label: const Text('Message'), style: FilledButton.styleFrom(shape: const StadiumBorder(), minimumSize: const Size.fromHeight(44), backgroundColor: MC.primary))),
            ]),
            const SizedBox(height: 12),
            Container(padding: const EdgeInsets.all(12), decoration: BoxDecoration(color: MC.low, borderRadius: BorderRadius.circular(12)), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text('DROP-OFF ADDRESS', style: MT.badge.copyWith(color: MC.onSurfaceVariant)),
              Text('45 Kalayaan Ave., Brgy. Central, Quezon City', style: MT.bodyLg.copyWith(fontWeight: FontWeight.w600)),
              const SizedBox(height: 8),
              Row(children: [const Icon(Icons.near_me, size: 18, color: MC.tertiary), const SizedBox(width: 8), Expanded(child: Text('"Blue gate beside bakery, ring the small doorbell twice"', style: MT.labelMd))]),
            ])),
          ])),
          const SizedBox(height: 12),
          MCard(color: MC.tertiaryFixed.withValues(alpha: .4), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            const Row(children: [MChip('Cash on delivery', bg: MC.tertiary, fg: Colors.white), SizedBox(width: 6), MChip('Mandatory', bg: MC.tertiaryFixed, fg: Color(0xFF92002A))]),
            const SizedBox(height: 8),
            Text('RIDER MUST COLLECT', style: MT.badge.copyWith(color: MC.onSurfaceVariant)),
            Text('₱500.00', style: MT.currency.copyWith(color: MC.tertiary)),
            Text('Items ₱450.00 + Delivery ₱50.00 · Count cash before handing over.', style: MT.bodySm.copyWith(color: MC.tertiary)),
          ])),
          const SizedBox(height: 16),
          PillButton.secondary('Navigate (Google Maps / Waze)', leading: Icons.near_me, onPressed: () => showNavigationPicker(context, address: '45 Kalayaan Ave., Brgy. Central, Quezon City', etaMins: 12, km: 4.2, title: 'Navigate to Customer')),
          const SizedBox(height: 8),
          PillButton('Proof of Delivery', onPressed: () => Navigator.pushNamed(context, '/pod')),
          TextButton.icon(onPressed: () {}, icon: const Icon(Icons.error, color: MC.tertiary, size: 18), label: Text('Delivery failed / Customer unavailable', style: MT.labelMd.copyWith(color: MC.tertiary))),
        ]),
      );
}
