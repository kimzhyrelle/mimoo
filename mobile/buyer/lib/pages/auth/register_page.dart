import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'login_page.dart';
import 'verify_email_page.dart';
import '../../services/api_service.dart';
import '../../widgets/address_dropdown.dart';

const _kPrimary = Color(0xFF4B2E8A);
const _kPrimaryLight = Color(0xFFEDE8F8);
const _kSurface = Color(0xFFF5F4FC);
const _kBorder = Color(0xFFDDD8F0);
const _kText = Color(0xFF1A1035);
const _kSubtext = Color(0xFF8A84A3);
const _kAccent = Color(0xFF6B38D4);

// ── Step labels ───────────────────────────────────────────────────────────────
const _steps = ['Details', 'Address', 'Verification', 'Security'];

class RegisterPage extends StatefulWidget {
  const RegisterPage({super.key});

  @override
  State<RegisterPage> createState() => _RegisterPageState();
}

class _RegisterPageState extends State<RegisterPage> {
  int _step = 0;
  bool _loading = false;

  // Step 1 — Personal info
  final _detailsKey = GlobalKey<FormState>();
  final _lastNameCtrl = TextEditingController();
  final _firstNameCtrl = TextEditingController();
  final _miCtrl = TextEditingController();
  String _sex = 'male'; // lowercase, matches API: 'male' | 'female'
  DateTime? _birthday;
  int? _age;

  // Step 2 — Address
  final _addressKey = GlobalKey<FormState>();
  // PSGC-backed address values (set by AddressDropdown callbacks)
  String _region = '';
  String? _province; // null for NCR
  String? _district; // NCR only
  String _municipalityCity = '';
  String _barangay = '';
  final _streetCtrl = TextEditingController();
  final _houseCtrl = TextEditingController();

  // Step 3 — Verification
  final _verifyKey = GlobalKey<FormState>();
  final _emailCtrl = TextEditingController();
  final _contactCtrl = TextEditingController();
  String? _uploadedId;

  // Step 4 — Security
  final _securityKey = GlobalKey<FormState>();
  final _passCtrl = TextEditingController();
  final _confirmPassCtrl = TextEditingController();
  bool _obscurePass = true;
  bool _obscureConfirm = true;

  @override
  void dispose() {
    _lastNameCtrl.dispose();
    _firstNameCtrl.dispose();
    _miCtrl.dispose();
    _streetCtrl.dispose();
    _houseCtrl.dispose();
    _emailCtrl.dispose();
    _contactCtrl.dispose();
    _passCtrl.dispose();
    _confirmPassCtrl.dispose();
    super.dispose();
  }

  // ── Birthday picker ──────────────────────────────────────────────────────────
  Future<void> _pickBirthday() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: DateTime(now.year - 18, now.month, now.day),
      firstDate: DateTime(1900),
      lastDate: DateTime(now.year - 10, now.month, now.day),
      builder: (context, child) => Theme(
        data: Theme.of(context).copyWith(
          colorScheme:
              Theme.of(context).colorScheme.copyWith(primary: _kPrimary),
        ),
        child: child!,
      ),
    );
    if (picked != null) {
      final age = now.year -
          picked.year -
          ((now.month < picked.month ||
                  (now.month == picked.month && now.day < picked.day))
              ? 1
              : 0);
      setState(() {
        _birthday = picked;
        _age = age;
      });
    }
  }

  // ── Navigation ───────────────────────────────────────────────────────────────
  void _next() {
    GlobalKey<FormState>? key;
    if (_step == 0) key = _detailsKey;
    if (_step == 1) key = _addressKey;
    if (_step == 2) key = _verifyKey;
    if (_step == 3) key = _securityKey;

    if (key != null && !(key.currentState?.validate() ?? false)) return;

    // Extra validation for step 1 birthday
    if (_step == 0 && _birthday == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please select your birthday')),
      );
      return;
    }

    // Extra validation for step 2: address dropdowns are not inside a Form
    if (_step == 1) {
      if (_region.isEmpty || _municipalityCity.isEmpty || _barangay.isEmpty) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'Please complete all address fields (region, city, barangay).',
              style: GoogleFonts.plusJakartaSans(color: Colors.white),
            ),
            backgroundColor: Colors.red.shade700,
            behavior: SnackBarBehavior.floating,
          ),
        );
        return;
      }
    }

    if (_step < 3) {
      setState(() => _step++);
    } else {
      _submit();
    }
  }

  Future<void> _submit() async {
    setState(() => _loading = true);

    // Build street_address: "<house>, <street>" when both filled; otherwise
    // whichever is non-empty, matching the web's single street_address field.
    final house = _houseCtrl.text.trim();
    final street = _streetCtrl.text.trim();
    final streetAddress = (house.isNotEmpty && street.isNotEmpty)
        ? '$house, $street'
        : (house.isNotEmpty ? house : street);

    try {
      final res = await ApiService.post(
        '/buyer/register',
        body: {
          'first_name': _firstNameCtrl.text.trim(),
          'last_name': _lastNameCtrl.text.trim(),
          if (_miCtrl.text.trim().isNotEmpty)
            'middle_initial': _miCtrl.text.trim(),
          'sex': _sex, // already lowercase: 'male' | 'female'
          'email': _emailCtrl.text.trim().toLowerCase(),
          'mobile': _contactCtrl.text.trim(),
          'date_of_birth': _birthday != null
              ? '${_birthday!.year}-'
                  '${_birthday!.month.toString().padLeft(2, '0')}-'
                  '${_birthday!.day.toString().padLeft(2, '0')}'
              : '',
          // Address — plain text names from PSGC API (same as web)
          'region': _region,
          if (_province != null) 'province': _province,
          if (_district != null) 'district': _district,
          'municipality_city': _municipalityCity,
          'barangay': _barangay,
          'street_address': streetAddress,
          // Security
          'password': _passCtrl.text,
          'password_confirmation': _confirmPassCtrl.text,
          'terms_accepted': true,
          // id_document intentionally omitted — API accepts null for mobile
        },
        includeAuth: false,
      );

      if (!mounted) return;

      final email = (res['data']?['email'] as String?) ??
          _emailCtrl.text.trim().toLowerCase();

      // Navigate to OTP verification screen
      Navigator.of(context).pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => VerifyEmailPage(email: email)),
        (route) => false,
      );
    } on ApiException catch (e) {
      if (!mounted) return;
      // Build a readable message from field-level errors if present
      String displayMsg = e.message;
      if (e.errors != null && e.errors!.isNotEmpty) {
        final fieldLines = e.errors!.entries
            .map((entry) => '• ${entry.value.first}')
            .join('\n');
        displayMsg = fieldLines;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(displayMsg,
              style: GoogleFonts.plusJakartaSans(color: Colors.white)),
          backgroundColor: Colors.red.shade700,
          behavior: SnackBarBehavior.floating,
          duration: const Duration(seconds: 6),
        ),
      );
    } catch (_) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Network error. Please try again.'),
          backgroundColor: Colors.red,
          behavior: SnackBarBehavior.floating,
        ),
      );
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kSurface,
      body: Stack(
        children: [
          // ── Purple blob ───────────────────────────────────────────────
          Positioned(
            top: -30,
            right: -50,
            child: Container(
              width: 220,
              height: 240,
              decoration: const BoxDecoration(
                color: _kPrimaryLight,
                shape: BoxShape.circle,
              ),
            ),
          ),

          SafeArea(
            child: Column(
              children: [
                // ── Step indicator ──────────────────────────────────────
                Padding(
                  padding: const EdgeInsets.fromLTRB(24, 16, 24, 0),
                  child: _StepIndicator(currentStep: _step, steps: _steps),
                ),

                // ── Step content ────────────────────────────────────────
                Expanded(
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.fromLTRB(24, 24, 24, 24),
                    child: _buildStep(),
                  ),
                ),

                // ── Bottom: Next button + already have account ──────────
                Container(
                  color: _kSurface,
                  padding: const EdgeInsets.fromLTRB(24, 12, 24, 8),
                  child: Column(
                    children: [
                      SizedBox(
                        width: double.infinity,
                        height: 52,
                        child: FilledButton(
                          onPressed: _loading ? null : _next,
                          style: FilledButton.styleFrom(
                            backgroundColor: _kPrimary,
                            shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12)),
                          ),
                          child: _loading
                              ? const SizedBox(
                                  width: 22,
                                  height: 22,
                                  child: CircularProgressIndicator(
                                      strokeWidth: 2, color: Colors.white),
                                )
                              : Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Text(
                                      _step < 3 ? 'Next' : 'Submit',
                                      style: GoogleFonts.plusJakartaSans(
                                          fontSize: 15,
                                          fontWeight: FontWeight.w700,
                                          color: Colors.white),
                                    ),
                                    if (_step < 3) ...[
                                      const SizedBox(width: 4),
                                      const Icon(Icons.chevron_right,
                                          color: Colors.white, size: 18),
                                    ],
                                  ],
                                ),
                        ),
                      ),
                      const SizedBox(height: 12),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text('Already have an account? ',
                              style: GoogleFonts.plusJakartaSans(
                                  fontSize: 13, color: _kSubtext)),
                          GestureDetector(
                            onTap: () => Navigator.pushReplacement(
                              context,
                              MaterialPageRoute(
                                  builder: (_) => const LoginPage()),
                            ),
                            child: Text('Log In',
                                style: GoogleFonts.plusJakartaSans(
                                    fontSize: 13,
                                    fontWeight: FontWeight.w700,
                                    color: _kAccent)),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ── Build current step content ────────────────────────────────────────────

  Widget _buildStep() {
    switch (_step) {
      case 0:
        return _buildDetails();
      case 1:
        return _buildAddress();
      case 2:
        return _buildVerification();
      case 3:
        return _buildSecurity();
      default:
        return const SizedBox();
    }
  }

  // ── Step 1: Personal Information ─────────────────────────────────────────

  Widget _buildDetails() {
    return Form(
      key: _detailsKey,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Personal Information',
              style: GoogleFonts.plusJakartaSans(
                  fontSize: 26,
                  fontWeight: FontWeight.w800,
                  color: _kText,
                  height: 1.2)),
          const SizedBox(height: 4),
          Text('Please enter your basic profile information',
              style:
                  GoogleFonts.plusJakartaSans(fontSize: 13, color: _kSubtext)),
          const SizedBox(height: 24),

          // Last name
          _MimooField(
            controller: _lastNameCtrl,
            label: 'LAST NAME',
            textInputAction: TextInputAction.next,
            textCapitalization: TextCapitalization.words,
            validator: (v) => (v == null || v.trim().isEmpty)
                ? 'Last name is required'
                : null,
          ),
          const SizedBox(height: 12),

          // First name + M.I. row
          Row(
            children: [
              Expanded(
                flex: 3,
                child: _MimooField(
                  controller: _firstNameCtrl,
                  label: 'FIRST NAME',
                  textInputAction: TextInputAction.next,
                  textCapitalization: TextCapitalization.words,
                  validator: (v) =>
                      (v == null || v.trim().isEmpty) ? 'Required' : null,
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                flex: 1,
                child: _MimooField(
                  controller: _miCtrl,
                  label: 'M.I.',
                  textInputAction: TextInputAction.next,
                  textCapitalization: TextCapitalization.characters,
                  maxLength: 1,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Sex radio — only 'male' and 'female' (lowercase), matching the API
          _FieldBox(
            label: 'SEX',
            child: Row(
              children: ['female', 'male'].map((option) {
                return Padding(
                  padding: const EdgeInsets.only(right: 16),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      SizedBox(
                        width: 24,
                        height: 24,
                        child: Radio<String>(
                          value: option,
                          groupValue: _sex,
                          activeColor: _kPrimary,
                          materialTapTargetSize:
                              MaterialTapTargetSize.shrinkWrap,
                          visualDensity: VisualDensity.compact,
                          onChanged: (v) {
                            if (v != null) setState(() => _sex = v);
                          },
                        ),
                      ),
                      const SizedBox(width: 4),
                      Text(
                          // Display capitalized; send lowercase to API
                          option[0].toUpperCase() + option.substring(1),
                          style: GoogleFonts.plusJakartaSans(
                              fontSize: 13, color: _kText)),
                    ],
                  ),
                );
              }).toList(),
            ),
          ),
          const SizedBox(height: 12),

          // Birthday + Age row
          Row(
            children: [
              // Birthday
              Expanded(
                flex: 3,
                child: GestureDetector(
                  onTap: _pickBirthday,
                  child: _FieldBox(
                    label: 'BIRTHDAY',
                    child: Row(
                      children: [
                        Expanded(
                          child: Text(
                            _birthday == null
                                ? 'DD/MM/YYYY'
                                : '${_birthday!.day.toString().padLeft(2, '0')}/'
                                    '${_birthday!.month.toString().padLeft(2, '0')}/'
                                    '${_birthday!.year}',
                            style: GoogleFonts.plusJakartaSans(
                              fontSize: 14,
                              color: _birthday == null ? _kSubtext : _kText,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ),
                        Icon(Icons.calendar_today_outlined,
                            size: 16, color: _kSubtext),
                      ],
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              // Age (auto)
              Expanded(
                flex: 2,
                child: _FieldBox(
                  label: 'AGE (AUTO)',
                  bgColor: const Color(0xFFF0EEF8),
                  child: Text(
                    _age != null ? '${_age!} yrs' : '—',
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 14,
                      fontWeight: FontWeight.w700,
                      color: _kPrimary,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // ── Step 2: Address ─────────────────────────────────────────────────────────

  Widget _buildAddress() {
    return Form(
      key: _addressKey,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Address',
              style: GoogleFonts.plusJakartaSans(
                  fontSize: 26, fontWeight: FontWeight.w800, color: _kText)),
          const SizedBox(height: 4),
          Text('Enter your delivery address',
              style:
                  GoogleFonts.plusJakartaSans(fontSize: 13, color: _kSubtext)),
          const SizedBox(height: 24),
          // PSGC-backed address selector.
          // The callback uses named params; _region maps to `region` (not
          // province — the old widget misnamed it).
          AddressDropdown(
            onChanged: ({
              required String region,
              String? province,
              String? district,
              required String municipalityCity,
              required String barangay,
            }) {
              setState(() {
                _region = region;
                _province = province;
                _district = district;
                _municipalityCity = municipalityCity;
                _barangay = barangay;
              });
            },
          ),
          const SizedBox(height: 4),
          _MimooField(
            controller: _streetCtrl,
            label: 'STREET',
            textInputAction: TextInputAction.next,
            textCapitalization: TextCapitalization.words,
          ),
          const SizedBox(height: 12),
          _MimooField(
            controller: _houseCtrl,
            label: 'HOUSE / UNIT NUMBER',
            textInputAction: TextInputAction.done,
          ),
        ],
      ),
    );
  }

  // ── Step 3: Verification ────────────────────────────────────────────────────

  Widget _buildVerification() {
    return Form(
      key: _verifyKey,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Verification',
              style: GoogleFonts.plusJakartaSans(
                  fontSize: 26, fontWeight: FontWeight.w800, color: _kText)),
          const SizedBox(height: 4),
          Text('Contact info & identity document',
              style:
                  GoogleFonts.plusJakartaSans(fontSize: 13, color: _kSubtext)),
          const SizedBox(height: 24),
          _MimooField(
            controller: _emailCtrl,
            label: 'EMAIL',
            keyboardType: TextInputType.emailAddress,
            textInputAction: TextInputAction.next,
            validator: (v) {
              if (v == null || v.trim().isEmpty) {
                return 'Email is required';
              }
              if (!RegExp(r'^[^@]+@[^@]+\.[^@]+').hasMatch(v)) {
                return 'Enter a valid email';
              }
              return null;
            },
          ),
          const SizedBox(height: 12),
          _MimooField(
            controller: _contactCtrl,
            label: 'CONTACT NUMBER',
            keyboardType: TextInputType.phone,
            textInputAction: TextInputAction.done,
            validator: (v) =>
                (v == null || v.trim().isEmpty) ? 'Required' : null,
          ),
          const SizedBox(height: 20),
          Text('Valid ID (optional now)',
              style: GoogleFonts.plusJakartaSans(
                  fontSize: 13, fontWeight: FontWeight.w700, color: _kText)),
          const SizedBox(height: 4),
          Text(
              'You can upload your government-issued ID later from your profile.',
              style:
                  GoogleFonts.plusJakartaSans(fontSize: 12, color: _kSubtext)),
          const SizedBox(height: 8),
          GestureDetector(
            onTap: () => setState(() => _uploadedId = 'id_document.jpg'),
            child: Container(
              width: double.infinity,
              height: 100,
              decoration: BoxDecoration(
                color: Colors.white,
                border: Border.all(
                    color: _uploadedId != null ? _kPrimary : _kBorder),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    _uploadedId != null
                        ? Icons.check_circle_outline
                        : Icons.upload_file_outlined,
                    color: _uploadedId != null ? _kPrimary : _kSubtext,
                    size: 28,
                  ),
                  const SizedBox(height: 6),
                  Text(
                    _uploadedId != null
                        ? 'ID noted — upload after registration'
                        : 'Tap to mark (upload later)',
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 13,
                      color: _uploadedId != null ? _kPrimary : _kSubtext,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ── Step 4: Security ────────────────────────────────────────────────────────

  Widget _buildSecurity() {
    return Form(
      key: _securityKey,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Security',
              style: GoogleFonts.plusJakartaSans(
                  fontSize: 26, fontWeight: FontWeight.w800, color: _kText)),
          const SizedBox(height: 4),
          Text('Create a strong password for your account',
              style:
                  GoogleFonts.plusJakartaSans(fontSize: 13, color: _kSubtext)),
          const SizedBox(height: 24),
          _MimooField(
            controller: _passCtrl,
            label: 'PASSWORD',
            obscureText: _obscurePass,
            textInputAction: TextInputAction.next,
            suffixIcon: IconButton(
              icon: Icon(
                _obscurePass
                    ? Icons.visibility_outlined
                    : Icons.visibility_off_outlined,
                color: _kSubtext,
                size: 18,
              ),
              onPressed: () => setState(() => _obscurePass = !_obscurePass),
            ),
            validator: (v) {
              if (v == null || v.isEmpty) {
                return 'Password is required';
              }
              if (v.length < 8) {
                return 'Minimum 8 characters';
              }
              return null;
            },
          ),
          const SizedBox(height: 12),
          _MimooField(
            controller: _confirmPassCtrl,
            label: 'CONFIRM PASSWORD',
            obscureText: _obscureConfirm,
            textInputAction: TextInputAction.done,
            suffixIcon: IconButton(
              icon: Icon(
                _obscureConfirm
                    ? Icons.visibility_outlined
                    : Icons.visibility_off_outlined,
                color: _kSubtext,
                size: 18,
              ),
              onPressed: () =>
                  setState(() => _obscureConfirm = !_obscureConfirm),
            ),
            validator: (v) {
              if (v == null || v.isEmpty) {
                return 'Please confirm password';
              }
              if (v != _passCtrl.text) {
                return 'Passwords do not match';
              }
              return null;
            },
          ),
        ],
      ),
    );
  }
}

// ── Step Indicator ────────────────────────────────────────────────────────────

class _StepIndicator extends StatelessWidget {
  const _StepIndicator({required this.currentStep, required this.steps});
  final int currentStep;
  final List<String> steps;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Progress line
        Row(
          children: List.generate(steps.length, (i) {
            final isActive = i <= currentStep;
            return Expanded(
              child: Container(
                height: 3,
                margin: EdgeInsets.only(right: i < steps.length - 1 ? 4 : 0),
                decoration: BoxDecoration(
                  color: isActive ? _kPrimary : _kBorder,
                  borderRadius: BorderRadius.circular(9999),
                ),
              ),
            );
          }),
        ),
        const SizedBox(height: 8),
        // Labels
        Row(
          children: List.generate(steps.length, (i) {
            final isActive = i == currentStep;
            return Expanded(
              child: Text(
                '${i + 1} ${steps[i]}',
                style: GoogleFonts.plusJakartaSans(
                  fontSize: 11,
                  fontWeight: isActive ? FontWeight.w700 : FontWeight.w400,
                  color: isActive ? _kPrimary : _kSubtext,
                ),
                overflow: TextOverflow.ellipsis,
              ),
            );
          }),
        ),
      ],
    );
  }
}

// ── Field Box (label + content, not a TextFormField) ─────────────────────────

class _FieldBox extends StatelessWidget {
  const _FieldBox({
    required this.label,
    required this.child,
    this.bgColor = Colors.white,
  });
  final String label;
  final Widget child;
  final Color bgColor;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(14, 8, 14, 10),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: _kBorder),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label,
              style: GoogleFonts.plusJakartaSans(
                  fontSize: 10,
                  fontWeight: FontWeight.w700,
                  color: _kSubtext,
                  letterSpacing: 0.5)),
          const SizedBox(height: 4),
          child,
        ],
      ),
    );
  }
}

// ── Reusable text field ───────────────────────────────────────────────────────

class _MimooField extends StatelessWidget {
  const _MimooField({
    required this.controller,
    required this.label,
    this.keyboardType,
    this.textInputAction,
    this.textCapitalization = TextCapitalization.none,
    this.obscureText = false,
    this.suffixIcon,
    this.validator,
    this.maxLength,
  });

  final TextEditingController controller;
  final String label;
  final TextInputType? keyboardType;
  final TextInputAction? textInputAction;
  final TextCapitalization textCapitalization;
  final bool obscureText;
  final Widget? suffixIcon;
  final String? Function(String?)? validator;
  final int? maxLength;

  @override
  Widget build(BuildContext context) {
    return TextFormField(
      controller: controller,
      keyboardType: keyboardType,
      textInputAction: textInputAction,
      textCapitalization: textCapitalization,
      obscureText: obscureText,
      validator: validator,
      maxLength: maxLength,
      style: GoogleFonts.plusJakartaSans(
          fontSize: 14, color: _kText, fontWeight: FontWeight.w500),
      decoration: InputDecoration(
        labelText: label,
        counterText: '',
        labelStyle: GoogleFonts.plusJakartaSans(
            fontSize: 10,
            fontWeight: FontWeight.w700,
            color: _kSubtext,
            letterSpacing: 0.5),
        floatingLabelBehavior: FloatingLabelBehavior.always,
        suffixIcon: suffixIcon,
        filled: true,
        fillColor: Colors.white,
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: _kBorder),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: _kPrimary, width: 1.5),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: Colors.red),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: Colors.red, width: 1.5),
        ),
        disabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: _kBorder),
        ),
      ),
    );
  }
}
