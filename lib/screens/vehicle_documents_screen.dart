import 'package:flutter/material.dart';
import '../core/theme.dart';
import '../l10n/generated/app_localizations.dart';

class VehicleDocumentsScreen extends StatelessWidget {
  const VehicleDocumentsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    final l10n = AppLocalizations.of(context);
    return MScaffold(
      title: l10n.vehicleTitle,
      body: ListView(padding: MS.pagePadding, children: [
        MCard(color: c.lowest, child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            IconCircle(Icons.two_wheeler, size: 48, bg: c.tint, fg: c.primary),
            const SizedBox(width: 12),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(l10n.vehicleType, style: ts(18, w: FontWeight.w700, c: c.text)),
              Text(l10n.vehiclePlate('123ABC'), style: ts(14, c: c.muted)),
            ])),
            Pill(l10n.vehicleActive, bg: c.greenSoft, fg: c.green),
          ]),
          const SizedBox(height: 16),
          _DocRow(label: l10n.vehicleLicense, status: l10n.vehicleVerified, detail: l10n.vehicleLicenseDetail, icon: Icons.badge_outlined),
          const SizedBox(height: 12),
          _DocRow(label: l10n.vehicleOrCr, status: l10n.vehicleVerified, detail: l10n.vehicleOrCrDetail('Pedro Jose Garcia'), icon: Icons.description_outlined),
        ])),
        const SizedBox(height: 12),
        InfoBanner(Icons.info, l10n.vehicleInfoTitle,
            body: l10n.vehicleInfoBody, bg: c.low, fg: c.primary),
        const SizedBox(height: 16),
        PillButton(
          l10n.vehicleUpdate,
          leading: Icons.upload_file,
          trailing: null,
          onPressed: () {
            // TODO(api): upload replacement license / OR-CR for verification.
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(content: Text(l10n.vehicleUploadUnavailable)),
            );
          },
        ),
      ]),
    );
  }
}

class _DocRow extends StatelessWidget {
  final String label, status, detail;
  final IconData icon;
  const _DocRow({required this.label, required this.status, required this.detail, required this.icon});

  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(color: c.low, borderRadius: BorderRadius.circular(12)),
      child: Row(children: [
        Icon(icon, color: c.primary, size: 24),
        const SizedBox(width: 12),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(label, style: ts(14, w: FontWeight.w700, c: c.text)),
          Text(detail, style: ts(12, c: c.muted)),
        ])),
        Text(status,
            style: ts(12, w: FontWeight.w700, c: c.green),
            textAlign: TextAlign.end),
      ]),
    );
  }
}
