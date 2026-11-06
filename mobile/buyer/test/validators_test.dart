import 'package:flutter_test/flutter_test.dart';
import 'package:mobile_buyer/utils/validators.dart';

void main() {
  // ── Name validation ──────────────────────────────────────────────────────
  group('validateName', () {
    test('rejects empty name', () {
      expect(validateName(''), 'Name is required');
      expect(validateName('   '), 'Name is required');
      expect(validateName(null), 'Name is required');
    });

    test('rejects 1-character name (too short)', () {
      expect(validateName('A')?.contains('at least 2'), isTrue);
    });

    test('rejects 51-character name (too long)', () {
      final long = 'A' * 51;
      expect(validateName(long)?.contains('must not exceed 50'), isTrue);
    });

    test('accepts 2-character name', () {
      expect(validateName('Jo'), isNull);
    });

    test('accepts 50-character name', () {
      final fifty = 'A' * 50;
      expect(validateName(fifty), isNull);
    });

    test('rejects names with numbers', () {
      expect(validateName('John123')?.contains('letters only'), isTrue);
      expect(validateName('Maria5')?.contains('letters only'), isTrue);
    });

    test('rejects names with symbols (except allowed punctuation)', () {
      expect(validateName('John\$')?.contains('letters only'), isTrue);
      expect(validateName('Jane#')?.contains('letters only'), isTrue);
      expect(validateName('Name@')?.contains('letters only'), isTrue);
    });

    test('accepts names with ñ and accented characters', () {
      expect(validateName('Niño'), isNull);
      expect(validateName('Peñaflor'), isNull);
    });

    test('accepts names with hyphens', () {
      expect(validateName('Peñaflor-O\'Hara'), isNull);
      expect(validateName('Anne-Marie'), isNull);
    });

    test('accepts names with apostrophes', () {
      expect(validateName('O\'Hara'), isNull);
      expect(validateName('O\'Brien'), isNull);
    });

    test('accepts names with spaces (multi-word names)', () {
      expect(validateName('Niño Angelo'), isNull);
      expect(validateName('Maria Clara'), isNull);
    });

    test('trims leading/trailing whitespace before checking', () {
      expect(validateName('  Niño  '), isNull);
    });

    test('uses custom field name in error messages', () {
      expect(
          validateName('', fieldName: 'First name'), 'First name is required');
      expect(validateName('', fieldName: 'Last name'), 'Last name is required');
    });
  });

  group('validateFirstName', () {
    test('uses "First name" in messages', () {
      expect(validateFirstName(''), 'First name is required');
      expect(validateFirstName('X')?.contains('First name'), isTrue);
    });
  });

  group('validateLastName', () {
    test('uses "Last name" in messages', () {
      expect(validateLastName(''), 'Last name is required');
      expect(validateLastName('X')?.contains('Last name'), isTrue);
    });
  });

  // ── Email validation ──────────────────────────────────────────────────────
  group('validateEmail', () {
    test('rejects empty email', () {
      expect(validateEmail(''), 'Email is required');
      expect(validateEmail(null), 'Email is required');
    });

    test('rejects email without @', () {
      expect(validateEmail('testgmail.com')?.contains('@'), isTrue);
    });

    test('rejects email without domain', () {
      expect(validateEmail('test@')?.contains('domain'), isTrue);
    });

    test('rejects email without name/local part', () {
      expect(validateEmail('@gmail.com')?.contains('valid'), isTrue);
    });

    test('rejects email without .com / TLD', () {
      expect(validateEmail('test@gmail')?.contains('domain'), isTrue);
    });

    test('rejects email with space inside', () {
      expect(validateEmail('te st@gmail.com')?.contains('space'), isTrue);
    });

    test('accepts valid email', () {
      expect(validateEmail('test@gmail.com'), isNull);
      expect(validateEmail('user.name@example.org'), isNull);
    });

    test('trims leading and trailing spaces', () {
      expect(validateEmail('  test@gmail.com  '), isNull);
    });
  });

  group('normalizeEmail', () {
    test('lowercases and trims', () {
      expect(normalizeEmail('  Test@Gmail.COM  '), 'test@gmail.com');
      expect(normalizeEmail('USER@EXAMPLE.ORG'), 'user@example.org');
    });
  });

  // ── Mobile validation ──────────────────────────────────────────────────────
  group('validateMobile', () {
    test('rejects empty', () {
      expect(validateMobile(''), 'Contact number is required');
      expect(validateMobile(null), 'Contact number is required');
    });

    test('rejects letters', () {
      expect(validateMobile('09abc123456')?.contains('digits only'), isTrue);
    });

    test('rejects symbols', () {
      expect(validateMobile('09!23456789')?.contains('digits only'), isTrue);
    });

    test('rejects not starting with 09', () {
      final result = validateMobile('08123456789');
      expect(result?.contains('09'), isTrue);
    });

    test('rejects landline format (not mobile)', () {
      // 8-digit landline
      final result = validateMobile('81234567');
      expect(result != null, isTrue);
    });

    test('rejects 10 digits (too short)', () {
      final result = validateMobile('0912345678');
      expect(result?.contains('11 digits'), isTrue);
    });

    test('rejects 12 digits (too long)', () {
      final result = validateMobile('091234567890');
      expect(result?.contains('11 digits'), isTrue);
    });

    test('accepts exactly 11 digits starting with 09', () {
      expect(validateMobile('09123456789'), isNull);
    });

    test('accepts +63 format equivalent', () {
      expect(validateMobile('+639171234567'), isNull);
    });

    test('accepts +63 with spaces/dashes', () {
      expect(validateMobile('+63 917 123 4567'), isNull);
      expect(validateMobile('+63-917-123-4567'), isNull);
    });
  });

  group('normalizeMobile', () {
    test('converts +63 format to 09XXXXXXXXX', () {
      expect(normalizeMobile('+639171234567'), '09171234567');
    });

    test('converts 63 prefix to 09', () {
      expect(normalizeMobile('639171234567'), '09171234567');
    });

    test('keeps 09 format as-is', () {
      expect(normalizeMobile('09123456789'), '09123456789');
    });

    test('strips spaces and dashes', () {
      expect(normalizeMobile('0917 123 4567'), '09171234567');
      expect(normalizeMobile('0917-123-4567'), '09171234567');
    });

    test('treats 09 and +63 as the same number for duplicate checks', () {
      expect(normalizeMobile('09171234567'),
          equals(normalizeMobile('+639171234567')));
      expect(normalizeMobile('09171234567'),
          equals(normalizeMobile('639171234567')));
    });
  });

  // ── Password validation ─────────────────────────────────────────────────────
  group('validatePassword', () {
    test('rejects empty', () {
      expect(validatePassword(''), 'Password is required');
      expect(validatePassword(null), 'Password is required');
    });

    test('rejects 7 characters (too short)', () {
      expect(validatePassword('Aa1!bcd')?.contains('at least 8'), isTrue);
    });

    test('accepts 8 characters with all required types', () {
      expect(validatePassword('Aa1!bcde'), isNull);
    });

    test('rejects missing uppercase', () {
      expect(validatePassword('aa1!bcde')?.contains('uppercase'), isTrue);
    });

    test('rejects missing lowercase', () {
      expect(validatePassword('AA1!BCDE')?.contains('lowercase'), isTrue);
    });

    test('rejects missing number', () {
      expect(validatePassword('Aa!bcdef')?.contains('number'), isTrue);
    });

    test('rejects missing special character', () {
      expect(validatePassword('Aa1bcdef')?.contains('special'), isTrue);
    });

    test('accepts various special characters', () {
      expect(validatePassword('Aa1!bcde'), isNull);
      expect(validatePassword('Aa1@bcde'), isNull);
      expect(validatePassword('Aa1#bcde'), isNull);
      expect(validatePassword('Aa1\$bcde'), isNull);
      expect(validatePassword('Aa1%bcde'), isNull);
    });
  });

  group('validateConfirmPassword', () {
    test('rejects empty', () {
      expect(
          validateConfirmPassword('', 'Aa1!bcde'), 'Please confirm password');
    });

    test('rejects mismatch', () {
      expect(validateConfirmPassword('Aa1!bcdf', 'Aa1!bcde'),
          'Passwords do not match');
    });

    test('accepts match', () {
      expect(validateConfirmPassword('Aa1!bcde', 'Aa1!bcde'), isNull);
    });
  });

  // ── Birthday / age validation ────────────────────────────────────────────────
  group('validateBirthday', () {
    // Use a fixed "now" so tests are deterministic.
    final now = DateTime(2026, 10, 27, 12, 0);

    test('rejects null birthday', () {
      expect(validateBirthday(null, now: now), 'Please select your birthday');
    });

    test('rejects future date of birth', () {
      final future = DateTime(2027, 1, 1);
      expect(validateBirthday(future, now: now)?.contains('future'), isTrue);
    });

    test('rejects 17-year-old', () {
      // Born 2009-10-27 → turns 17 today, 18 tomorrow
      final seventeen = DateTime(2009, 10, 27);
      expect(validateBirthday(seventeen, now: now)?.contains('18'), isTrue);
    });

    test('accepts exactly 18 years old', () {
      final eighteen = DateTime(2008, 10, 27); // turns 18 today
      expect(validateBirthday(eighteen, now: now), isNull);
    });

    test('accepts 18 years and 1 day', () {
      // Born 2008-10-26 → already 18 years and 1 day old
      final eighteenPlus = DateTime(2008, 10, 26);
      expect(validateBirthday(eighteenPlus, now: now), isNull);
    });

    test('accepts older ages', () {
      expect(validateBirthday(DateTime(1990, 5, 15), now: now), isNull);
    });
  });

  group('computeAge', () {
    final now = DateTime(2026, 10, 27);

    test('computes exact age on birthday', () {
      expect(computeAge(DateTime(2008, 10, 27), now: now), 18);
    });

    test('computes age when birthday has passed this year', () {
      expect(computeAge(DateTime(2000, 1, 1), now: now), 26);
    });

    test('computes age when birthday has not yet occurred', () {
      // Born Dec 25, 2000 → turns 26 on Dec 25, 2026; on Oct 27 still 25
      expect(computeAge(DateTime(2000, 12, 25), now: now), 25);
    });
  });

  // ── OTP validation ─────────────────────────────────────────────────────────
  group('validateOtp', () {
    test('rejects blank', () {
      expect(validateOtp(''), 'OTP is required');
      expect(validateOtp('   '), 'OTP is required');
      expect(validateOtp(null), 'OTP is required');
    });

    test('rejects 5 digits (too short)', () {
      expect(validateOtp('12345')?.contains('6 digits'), isTrue);
    });

    test('rejects 7 digits (too long)', () {
      expect(validateOtp('1234567')?.contains('6 digits'), isTrue);
    });

    test('rejects letters', () {
      expect(validateOtp('abcdef')?.contains('6 digits'), isTrue);
    });

    test('rejects mixed letters and digits', () {
      expect(validateOtp('12ab56')?.contains('6 digits'), isTrue);
    });

    test('accepts exactly 6 digits', () {
      expect(validateOtp('123456'), isNull);
      expect(validateOtp('000000'), isNull);
      expect(validateOtp('999999'), isNull);
    });

    test('trims whitespace around OTP', () {
      expect(validateOtp('  123456  '), isNull);
    });
  });

  group('isOtpValid', () {
    final now = DateTime(2026, 10, 27, 12, 0, 0);

    test('valid within 5 minutes', () {
      final issued = now.subtract(Duration(minutes: 3));
      expect(isOtpValid(issued, now: now), isTrue);
    });

    test('valid at exactly 5 minutes', () {
      final issued = now.subtract(Duration(minutes: 5));
      expect(isOtpValid(issued, now: now), isTrue);
    });

    test('expired at 5 minutes and 1 second', () {
      final issued = now.subtract(Duration(minutes: 5, seconds: 1));
      expect(isOtpValid(issued, now: now), isFalse);
    });

    test('expired after longer period', () {
      final issued = now.subtract(Duration(minutes: 10));
      expect(isOtpValid(issued, now: now), isFalse);
    });
  });

  // ── Terms acceptance ─────────────────────────────────────────────────────────
  group('validateTermsAccepted', () {
    test('rejects when not accepted', () {
      expect(validateTermsAccepted(false)?.contains('Terms'), isTrue);
    });

    test('accepts when accepted', () {
      expect(validateTermsAccepted(true), isNull);
    });
  });
}
