# Product Report System

## Overview
Buyers can report products na suspicious or violates guidelines. Admin reviews reports and can delete product (approve) or keep it (reject).

## Database Structure

### `product_reports` Table
- `id` - Report ID
- `product_id` - Product being reported
- `reported_by` - User ID ng buyer who reported
- `reason` - Reason category (misleading, inappropriate, counterfeit, spam, other)
- `description` - Detailed explanation from reporter
- `status` - pending/approved/rejected
- `reviewed_at` - When admin reviewed
- `admin_notes` - Admin's notes about decision
- `created_at`, `updated_at`

## API Endpoints

### Buyer Endpoints

**POST `/api/products/{id}/report`**
- Report a product
- Requires auth (buyer)
- Prevents duplicate reports (same user, same product, pending status)

Request body:
```json
{
  "reason": "misleading", // or "inappropriate", "counterfeit", "spam", "other"
  "description": "Detailed explanation here..."
}
```

Response:
```json
{
  "message": "Product reported successfully. Our team will review it shortly.",
  "report": {
    "id": 1,
    "reason": "misleading",
    "status": "pending"
  }
}
```

### Admin Endpoints

**GET `/api/admin/reports`**
- Get all product reports
- Query params:
  - `status` - Filter by status (pending/approved/rejected)
  - `per_page` - Pagination (default: 20)
  - `page` - Page number

Response:
```json
{
  "data": [
    {
      "id": 1,
      "product": {
        "id": 5,
        "name": "Product Name",
        "sku": "SKU-123",
        "images": [...],
        "main_image": "...",
        "seller": {
          "id": 2,
          "name": "Seller Name"
        }
      },
      "reporter": {
        "id": 3,
        "name": "Buyer Name",
        "email": "buyer@example.com"
      },
      "reason": "misleading",
      "description": "This product...",
      "status": "pending",
      "created_at": "2026-10-21T..."
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

**POST `/api/admin/reports/{id}/approve`**
- Approve report = DELETE PRODUCT (soft delete)
- Request body (optional):
```json
{
  "notes": "Admin notes here..."
}
```

**POST `/api/admin/reports/{id}/reject`**
- Reject report = KEEP PRODUCT
- Request body (required):
```json
{
  "notes": "Reason for rejecting report..."
}
```

## Workflow

### 1. Buyer Reports Product
```
Buyer → Product Page → Click "Report" → Fill form → Submit
→ Creates ProductReport (status: pending)
→ Product stays visible
```

### 2. Admin Reviews Report
```
Admin → Reports Page → View report details
→ Option 1: Approve Report
   - Product is DELETED (soft delete)
   - Report status = 'approved'
   - Admin can add notes

→ Option 2: Reject Report
   - Product STAYS ACTIVE
   - Report status = 'rejected'
   - Admin MUST provide notes (reason)
```

### 3. Seller Notification (TODO - later discussion)
- How seller sees their product was reported
- Notification system
- Email alerts
- Dashboard indicators

## Report Reasons

1. **misleading** - False advertising, incorrect specs
2. **inappropriate** - Offensive content, adult material
3. **counterfeit** - Fake branded items
4. **spam** - Spam listings, duplicates
5. **other** - Other violations

## Features

✅ Buyers can report products
✅ Prevents duplicate reports
✅ Admin can view all reports
✅ Admin can approve (delete product) or reject (keep product)
✅ Admin must provide notes when rejecting
✅ Report history tracking
✅ Status filtering (pending/approved/rejected)

## TODO (Next Steps)

1. **Buyer Side UI**
   - Add "Report Product" button sa product page
   - Create report modal with form
   - Show success message after reporting

2. **Admin Side UI**
   - Create "Product Reports" page sa admin dashboard
   - List view ng reports
   - Review modal with product details
   - Approve/Reject actions

3. **Seller Notifications**
   - Badge sa seller dashboard showing reported products
   - Email notification when product is removed
   - Report details visible to seller (optional)

## Security

- Only authenticated buyers can report
- Admin session required for reviewing
- Prevents duplicate pending reports
- Soft delete products (recoverable)
- All actions logged with timestamps

## Notes

- Products with approved reports are soft-deleted (recoverable if needed)
- Buyers can only have 1 pending report per product
- Admin notes are required when rejecting reports
- Admin notes are optional when approving reports
