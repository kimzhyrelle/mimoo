import 'package:flutter/material.dart';
import '../core/connectivity.dart';
import '../core/theme.dart';
import '../widgets/common.dart';

class JobsTab extends StatefulWidget {
  const JobsTab({super.key});
  @override
  State<JobsTab> createState() => _JobsTabState();
}

class _JobsTabState extends State<JobsTab> {
  int seg = 0; // 0 = For Pickup, 1 = For Delivery
  final accepted = <String>{};

  Widget _segment() => Container(
        padding: const EdgeInsets.all(4),
        decoration: BoxDecoration(color: MC.container, borderRadius: BorderRadius.circular(99)),
        child: Row(children: [
          for (final e in [(0, Icons.inventory_2, 'For Pickup (3)'), (1, Icons.local_shipping, 'For Delivery (4)')])
            Expanded(child: GestureDetector(
              onTap: () => setState(() => seg = e.$1),
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 180),
                padding: const EdgeInsets.symmetric(vertical: 10),
                decoration: BoxDecoration(color: seg == e.$1 ? MC.primaryContainer : Colors.transparent, borderRadius: BorderRadius.circular(99)),
                child: Row(mainAxisAlignment: MainAxisAlignment.center, children: [
                  Icon(e.$2, size: 18, color: seg == e.$1 ? Colors.white : MC.onSurfaceVariant),
                  const SizedBox(width: 6),
                  Text(e.$3, style: MT.labelLg.copyWith(color: seg == e.$1 ? Colors.white : MC.onSurfaceVariant)),
                ]),
              ),
            )),
        ]),
      );

  Widget _pickupCard(String id, String seller, String addr, int parcels, String ago, {bool isNew = false}) {
    final done = accepted.contains(id);
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: MCard(
        onTap: () => Navigator.pushNamed(context, '/pickup-detail'),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
            Row(children: [if (isNew) const MChip('New', bg: MC.tertiaryContainer, fg: Colors.white), if (isNew) const SizedBox(width: 6), const MChip('Bilis Hatid', bg: MC.highest)]),
            Text(ago, style: MT.bodySm),
          ]),
          const SizedBox(height: 10),
          Text(seller, style: MT.headlineMd),
          const SizedBox(height: 4),
          Row(crossAxisAlignment: CrossAxisAlignment.start, children: [const Icon(Icons.location_on, size: 18, color: MC.primary), const SizedBox(width: 6), Expanded(child: Text(addr, style: MT.bodyMd.copyWith(color: MC.onSurfaceVariant)))]),
          const SizedBox(height: 10),
          Container(padding: const EdgeInsets.all(10), decoration: BoxDecoration(color: MC.container, borderRadius: BorderRadius.circular(8)), child: Row(children: [const Icon(Icons.inventory, size: 20, color: MC.primary), const SizedBox(width: 8), Text('$parcels parcel${parcels > 1 ? 's' : ''} ready for dispatch', style: MT.labelMd)])),
          const SizedBox(height: 12),
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
            Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text('Payout Rate', style: MT.bodySm), Text('₱30.00', style: MT.currency.copyWith(color: MC.primary))]),
            SizedBox(width: 170, child: PillButton(done ? 'Accepted' : 'Accept Pickup', trailing: done ? Icons.check : Icons.arrow_forward, height: 44, onPressed: done ? null : () => setState(() => accepted.add(id)))),
          ]),
        ]),
      ),
    );
  }

  Widget _deliveryCard(String trk, String dest, String status, String pay, {bool cod = true}) => Padding(
        padding: const EdgeInsets.only(bottom: 12),
        child: MCard(
          onTap: () => Navigator.pushNamed(context, status == 'At Hub' ? '/out-for-delivery' : '/delivery'),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [Text(trk, style: MT.labelLg), MChip(status, bg: status == 'Out for Delivery' ? MC.primary : MC.secondaryFixed, fg: status == 'Out for Delivery' ? Colors.white : const Color(0xFF24005B))]),
            const SizedBox(height: 10),
            Text('Destination', style: MT.bodySm),
            Row(children: [const Icon(Icons.pin_drop, size: 18, color: MC.tertiaryContainer), const SizedBox(width: 6), Expanded(child: Text(dest, style: MT.bodyMd.copyWith(fontWeight: FontWeight.w600)))]),
            const SizedBox(height: 10),
            Row(children: [MChip(pay, bg: cod ? MC.container : MC.greenBg, fg: cod ? MC.tertiary : MC.green, icon: cod ? Icons.payments : Icons.check_circle)]),
          ]),
        ),
      );

  @override
  Widget build(BuildContext context) => ListView(padding: const EdgeInsets.all(16), children: [
        const ConnectivityBanner(),
        _segment(),
        const SizedBox(height: 16),
        if (seg == 0) ...[
          InfoBanner(Icons.near_me, '3 pickup requests available near you', body: 'Fairview & Commonwealth corridor', bg: MC.primaryFixed, fg: const Color(0xFF5114C4)),
          const SizedBox(height: 12),
          _pickupCard('p1', 'Maria Clara Handicrafts', 'Blk 12 Lot 8, Sampaguita St., Commonwealth, QC', 2, 'Requested 10 mins ago', isNew: true),
          _pickupCard('p2', "Auring's Native Delicacies", 'Fairview Market Stalls, Fairview, QC', 1, 'Requested 25 mins ago'),
          MCard(color: MC.container, child: Column(children: [
            const IconDot(Icons.moped, size: 56),
            const SizedBox(height: 8),
            Text('No pickup requests right now', style: MT.headlineMd),
            Text('Stay online to receive new requests automatically.', style: MT.bodySm, textAlign: TextAlign.center),
          ])),
        ] else ...[
          InfoBanner(Icons.backpack, 'Active Jobs: 2/3', body: 'You can accept 1 more pickup', bg: MC.secondaryFixed, fg: const Color(0xFF24005B)),
          const SizedBox(height: 16),
          Text('Available pickup requests', style: MT.headlineMd),
          const SizedBox(height: 8),
          SizedBox(
            height: 130,
            child: ListView(scrollDirection: Axis.horizontal, children: [
              for (final s in [('Manila Craft Hub', 'Fairview Center Mall', '1.1 km'), ('QC Books & Stationery', 'Regalado Highway', '1.8 km')])
                Container(width: 240, margin: const EdgeInsets.only(right: 8), child: MCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                  Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [Text(s.$3, style: MT.bodySm), Text('₱30.00', style: MT.headlineMd.copyWith(color: MC.primary))]),
                  Text(s.$1, style: MT.labelLg, overflow: TextOverflow.ellipsis),
                  Text(s.$2, style: MT.bodySm),
                  PillButton('Accept', trailing: null, height: 32, onPressed: () => Navigator.pushNamed(context, '/pickup-detail')),
                ]))),
            ]),
          ),
          const SizedBox(height: 16),
          Text('Assigned Packages', style: MT.headlineMd),
          const SizedBox(height: 8),
          _deliveryCard('TRK-0012345', '45 Kalayaan Ave., Brgy. Central, Quezon City', 'Out for Delivery', 'COD ₱500.00'),
          _deliveryCard('TRK-0012388', 'Batasan Hills, Quezon City', 'At Hub', 'Paid online · ₱0.00', cod: false),
        ],
      ]);
}
