import 'package:flutter/material.dart';
import '../core/theme.dart';
import '../data/parcel_flow.dart';

/// Shared scan-point timeline for the rider delivery flow. Events arrive newest
/// first, matching the hub's custody log.
class CustodyTimeline extends StatelessWidget {
  final List<CustodyEvent> events;
  const CustodyTimeline({super.key, required this.events});

  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    return MCard(
      color: c.lowest,
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text('Custody timeline', style: ts(16, w: FontWeight.w700, c: c.text)),
        const SizedBox(height: 12),
        for (var i = 0; i < events.length; i++)
          Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Column(children: [
              Container(
                width: 12,
                height: 12,
                margin: const EdgeInsets.only(top: 3),
                decoration: BoxDecoration(
                  color: i == 0 ? c.primary : c.outlineVariant,
                  shape: BoxShape.circle,
                ),
              ),
              if (i != events.length - 1)
                Container(width: 2, height: 46, color: c.outlineVariant),
            ]),
            const SizedBox(width: 12),
            Expanded(
              child: Padding(
                padding: const EdgeInsets.only(bottom: 14),
                child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(children: [
                        Expanded(
                            child: Text(events[i].title,
                                style: ts(13, w: FontWeight.w700, c: c.text))),
                        Text(clock(events[i].at), style: ts(12, c: c.muted)),
                      ]),
                      Text(events[i].body, style: ts(12, c: c.muted, h: 1.35)),
                    ]),
              ),
            ),
          ]),
      ]),
    );
  }
}
