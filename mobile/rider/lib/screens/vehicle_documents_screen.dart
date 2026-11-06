import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:path/path.dart' as p;
import 'package:path_provider/path_provider.dart';
import '../core/theme.dart';
import '../l10n/generated/app_localizations.dart';

class VehicleDocumentsScreen extends StatefulWidget {
  const VehicleDocumentsScreen({super.key});
  @override
  State<VehicleDocumentsScreen> createState() => _VehicleDocumentsScreenState();
}

class _VehicleDocumentsScreenState extends State<VehicleDocumentsScreen> {
  bool _uploading = false;

  Future<void> _uploadDocument(BuildContext context) async {
    final messenger = ScaffoldMessenger.of(context);
    setState(() => _uploading = true);
    try {
      final file = await ImagePicker().pickImage(
          source: ImageSource.gallery, imageQuality: 80, maxWidth: 1600);
      if (file == null) return;
      final bytes = await file.readAsBytes();
      if (bytes.length > 5 * 1024 * 1024) {
        if (!mounted) return;
        messenger.showSnackBar(
            const SnackBar(content: Text('File must be under 5 MB.')));
        return;
      }
      final docsDir = await getApplicationDocumentsDirectory();
      final docPath = p.join(docsDir.path,
          'vehicle_doc_${DateTime.now().millisecondsSinceEpoch}.jpg');
      await File(file.path).copy(docPath);
      if (!mounted) return;
      messenger.showSnackBar(
          const SnackBar(content: Text('Document saved locally. Pending review.')));
    } catch (e) {
      if (!mounted) return;
      messenger.showSnackBar(SnackBar(content: Text('Upload failed: $e')));
    } finally {
      if (mounted) setState(() => _uploading = false);
    }
  }

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
          onPressed: _uploading ? null : () => _uploadDocument(context),
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
