# Seller Registration Form - New Fields Added

## Summary
Added additional required fields to the seller registration form as per TC-001 requirements:
- TIN (Tax Identification Number)
- Postal Code
- Business Type selection
- Privacy Policy acceptance checkbox
- Additional document uploads (DTI, BIR 2303, SEC Certificate)

## Changes Made

### 1. Frontend (SellerRegistration.tsx)

#### Step 1 - Personal Details
- ✅ **TIN field** added
  - Format: XXX-XXX-XXX-XXX or 9-12 digits
  - Required field with validation
  - Position: After Contact Number, before Birthday

#### Step 2 - Address
- ✅ **Postal Code field** added
  - 4-digit numeric format
  - Required field with validation
  - Position: After Barangay, before Street Address

#### Step 3 - Store & Documents
- ✅ **Business Type dropdown** added
  - Options: Sole Proprietor, Partnership, Corporation
  - Required field
  - Position: After Business Name, before Line of Business

- ✅ **Document Upload Fields:**
  - **Business Permit** (existing, kept)
  - **DTI Registration** (NEW - required for all)
  - **BIR Form 2303** (NEW - required for all)
  - **SEC Certificate** (NEW - conditionally required for Corporation only)
  - **Government ID** (existing, moved to Identity Verification section)

#### Step 4 - Password & Confirmation
- ✅ **Privacy Policy checkbox** added
  - Required field
  - Includes link to /privacy-policy page
  - Opens in new tab
  - Validation error if not checked

### 2. Database Migration
Created migration: `2026_10_19_141447_add_seller_registration_fields_to_users_table.php`

New columns added to `users` table:
- `tin` (string, 50 chars, nullable)
- `postal_code` (string, 10 chars, nullable)
- `business_type` (enum: sole_proprietor, partnership, corporation, nullable)
- `dti_registration` (string, nullable) - file path
- `bir_2303` (string, nullable) - file path
- `sec_certificate` (string, nullable) - file path
- `privacy_policy_accepted` (boolean, default false)

### 3. User Model (app/Models/User.php)
Updated to include new fields:
- Added all new fields to fillable array
- Updated PHPDoc annotations with new properties
- Added business_type enum documentation

### 4. Form Validation
Updated validation rules in `validate()` function:

**Step 1:**
- TIN: Required, must match format XXX-XXX-XXX-XXX or 9-12 digits

**Step 2:**
- Postal Code: Required, must be exactly 4 digits

**Step 3:**
- Business Type: Required
- DTI Registration: Required, PDF/JPG/PNG, max 5MB
- BIR 2303: Required, PDF/JPG/PNG, max 5MB
- SEC Certificate: Required only if business_type is 'corporation', PDF/JPG/PNG, max 5MB

**Step 4:**
- Privacy Policy: Must be checked/accepted

## Form Structure

### Step 1: Your Details
- First Name *
- Last Name *
- Middle Initial
- Sex *
- Email Address *
- Contact Number *
- **TIN (Tax Identification Number)** * ← NEW
- Birthday *
- Age (auto-calculated)

### Step 2: Address
- Region *
- Province * (or District * for NCR)
- Municipality/City *
- Barangay *
- **Postal Code** * ← NEW
- Street & House Number *

### Step 3: Store & Identity Verification
**Store Details:**
- Business Name *
- **Business Type** * ← NEW (Sole Proprietor/Partnership/Corporation)
- Line of Business *

**Business Documents:**
- Business Permit * (PDF/JPG/PNG, max 5MB)
- **DTI Registration** * ← NEW
- **BIR Form 2303** * ← NEW
- **SEC Certificate** * ← NEW (shown only if Corporation selected)

**Identity Verification:**
- Government-Issued ID * (PDF/JPG/PNG, max 5MB)

### Step 4: Secure Your Account
- Password * (minimum 8 characters)
- Confirm Password *
- **Privacy Policy Acceptance** * ← NEW (checkbox with link)

## File Upload Requirements
All document uploads accept:
- File types: PDF, JPG, JPEG, PNG
- Maximum size: 5MB per file
- Validation on both client and server side

## Next Steps (Backend)
The backend registration handler in FortifyServiceProvider will need to be updated to:
1. Handle the new document file uploads (dti_registration, bir_2303, sec_certificate)
2. Store uploaded files in appropriate storage location
3. Save file paths to database
4. Validate business_type and conditionally require SEC certificate
5. Validate TIN format
6. Validate postal code format
7. Store privacy_policy_accepted flag

## Testing Checklist
- [ ] TIN field validates correct formats
- [ ] Postal code accepts only 4 digits
- [ ] Business type dropdown works correctly
- [ ] SEC certificate field shows/hides based on business type
- [ ] All document uploads accept valid file types
- [ ] Privacy policy checkbox is required
- [ ] Privacy policy link opens in new tab
- [ ] Form submission includes all new fields
- [ ] Database stores all new field values correctly
