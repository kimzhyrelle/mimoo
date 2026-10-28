import 'package:flutter/material.dart';
import '../core/theme.dart';
import '../widgets/common.dart';

class EarningsTab extends StatefulWidget {
  const EarningsTab({super.key});
  @override
  State<EarningsTab> createState() => _EarningsTabState();
}

class _EarningsTabState extends State<EarningsTab> {
  int seg = 0; // Today / This Week / Profit
  @override
  Widget build(BuildContext context) {
    final labels = ['Today', 'This Week', 'Profit'];
    return ListView(padding: const EdgeInsets.all(16), children: [
      Container(
        padding: const EdgeInsets.all(4),
        decoration: BoxDecoration(color: MC.container, borderRadius: BorderRadius.circular(99)),
        child: Row(children: [
          for (var i = 0; i < 3; i++)
            Expanded(child: GestureDetector(onTap: () => setState(() => seg = i), child: Container(
              padding: const EdgeInsets.symmetric(vertical: 10),
              decoration: BoxDecoration(color: seg == i ? MC.primaryContainer : Colors.transparent, borderRadius: BorderRadius.circular(99)),
              child: Center(child: Text(labels[i], style: MT.labelLg.copyWith(color: seg == i ? Colors.white : MC.onSurfaceVariant))),
            ))),
        ]),
      ),
      const SizedBox(height: 16),
      if (seg == 2) ..._profit() else ..._today(seg == 1),
    ]);
  }

  List<Widget> _today(bool week) => [
        MCard(radius: 16, child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          const MChip('Net Payout Accrued', bg: MC.container, fg: MC.primaryContainer),
          const SizedBox(height: 8),
          Text(week ? '₱1,180.00' : '₱210.00', style: MT.currency.copyWith(fontSize: 32)),
          Text(week ? 'Oct 26 – Nov 1 · 21 jobs' : 'Oct 28, 2026 · 5 jobs completed', style: MT.bodyMd.copyWith(color: MC.onSurfaceVariant)),
        ])),
        const SizedBox(height: 12),
        MCard(color: MC.low, child: Row(children: [
          const IconDot(Icons.account_balance_wallet, bg: MC.secondaryContainer, fg: MC.onSecondaryContainer),
          const SizedBox(width: 10),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text('COD ON-HAND', style: MT.badge.copyWith(color: MC.onSurfaceVariant)), Text('₱1,800.00 to Remit', style: MT.headlineMd)])),
        ])),
        const SizedBox(height: 12),
        MCard(child: Column(children: [
          _row(Icons.inventory_2, '2 pickups', '2 × ₱30.00', '₱60.00'),
          _row(Icons.check_circle, '3 successful deliveries', '3 × ₱50.00', '₱150.00'),
          _row(Icons.cancel, '1 failed attempt', 'Customer unreachable', '₱0.00', muted: true),
          Container(margin: const EdgeInsets.only(top: 8), padding: const EdgeInsets.all(12), decoration: BoxDecoration(color: MC.low, borderRadius: BorderRadius.circular(12)), child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [Text("TODAY'S NET TOTAL", style: MT.badge), Text('₱210.00', style: MT.headlineLg.copyWith(color: MC.primaryContainer))])),
        ])),
      ];

  Widget _row(IconData i, String t, String s, String amt, {bool muted = false}) => Padding(
        padding: const EdgeInsets.symmetric(vertical: 6),
        child: Row(children: [
          IconDot(i, size: 32, bg: MC.container, fg: muted ? MC.outline : MC.primaryContainer),
          const SizedBox(width: 10),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(t, style: MT.bodyMd.copyWith(fontWeight: FontWeight.w500, color: muted ? MC.onSurfaceVariant : MC.onSurface)), Text(s, style: MT.bodySm)])),
          Text(amt, style: MT.headlineMd.copyWith(color: muted ? MC.outline : MC.primaryContainer)),
        ]),
      );

  // Profit view (ERP: commission-free profit)
  List<Widget> _profit() => [
        MCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [const Icon(Icons.tune, size: 18, color: MC.primary), const SizedBox(width: 6), Text('Date Range: Custom Period', style: MT.labelLg)]),
          const SizedBox(height: 10),
          Row(children: [
            Expanded(child: _date('From', 'Oct 01, 2026')),
            const SizedBox(width: 8),
            Expanded(child: _date('To', 'Oct 28, 2026')),
          ]),
          const SizedBox(height: 12),
          PillButton('Apply Filter Range', leading: Icons.refresh, trailing: null, height: 44, bg: MC.primary, onPressed: () {}),
        ])),
        const SizedBox(height: 12),
        MCard(color: MC.container, radius: 16, child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text('NET PROFIT ACCRUED', style: MT.badge.copyWith(color: MC.onSurfaceVariant)),
          Text('₱1,560.00', style: MT.headlineXl.copyWith(color: MC.primary)),
          Text('Oct 01 – Oct 28, 2026 • 38 jobs completed', style: MT.bodySm),
        ])),
        const SizedBox(height: 12),
        MCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text('Earnings Trend', style: MT.headlineMd),
          const SizedBox(height: 8),
          SizedBox(height: 140, width: double.infinity, child: CustomPaint(painter: _TrendPainter())),
          const SizedBox(height: 4),
          Row(mainAxisAlignment: MainAxisAlignment.spaceAround, children: [for (final w in [('Wk 1', '₱290'), ('Wk 2', '₱380'), ('Wk 3', '₱450'), ('Wk 4', '₱440')]) Column(children: [Text(w.$1, style: MT.bodySm), Text(w.$2, style: MT.badge.copyWith(color: MC.onSurface))])]),
        ])),
        const SizedBox(height: 12),
        for (final r in [
          (Icons.account_balance_wallet, 'Gross Earnings', 'Combined rider trip ledger', '₱1,560.00'),
          (Icons.storefront, 'Pickup Pay', '6 pickups × ₱30.00', '₱180.00'),
          (Icons.local_shipping, 'Delivery Pay', '27 deliveries × ₱50.00 + surge', '₱1,380.00'),
          (Icons.gavel, 'Deductions', 'Disputes, cancellations & penalties', '₱0.00'),
        ])
          Padding(padding: const EdgeInsets.only(bottom: 8), child: MCard(child: Row(children: [
            IconDot(r.$1), const SizedBox(width: 12),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(r.$2, style: MT.labelLg), Text(r.$3, style: MT.bodySm)])),
            Text(r.$4, style: MT.headlineMd),
          ]))),
        MCard(color: MC.high, child: Row(children: [
          const IconDot(Icons.check_circle, bg: MC.primaryContainer, fg: Colors.white), const SizedBox(width: 12),
          Expanded(child: Text('Net Profit', style: MT.labelLg)),
          Text('₱1,560.00', style: MT.currency.copyWith(color: MC.primary)),
        ])),
      ];

  Widget _date(String l, String v) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(color: MC.low, borderRadius: BorderRadius.circular(12)),
        child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(l, style: MT.bodySm), Text(v, style: MT.labelMd)]), const Icon(Icons.calendar_today, size: 18, color: MC.onSurfaceVariant)]),
      );
}

class _TrendPainter extends CustomPainter {
  @override
  void paint(Canvas c, Size s) {
    final pts = [Offset(0.06, .80), Offset(.35, .42), Offset(.62, .33), Offset(.94, .21)];
    Offset p(Offset o) => Offset(o.dx * s.width, o.dy * s.height);
    final path = Path()..moveTo(p(pts[0]).dx, p(pts[0]).dy);
    for (var i = 1; i < pts.length; i++) {
      final a = p(pts[i - 1]), b = p(pts[i]);
      path.cubicTo((a.dx + b.dx) / 2, a.dy, (a.dx + b.dx) / 2, b.dy, b.dx, b.dy);
    }
    final fill = Path.from(path)..lineTo(p(pts.last).dx, s.height)..lineTo(p(pts.first).dx, s.height)..close();
    c.drawPath(fill, Paint()..shader = LinearGradient(begin: Alignment.topCenter, end: Alignment.bottomCenter, colors: [MC.primaryContainer.withValues(alpha: .38), MC.primaryContainer.withValues(alpha: 0)]).createShader(Offset.zero & s));
    c.drawPath(path, Paint()..color = MC.primaryContainer..style = PaintingStyle.stroke..strokeWidth = 3..strokeCap = StrokeCap.round);
    for (final o in pts) {
      c.drawCircle(p(o), 5, Paint()..color = Colors.white);
      c.drawCircle(p(o), 5, Paint()..color = MC.primary..style = PaintingStyle.stroke..strokeWidth = 2.5);
    }
  }
  @override
  bool shouldRepaint(_) => false;
}
