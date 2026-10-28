import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../core/theme.dart';

class CollectedParcel { final String tracking, item, deliveredAt, address; final double cod;
  const CollectedParcel(this.tracking, this.item, this.cod, this.deliveredAt, this.address); }

enum RemitStatus { none, pending, confirmed, short }
enum RemitMethod { gcash, hubCounter }

/// Rebuilt: the export's amount field was missing, so this has a real input with live shortage logic.
class CodRemittanceScreen extends StatefulWidget {
  final List<CollectedParcel> parcels; final String fleetName, dueLabel; final RemitStatus status; final String? reference; final double outstanding;
  final Future<void> Function(double amount, RemitMethod method) onSubmit;
  const CodRemittanceScreen({super.key, required this.parcels, required this.fleetName, required this.dueLabel, required this.onSubmit,
    this.status = RemitStatus.none, this.reference, this.outstanding = 0});
  @override State<CodRemittanceScreen> createState() => _CodState();
}

class _CodState extends State<CodRemittanceScreen> {
  late final _c = TextEditingController(text: _due.toStringAsFixed(2));
  RemitMethod _m = RemitMethod.gcash; bool _busy = false;
  double get _due => widget.parcels.fold(0, (s, p) => s + p.cod);
  double get _amt => double.tryParse(_c.text) ?? 0;
  double get _gap => double.parse((_due - _amt).toStringAsFixed(2));
  @override void dispose() { _c.dispose(); super.dispose(); }

  @override
  Widget build(BuildContext context) {
    final short = _gap > 0, over = _gap < 0;
    return MScaffold(title: 'COD Balance', body: ListView(padding: const EdgeInsets.all(16), children: [
      MCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [const IconCircle(Icons.payments, bg: MC.coral, fg: Colors.white), const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text('COD TO REMIT', style: ts(11, w: FontWeight.w800, c: MC.coralDark)), Text(peso(_due), style: ts(28, w: FontWeight.w800))])),
          Pill('Due ${widget.dueLabel}', bg: MC.coralDark, fg: Colors.white, icon: Icons.alarm)]),
        const SizedBox(height: 12),
        InfoBanner(Icons.info, 'Remit the full collected amount', body: 'Send it to ${widget.fleetName} via GCash or the Hub Remittance Desk before closing shift.', fg: MC.coralDark),
      ])),
      const SizedBox(height: 12),
      MCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [Text('Collected Orders', style: ts(18, w: FontWeight.w700)), Pill('${widget.parcels.length} parcels')]),
        const SizedBox(height: 8),
        for (final p in widget.parcels) Container(margin: const EdgeInsets.only(top: 8), padding: const EdgeInsets.all(10), decoration: BoxDecoration(color: MC.tintLow, borderRadius: BorderRadius.circular(8)),
          child: Row(children: [Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(p.item, style: ts(14, w: FontWeight.w700)), Text('${p.tracking} · ${p.address}', style: ts(12, c: MC.muted))])),
            Column(crossAxisAlignment: CrossAxisAlignment.end, children: [Text(peso(p.cod), style: ts(14, w: FontWeight.w800, c: MC.violetDark)), Text('Delivered ${p.deliveredAt}', style: ts(11, c: MC.muted))])])),
        const SizedBox(height: 8),
        Container(padding: const EdgeInsets.all(10), decoration: BoxDecoration(color: MC.tint, borderRadius: BorderRadius.circular(8)),
          child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [Text('Total Reconciled Cash', style: ts(12, w: FontWeight.w600)), Text(peso(_due), style: ts(18, w: FontWeight.w800))])),
      ])),
      const SizedBox(height: 12),
      if (widget.status != RemitStatus.none) _statusCard() else MCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text('Remit Cash', style: ts(18, w: FontWeight.w700)), Text('Specify actual amount being deposited', style: ts(12, c: MC.muted)), const SizedBox(height: 12),
        TextField(controller: _c, onChanged: (_) => setState(() {}), keyboardType: const TextInputType.numberWithOptions(decimal: true),
          inputFormatters: [FilteringTextInputFormatter.allow(RegExp(r'^\d{0,6}(\.\d{0,2})?'))],
          style: ts(24, w: FontWeight.w800), decoration: InputDecoration(prefixText: '₱ ', filled: true, fillColor: MC.tintLow, border: OutlineInputBorder(borderRadius: BorderRadius.circular(99), borderSide: BorderSide.none))),
        const SizedBox(height: 8),
        Row(children: [ActionChip(label: Text('Match ${peso(_due)}'), onPressed: () => setState(() => _c.text = _due.toStringAsFixed(2)))]),
        const SizedBox(height: 8),
        if (short) InfoBanner(Icons.warning, '${peso(_gap)} short', body: 'Logistics will flag this difference and deduct it from weekly pay.', bg: MC.coralSoft, fg: MC.coral)
        else if (over) InfoBanner(Icons.error, '${peso(-_gap)} over', body: 'Amount exceeds the COD collected. Check your count.', bg: MC.errorSoft, fg: MC.coral)
        else InfoBanner(Icons.task_alt, 'Amount matches the daily total', body: 'Ready for clearing.'),
        const SizedBox(height: 12),
        Row(children: [
          for (final (m, t, s) in [(RemitMethod.gcash, 'GCash Hub', 'Instant receipt'), (RemitMethod.hubCounter, 'Hub Counter', 'Physical drop')])
            Expanded(child: InkWell(onTap: () => setState(() => _m = m), child: Container(margin: const EdgeInsets.all(2), padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(color: MC.tintLow, borderRadius: BorderRadius.circular(8), border: Border.all(color: _m == m ? MC.violet : Colors.transparent)),
              child: Row(children: [Icon(_m == m ? Icons.radio_button_checked : Icons.radio_button_off, size: 20, color: _m == m ? MC.violet : MC.outline), const SizedBox(width: 8),
                Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(t, style: ts(12, w: FontWeight.w700)), Text(s, style: ts(11, c: MC.muted))]))]))))]),
        const SizedBox(height: 12),
        PillButton('Submit Remittance ${peso(_amt)}', icon: Icons.arrow_forward, loading: _busy, onPressed: (_amt <= 0 || over) ? null : () async {
          setState(() => _busy = true); try { await widget.onSubmit(_amt, _m); } finally { if (mounted) setState(() => _busy = false); } }),
      ])),
    ]));
  }

  Widget _statusCard() {
    final (pill, bg, fg, icon, title, right) = switch (widget.status) {
      RemitStatus.pending => ('Pending confirmation by Logistics', MC.tintHi, MC.text, Icons.hourglass_top, '${peso(_due)} pending verification', 'Est. 15 mins'),
      RemitStatus.confirmed => ('Confirmed', MC.tintHi, MC.violetDark, Icons.check_circle, 'Balance: ${peso(0)}', 'Ref ${widget.reference ?? ''}'),
      _ => ('Short-remitted ${peso(widget.outstanding)} outstanding', MC.coral, Colors.white, Icons.report_problem, '${peso(widget.outstanding)} outstanding balance', 'Deduction pending'),
    };
    return MCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Text('Remittance Status', style: ts(18, w: FontWeight.w700)), const SizedBox(height: 8), Pill(pill, bg: bg, fg: fg, icon: icon), const SizedBox(height: 8),
      Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [Text(title, style: ts(14, w: FontWeight.w700)), Text(right, style: ts(12, c: MC.muted))])]));
  }
}
