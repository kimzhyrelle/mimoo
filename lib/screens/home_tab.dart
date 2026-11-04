import 'dart:io';

import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import '../core/job_offer_notifications.dart';
import '../core/rider_status_controller.dart';
import '../core/theme.dart';
import '../data/parcel_flow.dart';
import '../data/repositories/app_repositories_scope.dart';
import '../widgets/common.dart';
import '../widgets/shell.dart';
import 'location_permission_sheet.dart';
import 'pickup_detail_screen.dart';

class HomeTab extends StatefulWidget {
  final VoidCallback? onOpenEarnings;
  const HomeTab({super.key, this.onOpenEarnings});
  @override
  State<HomeTab> createState() => _HomeTabState();
}

class _HomeTabState extends State<HomeTab> {
  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    final status = RiderStatusScope.of(context);
    final repositories = AppRepositoriesScope.of(context);
    final parcels = repositories.parcels;
    final online = status.isOnline;
    return Center(
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 600),
        child:
            ListView(padding: const EdgeInsets.only(bottom: MS.xl), children: [
          Container(
            color: c.primary,
            padding: const EdgeInsets.fromLTRB(MS.lg, MS.lg, MS.lg, 20),
            child: Column(children: [
              // ── Profile header card — tappable → Edit Personal Info ──
              ListenableBuilder(
                listenable: repositories.riderProfile,
                builder: (context, _) {
                  final profile = repositories.riderProfile.profile;
                  final name = (profile?.name ?? '').trim().isEmpty
                      ? 'Rider'
                      : profile!.name;
                  final fleet = profile?.fleetName ?? '';
                  final hub = profile?.hubName ?? '';
                  final subtitle = [fleet, hub]
                      .where((s) => s.isNotEmpty)
                      .join(' • ');
                  final photoPath = profile?.photoPath;
                  final hasPhoto =
                      photoPath != null && File(photoPath).existsSync();

                  return InkWell(
                    onTap: () =>
                        Navigator.pushNamed(context, '/profile'),
                    borderRadius: BorderRadius.circular(12),
                    child: Row(children: [
                      Expanded(
                          child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                            Text(name,
                                style: MT.headlineLg
                                    .copyWith(color: Colors.white),
                                overflow: TextOverflow.ellipsis,
                                maxLines: 1),
                            if (subtitle.isNotEmpty)
                              Text(subtitle,
                                  style: MT.bodySm.copyWith(
                                      color: const Color(0xFFE4D9FF)),
                                  overflow: TextOverflow.ellipsis,
                                  maxLines: 1),
                          ])),
                      const SizedBox(width: 12),
                      CircleAvatar(
                        radius: 24,
                        backgroundColor: c.highest,
                        backgroundImage:
                            hasPhoto ? FileImage(File(photoPath)) : null,
                        child: hasPhoto
                            ? null
                            : Icon(Icons.person, color: c.primary),
                      ),
                      const SizedBox(width: 4),
                      const Icon(Icons.chevron_right,
                          color: Colors.white54, size: 18),
                    ]),
                  );
                },
              ),
              const SizedBox(height: 16),
              if (!status.locationOn) ...[
                LocationOffBanner(
                  onFix: () => toggleRiderStatus(context, status, true),
                ),
                const SizedBox(height: 8),
              ],
            ]),
          ),
          Padding(
            padding: MS.pagePadding,
            child: Column(children: [
              LayoutBuilder(builder: (context, constraints) {
                final scale = MediaQuery.textScalerOf(context).scale(1);
                final count = scale > 1.2 || constraints.maxWidth < 380
                    ? 1
                    : constraints.maxWidth < 480
                        ? 2
                        : 3;
                final width =
                    (constraints.maxWidth - MS.md * (count - 1)) / count;
                return ListenableBuilder(
                  listenable: parcels,
                  builder: (context, _) {
                    final today = DateTime.now();
                    final startOfDay =
                        DateTime(today.year, today.month, today.day);
                    final active = parcels.active.length;
                    final cod = parcels.codOnHand;
                    final earnings = parcels.earningsSince(startOfDay);
                    final jobs = parcels.completedJobsSince(startOfDay);
                    return Wrap(spacing: MS.md, runSpacing: MS.md, children: [
                      SizedBox(
                          width: width,
                          child: _Stat('Active', '$active', '/3',
                              active >= 3 ? 'Capacity full' : 'Capacity OK')),
                      SizedBox(
                          width: width,
                          child: _Stat(
                              'Earned', peso(earnings), '', '$jobs trips')),
                      SizedBox(
                          width: width,
                          child: _Stat(
                              'COD Remit', peso(cod), '', 'Due 10:00 PM',
                              warn: cod > 0, onTap: widget.onOpenEarnings)),
                    ]);
                  },
                );
              }),
              const SizedBox(height: MS.xl),
              Text('Active Dispatch', style: MT.headlineMd),
              const SizedBox(height: MS.md),
              ListenableBuilder(
                listenable: parcels,
                builder: (context, _) {
                  final active = parcels.active;
                  if (active.isEmpty) {
                    return const MCard(
                      child: Center(child: Text('No active dispatch')),
                    );
                  }
                  final parcel = active.first;
                  final isPickup = parcel.jobType == ParcelJobType.pickup;
                  final destination =
                      isPickup ? parcel.sellerAddress : parcel.address;
                  return MCard(
                    onTap: () => Navigator.pushNamed(
                      context,
                      isPickup ? '/pickup-detail' : '/delivery',
                      arguments: parcel.trackingNo,
                    ),
                    child: Column(children: [
                      Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            MChip(parcel.status.label,
                                bg: c.tertiaryFixed, fg: c.tertiary),
                            Text(parcel.trackingNo,
                                style: MT.labelMd
                                    .copyWith(color: c.onSurfaceVariant)),
                          ]),
                      const SizedBox(height: 12),
                      RouteTimeline(
                        fromLabel: isPickup ? 'Pickup point' : 'Hub',
                        from: isPickup
                            ? parcel.sellerName
                            : parcel.areaName ?? '',
                        toLabel: isPickup
                            ? 'Pickup address'
                            : 'Drop-off destination',
                        to: destination,
                      ),
                      const SizedBox(height: 12),
                      if (isPickup)
                        PillButton(
                          'Navigate to Pickup',
                          height: 48,
                          onPressed: () => showNavigationPicker(
                            context,
                            address: destination,
                            title: 'Navigate to Seller',
                          ),
                        ),
                    ]),
                  );
                },
              ),
              const SizedBox(height: MS.xl),
              Text('Recent activity', style: MT.headlineMd),
              const SizedBox(height: MS.md),
              const _RecentActivityList(),
              if (kDebugMode && online) ...[
                const SizedBox(height: 8),
                Row(mainAxisAlignment: MainAxisAlignment.end, children: [
                  TextButton.icon(
                    onPressed: () => Navigator.pushNamed(context, '/job-offer'),
                    icon: const Icon(Icons.local_fire_department, size: 18),
                    label: const Text('DEBUG: Simulate job offer'),
                  ),
                  TextButton.icon(
                    onPressed: () => JobOfferNotifications.showJobOffer(
                      offerId: 'OFFER-1001',
                      area: 'Commonwealth, QC',
                      payout: '₱30.00',
                      cod: '₱500.00',
                      km: '7.4',
                    ),
                    icon: const Icon(Icons.notifications_active, size: 18),
                    label: const Text('Test notification'),
                  ),
                ]),
              ],
            ]),
          ),
        ]),
      ),
    );
  }
}

class _Stat extends StatelessWidget {
  final String label, value, suffix, sub;
  final bool warn;
  final VoidCallback? onTap;
  const _Stat(this.label, this.value, this.suffix, this.sub,
      {this.warn = false, this.onTap});
  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    return MCard(
      color: warn ? c.tertiaryFixed : c.lowest,
      onTap: onTap,
      child: ConstrainedBox(
          constraints: const BoxConstraints(minHeight: 80),
          child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(label.toUpperCase(),
                    style: MT.badge.copyWith(color: c.onSurfaceVariant),
                    overflow: TextOverflow.ellipsis),
                Text.rich(
                    TextSpan(
                        text: value,
                        style:
                            MT.currency.copyWith(color: warn ? c.tertiary : c.primary),
                        children: [TextSpan(text: suffix, style: MT.bodyMd)]),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis),
                Text(sub,
                    style:
                        MT.badge.copyWith(color: warn ? c.tertiary : c.secondary),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis),
              ])),
    );
  }
}

/// Completed and failed jobs from the same dispatch store used by Jobs and
/// History, so Home never invents a separate activity feed.
class _RecentActivityList extends StatelessWidget {
  const _RecentActivityList();
  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    final parcels = AppRepositoriesScope.of(context).parcels;
    return ListenableBuilder(
      listenable: parcels,
      builder: (context, _) {
        final items = parcels.parcels
            .where((p) =>
                p.status == ParcelStatus.delivered ||
                p.status == ParcelStatus.failedDelivery)
            .take(3)
            .toList();
        if (items.isEmpty) {
          return MCard(
              color: c.container,
              child: Text('No recent activity yet.', style: MT.bodySm));
        }
        return MCard(
          child: Column(children: [
            for (var i = 0; i < items.length; i++) ...[
              if (i > 0) const Divider(height: 1),
              ListTile(
                contentPadding:
                    const EdgeInsets.symmetric(horizontal: 4, vertical: 6),
                leading: IconDot(
                  items[i].status == ParcelStatus.delivered
                      ? Icons.check_circle
                      : Icons.error_outline,
                  bg: items[i].status == ParcelStatus.delivered
                      ? c.greenBg
                      : c.coralSoft,
                  fg: items[i].status == ParcelStatus.delivered
                      ? c.green
                      : c.coral,
                ),
                title: Text(
                    '${items[i].status == ParcelStatus.delivered ? 'Delivered' : 'Failed'} · ${items[i].buyer}',
                    style: MT.labelLg),
                subtitle: Text(
                    '${items[i].tracking} · ${clock(items[i].deliveredAt ?? items[i].assignedAt ?? items[i].updatedAt)}',
                    style: MT.bodySm),
                trailing: const Icon(Icons.chevron_right),
                onTap: () => Navigator.pushNamed(context, '/delivery',
                    arguments: items[i].tracking),
              ),
            ],
          ]),
        );
      },
    );
  }
}
