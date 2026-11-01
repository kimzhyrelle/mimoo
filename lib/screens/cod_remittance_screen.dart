import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../core/theme.dart';
import '../data/parcel_flow.dart';
import '../widgets/common.dart';

enum RemitStatus { none, pending, confirmed, short }

enum RemitMethod { gcash, hubCounter }

/// Rider-side COD reconciliation. The amount must match the collected cash, and
/// every remittance needs a reference that the hub can verify.
class CodRemittanceScreen extends StatefulWidget {
  final List<DispatchParcel> parcels;
  final String fleetName, dueLabel;
  final RemitStatus status;
  final String? reference;
  final double outstanding;
  final Future<void> Function(
      double amount, RemitMethod method, String reference) onSubmit;
  const CodRemittanceScreen({
    super.key,
    required this.parcels,
    required this.fleetName,
    required this.dueLabel,
    required this.onSubmit,
    this.status = RemitStatus.none,
    this.reference,
    this.outstanding = 0,
  });
  @override
  State<CodRemittanceScreen> createState() => _CodState();
}

class _CodState extends State<CodRemittanceScreen> {
  late final _c = TextEditingController(text: _due.toStringAsFixed(2));
  final _ref = TextEditingController();
  RemitMethod _m = RemitMethod.gcash;
  bool _busy = false;

  double get _due => widget.parcels.fold(0, (s, p) => s + p.cod);
  double get _amt => double.tryParse(_c.text) ?? 0;
  double get _gap => double.parse((_due - _amt).toStringAsFixed(2));
  bool get _refValid =>
      _ref.text.trim().length >= 3 && _ref.text.trim().length <= 80;

  @override
  void dispose() {
    _c.dispose();
    _ref.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final short = _gap > 0, over = _gap < 0;
    final showEmpty =
        widget.parcels.isEmpty && widget.status == RemitStatus.none;
    return MScaffold(
        title: 'COD Balance',
        body: ListView(padding: MS.pagePadding, children: [
          if (showEmpty)
            MCard(
                color: MC.low,
                child: Column(children: [
                  const IconDot(Icons.task_alt,
                      size: 56, bg: MC.greenBg, fg: MC.green),
                  const SizedBox(height: 8),
                  Text('No unremitted COD', style: ts(18, w: FontWeight.w700)),
                  const SizedBox(height: 4),
                  Text('All collected cash is reconciled.',
                      style: ts(12, c: MC.muted), textAlign: TextAlign.center),
                ]))
          else ...[
            if (widget.parcels.isEmpty && widget.status != RemitStatus.none)
              _statusCard()
            else ...[
              MCard(
                  child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                    Row(children: [
                      const IconCircle(Icons.payments,
                          bg: MC.coral, fg: Colors.white),
                      const SizedBox(width: 12),
                      Expanded(
                          child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                            Text('COD TO REMIT',
                                style: ts(12,
                                    w: FontWeight.w800, c: MC.coralDark)),
                            Text(peso(_due), style: ts(28, w: FontWeight.w800)),
                          ])),
                      Pill('Due ${widget.dueLabel}',
                          bg: MC.coralDark,
                          fg: Colors.white,
                          icon: Icons.alarm),
                    ]),
                    const SizedBox(height: 12),
                    InfoBanner(Icons.info, 'Remit the full collected amount',
                        body:
                            'Send it to ${widget.fleetName} via GCash or the Hub Remittance Desk before closing shift.',
                        fg: MC.coralDark),
                  ])),
              const SizedBox(height: 12),
              MCard(
                  child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                    Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text('Collected Orders',
                              style: ts(18, w: FontWeight.w700)),
                          Pill('${widget.parcels.length} parcels'),
                        ]),
                    const SizedBox(height: 8),
                    for (final p in widget.parcels)
                      Container(
                        margin: const EdgeInsets.only(top: 8),
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                            color: MC.tintLow,
                            borderRadius: BorderRadius.circular(8)),
                        child: Row(children: [
                          Expanded(
                              child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                Text(p.item ?? 'Parcel',
                                    style: ts(14, w: FontWeight.w700)),
                                Text('${p.tracking} · ${p.address}',
                                    style: ts(12, c: MC.muted)),
                              ])),
                          Column(
                              crossAxisAlignment: CrossAxisAlignment.end,
                              children: [
                                Text(peso(p.cod),
                                    style: ts(14,
                                        w: FontWeight.w800, c: MC.violetDark)),
                                Text(
                                    'Delivered ${p.deliveredAt == null ? '—' : clock(p.deliveredAt!)}',
                                    style: ts(12, c: MC.muted)),
                              ]),
                        ]),
                      ),
                    const SizedBox(height: 8),
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                          color: MC.tint,
                          borderRadius: BorderRadius.circular(8)),
                      child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text('Total Reconciled Cash',
                                style: ts(12, w: FontWeight.w600)),
                            Text(peso(_due), style: ts(18, w: FontWeight.w800)),
                          ]),
                    ),
                  ])),
              const SizedBox(height: 12),
              if (widget.status != RemitStatus.none)
                _statusCard()
              else
                MCard(
                    child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                      Text('Remit Cash', style: ts(18, w: FontWeight.w700)),
                      Text('Specify actual amount being deposited',
                          style: ts(12, c: MC.muted)),
                      const SizedBox(height: 12),
                      TextField(
                        controller: _c,
                        onChanged: (_) => setState(() {}),
                        keyboardType: const TextInputType.numberWithOptions(
                            decimal: true),
                        inputFormatters: [
                          FilteringTextInputFormatter.allow(
                              RegExp(r'^\d{0,6}(\.\d{0,2})?'))
                        ],
                        style: ts(24, w: FontWeight.w800),
                        decoration: InputDecoration(
                            prefixText: '₱ ',
                            filled: true,
                            fillColor: MC.tintLow,
                            border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(99),
                                borderSide: BorderSide.none)),
                      ),
                      const SizedBox(height: 8),
                      Row(children: [
                        ActionChip(
                            label: Text('Match ${peso(_due)}'),
                            onPressed: () => setState(
                                () => _c.text = _due.toStringAsFixed(2))),
                      ]),
                      const SizedBox(height: 8),
                      if (short)
                        InfoBanner(Icons.warning, '${peso(_gap)} short',
                            body:
                                'Logistics will flag this difference and deduct it from weekly pay.',
                            bg: MC.coralSoft,
                            fg: MC.coral)
                      else if (over)
                        InfoBanner(Icons.error, '${peso(-_gap)} over',
                            body:
                                'Amount exceeds the COD collected. Check your count.',
                            bg: MC.errorSoft,
                            fg: MC.coral)
                      else
                        InfoBanner(
                            Icons.task_alt, 'Amount matches the daily total',
                            body: 'Ready for clearing.'),
                      const SizedBox(height: 12),
                      TextField(
                        controller: _ref,
                        onChanged: (_) => setState(() {}),
                        inputFormatters: [LengthLimitingTextInputFormatter(80)],
                        textCapitalization: TextCapitalization.characters,
                        decoration: InputDecoration(
                          labelText: 'Reference',
                          hintText: 'OR / GCash ref',
                          helperText: 'Required · 3–80 characters',
                          filled: true,
                          fillColor: MC.tintLow,
                          border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(16),
                              borderSide: BorderSide.none),
                        ),
                      ),
                      const SizedBox(height: 12),
                      Row(children: [
                        for (final (m, t, s) in [
                          (RemitMethod.gcash, 'GCash Hub', 'Instant receipt'),
                          (
                            RemitMethod.hubCounter,
                            'Hub Counter',
                            'Physical drop'
                          )
                        ])
                          Expanded(
                              child: InkWell(
                            onTap: () => setState(() => _m = m),
                            child: Container(
                              margin: const EdgeInsets.all(2),
                              padding: const EdgeInsets.all(10),
                              decoration: BoxDecoration(
                                  color: MC.tintLow,
                                  borderRadius: BorderRadius.circular(8),
                                  border: Border.all(
                                      color: _m == m
                                          ? MC.violet
                                          : Colors.transparent)),
                              child: Row(children: [
                                Icon(
                                    _m == m
                                        ? Icons.radio_button_checked
                                        : Icons.radio_button_off,
                                    size: 20,
                                    color: _m == m ? MC.violet : MC.outline),
                                const SizedBox(width: 8),
                                Expanded(
                                    child: Column(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        children: [
                                      Text(t,
                                          style: ts(12, w: FontWeight.w700)),
                                      Text(s, style: ts(12, c: MC.muted)),
                                    ])),
                              ]),
                            ),
                          )),
                      ]),
                      const SizedBox(height: 12),
                      PillButton(
                        'Submit Remittance ${peso(_amt)}',
                        icon: Icons.arrow_forward,
                        loading: _busy,
                        onPressed: (_amt <= 0 || over || !_refValid)
                            ? null
                            : () async {
                                setState(() => _busy = true);
                                try {
                                  await widget.onSubmit(
                                      _amt, _m, _ref.text.trim());
                                } finally {
                                  if (mounted) setState(() => _busy = false);
                                }
                              },
                      ),
                    ])),
            ],
          ],
        ]));
  }

  Widget _statusCard() {
    final (pill, bg, fg, icon, title, right) = switch (widget.status) {
      RemitStatus.pending => (
          'Pending confirmation by Logistics',
          MC.tintHi,
          MC.text,
          Icons.hourglass_top,
          '${peso(_due)} pending verification',
          'Est. 15 mins'
        ),
      RemitStatus.confirmed => (
          'Confirmed',
          MC.tintHi,
          MC.violetDark,
          Icons.check_circle,
          'Balance: ${peso(0)}',
          'Ref ${widget.reference ?? ''}'
        ),
      _ => (
          'Short-remitted ${peso(widget.outstanding)} outstanding',
          MC.coral,
          Colors.white,
          Icons.report_problem,
          '${peso(widget.outstanding)} outstanding balance',
          'Deduction pending'
        ),
    };
    return MCard(
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Text('Remittance Status', style: ts(18, w: FontWeight.w700)),
      const SizedBox(height: 8),
      Pill(pill, bg: bg, fg: fg, icon: icon),
      const SizedBox(height: 8),
      Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
        Text(title, style: ts(14, w: FontWeight.w700)),
        Text(right, style: ts(12, c: MC.muted)),
      ]),
    ]));
  }
}
