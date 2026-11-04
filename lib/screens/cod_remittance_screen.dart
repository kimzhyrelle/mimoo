import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import '../core/error_messages.dart';
import '../core/theme.dart';
import '../data/models/entities.dart';
import '../data/parcel_flow.dart';
import '../widgets/common.dart';

enum RemitMethod { gcash, hubCounter }

extension RemitMethodCopy on RemitMethod {
  String get label => switch (this) {
        RemitMethod.gcash => 'GCash',
        RemitMethod.hubCounter => 'Hub Remittance Desk',
      };

  String get hint => switch (this) {
        RemitMethod.gcash => 'Instant receipt',
        RemitMethod.hubCounter => 'Cash drop at the hub',
      };
}

/// Rider-side COD reconciliation. The summary stays pinned while the collected
/// orders list scrolls, and the Remit sheet confirms a full remittance locally.
class CodRemittanceScreen extends StatefulWidget {
  final List<DispatchParcel> parcels;
  final List<CodRemittance> history;
  final String fleetName, cutoff;
  final Future<void> Function(
      double amount, RemitMethod method, String reference) onRemit;
  const CodRemittanceScreen({
    super.key,
    required this.parcels,
    required this.history,
    required this.fleetName,
    required this.cutoff,
    required this.onRemit,
  });
  @override
  State<CodRemittanceScreen> createState() => _CodState();
}

class _CodState extends State<CodRemittanceScreen> {
  double get _total => widget.parcels.fold(0, (sum, p) => sum + p.cod);

  DateTime? get _cutoffToday {
    try {
      final parsed = DateFormat('h:mm a', 'en').parse(widget.cutoff);
      final now = DateTime.now();
      return DateTime(
          now.year, now.month, now.day, parsed.hour, parsed.minute);
    } catch (_) {
      return null;
    }
  }

  bool get _overdue {
    final cutoff = _cutoffToday;
    return cutoff != null && DateTime.now().isAfter(cutoff);
  }

  bool get _nearCutoff {
    final cutoff = _cutoffToday;
    if (cutoff == null || _overdue) return false;
    return cutoff.difference(DateTime.now()) <= const Duration(hours: 2);
  }

  List<DispatchParcel> get _sorted {
    final list = [...widget.parcels];
    list.sort((a, b) => (b.deliveredAt ?? b.updatedAt)
        .compareTo(a.deliveredAt ?? a.updatedAt));
    return list;
  }

  Future<void> _openRemit() async {
    if (_total <= 0) return;
    final submitted = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: MC.of(context).lowest,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(28))),
      builder: (_) => _RemitSheet(
        amount: _total,
        fleetName: widget.fleetName,
        cutoff: widget.cutoff,
        onSubmit: widget.onRemit,
      ),
    );
    if (submitted == true && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Remittance submitted. Balance is now cleared.')));
    }
  }

  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    return MScaffold(
      title: 'COD Balance',
      body: Column(children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
          child: _summaryCard(c),
        ),
        Expanded(
          child: ListView(
            padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),
            children: [
              if (widget.parcels.isEmpty)
                _emptyCard(c)
              else ...[
                _sectionHeader(c, 'Collected Orders',
                    '${widget.parcels.length} parcels'),
                for (final p in _sorted) _orderTile(c, p),
              ],
              if (widget.history.isNotEmpty) ...[
                const SizedBox(height: 8),
                _sectionHeader(c, 'Remitted', null),
                for (final r in widget.history) _historyTile(c, r),
              ],
            ],
          ),
        ),
      ]),
    );
  }

  Widget _summaryCard(MimooColors c) {
    final total = _total;
    final urgent = _overdue || _nearCutoff;
    final pillBg = urgent ? c.coral : c.primary;
    final pillFg = c.lowest;
    return MCard(
      color: c.lowest,
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          IconCircle(Icons.payments, bg: c.coral, fg: c.lowest),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('COD TO REMIT',
                      style: ts(12, w: FontWeight.w800, c: c.coral)),
                  Text(peso(total),
                      style: ts(28, w: FontWeight.w800, c: c.text)
                          .copyWith(fontFeatures: const [
                        FontFeature.tabularFigures()
                      ])),
                ]),
          ),
          Pill('Due ${widget.cutoff}',
              bg: pillBg, fg: pillFg, icon: Icons.alarm),
        ]),
        const SizedBox(height: 12),
        InfoBanner(
          Icons.info,
          'Remit the full collected amount',
          body:
              'Send it to ${widget.fleetName} via GCash or the Hub Remittance Desk before ${widget.cutoff}.',
          bg: c.tint,
          fg: c.primary,
        ),
        if (total > 0) ...[
          const SizedBox(height: 12),
          PillButton('Remit ${peso(total)}',
              icon: Icons.arrow_forward, onPressed: _openRemit),
        ],
      ]),
    );
  }

  Widget _sectionHeader(MimooColors c, String title, String? trailing) =>
      Padding(
        padding: const EdgeInsets.only(top: 4, bottom: 8),
        child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(title, style: ts(18, w: FontWeight.w700, c: c.text)),
              if (trailing != null) Pill(trailing),
            ]),
      );

  Widget _emptyCard(MimooColors c) => MCard(
        color: c.lowest,
        child: Column(children: [
          IconDot(Icons.task_alt, size: 56, bg: c.greenSoft, fg: c.green),
          const SizedBox(height: 8),
          Text('Balance ${peso(0)}',
              style: ts(20, w: FontWeight.w800, c: c.text)),
          const SizedBox(height: 4),
          Text('All collected cash has been remitted.',
              style: ts(12, c: c.muted), textAlign: TextAlign.center),
        ]),
      );

  Widget _orderTile(MimooColors c, DispatchParcel p) {
    final delivered = p.deliveredAt;
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: c.lowest,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: c.outlineVariant),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
        Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Expanded(
            child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(p.item ?? 'Parcel',
                      style: ts(14, w: FontWeight.w700, c: c.text)),
                  Text(p.buyer, style: ts(12, c: c.muted)),
                ]),
          ),
          Text(peso(p.cod),
              style: ts(14, w: FontWeight.w800, c: c.primary).copyWith(
                  fontFeatures: const [FontFeature.tabularFigures()])),
        ]),
        const SizedBox(height: 6),
        Row(children: [
          Expanded(
            child: Text(
              '${p.tracking}${kDot}Route ${p.routeCode ?? '—'}',
              style: ts(12, c: c.muted),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
          Text(
            delivered == null
                ? '—'
                : '${DateFormat('MMM d', 'en').format(delivered)}$kDot${clock(delivered)}',
            style: ts(12, c: c.muted).copyWith(
                fontFeatures: const [FontFeature.tabularFigures()]),
            textAlign: TextAlign.end,
          ),
        ]),
      ]),
    );
  }

  Widget _historyTile(MimooColors c, CodRemittance r) => Container(
        margin: const EdgeInsets.only(bottom: 8),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: c.low,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Row(children: [
          IconDot(Icons.check_circle_outline,
              size: 36, bg: c.greenSoft, fg: c.green),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(peso(r.amount),
                      style: ts(14, w: FontWeight.w700, c: c.text).copyWith(
                          fontFeatures: const [
                            FontFeature.tabularFigures()
                          ])),
                  Text(
                      '${r.reference}$kDot${r.method == RemitMethod.gcash.name ? RemitMethod.gcash.label : RemitMethod.hubCounter.label}',
                      style: ts(12, c: c.muted)),
                ]),
          ),
          Text(DateFormat('MMM d', 'en').format(r.createdAt),
              style: ts(12, c: c.muted)),
        ]),
      );
}

class _RemitSheet extends StatefulWidget {
  final double amount;
  final String fleetName, cutoff;
  final Future<void> Function(
      double amount, RemitMethod method, String reference) onSubmit;
  const _RemitSheet({
    required this.amount,
    required this.fleetName,
    required this.cutoff,
    required this.onSubmit,
  });
  @override
  State<_RemitSheet> createState() => _RemitSheetState();
}

class _RemitSheetState extends State<_RemitSheet> {
  final _reference = TextEditingController();
  RemitMethod _method = RemitMethod.gcash;
  bool _busy = false;
  String? _error;

  bool get _valid => _reference.text.trim().length >= 3;

  @override
  void dispose() {
    _reference.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_valid || _busy) return;
    setState(() {
      _busy = true;
      _error = null;
    });
    try {
      await widget.onSubmit(
          widget.amount, _method, _reference.text.trim());
      if (mounted) Navigator.pop(context, true);
    } catch (error) {
      if (mounted) setState(() => _error = friendlyError(error));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    return Padding(
      padding: EdgeInsets.fromLTRB(
          20, 12, 20, 20 + MediaQuery.of(context).viewInsets.bottom),
      child: SingleChildScrollView(
        child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                  child: Container(
                      width: 44,
                      height: 5,
                      decoration: BoxDecoration(
                          color: c.outlineVariant,
                          borderRadius: BorderRadius.circular(9)))),
              const SizedBox(height: 16),
              Text('Remit COD', style: ts(20, w: FontWeight.w700, c: c.text)),
              const SizedBox(height: 4),
              Text(
                  'Send ${peso(widget.amount)} to ${widget.fleetName} before ${widget.cutoff}.',
                  style: ts(13, c: c.muted, h: 1.4)),
              const SizedBox(height: 16),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                    color: c.low, borderRadius: BorderRadius.circular(12)),
                child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('AMOUNT',
                          style:
                              ts(12, w: FontWeight.w800, c: c.muted)),
                      Text(peso(widget.amount),
                          style: ts(28, w: FontWeight.w800, c: c.text)
                              .copyWith(fontFeatures: const [
                            FontFeature.tabularFigures()
                          ])),
                    ]),
              ),
              const SizedBox(height: 16),
              Text('PAYMENT METHOD',
                  style: ts(12, w: FontWeight.w800, c: c.muted)),
              const SizedBox(height: 6),
              for (final method in RemitMethod.values)
                _methodTile(c, method),
              const SizedBox(height: 4),
              TextField(
                controller: _reference,
                onChanged: (_) => setState(() {}),
                inputFormatters: [LengthLimitingTextInputFormatter(80)],
                textCapitalization: TextCapitalization.characters,
                decoration: InputDecoration(
                  labelText: 'Reference',
                  hintText: 'OR / GCash ref',
                  helperText: 'Required · 3–80 characters',
                  filled: true,
                  fillColor: c.low,
                  border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(16),
                      borderSide: BorderSide.none),
                ),
              ),
              if (_error != null) ...[
                const SizedBox(height: 8),
                Text(_error!,
                    style: ts(12, w: FontWeight.w600, c: c.coral)),
              ],
              const SizedBox(height: 16),
              PillButton('Confirm remittance',
                  icon: Icons.check_circle,
                  loading: _busy,
                  onPressed: !_valid || _busy ? null : _submit),
            ]),
      ),
    );
  }

  Widget _methodTile(MimooColors c, RemitMethod method) {
    final selected = _method == method;
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: InkWell(
        onTap: () => setState(() => _method = method),
        borderRadius: BorderRadius.circular(12),
        child: Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: selected ? c.tint : c.low,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
                color: selected ? c.primary : c.outlineVariant),
          ),
          child: Row(children: [
            Icon(
                selected
                    ? Icons.radio_button_checked
                    : Icons.radio_button_off,
                color: selected ? c.primary : c.outline),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(method.label,
                        style: ts(14, w: FontWeight.w700, c: c.text)),
                    Text(method.hint, style: ts(12, c: c.muted)),
                  ]),
            ),
          ]),
        ),
      ),
    );
  }
}
