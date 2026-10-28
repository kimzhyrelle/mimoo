import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:url_launcher/url_launcher.dart';
import '../core/theme.dart';

class PickupJob {
  final String tracking, seller, merchantId, address, phone, parcelLabel; final double pay, km; final int etaMins;
  const PickupJob({required this.tracking, required this.seller, required this.merchantId, required this.address, required this.phone,
    required this.parcelLabel, required this.pay, required this.km, required this.etaMins});
}

class PickupDetailScreen extends StatelessWidget {
  final PickupJob job; final VoidCallback onBack, onScanWaybill;
  const PickupDetailScreen({super.key, required this.job, required this.onBack, required this.onScanWaybill});

  @override
  Widget build(BuildContext context) => MScaffold(
        title: 'Pickup', onBack: onBack, actions: [Padding(padding: const EdgeInsets.only(right: 12), child: Pill(job.tracking, icon: Icons.qr_code_2))],
        bottom: SafeArea(child: Padding(padding: const EdgeInsets.all(12), child: Row(children: [
          Expanded(child: PillButton('Navigate', icon: Icons.navigation, style: BtnStyle.tonal, onPressed: () => showNavigationPicker(context, address: job.address, etaMins: job.etaMins, km: job.km, title: 'Navigate to Seller'))), const SizedBox(width: 8),
          Expanded(child: PillButton('Scan Waybill', icon: Icons.arrow_forward, onPressed: onScanWaybill))]))),
        body: ListView(padding: const EdgeInsets.all(16), children: [
          MCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(job.seller, style: ts(18, w: FontWeight.w700)), const SizedBox(height: 4),
                const Pill('Verified seller', icon: Icons.verified), const SizedBox(height: 4),
                Text('Merchant ID: ${job.merchantId}', style: ts(12, c: MC.muted))])),
              const Pill('Standard pickup', bg: MC.tint)]),
            const SizedBox(height: 12),
            Container(padding: const EdgeInsets.all(12), decoration: BoxDecoration(color: MC.tintLow, borderRadius: BorderRadius.circular(8)), child: Column(children: [
              Row(crossAxisAlignment: CrossAxisAlignment.start, children: [const IconCircle(Icons.storefront, size: 28), const SizedBox(width: 10),
                Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text('PICKUP ADDRESS', style: ts(11, w: FontWeight.w800, c: MC.muted)), Text(job.address, style: ts(14, w: FontWeight.w500, h: 1.3))]))]),
              Align(alignment: Alignment.centerRight, child: TextButton.icon(icon: const Icon(Icons.content_copy, size: 16), label: const Text('Copy Address'),
                onPressed: () { Clipboard.setData(ClipboardData(text: job.address)); ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Address copied'))); })),
            ])),
            const SizedBox(height: 12),
            Container(padding: const EdgeInsets.all(12), decoration: BoxDecoration(color: MC.tint, borderRadius: BorderRadius.circular(8)), child: Row(children: [
              const IconCircle(Icons.phone_in_talk, bg: Colors.white, fg: MC.violetDark), const SizedBox(width: 12),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text('SELLER CONTACT', style: ts(11, w: FontWeight.w800, c: MC.muted)), Text(job.phone, style: ts(14, w: FontWeight.w700))])),
              FilledButton.icon(style: FilledButton.styleFrom(backgroundColor: MC.violetDark), icon: const Icon(Icons.call, size: 16), label: const Text('Call Seller'),
                onPressed: () => launchUrl(Uri.parse('tel:${job.phone}')))])),
            const SizedBox(height: 12),
            Row(children: [
              Expanded(child: _meta(Icons.inventory_2, 'PARCEL DETAILS', '1 Parcel', job.parcelLabel)), const SizedBox(width: 8),
              Expanded(child: _meta(Icons.payments, 'PICKUP PAY', peso(job.pay), 'Guaranteed base tier', big: true))]),
          ])),
          const SizedBox(height: 12),
          InfoBanner(Icons.near_me, '${job.km} km away · Est. ${job.etaMins} mins'),
        ]));

  Widget _meta(IconData i, String k, String v, String sub, {bool big = false}) => Container(padding: const EdgeInsets.all(12),
    decoration: BoxDecoration(color: MC.tintLow, borderRadius: BorderRadius.circular(8)),
    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Row(children: [Icon(i, size: 16, color: MC.muted), const SizedBox(width: 4), Text(k, style: ts(11, w: FontWeight.w800, c: MC.muted))]),
      const SizedBox(height: 4), Text(v, style: big ? ts(24, w: FontWeight.w800, c: MC.violetDark) : ts(14, w: FontWeight.w700)), Text(sub, style: ts(12, c: MC.muted))]));
}

/// Navigation picker bottom sheet (Google Maps / Waze). Pass lat/lng to navigate
/// to coordinates, otherwise the address is used. Shared by pickup and delivery.
Future<void> showNavigationPicker(
  BuildContext context, {
  required String address,
  int etaMins = 0,
  double km = 0,
  String title = 'Navigate to Seller',
  double? lat,
  double? lng,
}) {
  final destination = lat != null && lng != null ? '$lat,$lng' : address;
  final q = Uri.encodeComponent(destination);
  final etaLabel = etaMins > 0 ? '$etaMins mins · ' : '';
  final kmLabel = km > 0 ? '$km km fastest' : 'Fastest route';
  final apps = [
    ('Google Maps', '$etaLabel$kmLabel', 'https://www.google.com/maps/dir/?api=1&destination=$q&travelmode=driving', 'Recommended'),
    ('Waze', 'Real-time traffic route updates', 'https://waze.com/ul?q=$q&navigate=yes', null),
  ];
  return showModalBottomSheet<void>(context: context, backgroundColor: Colors.white,
    shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(28))),
    builder: (ctx) => SafeArea(child: Padding(padding: const EdgeInsets.all(20), child: Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.start, children: [
      Center(child: Container(width: 48, height: 6, decoration: BoxDecoration(color: const Color(0xFFCBC3D7), borderRadius: BorderRadius.circular(9)))),
      const SizedBox(height: 12), Text(title, style: ts(22, w: FontWeight.w700)), Text('Choose your preferred GPS navigation app', style: ts(12, c: MC.muted)), const SizedBox(height: 12),
      for (final a in apps) Padding(padding: const EdgeInsets.only(bottom: 8), child: InkWell(borderRadius: BorderRadius.circular(12),
        onTap: () { launchUrl(Uri.parse(a.$3), mode: LaunchMode.externalApplication); Navigator.pop(ctx); },
        child: Container(padding: const EdgeInsets.all(14), decoration: BoxDecoration(color: MC.tintLow, borderRadius: BorderRadius.circular(12)), child: Row(children: [
          const IconCircle(Icons.map, bg: Colors.white, size: 48), const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(a.$1, style: ts(14, w: FontWeight.w700)), Text(a.$2, style: ts(12, c: MC.muted))])),
          if (a.$4 != null) Pill(a.$4!), const Icon(Icons.chevron_right)])))),
      Center(child: TextButton(onPressed: () => Navigator.pop(ctx), child: Text('Dismiss', style: ts(14, w: FontWeight.w700, c: MC.muted)))),
    ]))));
}
