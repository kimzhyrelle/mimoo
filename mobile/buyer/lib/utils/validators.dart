/// Centralized validation rules for the mimoo registration form.
///
/// All functions return `null` when the value is valid, or an error message
/// string when invalid — the convention expected by Flutter's `FormField`
/// validators.
library;

/// ── Name validation ────────────────────────────────────────────────────────
///
/// Allows letters (including accented characters like ñ, É, ü), spaces,
/// hyphens, and apostrophes. Rejects digits and other symbols.
/// Length must be between 2 and 50 characters (after trimming).

final _nameRegex = RegExp(r"^[\p{L}][\p{L}\s'.\-]*$", unicode: true);

String? validateName(String? value, {String fieldName = 'Name'}) {
  final v = value?.trim() ?? '';
  if (v.isEmpty) return '$fieldName is required';
  if (v.length < 2) return '$fieldName must be at least 2 characters';
  if (v.length > 50) return '$fieldName must not exceed 50 characters';
  if (!_nameRegex.hasMatch(v)) {
    return '$fieldName must contain letters only';
  }
  return null;
}

// Convenience wrappers for individual name fields.
String? validateFirstName(String? v) =>
    validateName(v, fieldName: 'First name');
String? validateLastName(String? v) => validateName(v, fieldName: 'Last name');

/// ── Email validation ──────────────────────────────────────────────────────
///
/// Trims leading/trailing spaces before validating.
/// Accepts standard email format: local@domain.tld

final _emailRegex = RegExp(r'^[^\s@]+@[^\s@]+\.[^\s@]{2,}$');

String? validateEmail(String? value) {
  final v = value?.trim() ?? '';
  if (v.isEmpty) return 'Email is required';
  if (v.contains(' ')) return 'Email must not contain spaces';
  if (!v.contains('@')) return 'Email must contain @';
  if (!v.contains('.')) return 'Email must contain a domain (e.g. .com)';
  if (!_emailRegex.hasMatch(v)) return 'Enter a valid email';
  return null;
}

/// Normalize an email for storage / duplicate checks: trim + lowercase.
String normalizeEmail(String email) => email.trim().toLowerCase();

/// ── Mobile number validation ──────────────────────────────────────────────
///
/// Accepts the Philippine mobile format: 09XXXXXXXXX (11 digits, starts with 09).
/// The +63 equivalent (e.g. +639171234567 or 09171234567) is normalized for
/// duplicate checks via [normalizeMobile].

String? validateMobile(String? value) {
  final v = value?.trim() ?? '';
  if (v.isEmpty) return 'Contact number is required';
  // Strip common separators that users might type.
  final digits = v.replaceAll(RegExp(r'[\s\-()]'), '');

  if (digits.startsWith('+63')) {
    final rest = digits.substring(3);
    if (!RegExp(r'^9\d{9}$').hasMatch(rest)) {
      return 'Enter a valid mobile number (09XXXXXXXXX)';
    }
    return null;
  }
  if (!RegExp(r'^\d+$').hasMatch(digits)) {
    return 'Mobile number must contain digits only';
  }
  if (!digits.startsWith('09')) {
    return 'Mobile number must start with 09';
  }
  if (digits.length < 11) return 'Mobile number must be 11 digits';
  if (digits.length > 11) return 'Mobile number must be 11 digits';
  if (digits.length == 10) return 'Mobile number must be 11 digits';
  return null;
}

/// Normalize a Philippine mobile number to 09XXXXXXXXX format for
/// duplicate detection. Handles +63, 63, and 09 prefixes.
String normalizeMobile(String number) {
  var n = number.trim().replaceAll(RegExp(r'[\s\-()]'), '');
  if (n.startsWith('+63')) {
    n = '0${n.substring(3)}';
  } else if (n.startsWith('63')) {
    n = '0${n.substring(2)}';
  }
  return n;
}

/// ── Password validation ────────────────────────────────────────────────────
///
/// Minimum 8 characters, must contain at least:
///   - one uppercase letter
///   - one lowercase letter
///   - one digit
///   - one special character

String? validatePassword(String? value) {
  final v = value ?? '';
  if (v.isEmpty) return 'Password is required';
  if (v.length < 8) return 'Password must be at least 8 characters';
  if (!RegExp(r'[A-Z]').hasMatch(v)) {
    return 'Password must contain an uppercase letter';
  }
  if (!RegExp(r'[a-z]').hasMatch(v)) {
    return 'Password must contain a lowercase letter';
  }
  if (!RegExp(r'\d').hasMatch(v)) {
    return 'Password must contain a number';
  }
  if (!RegExp(r'[^a-zA-Z\d]').hasMatch(v)) {
    return 'Password must contain a special character';
  }
  return null;
}

String? validateConfirmPassword(String? value, String originalPassword) {
  final v = value ?? '';
  if (v.isEmpty) return 'Please confirm password';
  if (v != originalPassword) return 'Passwords do not match';
  return null;
}

/// ── Date of birth / age validation ──────────────────────────────────────────
///
/// Rejects future dates and ages below 18.

String? validateBirthday(DateTime? birthday, {DateTime? now}) {
  final reference = now ?? DateTime.now();
  if (birthday == null) return 'Please select your birthday';
  if (birthday.isAfter(reference)) {
    return 'Date of birth cannot be in the future';
  }
  final age = reference.year -
      birthday.year -
      ((reference.month < birthday.month ||
              (reference.month == birthday.month &&
                  reference.day < birthday.day))
          ? 1
          : 0);
  if (age < 18) {
    return 'You must be at least 18 years old to register';
  }
  return null;
}

/// Compute age from a birthday, relative to [now] (defaults to today).
int computeAge(DateTime birthday, {DateTime? now}) {
  final reference = now ?? DateTime.now();
  return reference.year -
      birthday.year -
      ((reference.month < birthday.month ||
              (reference.month == birthday.month &&
                  reference.day < birthday.day))
          ? 1
          : 0);
}

/// ── OTP validation ─────────────────────────────────────────────────────────
///
/// OTP must be exactly 6 digits.

String? validateOtp(String? value) {
  final v = value?.trim() ?? '';
  if (v.isEmpty) return 'OTP is required';
  if (!RegExp(r'^\d{6}$').hasMatch(v)) {
    return 'OTP must be exactly 6 digits';
  }
  return null;
}

/// Check whether an OTP is still valid given its issue time and a 5-minute
/// window. Returns `true` if valid, `false` if expired.
bool isOtpValid(DateTime issuedAt,
    {DateTime? now, Duration validity = const Duration(minutes: 5)}) {
  final reference = now ?? DateTime.now();
  return reference.difference(issuedAt) <= validity;
}

/// ── Terms acceptance ────────────────────────────────────────────────────────

String? validateTermsAccepted(bool accepted) {
  if (!accepted) return 'You must accept the Terms to continue';
  return null;
}
