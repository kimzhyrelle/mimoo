/**
 * Shared validation utilities for client-side validation.
 * These rules mirror the server-side validation in Laravel.
 */

export interface ValidationResult {
    valid: boolean;
    error?: string;
}

/**
 * Validate name fields (first name, last name)
 * Rules: 2-50 chars, letters (including ñ), spaces, hyphen, apostrophe, period only
 */
export function validateName(value: string, fieldLabel: string = 'Name'): ValidationResult {
    const trimmed = value.trim();
    
    if (!trimmed) {
        return { valid: false, error: `${fieldLabel} is required.` };
    }
    
    if (trimmed.length < 2) {
        return { valid: false, error: `${fieldLabel} must be at least 2 characters.` };
    }
    
    if (trimmed.length > 50) {
        return { valid: false, error: `${fieldLabel} must not exceed 50 characters.` };
    }
    
    // Allow letters (including ñ/Ñ), spaces, hyphen, apostrophe, period
    const namePattern = /^[a-zA-ZñÑ\s\-'.]+$/;
    if (!namePattern.test(trimmed)) {
        return { valid: false, error: 'Only letters are allowed.' };
    }
    
    return { valid: true };
}

/**
 * Validate password with specific requirements
 * Rules: min 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special character
 */
export function validatePassword(value: string): ValidationResult {
    if (value.length < 8) {
        return { valid: false, error: 'Password must be at least 8 characters.' };
    }
    
    if (!/[A-Z]/.test(value)) {
        return { valid: false, error: 'Password must contain at least one uppercase letter.' };
    }
    
    if (!/[a-z]/.test(value)) {
        return { valid: false, error: 'Password must contain at least one lowercase letter.' };
    }
    
    if (!/[0-9]/.test(value)) {
        return { valid: false, error: 'Password must contain at least one number.' };
    }
    
    if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(value)) {
        return { valid: false, error: 'Password must contain at least one special character.' };
    }
    
    return { valid: true };
}

/**
 * Normalize mobile number to 09XXXXXXXXX format
 * Accepts: 09XXXXXXXXX or +639XXXXXXXXX
 */
export function normalizeMobile(value: string): string {
    const cleaned = value.replace(/\s/g, '');
    
    // If starts with +639, convert to 09
    if (cleaned.startsWith('+639')) {
        return '0' + cleaned.substring(3);
    }
    
    // If starts with 639, convert to 09
    if (cleaned.startsWith('639')) {
        return '0' + cleaned.substring(2);
    }
    
    return cleaned;
}

/**
 * Validate mobile number
 * Accepts: 09XXXXXXXXX or +639XXXXXXXXX (11 or 13 digits)
 */
export function validateMobile(value: string): ValidationResult {
    const normalized = normalizeMobile(value);
    
    if (!normalized) {
        return { valid: false, error: 'Mobile number is required.' };
    }
    
    // Must be exactly 11 digits starting with 09
    const mobilePattern = /^09\d{9}$/;
    if (!mobilePattern.test(normalized)) {
        return { valid: false, error: 'Enter a valid PH mobile number (09XXXXXXXXX or +639XXXXXXXXX).' };
    }
    
    return { valid: true };
}

/**
 * Validate and trim email
 */
export function validateAndTrimEmail(value: string): { value: string; valid: boolean; error?: string } {
    const trimmed = value.trim();
    
    if (!trimmed) {
        return { value: trimmed, valid: false, error: 'Email address is required.' };
    }
    
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(trimmed)) {
        return { value: trimmed, valid: false, error: 'Enter a valid email address.' };
    }
    
    return { value: trimmed, valid: true };
}

/**
 * Calculate age from birthday
 */
export function calculateAge(birthday: string): number | null {
    if (!birthday) return null;
    
    const dob = new Date(birthday + 'T00:00:00');
    const today = new Date();
    
    if (isNaN(dob.getTime()) || dob > today) return null;
    
    let age = today.getFullYear() - dob.getFullYear();
    const monthDiff = today.getMonth() - dob.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
        age--;
    }
    
    return age;
}

/**
 * Validate age (must be 18+)
 */
export function validateAge(birthday: string): ValidationResult {
    if (!birthday) {
        return { valid: false, error: 'Birthday is required.' };
    }
    
    const age = calculateAge(birthday);
    
    if (age === null) {
        return { valid: false, error: 'Enter a valid past date of birth.' };
    }
    
    if (age < 18) {
        return { valid: false, error: 'You must be at least 18 years old.' };
    }
    
    return { valid: true };
}

/**
 * Validate OTP code (6 digits)
 */
export function validateOTP(code: string): ValidationResult {
    const trimmed = code.trim();
    
    if (!trimmed) {
        return { valid: false, error: 'Verification code is required.' };
    }
    
    if (!/^\d{6}$/.test(trimmed)) {
        return { valid: false, error: 'Verification code must be exactly 6 digits.' };
    }
    
    return { valid: true };
}
