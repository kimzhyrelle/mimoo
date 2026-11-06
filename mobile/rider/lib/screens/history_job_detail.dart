import 'package:flutter/material.dart';
import '../core/theme.dart';
import '../widgets/common.dart';

class HistoryJob {
  final String tracking, type, status, time, label, location;
  final double earnings;
  final String? cod;
  final bool isToday;
  const HistoryJob({
    required this.tracking,
    required this.type,
    required this.status,
    required this.time,
    required this.label,
    required this.location,
    required this.earnings,
    this.cod,
    this.isToday = true,
  });
}

/// Read-only record opened from the History tab. There are deliberately no
/// actions here; money lives in Earnings and live jobs live in Jobs.
class HistoryJobDetailScreen extends StatelessWidget {
  final HistoryJob job;
  const HistoryJobDetailScreen({super.key, required this.job});

  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    return MScaffold(
      title: job.tracking,
      body: ListView(padding: MS.pagePadding, children: [
        MCard(color: c.lowest, child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            MChip(job.type, bg: c.container, fg: c.secondary),
            const SizedBox(width: 8),
            Expanded(child: FittedBox(
              fit: BoxFit.scaleDown,
              alignment: Alignment.centerRight,
              child: MChip(job.status, bg: c.highest, fg: c.primary),
            )),
          ]),
          const SizedBox(height: 10),
          Text(job.tracking, style: ts(22, w: FontWeight.w800, c: c.text)),
          Text(job.time, style: ts(12, c: c.muted)),
          const SizedBox(height: 12),
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(color: c.low, borderRadius: BorderRadius.circular(12)),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(job.label.toUpperCase(), style: ts(12, w: FontWeight.w800, c: c.secondary)),
              Text(job.location, style: ts(14, w: FontWeight.w500, c: c.text)),
            ]),
          ),
        ])),
        const SizedBox(height: 12),
        MCard(color: c.lowest, padding: EdgeInsets.zero, child: Column(children: [
          _line(context, 'Job type', job.type),
          _divider(c),
          _line(context, 'Status', job.status),
          _divider(c),
          _line(context, 'Completed at', job.time),
          _divider(c),
          _line(context, 'Rider pay', peso(job.earnings)),
          if (job.cod != null) ...[
            _divider(c),
            _line(context, 'Cash collected', job.cod!),
          ],
        ])),
        const SizedBox(height: 12),
        InfoBanner(Icons.lock_outline, 'Read-only record', body: 'This job is closed. Contact Logistics if something looks wrong.', bg: c.low, fg: c.primary),
      ]),
    );
  }

  Widget _divider(MimooColors c) => Divider(height: 1, indent: 16, endIndent: 16, color: c.outlineVariant);

  Widget _line(BuildContext context, String label, String value) {
    final c = MC.of(context);
    return ListTile(
      minVerticalPadding: MS.md,
      title: Text(label, style: ts(12, c: c.muted)),
      trailing: Text(value, style: ts(14, w: FontWeight.w700, c: c.text)),
    );
  }
}
