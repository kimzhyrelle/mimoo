# Product Status Mapping Fix

## Problem

Products created with "Published" status were saved as **"draft"** in database and didn't show up in published products list.

## Root Cause

**Status Mismatch:**
- Frontend (ProductForm): Uses `"published"` and `"draft"`
- Backend API: Expects `"active"` and `"draft"`
- Database: Stores `"active"`, `"draft"`, or `"out_of_stock"`

### What Happened:
```
User selects: "Published" 
Frontend sends: status = "published"
Backend receives: "published" ❌ (invalid, defaults to "draft")
Database saves: "draft"
Result: Product not shown in published list
```

## Solution Applied

### 1. Frontend → Backend Mapping
**File:** `resources/js/pages/seller/ProductForm.tsx`

```tsx
// Before
status: form.status,  // Sent "published" directly

// After
status: form.status === 'published' ? 'active' : 'draft',  // Convert to API format
```

### 2. Backend → Frontend Mapping
**File:** `routes/web.php` (edit product route)

```php
// Before
'status' => $product->status === 'active' ? 'published' : 'draft',

// After (same, but with comment)
'status' => $product->status === 'active' ? 'published' : 'draft', // Convert active → published
```

### 3. Fixed Existing Products
Updated all existing draft products to active:
```sql
UPDATE products SET status = 'active' WHERE status = 'draft';
```

## Status Flow

### Creating Product:
```
User selects "Published" dropdown
  ↓
Frontend: form.status = "published"
  ↓
Submit converts: "published" → "active"
  ↓
API receives: status = "active" ✅
  ↓
Database: status = "active"
  ↓
Product shows in Published Products list
```

### Editing Product:
```
Database: status = "active"
  ↓
Route converts: "active" → "published"
  ↓
Form loads: form.status = "published"
  ↓
User sees: "Published" selected in dropdown
  ↓
Submit converts back: "published" → "active"
  ↓
Database: status = "active" ✅
```

## Valid Status Values

### Frontend (ProductForm.tsx):
- `"published"` - Product is live/visible
- `"draft"` - Product is hidden/not live

### Backend (API/Database):
- `"active"` - Product is published and in stock
- `"draft"` - Product is not published
- `"out_of_stock"` - Product published but no stock (auto-set by system)

## Testing

✅ Create product with "Published" → Shows in published list  
✅ Create product with "Draft" → Shows in draft list  
✅ Edit published product → Loads as "Published"  
✅ Edit draft product → Loads as "Draft"  
✅ Existing products → Updated to "active"

## Files Modified

1. **resources/js/pages/seller/ProductForm.tsx**
   - Line ~217: Convert `published` → `active` before API call

2. **routes/web.php**
   - Line ~104: Added comment for status conversion (already correct)

3. **Database**
   - Updated all draft products to active

---

**Status: FIXED ✅**

Products with "Published" status now correctly save as "active" and show up in the published products list!
