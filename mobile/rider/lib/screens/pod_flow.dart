import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:permission_handler/permission_handler.dart';
import '../core/theme.dart';
import '../core/connectivity.dart';
import '../data/parcel_flow.dart';
import '../data/repositories/app_repositories_scope.dart';
import '../l10n/generated/app_localizations.dart';
import '../widgets/common.dart';
import 'delivered.dart';

/// Proof of delivery: 1) live photo upload  2) 6-digit OTP verified server-side
///                    3) COD exact amount.
/// Prepaid parcels skip step 3.
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

  /// Server-side storage path returned after successful upload.
  String? _uploadedPath;
  bool _uploading = false;

  bool _otpSending = false;
  bool _otpVerifying = false;
  bool _otpVerified = false;
  int _otpResendAfter = 60; // seconds
  DateTime? _otpSentAt;
  String? _otpError;
  String? _otpLockoutUntil;
  String? _debugOtp; // only populated in kDebugMode

  bool _finishing = false;
  String otp = '';
  String cod = '';

  final ImagePicker _picker = ImagePicker();

  DispatchParcel get parcel => widget.parcel;
  double get due => parcel.cod;
  int get stepCount => parcel.isCod ? 3 : 2;
  double get codVal => double.tryParse(cod) ?? 0;
  bool get codMatch => (codVal * 100).round() == (due * 100).round();

  // ── Step 1: Live photo capture + upload ─────────────────────────────────

  Future<void> _takePhoto() async {
    if (_uploading) return;
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
          content: Text('Camera permission is required for the delivery photo')));
      return;
    }

    final photo = await _picker.pickImage(
        source: ImageSource.camera, imageQuality: 70, maxWidth: 1600);
    if (photo == null || !mounted) return;
    final bytes = await photo.readAsBytes();
    if (!mounted) return;

    final extension = photo.name.split('.').last.toLowerCase();
    if (!const {'jpg', 'jpeg', 'png'}.contains(extension) ||
        bytes.length > 5 * 1024 * 1024) {
      messenger.showSnackBar(SnackBar(
          content: Text(AppLocalizations.of(context).podPhotoSizeError)));
      return;
    }

    setState(() {
      _photo = photo;
      _photoBytes = bytes;
      _uploadedPath = null; // reset so the upload runs again
      _uploading = true;
    });

    try {
      final path = await AppRepositoriesScope.of(context)
          .parcels
          .uploadPhoto(photo.path, purpose: 'pod');
      if (!mounted) return;
      setState(() => _uploadedPath = path);
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _photo = null;
        _photoBytes = null;
        _uploadedPath = null;
      });
      final msg = e.toString().replaceFirst('Exception: ', '');
      messenger.showSnackBar(
        SnackBar(content: Text(msg.isEmpty ? 'Photo upload failed.' : msg)),
      );
    } finally {
      if (mounted) setState(() => _uploading = false);
    }
  }

  // ── Step 2: OTP send + verify ────────────────────────────────────────────

  Future<void> _sendOtp() async {
    if (_otpSending) return;
    // Respect resend cooldown.
    if (_otpSentAt != null) {
      final elapsed = DateTime.now().difference(_otpSentAt!).inSeconds;
      if (elapsed < _otpResendAfter) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
            content: Text(
                'Wait ${_otpResendAfter - elapsed}s before resending.')));
        return;
      }
    }
    setState(() {
      _otpSending = true;
      _otpError = null;
      _otpLockoutUntil = null;
      otp = '';
      _otpVerified = false;
    });
    try {
      final result = await AppRepositoriesScope.of(context)
          .parcels
          .sendDeliveryOtp(parcel.trackingNo);
      if (!mounted) return;
      setState(() {
        _otpSentAt = DateTime.now();
        _otpResendAfter = (result['resend_after'] as num?)?.toInt() ?? 60;
        if (kDebugMode) _debugOtp = result['debug_otp'] as String?;
      });
    } catch (e) {
      if (!mounted) return;
      final msg = e.toString().replaceFirst('Exception: ', '');
      setState(() => _otpError = msg.isEmpty ? 'Could not send OTP.' : msg);
    } finally {
      if (mounted) setState(() => _otpSending = false);
    }
  }

  Future<void> _verifyOtp() async {
    if (_otpVerifying || otp.length != 6) return;
    setState(() {
      _otpVerifying = true;
      _otpError = null;
    });
    try {
      await AppRepositoriesScope.of(context)
          .parcels
          .verifyDeliveryOtp(parcel.trackingNo, otp);
      if (!mounted) return;
      setState(() => _otpVerified = true);
      // Auto-advance to step 3 (or finish for prepaid).
      if (parcel.isCod) {
        setState(() => step = 2);
      } else {
        await _finish();
      }
    } catch (e) {
      if (!mounted) return;
      final msg = e.toString().replaceFirst('Exception: ', '');
      setState(() {
        _otpError = msg.isEmpty ? 'Incorrect code.' : msg;
        otp = ''; // clear so rider types again
        // Check for lockout message.
        if (msg.toLowerCase().contains('locked')) {
          _otpLockoutUntil = msg;
        }
      });
    } finally {
      if (mounted) setState(() => _otpVerifying = false);
    }
  }

  // ── Step 3 / finish ───────────────────────────────────────────────────────

  Future<void> _finish() async {
    if (_finishing) return;
    final messenger = ScaffoldMessenger.of(context);
    setState(() => _finishing = true);
    try {
      final path = _uploadedPath;
      if (path == null) {
        throw Exception('Photo has not been uploaded yet.');
      }
      await AppRepositoriesScope.of(context).parcels.completeDelivery(
            parcel.trackingNo,
            photoPath: path,
            otpVerified: true, // enforced server-side via delivery_otp_verified_at
            codCollected: parcel.isCod ? codVal : 0,
          );
      if (!mounted) return;
      Navigator.pushReplacementNamed(
        context,
        '/delivered',
        arguments: DeliveredArgs(tracking: parcel.tracking, photoPath: path),
      );
    } catch (e) {
      if (!mounted) return;
      final msg = e.toString().replaceFirst('Exception: ', '');
      messenger.showSnackBar(
        SnackBar(content: Text(msg.isEmpty ? 'Could not complete delivery.' : msg)),
      );
    } finally {
      if (mounted) setState(() => _finishing = false);
    }
  }

  // ── Build ─────────────────────────────────────────────────────────────────

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
            ..._otpStep()
          else
            ..._codStep(),
        ])),
      );

  // ── Step 1 widget ─────────────────────────────────────────────────────────

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
                  if (_uploading)
                    Container(
                        color: Colors.black45,
                        child: const Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              CircularProgressIndicator(color: Colors.white),
                              SizedBox(height: 12),
                              Text('Uploading…',
                                  style: TextStyle(
                                      color: Colors.white,
                                      fontWeight: FontWeight.w600)),
                            ])),
                  Positioned(
                      bottom: 16,
                      child: _photo != null && !_uploading
                          ? Row(mainAxisSize: MainAxisSize.min, children: [
                              FilledButton.icon(
                                  onPressed: _takePhoto,
                                  icon: const Icon(Icons.replay),
                                  label: const Text('Retake'),
                                  style: FilledButton.styleFrom(
                                      backgroundColor: Colors.white,
                                      foregroundColor: MC.primary)),
                              const SizedBox(width: 8),
                              if (_uploadedPath != null)
                                FilledButton.icon(
                                    onPressed: () {
                                      setState(() => step = 1);
                                      _sendOtp();
                                    },
                                    icon: const Icon(Icons.arrow_forward),
                                    label: const Text('Use Photo')),
                            ])
                          : _photo == null
                              ? GestureDetector(
                                  onTap: _takePhoto,
                                  child: Container(
                                      width: 76,
                                      height: 76,
                                      decoration: const BoxDecoration(
                                          color: MC.primaryContainer,
                                          shape: BoxShape.circle),
                                      child: const Icon(Icons.photo_camera,
                                          color: Colors.white, size: 32)))
                              : const SizedBox.shrink()),
                ]),
          ),
        ),
        const SizedBox(height: 12),
        if (_uploadedPath != null)
          InfoBanner(Icons.check_circle_outline, 'Photo uploaded successfully.',
              bg: MC.greenBg, fg: MC.green),
        const SizedBox(height: 4),
        PillButton(
          'Continue to Step 2',
          onPressed: _uploadedPath == null
              ? null
              : () {
                  setState(() => step = 1);
                  _sendOtp();
                },
        ),
      ];

  // ── Step 2 widget ─────────────────────────────────────────────────────────

  List<Widget> _otpStep() {
    final locked = _otpLockoutUntil != null;
    return [
      Text('Ask the buyer for the 6-digit delivery code', style: MT.headlineMd),
      const SizedBox(height: 4),
      if (_otpSentAt != null)
        Text('Code sent to buyer\'s registered contact.',
            style: MT.bodySm.copyWith(color: MC.onSurfaceVariant))
      else if (_otpSending)
        Text('Sending code…', style: MT.bodySm.copyWith(color: MC.onSurfaceVariant)),
      const SizedBox(height: 12),
      if (locked)
        InfoBanner(Icons.lock_clock, _otpLockoutUntil!,
            bg: MC.errorContainer, fg: const Color(0xFF93000A))
      else if (_otpError != null && _otpLockoutUntil == null)
        InfoBanner(Icons.error_outline, _otpError!,
            bg: MC.errorContainer, fg: const Color(0xFF93000A)),
      if (kDebugMode && _debugOtp != null) ...[
        const SizedBox(height: 4),
        InfoBanner(
            Icons.bug_report, 'DEBUG — buyer code: $_debugOtp',
            bg: MC.amberBg, fg: MC.amberText),
      ],
      const SizedBox(height: 8),
      Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
        for (var i = 0; i < 6; i++)
          Container(
              width: 48,
              height: 56,
              decoration: BoxDecoration(
                  color: _otpVerified
                      ? MC.greenBg
                      : (i == otp.length ? MC.primaryFixed : MC.low),
                  borderRadius: BorderRadius.circular(12)),
              child: Center(
                  child: Text(i < otp.length ? otp[i] : '',
                      style: MT.currency))),
      ]),
      const SizedBox(height: 16),
      Keypad(
          bottomLeft: ' ',
          onKey: (k) {
            if (locked || _otpVerified || _otpVerifying) return;
            if (k != ' ' && otp.length < 6) setState(() => otp += k);
          },
          onBackspace: () {
            if (locked || _otpVerified || _otpVerifying) return;
            if (otp.isNotEmpty) {
              setState(() => otp = otp.substring(0, otp.length - 1));
            }
          }),
      const SizedBox(height: 12),
      if (!locked && _otpSentAt != null)
        TextButton(
            onPressed: _otpSending ? null : _sendOtp,
            child: Text(_otpSending ? 'Sending…' : 'Resend code')),
      const SizedBox(height: 4),
      PillButton(
        parcel.isCod ? 'Continue to Step 3 (Payment)' : 'Mark as Delivered',
        loading: _otpVerifying || _finishing,
        onPressed: (otp.length == 6 && !locked && !_otpVerified)
            ? _verifyOtp
            : null,
      ),
    ];
  }

  // ── Step 3 widget ─────────────────────────────────────────────────────────

  List<Widget> _codStep() {
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
