import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../core/theme.dart';
import '../data/parcel_flow.dart';
import '../data/repositories/app_repositories_scope.dart';
import '../widgets/common.dart';
import '../widgets/custody_timeline.dart';
import 'pickup_detail_screen.dart';
import 'out_for_delivery_screen.dart';

class DeliveryDetailScreen extends StatelessWidget {
  final DispatchParcel parcel;
  const DeliveryDetailScreen({super.key, required this.parcel});

  Future<void> _callBuyer(BuildContext context) async {
    final messenger = ScaffoldMessenger.of(context);
    final uri = Uri.parse('tel:${parcel.phone}');
    var opened = false;
    try {
      opened = await canLaunchUrl(uri) && await launchUrl(uri);
    } catch (_) {
      opened = false;
    }
    if (!opened && context.mounted) {
      messenger.showSnackBar(
          const SnackBar(content: Text("Couldn't start the call")));
    }
  }

  Future<void> _fail(BuildContext context) async {
    final reason = await showModalBottomSheet<String>(
      context: context,
      backgroundColor: MC.lowest,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 20),
          child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                    child: Container(
                        width: 44,
                        height: 5,
                        decoration: BoxDecoration(
                            color: MC.outlineVariant,
                            borderRadius: BorderRadius.circular(9)))),
                const SizedBox(height: 16),
                Text('Why did the delivery fail?', style: MT.headlineMd),
                const SizedBox(height: 8),
                for (final reason in failureReasons)
                  ListTile(
                    contentPadding: EdgeInsets.zero,
                    leading: Icon(Icons.report_problem_outlined,
                        color: MC.tertiaryContainer),
                    title: Text(reason,
                        style: MT.bodyMd.copyWith(fontWeight: FontWeight.w600)),
                    trailing: const Icon(Icons.chevron_right),
                    onTap: () => Navigator.pop(ctx, reason),
                  ),
              ]),
        ),
      ),
    );
    if (reason == null || !context.mounted) return;

    // Disable the button while the request is in flight by tracking state at
    // the DeliveryDetailScreen level — use a local flag so we don't need extra
    // setState calls on a StatelessWidget.
    try {
      await AppRepositoriesScope.of(context).parcels.transition(
            parcel.trackingNo,
            ParcelStatus.failedDelivery,
            reason: reason,
          );
    } catch (e) {
      if (!context.mounted) return;
      final msg = e.toString().replaceFirst('Exception: ', '');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(msg.isEmpty ? 'Failed to update status.' : msg)),
      );
      return;
    }
    if (!context.mounted) return;
    Navigator.pushNamedAndRemoveUntil(context, '/home', (_) => false);
  }

  @override
  Widget build(BuildContext context) {
    final p = parcel;
    final step = p.lifecycleStep;
    final canAct = p.status == ParcelStatus.outForDelivery;
    return Scaffold(
      appBar: MSubAppBar(
        title:
            Text(p.tracking, style: MT.labelLg.copyWith(color: Colors.white)),
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 12),
            child: MChip(p.status.label,
                bg: p.status == ParcelStatus.failedDelivery
                    ? MC.coral
                    : MC.container,
                fg: p.status == ParcelStatus.failedDelivery
                    ? Colors.white
                    : MC.primary),
          ),
        ],
      ),
      body: MPageBody(
          child: ListView(padding: MS.pagePadding, children: [
        if (p.status == ParcelStatus.failedDelivery) ...[
          InfoBanner(
            Icons.warning,
            'Overdue Â· ${p.failReason ?? 'Delivery failed'}',
            body:
                'Return the parcel to the hub. Logistics clears it when it is scanned back in.',
            bg: MC.coral,
            fg: Colors.white,
          ),
          const SizedBox(height: 12),
        ],
        if (p.status == ParcelStatus.delivered) ...[
          InfoBanner(Icons.check_circle, 'Delivered',
              body: 'Handover completed to ${p.buyer}.',
              bg: MC.greenBg,
              fg: MC.green),
          const SizedBox(height: 12),
        ],
        MCard(
            child: Column(children: [
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
            Text('DELIVERY LIFECYCLE',
                style: MT.badge.copyWith(color: MC.onSurfaceVariant)),
            Text('Step ${step + 1} of 5',
                style: MT.badge.copyWith(color: MC.primary)),
          ]),
          const SizedBox(height: 12),
          Row(children: [
            for (var i = 0; i < lifecycleSteps.length; i++)
              Expanded(
                  child: Column(children: [
                CircleAvatar(
                  radius: 12,
                  backgroundColor: i < step
                      ? MC.primary
                      : (i == step ? MC.primaryContainer : MC.highest),
                  child: Icon(
                    i < step
                        ? Icons.check
                        : (i == step
                            ? Icons.sports_motorsports
                            : Icons.circle_outlined),
                    size: 14,
                    color: i <= step ? Colors.white : MC.onSurfaceVariant,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  lifecycleSteps[i],
                  style: MT.badge.copyWith(
                      fontSize: 12,
                      color: i == step
                          ? MC.primaryContainer
                          : MC.onSurfaceVariant),
                  textAlign: TextAlign.center,
                ),
              ])),
          ]),
        ])),
        const SizedBox(height: 12),
        MCard(
            child:
                Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            const CircleAvatar(
                radius: 24,
                backgroundColor: MC.highest,
                child: Icon(Icons.person, color: MC.primary)),
            const SizedBox(width: 12),
            Expanded(
                child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                  Text(p.buyer, style: MT.headlineMd),
                  Text(p.phone, style: MT.bodySm),
                ])),
            Flexible(
              child: Text(p.areaName ?? '',
                  style: MT.bodySm.copyWith(
                      color: MC.secondary, fontWeight: FontWeight.w600),
                  maxLines: 2,
                  textAlign: TextAlign.end,
                  overflow: TextOverflow.ellipsis),
            ),
          ]),
          const SizedBox(height: 12),
          Row(children: [
            Expanded(
                child: OutlinedButton.icon(
                    onPressed: () => _callBuyer(context),
                    icon: const Icon(Icons.call, size: 18),
                    label: const Text('Call'),
                    style: OutlinedButton.styleFrom(
                        shape: const StadiumBorder(),
                        minimumSize: const Size.fromHeight(48),
                        foregroundColor: MC.primary,
                        side: BorderSide.none,
                        backgroundColor: MC.container))),
            const SizedBox(width: 8),
            Expanded(
                child: FilledButton.icon(
                    onPressed: () =>
                        Navigator.pushNamed(context, '/chat/thread'),
                    icon: const Icon(Icons.chat, size: 18),
                    label: const Text('Message'),
                    style: FilledButton.styleFrom(
                        shape: const StadiumBorder(),
                        minimumSize: const Size.fromHeight(48),
                        backgroundColor: MC.primary))),
          ]),
          const SizedBox(height: 12),
          Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                  color: MC.low, borderRadius: BorderRadius.circular(12)),
              child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('DROP-OFF ADDRESS',
                        style: MT.badge.copyWith(color: MC.onSurfaceVariant)),
                    Text(p.address,
                        style: MT.bodyLg.copyWith(fontWeight: FontWeight.w600)),
                    const SizedBox(height: 4),
                    Text('${p.areaName} Â· Route ${p.routeCode}',
                        style: MT.bodySm.copyWith(
                            color: MC.primary, fontWeight: FontWeight.w700)),
                    if (p.landmark?.isNotEmpty == true) ...[
                      const SizedBox(height: 8),
                      Row(children: [
                        const Icon(Icons.near_me, size: 18, color: MC.tertiary),
                        const SizedBox(width: 8),
                        Expanded(
                            child: Text('"${p.landmark}"', style: MT.labelMd))
                      ]),
                    ],
                  ])),
          const SizedBox(height: 12),
          Wrap(
              spacing: 8,
              runSpacing: 6,
              crossAxisAlignment: WrapCrossAlignment.center,
              children: [
                Text(p.carrierLabel,
                    style: MT.bodySm.copyWith(color: MC.onSurfaceVariant)),
                MChip(p.weightLabel, bg: MC.highest),
                Text(p.item ?? 'Parcel',
                    style: MT.bodySm,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis),
              ]),
        ])),
        const SizedBox(height: 12),
        if (p.isCod)
          MCard(
              color: MC.tertiaryFixed.withValues(alpha: .4),
              child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const MChip('Cash on delivery',
                        bg: MC.tertiary, fg: Colors.white),
                    const SizedBox(height: 8),
                    Text('RIDER MUST COLLECT',
                        style: MT.badge.copyWith(color: MC.onSurfaceVariant)),
                    Text(peso(p.cod),
                        style: MT.currency.copyWith(color: MC.tertiary)),
                    Text('Count cash before handing over.',
                        style: MT.bodySm.copyWith(color: MC.tertiary)),
                  ]))
        else
          InfoBanner(Icons.verified, 'Paid online',
              body: 'No cash to collect for this parcel.',
              bg: MC.greenBg,
              fg: MC.green),
        const SizedBox(height: 12),
        CustodyTimeline(events: p.custody),
        const SizedBox(height: 16),
        PillButton.secondary('Navigate (Google Maps / Waze)',
            leading: Icons.near_me,
            onPressed: () => showNavigationPicker(context,
                address: p.address,
                etaMins: 12,
                km: 4.2,
                title: 'Navigate to Customer')),
        if (canAct) ...[
          const SizedBox(height: 8),
          PillButton('Proof of Delivery',
              onPressed: () =>
                  Navigator.pushNamed(context, '/pod', arguments: p.tracking)),
          const SizedBox(height: 8),
          TextButton.icon(
            onPressed: () => _fail(context),
            icon: const Icon(Icons.error, color: MC.tertiary, size: 18),
            label: Text('Delivery failed / Customer unavailable',
                style: MT.labelMd.copyWith(color: MC.tertiary)),
          ),
        ],
      ])),
    );
  }
}
