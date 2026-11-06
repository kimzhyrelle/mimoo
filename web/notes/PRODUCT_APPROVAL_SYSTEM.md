# Product Approval System Implementation

## Overview
Nag-implement ng product approval system kung saan lahat ng bagong products mula sa sellers ay dumaan muna sa admin approval bago lumabas sa buyer side.

## Features Implemented

### 1. Database Changes
- **New Migration**: `2026_10_21_060213_add_approval_status_to_products_table.php`
  - `approval_status` column (enum: pending, approved, rejected) - default: 'pending'
  - `rejection_reason` column (text, nullable) - para sa reason kung bakit na-reject
  - `approved_at` timestamp - kung kailan na-approve
  - `approved_by` foreign key - admin user who approved
  - Added index on `approval_status` for query performance

### 2. Model Updates
- **Product Model** (`app/Models/Product.php`)
  - Added new fillable fields: `approval_status`, `rejection_reason`, `approved_at`, `approved_by`
  - Added `approvedBy()` relationship - connects to User model
  - Added datetime cast for `approved_at`

### 3. Controller Changes
- **ProductController** (`app/Http/Controllers/ProductController.php`)
  
  **Public Endpoints (Buyers):**
  - `index()` - Updated to only show `approved` products
  
  **Seller Endpoints:**
  - `sellerIndex()` - Now returns `approval_status` and `rejection_reason`
  - `store()` - Products automatically set to `pending` on creation
  
  **New Admin Endpoints:**
  - `adminPendingProducts()` - List all pending products with seller info
  - `adminAllProducts()` - List all products with filters (pending/approved/rejected)
  - `adminApproveProduct($id)` - Approve a product
  - `adminRejectProduct($id)` - Reject a product with reason

### 4. Routes Added
**Admin Routes** (in `routes/web.php`):
```php
Route::get('/admin/product-approval', [AdminController::class, 'productApproval']);
Route::get('/api/admin/products/pending', [ProductController::class, 'adminPendingProducts']);
Route::get('/api/admin/products', [ProductController::class, 'adminAllProducts']);
Route::post('/api/admin/products/{id}/approve', [ProductController::class, 'adminApproveProduct']);
Route::post('/api/admin/products/{id}/reject', [ProductController::class, 'adminRejectProduct']);
```

### 5. Admin Dashboard
- **New Page**: `resources/js/pages/admin/product-approval.tsx`
  - View all products (filter by: all, pending, approved, rejected)
  - Search by product name, SKU, or seller name
  - View product details with images
  - Approve products
  - Reject products with reason
  - Stats cards showing product counts
  - Pagination support

**Features:**
- ✅ Real-time stats (total, pending, approved, rejected)
- ✅ Filter tabs
- ✅ Search functionality
- ✅ Product detail modal with images
- ✅ Approve/Reject actions
- ✅ Rejection reason required for rejecting products
- ✅ Seller information display
- ✅ Responsive design

### 6. Seller Dashboard Updates
- **SellerProducts.tsx** - Updated to show approval status
  - Added "Approval" column sa products table
  - Shows badge with status: ⏳ Pending, ✓ Approved, ✗ Rejected
  - Color-coded badges (yellow, green, red)
  - Tooltip shows rejection reason kung na-reject

## Workflow

### Seller Creates Product:
1. Seller fills out product form
2. Submits product
3. Product is created with `approval_status = 'pending'`
4. Product is NOT visible sa buyer side

### Admin Reviews Product:
1. Admin visits `/admin/product-approval`
2. Sees list of pending products
3. Clicks "Review" to see details
4. Options:
   - **Approve**: Product becomes visible to buyers (`approval_status = 'approved'`)
   - **Reject**: Product stays hidden, seller sees rejection reason (`approval_status = 'rejected'`)

### Seller Views Product Status:
1. Seller visits `/seller/products`
2. Sees approval status badge on each product
3. Can see rejection reason by hovering over rejected products

### Buyer Sees Products:
1. Only `approved` AND `active` products appear on buyer side
2. Pending and rejected products are hidden

## API Responses

### Admin Pending Products
```json
{
  "data": [
    {
      "id": 1,
      "name": "Sample Product",
      "description": "Description here",
      "price": 100.00,
      "stock": 50,
      "approval_status": "pending",
      "seller": {
        "id": 2,
        "name": "Seller Name",
        "email": "seller@example.com"
      },
      "created_at": "2026-10-21T06:30:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 100
  }
}
```

### Approve Product Response
```json
{
  "message": "Product approved successfully.",
  "product": {
    "id": 1,
    "name": "Sample Product",
    "approval_status": "approved",
    "approved_at": "2026-10-21T06:45:00.000000Z"
  }
}
```

### Reject Product Request
```json
{
  "reason": "Product images are not clear. Please upload better quality photos."
}
```

## Security
- ✅ Only admin users can approve/reject products
- ✅ Only sellers can create products
- ✅ Buyers only see approved products
- ✅ CSRF protection on all admin actions

## Database Migration
Run: `php artisan migrate`

## Notes
- Products automatically set to `pending` upon creation
- Admin must approve before product appears to buyers
- Sellers can see approval status in their product list
- Rejection reason is visible to seller to help them improve their listing
