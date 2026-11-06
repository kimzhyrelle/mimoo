import 'package:flutter/material.dart';
import 'package:permission_handler/permission_handler.dart';
import '../core/theme.dart';
import '../l10n/generated/app_localizations.dart';

/// Call when the rider taps "Go Online" and location is off/denied.
Future<void> showLocationPermissionSheet(BuildContext context,
    {String area = 'Quezon City', bool permanentlyDenied = true}) {
  return showModalBottomSheet<void>(
      context: context,
      backgroundColor: Colors.white,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(28))),
      builder: (ctx) => Padding(
          padding: EdgeInsets.fromLTRB(
              20, 8, 20, 24 + MediaQuery.of(ctx).padding.bottom),
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            Container(
                width: 40,
                height: 6,
                margin: const EdgeInsets.symmetric(vertical: 8),
                decoration: BoxDecoration(
                    color: const Color(0xFFCBC3D7),
                    borderRadius: BorderRadius.circular(9))),
            const IconCircle(Icons.location_on,
                bg: Color(0xFFEDE7FB), fg: MC.violet, size: 80),
            const SizedBox(height: 12),
            Text('Location is needed to go Online',
                textAlign: TextAlign.center, style: ts(22, w: FontWeight.w700)),
            const SizedBox(height: 8),
            Text(
                'We use your real-time location so Logistics can assign nearby pickup and delivery jobs, calculate accurate ETAs, and ensure rider safety.',
                textAlign: TextAlign.center,
                style: ts(14, c: MC.muted, h: 1.4)),
            const SizedBox(height: 16),
            Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                    color: MC.tintLow, borderRadius: BorderRadius.circular(12)),
                child: Column(children: [
                  for (final t in [
                    'Accurate nearby order matching',
                    'Turn-by-turn navigation in $area'
                  ])
                    Padding(
                        padding: const EdgeInsets.symmetric(vertical: 4),
                        child: Row(children: [
                          const IconCircle(Icons.check, size: 24),
                          const SizedBox(width: 10),
                          Text(t, style: ts(14, w: FontWeight.w700))
                        ])),
                ])),
            const SizedBox(height: 16),
            PillButton(
              permanentlyDenied ? 'Open Settings' : 'Allow Location',
              icon: Icons.arrow_forward,
              onPressed: () async {
                if (permanentlyDenied) {
                  await openAppSettings();
                } else {
                  await Permission.location.request();
                }
                if (ctx.mounted) Navigator.pop(ctx);
              },
            ),
            const SizedBox(height: 4),
            TextButton(
                onPressed: () => Navigator.pop(ctx),
                child: Text('Not now',
                    style: ts(14, w: FontWeight.w700, c: MC.muted))),
            Row(mainAxisAlignment: MainAxisAlignment.center, children: [
              const Icon(Icons.lock, size: 14, color: MC.outline),
              const SizedBox(width: 6),
              Text('Location data is encrypted and active only during shifts',
                  style: ts(12, c: MC.muted))
            ]),
          ])));
}

/// Amber banner shown on Home while location is off.
class LocationOffBanner extends StatelessWidget {
  const LocationOffBanner({super.key, required this.onFix});

  final VoidCallback onFix;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Semantics(
      button: true,
      label: l10n.locationOffBanner,
      child: Material(
        color: MC.amberSoft,
        borderRadius: BorderRadius.circular(12),
        child: InkWell(
          onTap: onFix,
          borderRadius: BorderRadius.circular(12),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            child: Row(children: [
              const Icon(Icons.location_off, size: 18, color: MC.amber),
              const SizedBox(width: 8),
              Expanded(
                child: Text(l10n.locationOffBanner,
                    style: ts(12, w: FontWeight.w600, c: MC.amber),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis),
              ),
              const SizedBox(width: 8),
              Text(l10n.locationOffFix,
                  style: ts(12, w: FontWeight.w700, c: MC.amber)),
            ]),
          ),
        ),
      ),
    );
  }
}
