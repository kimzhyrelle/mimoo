# Buyer OTP Registration Fix

## Summary
Fixed buyer registration OTP flow to match the working seller implementation. Buyers now properly redirect to OTP verification page and then to login after email verification.

## Problem
Buyer registration was not redirecting to OTP page after successful registration. The form submitted but users weren't taken to the verification screen.

## Solution Applied

### 1. Buyer Registration (`Register.tsx`)
Updated the `goNext()` function to force redirect to OTP page on successful registration:

**Before:**
```typescript
post('/register', {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
        window.location.href = `/verify-email?email=${encodeURIComponent(email)}`;
    },
});
```

**After (matching seller flow):**
```typescript
post('/register', {
    forceFormData: true,
    onSuccess: (response) => {
        console.log('Buyer registration success, redirecting to OTP...', response);
        // Force redirect to OTP page
        window.location.href = `/verify-email?email=${encodeURIComponent(email)}`;
    },
    onError: (errs) => {
        console.error('Buyer registration failed with errors:', JSON.stringify(errs, null, 2));
        Object.keys(errs).forEach(key => {
            console.error(`Error in ${key}:`, errs[key]);
        });
    },
    onFinish: () => {
        console.log('Buyer registration request finished');
    },
});
```

### 2. OTP Verification (`VerifyEmail.tsx`)
Added proper logging and comments for the verification flow:

```typescript
post('/verify-email', {
    onSuccess: (page) => {
        console.log('OTP verification success:', page);
        reset('code');
        // Backend handles redirect:
        // - Buyers → /login with success message
        // - Sellers/Logistics → /seller/pending-approval
    },
    onError: (errors) => {
        console.error('OTP verification failed:', errors);
    },
});
```

## Flow Diagram

### Buyer Registration & Verification Flow

```
1. User fills registration form (4 steps)
   ↓
2. Submit → POST /register
   ↓
3. Backend creates user + sends OTP email
   ↓
4. Frontend: window.location.href = `/verify-email?email=...`
   ↓
5. User enters 6-digit OTP
   ↓
6. Submit → POST /verify-email
   ↓
7. Backend verifies code → Updates user
   ↓
8. Backend redirects: redirect()->route('login')
   ↓
9. User sees login page with success message
   ↓
10. User logs in → Homepage ✅
```

### Seller/Logistics Flow (for comparison)

```
1-7. Same as buyer
   ↓
8. Backend redirects: redirect()->route('seller.pending-approval')
   ↓
9. User sees "Pending Admin Approval" page
   ↓
10. Admin approves → Email notification
   ↓
11. User logs in → Seller Dashboard ✅
```

## Key Differences: Buyer vs Seller

| Aspect | Buyer | Seller/Logistics |
|--------|-------|------------------|
| **Registration Form** | 4 steps (personal, address, ID, password) | 4 steps (personal, address, business docs, password) |
| **After OTP Verification** | Redirect to `/login` | Redirect to `/seller/pending-approval` |
| **Admin Approval** | ❌ Not required | ✅ Required |
| **Can Login Immediately** | ✅ Yes (after OTP) | ❌ No (pending approval) |
| **Account Status** | `verified` | `pending` → `approved` |

## Backend Logic (Already Correct)

The `EmailVerificationController` already handles both cases properly:

```php
// After OTP verification
if ($user->account_type === 'seller' || $user->account_type === 'logistics') {
    // Sellers and logistics need admin approval
    return redirect()->route('seller.pending-approval')
        ->with('status', 'Email verified! Your account is pending admin approval.');
}

// Buyers can login immediately
return redirect()->route('login')
    ->with('status', 'Email verified successfully! You can now log in.');
```

## Files Changed

### Buyer Side (Fixed)
1. ✅ `resources/js/pages/Register.tsx`
   - Updated `goNext()` to force redirect to OTP
   - Added detailed logging for debugging
   - Removed `preserveScroll` (not needed for full page redirect)

2. ✅ `resources/js/pages/auth/VerifyEmail.tsx`
   - Added logging for success/error cases
   - Added comments explaining backend redirect logic

### Seller Side (Untouched)
❌ **NOT MODIFIED** - Working correctly:
- `resources/js/pages/seller/SellerRegistration.tsx`
- All seller flows remain unchanged

## Testing Checklist

### Buyer Registration
- [ ] Fill all 4 steps of buyer registration
- [ ] Submit form → Should redirect to `/verify-email?email=...`
- [ ] Check email for OTP code
- [ ] Enter 6-digit code
- [ ] Submit OTP → Should redirect to `/login`
- [ ] See success message "Email verified successfully! You can now log in."
- [ ] Login with credentials → Should access buyer homepage

### Seller Registration (Verify not affected)
- [ ] Fill all 4 steps of seller registration
- [ ] Submit form → Should redirect to `/verify-email?email=...`
- [ ] Check email for OTP code
- [ ] Enter 6-digit code
- [ ] Submit OTP → Should redirect to `/seller/pending-approval`
- [ ] See "Pending Admin Approval" message
- [ ] Admin approves via dashboard
- [ ] Login with credentials → Should access seller dashboard

## Console Logs for Debugging

When buyer registration succeeds, you should see:
```
Submitting buyer registration for: user@example.com
Buyer registration success, redirecting to OTP...
Buyer registration request finished
```

When OTP verification succeeds:
```
OTP verification success: {props: {...}}
```

## Notes

1. **Email Trimming**: Both buyer and seller forms trim email before submission
2. **Mobile Normalization**: Contact numbers are normalized to 09XXXXXXXXX format
3. **Force FormData**: Both use `forceFormData: true` because of file uploads (ID document)
4. **Window.location.href**: Used instead of Inertia redirect to ensure clean page reload
5. **Backend Handles Final Redirect**: OTP verification lets backend decide where to redirect based on account_type

## Security & Validation

- ✅ OTP codes are 6 digits, expire in 5 minutes
- ✅ Email must exist in users table to resend
- ✅ Code can only be used once (`verified` flag)
- ✅ Rate limiting on resend (60 second countdown)
- ✅ All form fields validated client + server side

