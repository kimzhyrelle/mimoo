import 'dart:async';
import 'dart:math' as math;
import 'package:flutter/material.dart';
import '../core/theme.dart';

class JobOffer {
  final String id, tracking, merchant, pickupArea, dropArea, parcelLabel; final double pickupPay, cod, km;
  final DateTime expiresAt; final int totalSeconds;
  const JobOffer({required this.id, required this.tracking, required this.merchant, required this.pickupArea, required this.dropArea,
    required this.parcelLabel, required this.pickupPay, required this.cod, required this.km, required this.expiresAt, this.totalSeconds = 60});
}

enum OfferResult { accepted, taken, expired }
enum OfferStatus { active, expired, taken }

/// Countdown is derived from the server's expiresAt, so it survives rebuilds and app resume.
class JobOfferScreen extends StatefulWidget {
  final JobOffer offer; final Future<OfferResult> Function() onAccept; final VoidCallback onDecline, onBackHome; final VoidCallback? onExpired;
  const JobOfferScreen({super.key, required this.offer, required this.onAccept, required this.onDecline, required this.onBackHome, this.onExpired});
  @override State<JobOfferScreen> createState() => _JobOfferState();
}

class _JobOfferState extends State<JobOfferScreen> {
  late Timer _t; late int _left; OfferStatus _status = OfferStatus.active; bool _busy = false;
  int _calc() => math.max(0, widget.offer.expiresAt.difference(DateTime.now()).inSeconds);

  @override
  void initState() {
    super.initState();
    _left = _calc();
    _t = Timer.periodic(const Duration(seconds: 1), (_) {
      final l = _calc();
      if (l <= 0) { _t.cancel(); if (_status == OfferStatus.active) { setState(() { _left = 0; _status = OfferStatus.expired; }); widget.onExpired?.call(); } }
      else { setState(() => _left = l); }
    });
  }
  @override void dispose() { _t.cancel(); super.dispose(); }

  Future<void> _accept() async {
    setState(() => _busy = true);
    final r = await widget.onAccept();
    if (!mounted) return;
    setState(() { _busy = false; if (r == OfferResult.taken) _status = OfferStatus.taken; if (r == OfferResult.expired) _status = OfferStatus.expired; });
  }

  @override
  Widget build(BuildContext context) {
    final o = widget.offer;
    return MScaffold(body: ListView(padding: const EdgeInsets.all(16), children: [
      if (_status == OfferStatus.expired) _closed(Icons.timer_off, 'Offer expired', 'TIMEOUT',
          'This offer has expired and was automatically dispatched to the next closest rider. Keep your status active to catch new bookings!')
      else if (_status == OfferStatus.taken) _closed(Icons.sync, 'Already assigned', 'TAKEN', 'Accepted by another nearby partner rider')
      else MCard(padding: EdgeInsets.zero, child: Column(children: [
        Container(padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          decoration: const BoxDecoration(gradient: LinearGradient(colors: [MC.coral, MC.coralDark, MC.coral]), borderRadius: BorderRadius.vertical(top: Radius.circular(12))),
          child: Row(children: [Text('mimoo', style: ts(18, w: FontWeight.w800, c: Colors.white)), const SizedBox(width: 8), const Pill('Rider'), const Spacer(),
            IconButton(visualDensity: VisualDensity.compact, icon: const Icon(Icons.close, color: Colors.white, size: 18), onPressed: widget.onDecline)])),
        Padding(padding: const EdgeInsets.all(20), child: Column(children: [
          SizedBox(width: 144, height: 144, child: Stack(alignment: Alignment.center, children: [
            SizedBox.expand(child: CircularProgressIndicator(value: _left / o.totalSeconds, strokeWidth: 8, strokeCap: StrokeCap.round, color: _left <= 10 ? MC.coral : MC.coral, backgroundColor: MC.tint)),
            Column(mainAxisSize: MainAxisSize.min, children: [Text('${_left}s', style: ts(28, w: FontWeight.w800)), Text('REMAINING', style: ts(11, w: FontWeight.w800, c: MC.coralDark))]),
          ])),
          const SizedBox(height: 8),
          Text('Expires in ${o.totalSeconds}s from dispatch • Job auto-reallocates', style: ts(12, c: MC.muted)),
          const SizedBox(height: 16),
          Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(color: MC.tintLow, borderRadius: BorderRadius.circular(8)),
            child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text('Pickup Pay', style: ts(12, c: MC.muted)), Text(peso(o.pickupPay), style: ts(28, w: FontWeight.w800, c: MC.violetDark))]),
              Column(crossAxisAlignment: CrossAxisAlignment.end, children: [Pill('COD ${peso(o.cod)}', bg: MC.coralSoft, fg: MC.coral), const SizedBox(height: 2), Text('To Collect', style: ts(12, w: FontWeight.w600, c: MC.muted))]),
            ])),
          const SizedBox(height: 16),
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [Pill(o.parcelLabel, icon: Icons.inventory_2), Text(o.tracking, style: ts(12, c: MC.muted))]),
          const SizedBox(height: 12),
          _node(MC.violetDark, 'PICKUP LOCATION', o.merchant, o.pickupArea),
          _node(MC.coralDark, 'DROP-OFF ZONE', o.dropArea, 'Full address revealed upon acceptance', last: true),
          const SizedBox(height: 8),
          Align(alignment: Alignment.centerLeft, child: Pill('${o.km} km', bg: MC.tintHi, fg: MC.text, icon: Icons.near_me)),
          const SizedBox(height: 16),
          PillButton('Accept Offer', icon: Icons.arrow_forward, loading: _busy, onPressed: _accept),
          const SizedBox(height: 4),
          TextButton(onPressed: widget.onDecline, child: Text('Decline', style: ts(14, w: FontWeight.w700, c: MC.violetDark))),
        ])),
      ])),
    ]));
  }

  Widget _node(Color c, String k, String v, String sub, {bool last = false}) => IntrinsicHeight(child: Row(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
    SizedBox(width: 24, child: Column(children: [const SizedBox(height: 4), CircleAvatar(radius: 6, backgroundColor: c), if (!last) Expanded(child: Container(width: 2, color: MC.tintHi))])),
    Expanded(child: Padding(padding: EdgeInsets.only(bottom: last ? 0 : 12), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Text(k, style: ts(11, w: FontWeight.w800, c: c)), Text(v, style: ts(18, w: FontWeight.w700)), Text(sub, style: ts(12, c: MC.muted))]))),
  ]));

  Widget _closed(IconData i, String title, String tag, String body) => MCard(color: MC.tint, child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
    Row(children: [IconCircle(i, bg: MC.tintHi), const SizedBox(width: 12), Text(title, style: ts(18, w: FontWeight.w700)), const SizedBox(width: 8), Pill(tag)]),
    const SizedBox(height: 8), Text(body, style: ts(12, c: MC.muted, h: 1.4)), const SizedBox(height: 12),
    PillButton('Back to Home', icon: Icons.arrow_back, style: BtnStyle.outline, onPressed: widget.onBackHome),
  ]));
}
