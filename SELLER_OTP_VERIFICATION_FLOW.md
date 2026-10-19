# Seller Registration - OTP Verification Before Admin Approval

## Summary
Updated the seller registration flow to require email OTP verification BEFORE the seller application appears in the admin approval queue. This ensures only verified sellers are reviewed by administrators.

## Flow Overview

### Before (Old Flow):
1. Seller fills registration form
2. ✅ Submit → **Immediately appears in admin panel**
3. Admin approves/disapproves
4. Seller can login

### After (New Flow):
1. Seller fills registration form  
2. ✅ Submit → **Redirected to OTP verification page**
3. Seller receives 6-digit OTP via email
4. Seller enters OTP code
5. ✅ OTP Verified → **Now appears in admin panel**
6. Admin approves/disapproves
7. Seller can login

## Changes Made

### 1. Admin Controller (`AdminController.php`)
Updated `sellerApproval()` method to filter by `verification_status`:

```php
->where('account_type', 'seller')
->where('verification_status', 'verified') // ← NEW: Only show verified sellers
->whereIn('status', ['pending', 'approved', 'disapproved'])
```

**Stats also filtered by verification status:**
- Total sellers: Only counts verified sellers
- Pending: Only counts verified + pending sellers
- Approved: Only counts verified + approved sellers
- Disapproved: Only counts verified + disapproved sellers

### 2. Frontend Updates (`SellerRegistration.tsx`)
- **Step 4 info message** updated to mention OTP verification:
  > "After submitting, you'll receive an **OTP verification code** via email. Once verified, an administrator will review your business details and ID."

- **Success modal** disabled (system automatically redirects to OTP page)

## OTP Verification System (Already Exists)

### Email Verification Controller
- `showVerification()` - Shows OTP input page
- `verifyOtp()` - Validates 6-digit OTP code
- `resendOtp()` - Resends OTP if expired

### OTP Features:
- ✅ 6-digit numeric code
- ✅ 5-minute expiration
- ✅ Email notification with code
- ✅ Resend functionality
- ✅ Tracks verification status in `email_verifications` table

### Routes:
```php
Route::get('/verify-email', [EmailVerificationController::class, 'showVerification'])->name('otp.show');
Route::post('/verify-email', [EmailVerificationController::class, 'verifyOtp'])->name('otp.verify');
Route::post('/verify-email/resend', [EmailVerificationController::class, 'resendOtp'])->name('otp.resend');
```

## Database Fields

### `users` table:
- `verification_status` - enum('unverified', 'verified')
- `email_verified_at` - timestamp
- `status` - enum('pending', 'approved', 'disapproved')

### `email_verifications` table:
- `email` - user's email
- `code` - 6-digit OTP
- `expires_at` - expiration timestamp (5 minutes)
- `verified` - boolean flag

## User Journey

### Seller Registration:
1. **Form Submission** (`/seller-registration`)
   - Fills all 4 steps
   - Uploads documents
   - Accepts privacy policy
   - Creates account

2. **Automatic Redirect** (via `RegisterResponse.php`)
   - User is logged out
   - Redirected to `/verify-email?email={email}`
   - OTP code sent to email

3. **OTP Verification** (`/verify-email`)
   - Enters 6-digit code
   - Can resend if expired
   - On success: `verification_status` = 'verified'

4. **Admin Review** (Admin Panel)
   - **Only verified sellers** appear in Seller Approval page
   - Admin reviews documents
   - Approves or disapproves

5. **Login** (`/login`)
   - After admin approval
   - Seller can access seller dashboard

### Buyer Registration (Same Flow):
Buyers also go through OTP verification before they can login, using the exact same mechanism.

## Benefits

### Security:
- ✅ Confirms valid email addresses
- ✅ Prevents spam/fake registrations
- ✅ Reduces admin workload (no reviewing unverified accounts)

### User Experience:
- ✅ Clear two-step process: Email → Admin
- ✅ Automated email notifications
- ✅ Resend functionality if code expires

### Admin Experience:
- ✅ Only see legitimate, verified sellers
- ✅ Cleaner approval queue
- ✅ More accurate statistics

## Testing Checklist
- [ ] Seller registration form submits successfully
- [ ] User redirected to OTP verification page
- [ ] OTP email received with 6-digit code
- [ ] Valid OTP verifies successfully
- [ ] Invalid OTP shows error
- [ ] Expired OTP shows expiration message
- [ ] Resend OTP works correctly
- [ ] Verified seller appears in admin panel
- [ ] Unverified seller does NOT appear in admin panel
- [ ] Stats count only verified sellers
- [ ] After admin approval, seller can login

## Notes
- OTP system is shared between buyers and sellers
- Same verification table and controller
- Same email notification template
- Login checks both `verification_status` and `status` (admin approval)
