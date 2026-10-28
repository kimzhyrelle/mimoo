import 'package:flutter/material.dart';
import '../core/theme.dart';
import '../widgets/common.dart';

class HistoryTab extends StatelessWidget {
  const HistoryTab({super.key});
  @override
  Widget build(BuildContext context) {
    Widget job(String trk, String type, String status, Color sBg, Color sFg, String time, String label, String loc, String earn, {String? cod}) => Padding(
          padding: const EdgeInsets.only(bottom: 12),
          child: MCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              Row(children: [Text(trk, style: MT.labelLg), const SizedBox(width: 8), MChip(type, bg: MC.container, fg: MC.secondary)]),
              MChip(status, bg: sBg, fg: sFg),
            ]),
            Text(time, style: MT.bodySm),
            const SizedBox(height: 10),
            Container(width: double.infinity, padding: const EdgeInsets.all(12), decoration: BoxDecoration(color: MC.low, borderRadius: BorderRadius.circular(8)), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(label.toUpperCase(), style: MT.badge.copyWith(color: MC.secondary)),
              Text(loc, style: MT.bodyMd.copyWith(fontWeight: FontWeight.w500)),
            ])),
            const SizedBox(height: 10),
            Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              if (cod != null) MChip(cod, bg: MC.highest, fg: MC.onSecondaryContainer) else const SizedBox(),
              Column(crossAxisAlignment: CrossAxisAlignment.end, children: [Text('EARNINGS', style: MT.badge.copyWith(color: MC.secondary)), Text(earn, style: MT.currency.copyWith(color: earn == '₱0.00' ? MC.outline : MC.primary))]),
            ]),
          ])),
        );
    return ListView(padding: const EdgeInsets.all(16), children: [
      Text('RIDER DISPATCH LOG', style: MT.badge.copyWith(color: MC.secondary)),
      Text('Job History', style: MT.headlineLg),
      const SizedBox(height: 12),
      Row(children: [
        for (final f in ['Today', 'This week', 'Custom'])
          Padding(padding: const EdgeInsets.only(right: 8), child: ChoiceChip(label: Text(f), selected: f == 'Today', showCheckmark: false, selectedColor: MC.primaryContainer, backgroundColor: MC.container, labelStyle: MT.labelLg.copyWith(color: f == 'Today' ? Colors.white : MC.onSurfaceVariant), shape: const StadiumBorder(), side: BorderSide.none, onSelected: (_) {})),
      ]),
      const SizedBox(height: 12),
      job('TRK-0012345', 'Delivery', 'Delivered', MC.high, MC.primary, 'Oct 28, 2026, 2:15 PM', 'Buyer location', '45 Kalayaan Ave., Brgy. Central, QC', '+₱50.00', cod: 'COD ₱500.00'),
      job('TRK-0012388', 'Pickup', 'Picked Up', MC.primaryFixed, const Color(0xFF5114C4), 'Oct 28, 2026, 11:30 AM', 'Seller hub', 'Maria Clara Handicrafts, Commonwealth', '+₱30.00'),
      job('TRK-0012301', 'Delivery', 'Failed (Attempt 1)', MC.highest, MC.onSurface, 'Oct 28, 2026, 09:15 AM', 'Incident note', 'Buyer not home · Rescheduled', '₱0.00'),
      job('TRK-0011982', 'Delivery', 'Return to Sender', MC.errorContainer, const Color(0xFF93000A), 'Oct 27, 2026, 4:40 PM', 'RTS reason', 'Refused by buyer · Returned to hub', '₱0.00'),
    ]);
  }
}
