# Admin Sidebar Update - Seller Approval Feature

## Summary
Updated the admin sidebar navigation to focus on core admin features and converted the registration management page to specifically handle seller approvals only.

## Changes Made

### 1. Admin Sidebar Navigation (`admin-shell.tsx`)
Updated to show only the 7 core admin features:
- **Dashboard** - Overall sales and order statistics
- **Commission** - 10% per item sold management
- **Reports** - Gross sales, admin commission, and net seller sales
- **Seller Approval** - Verify valid ID and business permit (NEW)
- **Product Approval** - Approve or reject new products
- **Notifications** - Notify sellers of approvals/rejections
- **Monitoring** - Monitor orders, cancellations, shipment statuses

### 2. Page Conversion
- **Renamed**: `manage-registrations.tsx` → `seller-approval.tsx`
- **Focused**: Now only shows seller accounts (not buyers)
- **Updated UI**: 
  - Removed role filter (only sellers shown)
  - Updated page title and descriptions
  - Changed role badge to always show "Seller" in purple

### 3. Backend Updates (`AdminController.php`)
Added new methods specifically for seller approval:
- `sellerApproval()` - Shows only seller registrations with their stats
- `approveSellerRegistration()` - Approves seller accounts with validation
- `disapproveSellerRegistration()` - Disapproves seller accounts with validation

### 4. Routes (`web.php`)
Added new seller-specific routes:
```php
Route::get('/seller-approval', [AdminController::class, 'sellerApproval'])->name('seller-approval');
Route::post('/seller-approval/{userId}/approve', [AdminController::class, 'approveSellerRegistration']);
Route::post('/seller-approval/{userId}/disapprove', [AdminController::class, 'disapproveSellerRegistration']);
```

## File Changes
1. ✅ `resources/js/layouts/admin/admin-shell.tsx` - Updated nav items
2. ✅ `resources/js/pages/admin/manage-registrations.tsx` → `seller-approval.tsx` - Renamed and updated
3. ✅ `app/Http/Controllers/Admin/AdminController.php` - Added seller approval methods
4. ✅ `routes/web.php` - Added seller approval routes

## Next Steps
The old `manage-registrations` routes are still available if needed, but the main admin sidebar now directs to the seller-specific approval page.
