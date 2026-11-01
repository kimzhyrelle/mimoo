import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../core/theme.dart';
import '../data/parcel_flow.dart';
import '../widgets/custody_timeline.dart';

const List<String> lifecycleSteps = [
  'Inbound',
  'Sorted',
  'At Hub',
  'Out for Del.',
  'Delivered'
];

/// "At Hub" job detail. Proof of Delivery stays locked until the rider marks Out
/// for Delivery (Delivered cannot be skipped).
class OutForDeliveryScreen extends StatefulWidget {
  final DispatchParcel parcel;
  final VoidCallback onBack;
  final Future<bool> Function() onMarkOut;
  const OutForDeliveryScreen({
    super.key,
    required this.parcel,
    required this.onBack,
    required this.onMarkOut,
  });
  @override
  State<OutForDeliveryScreen> createState() => _OfdState();
}

class _OfdState extends State<OutForDeliveryScreen> {
  bool _busy = false;
  late bool _done;

  @override
  void initState() {
    super.initState();
    _done = widget.parcel.status != ParcelStatus.assigned;
  }

  @override
  Widget build(BuildContext context) {
    final p = widget.parcel;
    final current = _done ? 3 : p.lifecycleStep;
    return MScaffold(
      title: p.tracking,
      onBack: widget.onBack,
      actions: [
        Padding(
          padding: const EdgeInsets.only(right: 12),
          child: Pill(_done ? 'Out for delivery' : 'At hub',
              bg: MC.violet, fg: Colors.white),
        ),
      ],
      body: ListView(padding: MS.pagePadding, children: [
        MCard(
            child: Column(children: [
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
            Text('Delivery Progress', style: ts(18, w: FontWeight.w700)),
            Text('STEP ${current + 1} OF 5',
                style: ts(12, w: FontWeight.w800, c: MC.violetDark)),
          ]),
          const SizedBox(height: 12),
          Row(children: [
            for (var i = 0; i < lifecycleSteps.length; i++)
              Expanded(
                  child: Column(children: [
                CircleAvatar(
                  radius: 16,
                  backgroundColor: i < current
                      ? MC.tintHi
                      : i == current
                          ? MC.violet
                          : MC.tint,
                  child: Icon(
                    i < current
                        ? Icons.check
                        : [
                            Icons.assignment,
                            Icons.inventory,
                            Icons.warehouse,
                            Icons.local_shipping,
                            Icons.verified
                          ][i],
                    size: 16,
                    color: i == current ? Colors.white : MC.violetDark,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  lifecycleSteps[i],
                  textAlign: TextAlign.center,
                  style: ts(12,
                      w: i == current ? FontWeight.w800 : FontWeight.w600,
                      c: i > current ? MC.outline : MC.text),
                ),
              ])),
          ]),
        ])),
        const SizedBox(height: 12),
        MCard(
            child:
                Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            const IconCircle(Icons.person, size: 48),
            const SizedBox(width: 12),
            Expanded(
                child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                  Text('Recipient', style: ts(12, c: MC.muted)),
                  Text(p.buyer, style: ts(18, w: FontWeight.w800)),
                  Text(p.phone, style: ts(12, c: MC.muted)),
                ])),
            IconButton.filledTonal(
                icon: const Icon(Icons.call),
                onPressed: () => launchUrl(Uri.parse('tel:${p.phone}'))),
            IconButton.filled(
                style: IconButton.styleFrom(backgroundColor: MC.violetDark),
                icon: const Icon(Icons.sms),
                onPressed: () => launchUrl(Uri.parse('sms:${p.phone}'))),
          ]),
          const SizedBox(height: 12),
          Text('DROP-OFF ADDRESS',
              style: ts(12, w: FontWeight.w800, c: MC.muted)),
          Text(p.address, style: ts(16, w: FontWeight.w700)),
          const SizedBox(height: 4),
          Row(children: [
            const Icon(Icons.alt_route, size: 16, color: MC.violetDark),
            const SizedBox(width: 6),
            Expanded(
                child: Text('${p.areaName} Â· Route ${p.routeCode}',
                    style: ts(12, w: FontWeight.w600, c: MC.violetDark))),
          ]),
          const SizedBox(height: 8),
          if (p.landmark?.isNotEmpty == true)
            InfoBanner(Icons.doorbell, 'Rider Delivery Note',
                body: 'â€œ${p.landmark}â€'),
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
                color: MC.tint, borderRadius: BorderRadius.circular(12)),
            child: Row(children: [
              const IconCircle(Icons.inventory_2, bg: Colors.white),
              const SizedBox(width: 10),
              Expanded(
                  child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                    Text(p.item ?? 'Parcel', style: ts(14, w: FontWeight.w700)),
                    Text('${p.carrierLabel} Â· ${p.weightLabel}',
                        style: ts(12, c: MC.muted)),
                  ])),
              Pill(p.weightLabel, bg: Colors.white, fg: MC.muted),
            ]),
          ),
          const SizedBox(height: 12),
          if (p.isCod)
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                  color: MC.coralSoft, borderRadius: BorderRadius.circular(12)),
              child: Row(children: [
                const IconCircle(Icons.payments,
                    bg: MC.coral, fg: Colors.white, size: 44),
                const SizedBox(width: 12),
                Expanded(
                    child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                      const Pill('Cash on delivery',
                          bg: MC.coral, fg: Colors.white),
                      Text('Collect Exact Amount',
                          style: ts(12, w: FontWeight.w600)),
                    ])),
                Text(peso(p.cod),
                    style: ts(24, w: FontWeight.w800, c: MC.coral)),
              ]),
            )
          else
            InfoBanner(Icons.verified, 'Paid online',
                body: 'No cash to collect for this parcel.',
                bg: MC.greenBg,
                fg: MC.green),
        ])),
        const SizedBox(height: 12),
        CustodyTimeline(events: p.custody),
        const SizedBox(height: 12),
        MCard(
            child: Row(children: [
          const IconCircle(Icons.qr_code_scanner),
          const SizedBox(width: 12),
          Expanded(
              child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                Text('Hub Handover Verified',
                    style: ts(14, w: FontWeight.w700)),
                Text(
                    'Inbound scan ${p.receivedAt == null ? '—' : clock(p.receivedAt!)} Â· ${p.areaName ?? ''}',
                    style: ts(12, c: MC.muted)),
              ])),
          const Icon(Icons.verified, color: MC.violet),
        ])),
        const SizedBox(height: 16),
        PillButton(
          _done ? 'Out for Delivery Confirmed!' : 'Mark Out for Delivery',
          icon: _done ? Icons.check_circle : Icons.arrow_forward,
          loading: _busy,
          onPressed: _done
              ? null
              : () async {
                  final messenger = ScaffoldMessenger.of(context);
                  setState(() => _busy = true);
                  try {
                    final completed = await widget.onMarkOut();
                    if (mounted && completed) setState(() => _done = true);
                  } catch (error) {
                    if (mounted) {
                      messenger.showSnackBar(SnackBar(content: Text('$error')));
                    }
                  } finally {
                    if (mounted) setState(() => _busy = false);
                  }
                },
        ),
        const SizedBox(height: 8),
        PillButton(
          'Proof of Delivery',
          icon: _done ? Icons.photo_camera : Icons.lock,
          style: BtnStyle.tonal,
          onPressed: _done
              ? () =>
                  Navigator.pushNamed(context, '/pod', arguments: p.tracking)
              : null,
        ),
        if (!_done)
          Padding(
            padding: const EdgeInsets.only(top: 8),
            child: Text(
              'Mark Out for Delivery first (Delivered cannot be skipped)',
              textAlign: TextAlign.center,
              style: ts(12, c: MC.muted),
            ),
          ),
      ]),
    );
  }
}
