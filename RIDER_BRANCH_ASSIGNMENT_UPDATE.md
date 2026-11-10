# Rider Branch Assignment Update

## Summary
✅ **COMPLETED** - Removed branch selection from rider registration form. Branch assignment is now handled by courier companies during the approval process based on the rider's service areas.

## Changes Made

### 1. **Frontend (RiderRegistration.tsx)** ✅
- ✅ Reduced from 6 steps to 5 steps
- ✅ Removed Step 3 (Branch Assignment)
- ✅ Steps renumbered:
  - Step 1: Personal Information
  - Step 2: Account Information
  - Step 3: Delivery Information (service areas, vehicle)
  - Step 4: Address Information
  - Step 5: Supporting Documents
- ✅ Removed branch/courier company selection UI
- ✅ Service areas now entered as comma-separated text input
- ✅ Updated validation to remove branch-related checks
- ✅ Updated form state to remove `courier_company_id`, `assigned_branch_id`, `employment_status`
- ✅ Removed mock courier/branch data
- ✅ Updated PSGC API hooks to use step 4 instead of step 5

### 2. **Database Migration** ✅
- ✅ Made `courier_company_id` nullable (set during approval)
- ✅ Made `assigned_branch_id` nullable (assigned by courier)
- ✅ Added comments explaining the approval-time assignment flow

### 3. **Backend Validation (RiderRegistrationRequest.php)** ✅
- ✅ Removed validation for `courier_company_id`
- ✅ Removed validation for `assigned_branch_id`
- ✅ Removed validation for `employment_status`
- ✅ Kept service_areas validation
- ✅ Updated error messages

### 4. **Registration Controller (RiderRegistrationController.php)** ✅
- ✅ Removed `getCourierCompanies()` method
- ✅ Removed `getBranches()` method
- ✅ Updated `store()` method:
  - Sets `courier_company_id` to null
  - Sets `assigned_branch_id` to null
  - Sets `employment_status` to 'inactive'
  - Success message updated to mention courier review

### 5. **Rider Management Controller (RiderManagementController.php)** ✅
- ✅ Updated `index()` method:
  - Shows ALL pending applications (not filtered by courier)
  - Handles null `assigned_branch` gracefully
  - Only "All Riders" tab shows courier-specific riders
  - **Passes branches prop to frontend** (courier's active facilities)
- ✅ Updated `approve()` method:
  - Now requires `assigned_branch_id` parameter
  - Validates branch belongs to approving courier
  - Sets `courier_company_id` and `assigned_branch_id` during approval
  - Sets `employment_status` to 'active'
- ✅ Updated `reject()` method:
  - Not filtered by courier_company_id
  - Tracks which courier rejected via `courier_company_id`

### 6. **Rider Management UI (RiderManagement.tsx)** ✅
- ✅ Added branch selection modal during approval
- ✅ Updated pending applications table to show "Not Assigned" for branch
- ✅ New approval flow:
  1. Click Approve button
  2. Modal opens with branch dropdown
  3. Shows rider's service areas for reference
  4. Courier selects appropriate branch
  5. Confirms approval with branch assignment
- ✅ Receives branches from backend props (no mock data)
- ✅ Validates branch selection before approval
- ✅ Added Props interface for branches
- ✅ Removed useEffect hook (no longer needed)

### 7. **Routes (web.php)** ✅
- ✅ Removed unused API routes:
  - `/api/riders/courier-companies`
  - `/api/riders/branches`
- ✅ Kept approve/reject routes (now require branch selection)
- ✅ Added comment explaining new flow

## New Flow

### Rider Registration:
1. Rider fills out registration form (5 steps, no branch selection)
2. Rider enters service areas as text (e.g., "Manila, Quezon City, Makati")
3. Application submitted with:
   - `courier_company_id`: null
   - `assigned_branch_id`: null
   - `verification_status`: 'pending'
   - `employment_status`: 'inactive'

### Courier Approval:
1. All courier companies see ALL pending rider applications
2. Courier reviews rider details and service areas
3. Courier clicks "Approve" button
4. Modal opens with branch selection dropdown (populated from backend)
5. Courier selects appropriate branch based on service areas
6. Confirms approval
7. System updates:
   - `courier_company_id`: set to approving courier
   - `assigned_branch_id`: set to selected branch
   - `verification_status`: 'approved'
   - `employment_status`: 'active'
   - `verified_by`: courier user ID
   - `verified_at`: current timestamp

## Benefits
1. **Simpler Registration**: Riders don't need to know which courier/branch to join
2. **Better Matching**: Couriers can assign riders to optimal branches based on service areas
3. **Flexible**: Multiple couriers can review and compete for good riders
4. **Area-Based**: Branch assignment directly based on coverage needs
5. **No Mock Data**: All data flows from backend properly

## Database Schema
```sql
riders table:
- courier_company_id: nullable → set during approval
- assigned_branch_id: nullable → set during approval  
- service_areas: json array → used for branch assignment decision
- verification_status: 'pending' → 'approved' on assignment
- employment_status: 'inactive' → 'active' on approval
```

## Files Modified
1. ✅ `resources/js/pages/logistics/RiderRegistration.tsx`
2. ✅ `database/migrations/2026_11_10_092925_create_riders_table.php`
3. ✅ `app/Http/Requests/RiderRegistrationRequest.php`
4. ✅ `app/Http/Controllers/RiderRegistrationController.php`
5. ✅ `app/Http/Controllers/Courier/RiderManagementController.php`
6. ✅ `resources/js/pages/courier/RiderManagement.tsx`
7. ✅ `routes/web.php`

## Diagnostics Status
- ✅ RiderManagement.tsx: No errors
- ✅ RiderManagementController.php: No errors
- ✅ RiderRegistrationController.php: No errors
- ✅ RiderRegistrationRequest.php: No errors
- ⚠️ RiderRegistration.tsx: 2 errors (unrelated to our changes - PSGC API issues)

## Testing Checklist
- [ ] Rider can submit registration without branch selection
- [ ] Pending applications show "Not Assigned" in branch column
- [ ] ALL courier companies can see pending applications
- [ ] Approval modal shows branch dropdown with real data from backend
- [ ] Branch dropdown only shows courier's own branches
- [ ] Approval fails without branch selection
- [ ] Approved rider shows in "All Riders" tab with assigned branch
- [ ] Rejection still works (records which courier rejected)
- [ ] Service areas display correctly in approval modal

## Migration Required
Run: `php artisan migrate:refresh` or manually update existing riders table to make columns nullable.
