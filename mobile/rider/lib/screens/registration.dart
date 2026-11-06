import 'dart:io';
import 'dart:math';

import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:path/path.dart' as p;
import 'package:path_provider/path_provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../core/theme.dart';
import '../data/models/entities.dart';
import '../data/repositories/app_repositories_scope.dart';
import '../widgets/common.dart';

/// 4-step self-registration (ERP flow). Steps 1–4 share one scaffold.
class RegistrationScreen extends StatefulWidget {
  const RegistrationScreen({super.key});
  @override
  State<RegistrationScreen> createState() => _RegistrationScreenState();
}

class _RegistrationScreenState extends State<RegistrationScreen> {
  int step = 0;
  bool _submitting = false;

  // ── Step 1: Personal details ──────────────────────────────────────────────
  final _lastName = TextEditingController();
  final _firstName = TextEditingController();
  final _middleInitial = TextEditingController();
  String? _sex;
  final _email = TextEditingController();
  final _phone = TextEditingController();
  DateTime? _birthday;
  Map<String, String> _fieldErrors = {};

  // ── Step 1b: Phone verification ──────────────────────────────────────────
  bool _phoneVerified = false;
  bool _otpSending = false;
  bool _otpVerifying = false;
  String _localOtpCode = '';
  String? _otpError;
  String? _otpLockoutMsg;
  String _otpCode = '';
  String? _debugOtp;
  DateTime? _otpSentAt;

  // ── Step 2: Address ───────────────────────────────────────────────────────
  String? _province;
  String? _city;
  String? _barangay;
  final _street = TextEditingController();
  final _houseNo = TextEditingController();
  final _postalCode = TextEditingController();

  // ── Step 3: Vehicle & license ─────────────────────────────────────────────
  int _vehicle = 0; // 0=Motorcycle 1=Sedan/MPV 2=Van/Cargo
  final _plateNo = TextEditingController();
  final _licenseNo = TextEditingController();
  final _licenseExpiry = TextEditingController();

  // ── Step 4: Documents ────────────────────────────────────────────────────
  XFile? _licensePhoto;
  XFile? _orCrPhoto;
  bool _agreed = false;

  static const _vehicleLabels = ['Motorcycle', 'Sedan / MPV', 'Van / Cargo'];
  static const _titles = [
    'Personal Details',
    'Phone Verification',
    'Address Details',
    'Vehicle & License',
    'Documents',
  ];

  final _picker = ImagePicker();

  @override
  void dispose() {
    _lastName.dispose();
    _firstName.dispose();
    _middleInitial.dispose();
    _email.dispose();
    _phone.dispose();
    _street.dispose();
    _houseNo.dispose();
    _postalCode.dispose();
    _plateNo.dispose();
    _licenseNo.dispose();
    _licenseExpiry.dispose();
    super.dispose();
  }

  // ── Age from birthday ─────────────────────────────────────────────────────
  int? get _age {
    if (_birthday == null) return null;
    final now = DateTime.now();
    var a = now.year - _birthday!.year;
    if (now.month < _birthday!.month ||
        (now.month == _birthday!.month && now.day < _birthday!.day)) {
      a--;
    }
    return a;
  }

  // ── Step 1 validation ─────────────────────────────────────────────────────
  bool get _step1Valid =>
      _lastName.text.trim().isNotEmpty &&
      _firstName.text.trim().isNotEmpty &&
      _sex != null &&
      _email.text.contains('@') &&
      _phone.text.trim().isNotEmpty &&
      _birthday != null &&
      (_age ?? 0) >= 18;

  // ── Step 1b validation ───────────────────────────────────────────────────
  bool get _step1bValid => _phoneVerified;

  // ── Step 3 validation ─────────────────────────────────────────────────────
  bool get _step3Valid => _plateNo.text.trim().isNotEmpty;

  // ── Step 4 validation ─────────────────────────────────────────────────────
  bool get _step4Valid =>
      _licensePhoto != null && _orCrPhoto != null && _agreed;

  // ── Discard confirmation ──────────────────────────────────────────────────
  Future<void> _discard() async {
    final out = await showDialog<bool>(
      context: context,
      builder: (c) => AlertDialog(
        title: Text('Discard your registration?', style: MT.headlineMd),
        content: const Text('Your entered details will be lost.'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(c, false),
              child: const Text('Keep Editing')),
          FilledButton(
              style:
                  FilledButton.styleFrom(backgroundColor: MC.tertiaryContainer),
              onPressed: () => Navigator.pop(c, true),
              child: const Text('Discard')),
        ],
      ),
    );
    if (out == true && mounted) Navigator.pop(context);
  }

  // ── Document picker ───────────────────────────────────────────────────────
  Future<void> _pickDocument(String type) async {
    final source = await showModalBottomSheet<ImageSource>(
      context: context,
      backgroundColor: MC.lowest,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(28))),
      builder: (c) => SafeArea(
              child: Column(mainAxisSize: MainAxisSize.min, children: [
            const SizedBox(height: 12),
            for (final o in [
              (Icons.photo_camera, 'Take Photo', ImageSource.camera),
              (Icons.photo_library, 'Choose from Gallery', ImageSource.gallery),
            ])
              ListTile(
                  leading: IconDot(o.$1, size: 40),
                  title: Text(o.$2, style: MT.labelLg),
                  onTap: () => Navigator.pop(c, o.$3)),
            Padding(
                padding: const EdgeInsets.all(12),
                child: Text('JPG or PNG, max 5 MB', style: MT.bodySm)),
          ])),
    );
    if (source == null || !mounted) return;

    final file = await _picker.pickImage(
        source: source, imageQuality: 80, maxWidth: 1600);
    if (file == null || !mounted) return;

    // Validate size.
    final bytes = await file.readAsBytes();
    if (bytes.length > 5 * 1024 * 1024) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('File must be under 5 MB.')));
      return;
    }
    final ext = file.name.split('.').last.toLowerCase();
    if (!{'jpg', 'jpeg', 'png'}.contains(ext)) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Only JPG and PNG files are accepted.')));
      return;
    }

    setState(() {
      if (type == 'license') {
        _licensePhoto = file;
      } else {
        _orCrPhoto = file;
      }
    });
  }

  // ── Submit — save to local SQLite ─────────────────────────────────────────
  Future<void> _submit() async {
    if (_submitting || !_step4Valid) return;
    final messenger = ScaffoldMessenger.of(context);
    setState(() {
      _submitting = true;
      _fieldErrors = {};
    });

    try {
      final repos = AppRepositoriesScope.of(context);

      // Save document photos to app documents directory.
      final docsDir = await getApplicationDocumentsDirectory();
      String? licensePath;
      String? orCrPath;
      if (_licensePhoto != null) {
        licensePath = p.join(docsDir.path, 'license.jpg');
        await File(_licensePhoto!.path).copy(licensePath);
      }
      if (_orCrPhoto != null) {
        orCrPath = p.join(docsDir.path, 'or_cr.jpg');
        await File(_orCrPhoto!.path).copy(orCrPath);
      }

      // Update the rider profile with the new info.
      final current = repos.riderProfile.profile;
      if (current != null) {
        final updated = RiderProfile(
          id: current.id,
          userId: current.userId,
          name: '${_firstName.text.trim()} ${_lastName.text.trim()}',
          email: _email.text.trim().toLowerCase(),
          phone: _phone.text.trim(),
          address: [
            _houseNo.text.trim(),
            _street.text.trim(),
            _barangay ?? '',
            _city ?? '',
            _province ?? '',
          ].where((s) => s.isNotEmpty).join(', '),
          vehicleType: _vehicleLabels[_vehicle],
          plateNo: _plateNo.text.trim(),
          licenseNo: _licenseNo.text.trim(),
          licenseExpiresOn: _licenseExpiry.text.trim().isNotEmpty
              ? _licenseExpiry.text.trim()
              : null,
          kycStatus: 'pending',
          areaId: current.areaId,
          areaName: current.areaName,
          fleetName: current.fleetName,
          hubName: current.hubName,
          isActive: current.isActive,
          checkedInAt: current.checkedInAt,
          suspendedAt: current.suspendedAt,
          suspensionReason: current.suspensionReason,
          photoPath: licensePath ?? current.photoPath,
        );
        await repos.riderProfile.saveProfile(updated);
      }

      if (!mounted) return;
      Navigator.pushReplacementNamed(context, '/pending');
    } catch (e) {
      if (!mounted) return;
      setState(() => _submitting = false);
      messenger.showSnackBar(
          SnackBar(content: Text(e.toString().replaceFirst('Exception: ', ''))));
    }
  }

  // ── Next button logic ─────────────────────────────────────────────────────
  void _next() {
    if (step == 0 && !_step1Valid) return;
    // After step 1, auto-send OTP when entering step 1b.
    if (step == 0 && _otpSentAt == null) {
      _sendPhoneOtp();
    }
    if (step == 3 && !_step3Valid) return;
    if (step == _titles.length - 1) {
      _submit();
    } else {
      setState(() => step++);
    }
  }

  bool get _nextEnabled {
    if (_submitting || _otpVerifying) return false;
    return switch (step) {
      0 => _step1Valid,
      1 => _step1bValid, // phone verified
      2 => true, // address fields are optional
      3 => _step3Valid,
      _ => _step4Valid,
    };
  }

  // ── Build ─────────────────────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: MSubAppBar(
          backgroundColor: MC.lowest,
          foregroundColor: MC.onSurface,
          onBack: () =>
              step == 0 ? Navigator.pop(context) : setState(() => step--),
          title:
              Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text('Rider Registration', style: MT.headlineMd),
            Text('Step ${step + 1} of ${_titles.length} · ${_titles[step]}', style: MT.bodySm),
          ]),
          actions: [
            TextButton(
                onPressed: _discard,
                child: Text('Discard',
                    style: MT.labelMd.copyWith(color: MC.tertiary))),
          ],
          bottom: PreferredSize(
              preferredSize: const Size.fromHeight(6),
              child: LinearProgressIndicator(
                  value: (step + 1) / _titles.length,
                  minHeight: 6,
                  backgroundColor: MC.high,
                  color: MC.primaryContainer)),
        ),
        body: Center(
          child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 600),
              child: ListView(padding: MS.pagePadding, children: [
                MCard(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          ...switch (step) {
                            0 => _s1(),
                            1 => _s1b(),
                            2 => _s2(),
                            3 => _s3(),
                            _ => _s4()
                          },
                        ]
                            .expand((w) => [w, const SizedBox(height: 14)])
                            .toList()
                          ..removeLast()),
                    ),
                const SizedBox(height: 16),
                PillButton(
                  step == _titles.length - 1 ? 'Submit Registration' : 'Next',
                  loading: _submitting || _otpVerifying,
                  onPressed: _nextEnabled ? _next : null,
                ),
              ])),
        ),
      );

  // ── Step 1 widgets ────────────────────────────────────────────────────────

  List<Widget> _s1() => [
        _field(_lastName, 'Last name', required: true,
            error: _fieldErrors['last_name']),
        _field(_firstName, 'First name', required: true,
            error: _fieldErrors['first_name']),
        _field(_middleInitial, 'Middle initial'),
        _dropdown('Sex', ['Male', 'Female'], _sex,
            required: true,
            error: _fieldErrors['sex'],
            onChanged: (v) => setState(() => _sex = v)),
        _field(_email, 'Email',
            required: true,
            keyboard: TextInputType.emailAddress,
            error: _fieldErrors['email']),
        _field(_phone, 'Contact number',
            required: true,
            keyboard: TextInputType.phone,
            hint: '09XXXXXXXXX or +639XXXXXXXXX',
            error: _fieldErrors['phone']),
        // Birthday date picker
        Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          _label('Birthday', required: true),
          const SizedBox(height: 6),
          InkWell(
            borderRadius: BorderRadius.circular(99),
            onTap: () async {
              final d = await showDatePicker(
                  context: context,
                  initialDate: DateTime(1995, 10, 24),
                  firstDate: DateTime(1940),
                  lastDate: DateTime.now());
              if (d != null) setState(() => _birthday = d);
            },
            child: Container(
                width: double.infinity,
                padding:
                    const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                decoration: BoxDecoration(
                    color: MC.low, borderRadius: BorderRadius.circular(99)),
                child: Text(
                    _birthday == null
                        ? 'MM/DD/YYYY'
                        : '${_birthday!.month}/${_birthday!.day}/${_birthday!.year}',
                    style: MT.bodyMd)),
          ),
          if (_age != null && _age! < 18)
            Padding(
                padding: const EdgeInsets.only(top: 4, left: 12),
                child: Text('You must be at least 18 years old',
                    style: MT.bodySm.copyWith(color: MC.error))),
          if (_fieldErrors.containsKey('birthday'))
            Padding(
                padding: const EdgeInsets.only(top: 4, left: 12),
                child: Text(_fieldErrors['birthday']!,
                    style: MT.bodySm.copyWith(color: MC.error))),
        ]),
        // Read-only age display
        Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text('Age', style: MT.labelMd),
          const SizedBox(height: 6),
          Container(
              width: double.infinity,
              padding:
                  const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
              decoration: BoxDecoration(
                  color: MC.high, borderRadius: BorderRadius.circular(99)),
              child: Text(_age == null ? '—' : '$_age years old',
                  style: MT.labelLg.copyWith(color: MC.primary))),
        ]),
      ];

  // ── Step 1b: Phone verification ────────────────────────────────────────────

  Future<void> _sendPhoneOtp() async {
    if (_otpSending) return;
    setState(() {
      _otpSending = true;
      _otpError = null;
      _otpLockoutMsg = null;
      _otpCode = '';
      _phoneVerified = false;
    });
    try {
      // Local OTP: generate a random 6-digit code and show it in debug mode.
      // In production this would send an SMS; for local demo we surface it via debugOtp.
      final code = (100000 + Random().nextInt(900000)).toString();
      _localOtpCode = code;
      if (!mounted) return;
      setState(() {
        _otpSentAt = DateTime.now();
        // Always show the code for local/demo (no real SMS).
        _debugOtp = code;
      });
    } catch (e) {
      if (!mounted) return;
      final msg = e.toString().replaceFirst('Exception: ', '');
      setState(() => _otpError = msg.isEmpty ? 'Could not send code.' : msg);
    } finally {
      if (mounted) setState(() => _otpSending = false);
    }
  }

  Future<void> _verifyPhoneOtp() async {
    if (_otpVerifying || _otpCode.length != 6) return;
    setState(() {
      _otpVerifying = true;
      _otpError = null;
    });
    try {
      // Local verification: compare against the generated code.
      await Future<void>.delayed(const Duration(milliseconds: 200));
      if (!mounted) return;
      if (_otpCode == _localOtpCode) {
        setState(() => _phoneVerified = true);
      } else {
        setState(() {
          _otpError = 'Incorrect code. Please check and try again.';
          _otpCode = '';
        });
      }
    } finally {
      if (mounted) setState(() => _otpVerifying = false);
    }
  }

  List<Widget> _s1b() {
    final locked = _otpLockoutMsg != null;
    return [
      Text('Verify your phone number', style: MT.headlineMd),
      const SizedBox(height: 4),
      Text(
          'A 6-digit code has been sent to ${_phone.text}. Enter it below to verify.',
          style: MT.bodyMd.copyWith(color: MC.onSurfaceVariant)),
      const SizedBox(height: 8),
      if (_otpSending)
        const Padding(
            padding: EdgeInsets.symmetric(vertical: 12),
            child: Center(child: CircularProgressIndicator()))
      else if (!_phoneVerified && _otpSentAt == null)
        PillButton('Send Verification Code', onPressed: _sendPhoneOtp),
      if (_otpSentAt != null && !_phoneVerified) ...[
        if (kDebugMode && _debugOtp != null)
          InfoBanner(Icons.bug_report, 'DEBUG — code: $_debugOtp',
              bg: MC.amberBg, fg: MC.amberText),
        const SizedBox(height: 8),
        if (locked)
          InfoBanner(Icons.lock_clock, _otpLockoutMsg!,
              bg: MC.errorContainer, fg: const Color(0xFF93000A))
        else if (_otpError != null)
          InfoBanner(Icons.error_outline, _otpError!,
              bg: MC.errorContainer, fg: const Color(0xFF93000A)),
        const SizedBox(height: 8),
        Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
          for (var i = 0; i < 6; i++)
            Container(
                width: 48,
                height: 56,
                decoration: BoxDecoration(
                    color: i == _otpCode.length ? MC.primaryFixed : MC.low,
                    borderRadius: BorderRadius.circular(12)),
                child: Center(
                    child: Text(i < _otpCode.length ? _otpCode[i] : '',
                        style: MT.currency))),
        ]),
        const SizedBox(height: 16),
        Keypad(
            bottomLeft: ' ',
            onKey: (k) {
              if (locked || _phoneVerified || _otpVerifying) return;
              if (k != ' ' && _otpCode.length < 6) {
                setState(() => _otpCode += k);
              }
            },
            onBackspace: () {
              if (locked || _phoneVerified || _otpVerifying) return;
              if (_otpCode.isNotEmpty) {
                setState(() => _otpCode = _otpCode.substring(0, _otpCode.length - 1));
              }
            }),
        const SizedBox(height: 12),
        if (!locked)
          TextButton(
              onPressed: _otpSending ? null : _sendPhoneOtp,
              child: Text(_otpSending ? 'Sending…' : 'Resend code')),
        const SizedBox(height: 4),
        PillButton(
          'Verify Code',
          loading: _otpVerifying,
          onPressed: (_otpCode.length == 6 && !locked && !_phoneVerified)
              ? _verifyPhoneOtp
              : null,
        ),
      ],
      if (_phoneVerified)
        InfoBanner(Icons.check_circle_outline, 'Phone number verified successfully.',
            bg: MC.greenBg, fg: MC.green),
    ];
  }

  // ── Step 2 widgets ────────────────────────────────────────────────────────

  List<Widget> _s2() => [
        _dropdown(
            'Province',
            ['Metro Manila (NCR)', 'Cavite', 'Cebu', 'Laguna'],
            _province,
            required: true,
            error: _fieldErrors['province'],
            onChanged: (v) => setState(() {
                  _province = v;
                  _city = null;
                  _barangay = null;
                })),
        _dropdown(
            'Municipality / City',
            _province == null
                ? []
                : ['Quezon City', 'Pasig', 'Manila', 'Santa Cruz'],
            _city,
            required: true,
            error: _fieldErrors['city'],
            onChanged: _province == null
                ? null
                : (v) => setState(() {
                      _city = v;
                      _barangay = null;
                    })),
        _dropdown(
            'Barangay',
            _city == null
                ? []
                : [
                    'Brgy. Central',
                    'Brgy. Kamuning',
                    'Poblacion I',
                    'Poblacion II'
                  ],
            _barangay,
            required: true,
            error: _fieldErrors['barangay'],
            onChanged: _city == null
                ? null
                : (v) => setState(() => _barangay = v)),
        _field(_street, 'Street', required: true,
            error: _fieldErrors['street']),
        Row(children: [
          Expanded(
              child:
                  _field(_houseNo, 'House / Unit no.', required: true)),
          const SizedBox(width: 12),
          Expanded(
              child: _field(_postalCode, 'Postal code',
                  required: true,
                  keyboard: TextInputType.number,
                  error: _fieldErrors['postal_code'])),
        ]),
      ];

  // ── Step 3 widgets ────────────────────────────────────────────────────────

  List<Widget> _s3() => [
        Text('Choose vehicle *', style: MT.labelLg),
        LayoutBuilder(builder: (context, constraints) {
          final columns = constraints.maxWidth < 420 ? 1 : 3;
          final width = columns == 1
              ? constraints.maxWidth
              : (constraints.maxWidth - 12) / 3;
          return Wrap(spacing: 6, runSpacing: 8, children: [
            for (final v in [
              (Icons.two_wheeler, 'Motorcycle', 'Up to 20 kg'),
              (Icons.directions_car, 'Sedan / MPV', 'Up to 200 kg'),
              (Icons.airport_shuttle, 'Van / Cargo', 'Up to 800 kg')
            ].asMap().entries)
              SizedBox(
                width: width,
                child: GestureDetector(
                  onTap: () => setState(() => _vehicle = v.key),
                  child: Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: _vehicle == v.key ? MC.high : MC.lowest,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                          color: _vehicle == v.key
                              ? MC.primary
                              : MC.outlineVariant),
                    ),
                    child: Column(children: [
                      IconDot(v.value.$1,
                          size: 40,
                          bg: _vehicle == v.key ? MC.primary : MC.low,
                          fg: _vehicle == v.key
                              ? Colors.white
                              : MC.onSurfaceVariant),
                      const SizedBox(height: 6),
                      Text(v.value.$2,
                          style: MT.labelMd, textAlign: TextAlign.center),
                      Text(v.value.$3, style: MT.bodySm),
                    ]),
                  ),
                ),
              ),
          ]);
        }),
        _field(_plateNo, 'Plate number',
            required: true,
            hint: 'ABC123',
            error: _fieldErrors['plate_no']),
        _field(_licenseNo, "Driver's License No.",
            required: true,
            hint: 'A00-00-000000',
            error: _fieldErrors['license_no']),
        _field(_licenseExpiry, 'License expiry date',
            required: true,
            hint: 'MM/DD/YYYY',
            error: _fieldErrors['license_expiry']),
      ];

  // ── Step 4 widgets ────────────────────────────────────────────────────────

  List<Widget> _s4() => [
        _docUpload("Upload ID / Driver's License", _licensePhoto,
            () => _pickDocument('license')),
        _docUpload('Upload Vehicle OR/CR', _orCrPhoto,
            () => _pickDocument('or_cr')),
        if (_fieldErrors.containsKey('license_photo') ||
            _fieldErrors.containsKey('or_cr_photo'))
          InfoBanner(Icons.error_outline,
              _fieldErrors['license_photo'] ?? _fieldErrors['or_cr_photo']!,
              bg: MC.errorContainer, fg: const Color(0xFF93000A)),
        CheckboxListTile(
          value: _agreed,
          onChanged: (v) => setState(() => _agreed = v ?? false),
          controlAffinity: ListTileControlAffinity.leading,
          contentPadding: EdgeInsets.zero,
          title: Text('I agree to the Terms and Privacy Policy *',
              style: MT.bodySm.copyWith(color: MC.onSurface)),
        ),
      ];

  // ── Shared helpers ────────────────────────────────────────────────────────

  Widget _label(String text, {bool required = false}) => Text.rich(
      TextSpan(text: text, style: MT.labelMd, children: [
        if (required)
          const TextSpan(
              text: ' *', style: TextStyle(color: MC.tertiaryContainer))
      ]));

  Widget _field(TextEditingController ctrl, String label,
      {bool required = false,
      TextInputType? keyboard,
      String? hint,
      String? error}) =>
      Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        _label(label, required: required),
        const SizedBox(height: 6),
        TextField(
          controller: ctrl,
          keyboardType: keyboard,
          onChanged: (_) => setState(() {}),
          decoration: InputDecoration(
            hintText: hint,
            filled: true,
            fillColor: MC.low,
            border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(99),
                borderSide: BorderSide.none),
            errorText: error,
            contentPadding:
                const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
          ),
        ),
      ]);

  Widget _dropdown(String label, List<String> items, String? value,
      {bool required = false,
      String? error,
      ValueChanged<String?>? onChanged}) =>
      Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        _label(label, required: required),
        const SizedBox(height: 6),
        // ignore: deprecated_member_use
        DropdownButtonFormField<String>(
          // ignore: deprecated_member_use
          value: items.contains(value) ? value : null,
          items: items
              // ignore: deprecated_member_use
              .map((e) => DropdownMenuItem(value: e, child: Text(e)))
              .toList(),
          onChanged: onChanged,
          decoration: InputDecoration(
              filled: true,
              fillColor: MC.low,
              errorText: error,
              contentPadding:
                  const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(99),
                  borderSide: BorderSide.none)),
        ),
      ]);

  Widget _docUpload(String title, XFile? file, VoidCallback onTap) =>
      InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: onTap,
        child: Container(
          width: double.infinity,
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
              color: MC.low, borderRadius: BorderRadius.circular(12)),
          child: Row(children: [
            file != null
                ? ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: Image.file(File(file.path),
                        width: 48, height: 48, fit: BoxFit.cover))
                : IconDot(Icons.cloud_upload, bg: MC.high, fg: MC.primary),
            const SizedBox(width: 12),
            Expanded(
                child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                  Text('$title *', style: MT.labelLg),
                  Text(
                      file != null
                          ? '${file.name} · ready'
                          : 'Tap to upload · JPG, PNG · max 5 MB',
                      style: MT.bodySm),
                ])),
            if (file != null)
              const Icon(Icons.check_circle, color: MC.green, size: 20),
          ]),
        ),
      );
}

// ── Pending Approval screen ────────────────────────────────────────────────

class PendingApprovalScreen extends StatelessWidget {
  const PendingApprovalScreen({super.key});
  @override
  Widget build(BuildContext context) => Scaffold(
        body: SafeArea(
          child: MPageBody(
              child: ListView(padding: MS.pagePadding, children: [
            MCard(
                color: MC.low,
                radius: 16,
                padding: const EdgeInsets.all(24),
                child: Column(children: [
                  const IconDot(Icons.hourglass_top,
                      size: 64, bg: MC.highest, fg: MC.primaryContainer),
                  const SizedBox(height: 12),
                  const MChip('Pending approval',
                      bg: MC.amberBg, fg: MC.amberText, icon: Icons.schedule),
                  const SizedBox(height: 8),
                  Text('Registration submitted', style: MT.headlineLg),
                  const SizedBox(height: 6),
                  Text(
                      'Please wait for the approval of the Logistics / Sorting Center. The decision will be sent to your email.',
                      style: MT.bodyMd.copyWith(color: MC.onSurfaceVariant),
                      textAlign: TextAlign.center),
                ])),
            const SizedBox(height: 16),
            PillButton('Back to Login',
                onPressed: () => Navigator.pushNamedAndRemoveUntil(
                    context, '/login', (_) => false)),
            const SizedBox(height: 8),
            PillButton.secondary('Contact Logistics',
                leading: Icons.support_agent,
                trailing: null,
                onPressed: () => launchUrl(Uri.parse('tel:+639171234567'))),
            if (kDebugMode) ...[
              const SizedBox(height: 8),
              Row(mainAxisAlignment: MainAxisAlignment.center, children: [
                TextButton(
                    onPressed: () =>
                        Navigator.pushNamed(context, '/registration/approved'),
                    child: const Text('Demo: Approved')),
                TextButton(
                    onPressed: () =>
                        Navigator.pushNamed(context, '/registration/rejected'),
                    child: const Text('Demo: Rejected')),
              ]),
            ],
          ])),
        ),
      );
}
