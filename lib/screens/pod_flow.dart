import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:permission_handler/permission_handler.dart';
import '../core/connectivity.dart';
import '../core/theme.dart';
import '../data/parcel_flow.dart';
import '../data/repositories/app_repositories_scope.dart';
import '../l10n/generated/app_localizations.dart';
import '../widgets/common.dart';
import 'delivered.dart';

/// Proof of delivery: 1) live photo  2) 6-digit OTP  3) COD exact amount.
/// Prepaid parcels skip the COD step and finish after the OTP.
class PodFlowScreen extends StatefulWidget {
  final DispatchParcel parcel;
  const PodFlowScreen({super.key, required this.parcel});
  @override
  State<PodFlowScreen> createState() => _PodFlowScreenState();
}

class _PodFlowScreenState extends State<PodFlowScreen> {
  int step = 0;
  XFile? _photo;
  Uint8List? _photoBytes;
  String? _photoPath;
  bool _takingPhoto = false;
  bool _finishing = false;
  String otp = '';
  String cod = '';
  final ImagePicker _picker = ImagePicker();

  DispatchParcel get parcel => widget.parcel;
  double get due => parcel.cod;
  int get stepCount => parcel.isCod ? 3 : 2;
  double get codVal => double.tryParse(cod) ?? 0;
  bool get codMatch => (codVal * 100).round() == (due * 100).round();

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
          content: const Text(
              'Enable camera access for Mimoo Rider in App Settings to take the delivery photo.'),
          actions: [
            TextButton(
                onPressed: () => Navigator.pop(ctx),
                child: const Text('Cancel')),
            FilledButton(
                onPressed: () {
                  Navigator.pop(ctx);
                  openAppSettings();
                },
                child: const Text('Open Settings')),
          ],
        ),
      );
      return;
    }
    if (!status.isGranted) {
      messenger.showSnackBar(const SnackBar(
          content:
              Text('Camera permission is required for the delivery photo')));
      return;
    }

    setState(() => _takingPhoto = true);
    try {
      final photo = await _picker.pickImage(
          source: ImageSource.camera, imageQuality: 70, maxWidth: 1600);
      if (photo == null) return;
      final bytes = await photo.readAsBytes();
      if (!mounted) return;
      final extension = photo.name.split('.').last.toLowerCase();
      if (!const {'jpg', 'jpeg', 'png'}.contains(extension) ||
          bytes.length > 5 * 1024 * 1024) {
        messenger.showSnackBar(SnackBar(
            content: Text(AppLocalizations.of(context).podPhotoSizeError)));
        return;
      }
      // TODO: upload _photoPath (captured proof photo) before enabling step 2.
      setState(() {
        _photo = photo;
        _photoBytes = bytes;
        _photoPath = photo.path;
      });
    } catch (error) {
      if (mounted) {
        messenger.showSnackBar(
            SnackBar(content: Text('Could not take the photo: $error')));
      }
    } finally {
      if (mounted) setState(() => _takingPhoto = false);
    }
  }

  Future<void> _finish() async {
    if (_finishing) return;
    final messenger = ScaffoldMessenger.of(context);
    setState(() => _finishing = true);
    try {
      // TODO(api): upload the proof photo and validate the OTP with the backend.
      final path = _photoPath;
      if (path == null) return;
      final ok =
          await AppRepositoriesScope.of(context).parcels.completeDelivery(
                parcel.trackingNo,
                photoPath: path,
                otpVerified: otp.length == 6,
                codCollected: parcel.isCod ? codVal : 0,
              );
      if (!mounted) return;
      if (!ok) {
        messenger.showSnackBar(const SnackBar(
            content: Text('This parcel is no longer out for delivery.')));
        return;
      }
      Navigator.pushReplacementNamed(
        context,
        '/delivered',
        arguments:
            DeliveredArgs(tracking: parcel.tracking, photoPath: _photoPath),
      );
    } finally {
      if (mounted) setState(() => _finishing = false);
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: MSubAppBar(
          onBack: () =>
              step == 0 ? Navigator.pop(context) : setState(() => step--),
          title: Text('Step ${step + 1} of $stepCount',
              style: MT.labelLg.copyWith(color: Colors.white)),
        ),
        body: MPageBody(
            child: ListView(padding: MS.pagePadding, children: [
          const ConnectivityBanner(),
          Row(children: [
            for (var i = 0; i < stepCount; i++)
              Expanded(
                  child: Container(
                      height: 6,
                      margin: const EdgeInsets.symmetric(horizontal: 2),
                      decoration: BoxDecoration(
                          color: i <= step ? MC.primary : MC.highest,
                          borderRadius: BorderRadius.circular(99)))),
          ]),
          const SizedBox(height: 12),
          MCard(
              child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                Expanded(
                    child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                      Text(parcel.buyer, style: MT.headlineMd),
                      Text(parcel.address, style: MT.bodySm),
                    ])),
                Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
                  MChip(parcel.isCod ? 'COD parcel' : 'Paid online',
                      bg: parcel.isCod ? MC.tertiaryFixed : MC.greenBg,
                      fg: parcel.isCod ? const Color(0xFF92002A) : MC.green),
                  if (parcel.isCod && step != 2)
                    Text(peso(due),
                        style: MT.currency.copyWith(color: MC.primary)),
                ]),
              ])),
          const SizedBox(height: 12),
          if (step == 0)
            ..._photoStep()
          else if (step == 1)
            ..._otp()
          else
            ..._cod(),
        ])),
      );

  List<Widget> _photoStep() => [
        Text('Parcel Photo Proof', style: MT.headlineMd),
        const SizedBox(height: 4),
        InfoBanner(Icons.lock,
            'Photo must be taken live at handover. Gallery uploads are restricted.',
            bg: MC.container, fg: MC.onSurfaceVariant),
        const SizedBox(height: 8),
        AspectRatio(
          aspectRatio: 4 / 5,
          child: ClipRRect(
            borderRadius: BorderRadius.circular(16),
            child: Stack(
                fit: StackFit.expand,
                alignment: Alignment.center,
                children: [
                  if (_photoBytes != null)
                    Image.memory(_photoBytes!, fit: BoxFit.cover)
                  else
                    Container(
                        color: MC.inverseSurface,
                        child: const Icon(Icons.photo_camera,
                            size: 64, color: Colors.white54)),
                  if (_takingPhoto)
                    Container(
                        color: Colors.black45,
                        child: const Center(
                            child: CircularProgressIndicator(
                                color: Colors.white))),
                  Positioned(
                      bottom: 16,
                      child: _photo != null
                          ? Row(mainAxisSize: MainAxisSize.min, children: [
                              FilledButton.icon(
                                  onPressed: _takePhoto,
                                  icon: const Icon(Icons.replay),
                                  label: const Text('Retake'),
                                  style: FilledButton.styleFrom(
                                      backgroundColor: Colors.white,
                                      foregroundColor: MC.primary)),
                              const SizedBox(width: 8),
                              FilledButton.icon(
                                  onPressed: () => setState(() => step = 1),
                                  icon: const Icon(Icons.arrow_forward),
                                  label: const Text('Use Photo')),
                            ])
                          : GestureDetector(
                              onTap: _takePhoto,
                              child: Container(
                                  width: 76,
                                  height: 76,
                                  decoration: const BoxDecoration(
                                      color: MC.primaryContainer,
                                      shape: BoxShape.circle),
                                  child: const Icon(Icons.photo_camera,
                                      color: Colors.white, size: 32)))),
                ]),
          ),
        ),
        const SizedBox(height: 12),
        PillButton('Continue to Step 2',
            onPressed: _photo == null ? null : () => setState(() => step = 1)),
      ];

  List<Widget> _otp() => [
        Text('Ask the buyer for the 6-digit delivery code',
            style: MT.headlineMd),
        const SizedBox(height: 12),
        Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
          for (var i = 0; i < 6; i++)
            Container(
                width: 48,
                height: 56,
                decoration: BoxDecoration(
                    color: i == otp.length ? MC.primaryFixed : MC.low,
                    borderRadius: BorderRadius.circular(12)),
                child: Center(
                    child: Text(i < otp.length ? otp[i] : '',
                        style: MT.currency))),
        ]),
        const SizedBox(height: 16),
        Keypad(
            bottomLeft: ' ',
            onKey: (k) {
              if (k != ' ' && otp.length < 6) setState(() => otp += k);
            },
            onBackspace: () {
              if (otp.isNotEmpty) {
                setState(() => otp = otp.substring(0, otp.length - 1));
              }
            }),
        const SizedBox(height: 16),
        // TODO: validate against backend; after 3 wrong tries show 15-min lockout banner.
        PillButton(
          parcel.isCod ? 'Continue to Step 3 (Payment)' : 'Mark as Delivered',
          loading: _finishing,
          onPressed: otp.length == 6
              ? () {
                  if (parcel.isCod) {
                    setState(() => step = 2);
                  } else {
                    _finish();
                  }
                }
              : null,
        ),
      ];

  List<Widget> _cod() {
    final under = cod.isNotEmpty && codVal < due,
        over = cod.isNotEmpty && codVal > due;
    return [
      Text('Payment Reconciliation', style: MT.headlineMd),
      const SizedBox(height: 8),
      MCard(
          color: MC.low,
          child: Center(
              child: Column(children: [
            Text('Exact cash due', style: MT.labelMd),
            Text(peso(due), style: MT.headlineXl.copyWith(color: MC.primary)),
          ]))),
      const SizedBox(height: 8),
      Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
            color: codMatch
                ? MC.primaryFixed
                : (cod.isEmpty
                    ? MC.container
                    : MC.errorContainer.withValues(alpha: .4)),
            borderRadius: BorderRadius.circular(12)),
        child: Text('₱ ${cod.isEmpty ? '0.00' : cod}', style: MT.headlineXl),
      ),
      if (under || over)
        Padding(
            padding: const EdgeInsets.only(top: 8),
            child: InfoBanner(Icons.warning,
                'Amount must match ${peso(due)} exactly (₱${(codVal - due).abs().toStringAsFixed(2)} ${under ? 'under' : 'over'})',
                bg: MC.errorContainer, fg: const Color(0xFF93000A))),
      const SizedBox(height: 12),
      Keypad(onKey: (k) {
        if (k == '.' && cod.contains('.')) return;
        if (cod.contains('.') && cod.split('.')[1].length >= 2) return;
        setState(() => cod += k);
      }, onBackspace: () {
        if (cod.isNotEmpty) {
          setState(() => cod = cod.substring(0, cod.length - 1));
        }
      }),
      const SizedBox(height: 16),
      PillButton('Mark as Delivered',
          loading: _finishing, onPressed: codMatch ? _finish : null),
    ];
  }
}
