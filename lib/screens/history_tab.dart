import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../core/theme.dart';
import '../data/repositories/app_repositories_scope.dart';
import '../widgets/common.dart';
import 'history_job_detail.dart';

class HistoryTab extends StatefulWidget {
  const HistoryTab({super.key});
  @override
  State<HistoryTab> createState() => _HistoryTabState();
}

class _HistoryTabState extends State<HistoryTab> {
  int _filter = 0; // Today / This week / Custom

  static const _filters = ['Today', 'This week', 'Custom'];

  @override
  Widget build(BuildContext context) {
    final parcelsRepository = AppRepositoriesScope.of(context).parcels;
    return ListenableBuilder(
      listenable: parcelsRepository,
      builder: (context, _) {
        final parcels = {
          for (final parcel in parcelsRepository.parcels) parcel.id: parcel,
        };
        final now = DateTime.now();
        final today = DateTime(now.year, now.month, now.day);
        final weekStart =
            today.subtract(Duration(days: today.weekday - DateTime.monday));
        final jobs = parcelsRepository.statusLogs
            .where((log) {
              if (!const {
                'picked_up',
                'delivered',
                'failed_delivery',
                'return_to_sender',
                'returned',
              }.contains(log.toStatus)) {
                return false;
              }
              if (_filter == 0) {
                return log.createdAt.isAfter(today) ||
                    log.createdAt.isAtSameMomentAs(today);
              }
              if (_filter == 1) return !log.createdAt.isBefore(weekStart);
              return true;
            })
            .map((log) {
              final parcel = parcels[log.parcelId];
              if (parcel == null) return null;
              final pickedUp = log.toStatus == 'picked_up';
              final delivered = log.toStatus == 'delivered';
              final failed = log.toStatus == 'failed_delivery';
              final status = switch (log.toStatus) {
                'picked_up' => 'Picked Up',
                'delivered' => 'Delivered',
                'failed_delivery' =>
                  'Failed (Attempt ${parcel.deliveryAttempts})',
                'return_to_sender' => 'Return to Sender',
                _ => 'Returned',
              };
              return HistoryJob(
                tracking: parcel.trackingNo,
                type: pickedUp ? 'Pickup' : 'Delivery',
                status: status,
                time: DateFormat('MMM d, yyyy, h:mm a').format(log.createdAt),
                label: pickedUp ? 'Seller hub' : 'Buyer location',
                location: pickedUp
                    ? parcel.sellerAddress
                    : failed
                        ? '${log.reason ?? 'Delivery failed'} · Rescheduled'
                        : parcel.address,
                earnings: pickedUp
                    ? parcel.pickupFee
                    : delivered
                        ? parcel.deliveryFee
                        : 0,
                cod: delivered && parcel.codCollected
                    ? 'COD ${peso(parcel.codAmount)}'
                    : null,
                isToday: !log.createdAt.isBefore(today),
              );
            })
            .whereType<HistoryJob>()
            .toList(growable: false);
        return Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 600),
            child: ListView(padding: MS.pagePadding, children: [
              Text('RIDER DISPATCH LOG',
                  style: MT.badge.copyWith(color: MC.secondary)),
              Text('Job History', style: MT.headlineLg),
              const SizedBox(height: MS.md),
              MSegmentedControl(
                segments: _filters,
                selectedIndex: _filter,
                onSelectionChanged: (index) => setState(() => _filter = index),
              ),
              const SizedBox(height: MS.xl),
              if (jobs.isEmpty)
                MCard(
                    color: MC.container,
                    child: Column(children: [
                      const IconDot(Icons.inbox_outlined, size: 56),
                      const SizedBox(height: 8),
                      Text('No jobs in this period', style: MT.headlineMd),
                      Text('Completed pickups and deliveries will appear here.',
                          style: MT.bodySm, textAlign: TextAlign.center),
                    ]))
              else
                for (final job in jobs) _HistoryCard(job: job),
            ]),
          ),
        );
      },
    );
  }
}

class _HistoryCard extends StatelessWidget {
  final HistoryJob job;
  const _HistoryCard({required this.job});

  (Color, Color) get _statusColors {
    if (job.status.startsWith('Delivered')) return (MC.high, MC.primary);
    if (job.status.startsWith('Picked')) return (MC.primaryFixed, MC.primary);
    if (job.status.startsWith('Return')) {
      return (MC.errorContainer, MC.tertiary);
    }
    return (MC.highest, MC.onSurface);
  }

  @override
  Widget build(BuildContext context) {
    final (statusBg, statusFg) = _statusColors;
    final earned = job.earnings > 0 ? '+${peso(job.earnings)}' : peso(0);
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: MCard(
        onTap: () =>
            Navigator.pushNamed(context, '/history-job', arguments: job),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Wrap(spacing: MS.sm, runSpacing: MS.sm, children: [
            Text(job.tracking, style: MT.labelLg),
            MChip(job.type, bg: MC.container, fg: MC.secondary),
            MChip(job.status, bg: statusBg, fg: statusFg),
          ]),
          Text(job.time, style: MT.bodySm),
          const SizedBox(height: 10),
          Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                  color: MC.low, borderRadius: BorderRadius.circular(8)),
              child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(job.label.toUpperCase(),
                        style: MT.badge.copyWith(color: MC.secondary)),
                    Text(job.location,
                        style: MT.bodyMd.copyWith(fontWeight: FontWeight.w500)),
                  ])),
          const SizedBox(height: 10),
          Row(children: [
            if (job.cod != null)
              Expanded(
                child: Text(job.cod!,
                    style: MT.bodySm.copyWith(color: MC.onSurfaceVariant),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis),
              ),
            Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
              Text('EARNINGS', style: MT.badge.copyWith(color: MC.secondary)),
              Text(earned,
                  style: MT.currency.copyWith(
                      color: job.earnings > 0 ? MC.primary : MC.outline)),
            ]),
          ]),
        ]),
      ),
    );
  }
}
