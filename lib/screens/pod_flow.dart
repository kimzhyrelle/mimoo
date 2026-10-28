import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:permission_handler/permission_handler.dart';
import '../core/connectivity.dart';
import '../core/theme.dart';
import '../widgets/common.dart';

/// Proof of delivery: 1) live photo  2) 6-digit OTP  3) COD exact amount.
class PodFlowScreen extends StatefulWidget {
  const PodFlowScreen({super.key});
  @override
  State<PodFlowScreen> createState() => _PodFlowScreenState();
}

class _PodFlowScreenState extends State<PodFlowScreen> {
  int step = 0;
  XFile? _photo;
  Uint8List? _photoBytes;
  String? _photoPath;
  bool _takingPhoto = false;
  String otp = '';
  String cod = '';
  final ImagePicker _picker = ImagePicker();
  static const due = 500.0;

  double get codVal => double.tryParse(cod) ?? 0;
  bool get codMatch => (codVal - due).abs() < 0.001;

  Future<void> _takePhoto() async {
    if (_takingPhoto) return;
    final messenger = ScaffoldMessenger.of(context);
    final status = await Permission.camera.request();
    if (status.isPermanentlyDenied) {
      if (!mounted) return;
      await showDialog<void>(
        context: context,
        builder: (ctx) => AlertDialog(
          title: const Text('Camera permission is blocked'),
          content: const Text('Enable camera access for Mimoo Rider in App Settings to take the delivery photo.'),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Cancel')),
            FilledButton(onPressed: () { Navigator.pop(ctx); openAppSettings(); }, child: const Text('Open Settings')),
          ],
        ),
      );
      return;
    }
    if (!status.isGranted) {
      messenger.showSnackBar(const SnackBar(content: Text('Camera permission is required for the delivery photo')));
      return;
    }

    setState(() => _takingPhoto = true);
    try {
      final photo = await _picker.pickImage(source: ImageSource.camera, imageQuality: 70, maxWidth: 1600);
      if (photo == null) return;
      final bytes = await photo.readAsBytes();
      if (!mounted) return;
      // TODO: upload _photoPath (captured proof photo) before enabling step 2.
      setState(() {
        _photo = photo;
        _photoBytes = bytes;
        _photoPath = photo.path;
      });
    } catch (error) {
      if (mounted) messenger.showSnackBar(SnackBar(content: Text('Could not take the photo: $error')));
    } finally {
      if (mounted) setState(() => _takingPhoto = false);
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(
          backgroundColor: MC.header, foregroundColor: Colors.white,
          leading: IconButton(icon: const Icon(Icons.arrow_back), onPressed: () => step == 0 ? Navigator.pop(context) : setState(() => step--)),
          title: Text('Step ${step + 1} of 3', style: MT.labelLg.copyWith(color: Colors.white)),
        ),
        body: ListView(padding: const EdgeInsets.all(16), children: [
          const ConnectivityBanner(),
          Row(children: [for (var i = 0; i < 3; i++) Expanded(child: Container(height: 6, margin: const EdgeInsets.symmetric(horizontal: 2), decoration: BoxDecoration(color: i <= step ? MC.primary : MC.highest, borderRadius: BorderRadius.circular(99))))]),
          const SizedBox(height: 12),
          MCard(child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
            Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text('Juan Miguel D.', style: MT.headlineMd), Text('45 Kalayaan Ave., Brgy. Central, QC', style: MT.bodySm)]),
            Column(crossAxisAlignment: CrossAxisAlignment.end, children: [const MChip('COD parcel', bg: MC.tertiaryFixed, fg: Color(0xFF92002A)), Text('₱500.00', style: MT.currency.copyWith(color: MC.primary))]),
          ])),
          const SizedBox(height: 12),
          if (step == 0) ..._photoStep() else if (step == 1) ..._otp() else ..._cod(),
        ]),
      );

  List<Widget> _photoStep() => [
        Text('Parcel Photo Proof', style: MT.headlineMd),
        const SizedBox(height: 4),
        InfoBanner(Icons.lock, 'Photo must be taken live at handover. Gallery uploads are restricted.', bg: MC.container, fg: MC.onSurfaceVariant),
        const SizedBox(height: 8),
        AspectRatio(
          aspectRatio: 4 / 5,
          child: ClipRRect(
            borderRadius: BorderRadius.circular(16),
            child: Stack(fit: StackFit.expand, alignment: Alignment.center, children: [
              if (_photoBytes != null)
                Image.memory(_photoBytes!, fit: BoxFit.cover)
              else
                Container(color: MC.inverseSurface, child: const Icon(Icons.photo_camera, size: 64, color: Colors.white54)),
              if (_takingPhoto) Container(color: Colors.black45, child: const Center(child: CircularProgressIndicator(color: Colors.white))),
              Positioned(bottom: 16, child: _photo != null
                  ? Row(mainAxisSize: MainAxisSize.min, children: [
                      FilledButton.icon(onPressed: _takePhoto, icon: const Icon(Icons.replay), label: const Text('Retake'), style: FilledButton.styleFrom(backgroundColor: Colors.white, foregroundColor: MC.primary)),
                      const SizedBox(width: 8),
                      FilledButton.icon(onPressed: () => setState(() => step = 1), icon: const Icon(Icons.arrow_forward), label: const Text('Use Photo')),
                    ])
                  : GestureDetector(onTap: _takePhoto, child: Container(width: 76, height: 76, decoration: const BoxDecoration(color: MC.primaryContainer, shape: BoxShape.circle), child: const Icon(Icons.photo_camera, color: Colors.white, size: 32)))),
            ]),
          ),
        ),
        const SizedBox(height: 12),
        PillButton('Continue to Step 2', onPressed: _photo == null ? null : () => setState(() => step = 1)),
      ];

  List<Widget> _otp() => [
        Text('Ask the buyer for the 6-digit delivery code', style: MT.headlineMd),
        const SizedBox(height: 12),
        Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
          for (var i = 0; i < 6; i++)
            Container(width: 48, height: 56, decoration: BoxDecoration(color: i == otp.length ? MC.primaryFixed : MC.low, borderRadius: BorderRadius.circular(12)), child: Center(child: Text(i < otp.length ? otp[i] : '', style: MT.currency))),
        ]),
        const SizedBox(height: 16),
        Keypad(bottomLeft: ' ', onKey: (k) { if (k != ' ' && otp.length < 6) setState(() => otp += k); }, onBackspace: () { if (otp.isNotEmpty) setState(() => otp = otp.substring(0, otp.length - 1)); }),
        const SizedBox(height: 16),
        // TODO: validate against backend; after 3 wrong tries show 15-min lockout banner.
        PillButton('Continue to Step 3 (Payment)', onPressed: otp.length == 6 ? () => setState(() => step = 2) : null),
      ];

  List<Widget> _cod() {
    final under = cod.isNotEmpty && codVal < due, over = cod.isNotEmpty && codVal > due;
    return [
      Text('Payment Reconciliation', style: MT.headlineMd),
      const SizedBox(height: 8),
      MCard(color: MC.low, child: Center(child: Column(children: [Text('Exact cash due', style: MT.labelMd), Text('₱500.00', style: MT.headlineXl.copyWith(color: MC.primary))]))),
      const SizedBox(height: 8),
      Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(color: codMatch ? MC.primaryFixed : (cod.isEmpty ? MC.container : MC.errorContainer.withValues(alpha: .4)), borderRadius: BorderRadius.circular(12)),
        child: Text('₱ ${cod.isEmpty ? '0.00' : cod}', style: MT.headlineXl),
      ),
      if (under || over) Padding(padding: const EdgeInsets.only(top: 8), child: InfoBanner(Icons.warning, 'Amount must match ₱500.00 exactly (₱${(codVal - due).abs().toStringAsFixed(2)} ${under ? 'under' : 'over'})', bg: MC.errorContainer, fg: const Color(0xFF93000A))),
      const SizedBox(height: 12),
      Keypad(onKey: (k) { if (k == '.' && cod.contains('.')) return; if (cod.contains('.') && cod.split('.')[1].length >= 2) return; setState(() => cod += k); }, onBackspace: () { if (cod.isNotEmpty) setState(() => cod = cod.substring(0, cod.length - 1)); }),
      const SizedBox(height: 16),
      PillButton('Mark as Delivered', onPressed: codMatch ? () {
        // TODO: upload the proof photo at _photoPath once the upload API exists.
        Navigator.pushReplacementNamed(context, '/delivered', arguments: _photoPath);
      } : null),
      // TODO: prepaid orders skip this step (COD ₱0.00 → button enabled immediately).
    ];
  }
}
