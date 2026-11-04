import 'package:flutter/material.dart';
import '../core/connectivity.dart';
import '../core/rider_status_controller.dart';
import '../core/theme.dart';
import '../data/parcel_flow.dart';
import '../data/models/entities.dart' as db;
import '../data/repositories/app_repositories_scope.dart';
import '../widgets/common.dart';

class JobsTab extends StatefulWidget {
  const JobsTab({super.key});
  @override
  State<JobsTab> createState() => _JobsTabState();
}

class _JobsTabState extends State<JobsTab> {
  int seg = 0; // 0 = For Pickup, 1 = For Delivery
  ParcelStatus? _filter;

  Widget _segment(int pickupCount, int deliveryCount) {
    final c = MC.of(context);
    return Container(
      padding: const EdgeInsets.all(4),
      decoration: BoxDecoration(
          color: c.container, borderRadius: BorderRadius.circular(99)),
      child: Row(children: [
        for (final e in [
          (0, Icons.inventory_2, 'For Pickup ($pickupCount)'),
          (1, Icons.local_shipping, 'For Delivery ($deliveryCount)')
        ])
          Expanded(
              child: GestureDetector(
            onTap: () => setState(() => seg = e.$1),
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 180),
              padding: const EdgeInsets.symmetric(vertical: 10),
              decoration: BoxDecoration(
                  color: seg == e.$1 ? c.primary : Colors.transparent,
                  borderRadius: BorderRadius.circular(99)),
              child:
                  Row(mainAxisAlignment: MainAxisAlignment.center, children: [
                Icon(e.$2,
                    size: 18,
                    color: seg == e.$1 ? c.lowest : c.onSurfaceVariant),
                const SizedBox(width: 6),
                Text(e.$3,
                    style: MT.labelLg.copyWith(
                        color: seg == e.$1 ? c.lowest : c.onSurfaceVariant)),
              ]),
            ),
          )),
      ]),
    );
  }

  Widget _pickupCard(db.JobOffer offer, db.Parcel parcel) {
    final c = MC.of(context);
    final secondsLeft =
        offer.expiresAt.difference(DateTime.now()).inSeconds.clamp(0, 180);
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: MCard(
        onTap: () =>
            Navigator.pushNamed(context, '/job-offer', arguments: offer.id),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
            Row(children: [
              MChip('New', bg: c.tertiaryContainer, fg: c.lowest),
            ]),
            Text('Expires in ${secondsLeft}s', style: MT.bodySm),
          ]),
          const SizedBox(height: 10),
          Text(parcel.sellerName, style: MT.headlineMd),
          const SizedBox(height: 4),
          Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Icon(Icons.location_on, size: 18, color: c.primary),
            const SizedBox(width: 6),
            Expanded(
                child: Text(parcel.sellerAddress,
                    style: MT.bodyMd.copyWith(color: c.onSurfaceVariant)))
          ]),
          const SizedBox(height: 10),
          Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                  color: c.container, borderRadius: BorderRadius.circular(8)),
              child: Row(children: [
                Icon(Icons.inventory, size: 20, color: c.primary),
                const SizedBox(width: 8),
                Text(parcel.item ?? 'Parcel ready for dispatch',
                    style: MT.labelMd)
              ])),
          const SizedBox(height: 12),
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
            Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text('Payout Rate', style: MT.bodySm),
              Text(peso(offer.payout),
                  style: MT.currency.copyWith(color: c.primary))
            ]),
            SizedBox(
                width: 170,
                child: PillButton('View Offer',
                    trailing: Icons.arrow_forward,
                    height: 44,
                    onPressed: () => Navigator.pushNamed(context, '/job-offer',
                        arguments: offer.id))),
          ]),
        ]),
      ),
    );
  }

  Widget _pill(String label, int count,
      {required bool selected,
      required VoidCallback onTap,
      bool danger = false}) {
    final c = MC.of(context);
    final bg = selected
        ? (danger ? c.coral : c.primary)
        : (danger ? c.coralSoft : c.container);
    final fg = selected ? c.lowest : (danger ? c.coral : c.onSurfaceVariant);
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(99),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
          decoration:
              BoxDecoration(color: bg, borderRadius: BorderRadius.circular(99)),
          child:
              Text('$label $count', style: ts(12, w: FontWeight.w700, c: fg)),
        ),
      ),
    );
  }

  Widget _deliveryCard(DispatchParcel p) {
    final c = MC.of(context);
    final (bg, fg) = switch (p.status) {
      ParcelStatus.assigned || ParcelStatus.readyForPickup => (
          c.tertiaryFixed,
          c.tertiary
        ),
      ParcelStatus.pickedUp ||
      ParcelStatus.atHub ||
      ParcelStatus.outForDelivery =>
        (c.primary, c.lowest),
      ParcelStatus.delivered || ParcelStatus.returned => (c.greenBg, c.green),
      ParcelStatus.failedDelivery || ParcelStatus.returnToSender => (
          c.coral,
          c.lowest
        ),
    };
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: MCard(
        onTap: p.status == ParcelStatus.delivered
            ? null
            : () => Navigator.pushNamed(
                  context,
                  p.status == ParcelStatus.assigned
                      ? '/out-for-delivery'
                      : '/delivery',
                  arguments: p.tracking,
                ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
            Expanded(
                child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                  Text(p.tracking, style: MT.labelLg),
                  Text(p.carrierLabel, style: MT.bodySm),
                ])),
            MChip(p.status.label, bg: bg, fg: fg),
          ]),
          const SizedBox(height: 10),
          Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Icon(Icons.pin_drop, size: 18, color: c.tertiaryContainer),
            const SizedBox(width: 6),
            Expanded(
                child: Text(p.address,
                    style: MT.bodyMd.copyWith(fontWeight: FontWeight.w600))),
          ]),
          const SizedBox(height: 10),
          Wrap(spacing: 6, runSpacing: 6, children: [
            Text(p.isCod ? 'COD ${peso(p.cod)}' : 'Paid online',
                style:
                    MT.bodySm.copyWith(color: p.isCod ? c.tertiary : c.green)),
            MChip(p.weightLabel, bg: c.highest, fg: c.muted, icon: Icons.scale),
          ]),
        ]),
      ),
    );
  }

  Widget _deliverySegment(List<DispatchParcel> all, bool isOnline) {
    final c = MC.of(context);
    int count(ParcelStatus? status) => status == null
        ? all.length
        : all.where((p) => p.status == status).length;
    final filtered =
        _filter == null ? all : all.where((p) => p.status == _filter).toList();
    final groups =
        AppRepositoriesScope.of(context).parcels.groupedByArea(filtered);
    return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      if (!isOnline) ...[
        InfoBanner(
          Icons.pause_circle_outline,
          "You're off shift. The hub only dispatches parcels to active riders.",
          bg: c.amberSoft,
          fg: c.amberText,
        ),
        const SizedBox(height: 12),
      ],
      SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: Row(children: [
          _pill('All', count(null),
              selected: _filter == null,
              onTap: () => setState(() => _filter = null)),
          for (final status in ParcelStatus.values.where(
              (status) => status != ParcelStatus.atHub || count(status) > 0))
            _pill(
              status.label,
              count(status),
              selected: _filter == status,
              danger: status == ParcelStatus.failedDelivery ||
                  status == ParcelStatus.returnToSender,
              onTap: () => setState(() => _filter = status),
            ),
        ]),
      ),
      const SizedBox(height: 14),
      if (filtered.isEmpty)
        MCard(
            color: c.container,
            child: Column(children: [
              const IconDot(Icons.inbox_outlined, size: 56),
              const SizedBox(height: 8),
              Text('No parcels in this filter', style: MT.headlineMd),
              Text('New dispatches from the hub will appear here.',
                  style: MT.bodySm, textAlign: TextAlign.center),
            ]))
      else
        for (final entry in groups.entries) ...[
          Builder(builder: (context) {
            final parts = entry.key.split('|');
            final area = parts.first;
            final routeCode = parts.length > 1 ? parts[1] : '';
            final measured =
                entry.value.where((p) => p.weightKg != null).toList();
            final totalWeight =
                measured.fold<double>(0, (sum, p) => sum + (p.weightKg ?? 0));
            final weightText = measured.isEmpty
                ? 'No measured weights'
                : '${totalWeight.toStringAsFixed(2)} kg measured';
            return Padding(
              padding: const EdgeInsets.only(bottom: MS.md),
              child: Row(crossAxisAlignment: CrossAxisAlignment.end, children: [
                Expanded(
                    child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                      Text(area, style: MT.headlineMd),
                      Text(
                          'Route $routeCode · ${entry.value.length} parcel${entry.value.length == 1 ? '' : 's'} · $weightText',
                          style: MT.bodySm),
                    ])),
              ]),
            );
          }),
          for (final p in entry.value) _deliveryCard(p),
          const SizedBox(height: 6),
        ],
    ]);
  }

  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    final status = RiderStatusScope.of(context);
    final repositories = AppRepositoriesScope.of(context);
    final offers = repositories.offers;
    final parcels = repositories.parcels;
    return ListenableBuilder(
      listenable: Listenable.merge([parcels, status, offers]),
      builder: (context, _) {
        final pendingOffers = offers.offers
            .where((offer) => offer.state == 'pending')
            .toList(growable: false);
        final deliveryJobs = parcels.parcels
            .where((parcel) => parcel.jobType == db.ParcelJobType.delivery)
            .toList(growable: false);
        return Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 600),
            child: ListView(padding: MS.pagePadding, children: [
              const ConnectivityBanner(),
              _segment(pendingOffers.length, deliveryJobs.length),
              const SizedBox(height: MS.xl),
              if (seg == 0) ...[
                if (!status.isOnline) ...[
                  InfoBanner(
                    Icons.wifi_off_rounded,
                    "You're offline. Go Active to see and accept new job offers.",
                    bg: c.amberSoft,
                    fg: c.amberText,
                  ),
                  const SizedBox(height: 12),
                ],
                if (pendingOffers.isEmpty || !status.isOnline)
                  const MCard(
                      child: Center(child: Text('No pickup offers right now')))
                else
                  for (final offer in pendingOffers)
                    if (parcels.byTracking(offer.parcelId) case final parcel?)
                      _pickupCard(offer, parcel),
              ] else ...[
                _deliverySegment(deliveryJobs, status.isOnline),
              ],
            ]),
          ),
        );
      },
    );
  }
}
