# Per-Variant Restock Feature ✅

## Overview
Updated restock functionality to support **per-variation stock management**. Products with variants now show individual variant cards with separate stock inputs, while simple products use the original single-input design.

## Changes Made

### 1. Frontend - SellerProducts.tsx

#### Added State
- `variantRestockAmounts: Record<string, string>` - Tracks restock amount for each variant by variant ID

#### Updated Restock Modal
The modal now detects if product has variants and shows different UI:

**For Products WITH Variants:**
- Scrollable list of variant cards
- Each variant card shows:
  - Variant image (if available)
  - Combination name (e.g., "Red / Large")
  - SKU
  - Current stock with color-coded status:
    - **Red border/background**: Out of stock (0 units)
    - **Yellow border/background**: Low stock (≤5 units)
    - **Gray border**: Normal stock
  - Input field to add units
  - Live preview of new total (e.g., "→ 15 units")
- Bulk update: Updates all variants with entered amounts in one action
- Visual indicators: Shows which variants need attention

**For Products WITHOUT Variants:**
- Original simple design
- Single input field
- Current + add = new total preview

### 2. Backend - ProductController.php

#### New Method: `updateVariantStock()`
```php
PATCH /api/products/{productId}/variants/{variantId}/stock
```

**What it does:**
1. Finds the product and verifies ownership
2. Locates the specific variant in the `variants` JSON array
3. Updates the variant's stock
4. Recalculates total product stock (sum of all variant stocks)
5. Logs the stock change with variant ID in reason
6. Auto-updates product status (out_of_stock ↔ active) based on total stock
7. Returns updated variant and product data

**Request:**
```json
{
  "stock": 25  // New stock for this specific variant
}
```

**Response:**
```json
{
  "message": "Variant stock updated successfully.",
  "variant": {
    "id": "var-red-large",
    "stock": 25
  },
  "product": {
    "id": 123,
    "total_stock": 87,
    "status": "active"
  }
}
```

### 3. Routes - web.php
Added new route:
```php
Route::patch('/api/products/{id}/variants/{variantId}/stock', [ProductController::class, 'updateVariantStock']);
```

## How It Works

### User Flow for Products with Variants:

1. Click 3-dot menu on product with variants
2. Select "Restock"
3. Modal shows all variants in scrollable list
4. Each variant shows:
   - Visual preview (image if available)
   - Combination (Color / Size)
   - Current stock with status badge
   - Input to add units
5. User enters amounts for variants that need restocking
6. Click "Confirm Restock"
7. Frontend sends parallel API calls for each updated variant
8. Backend updates each variant's stock individually
9. Backend recalculates total product stock
10. Product list refreshes with new data
11. Toast shows: "Restocked 3 variants successfully!"

### Visual Design Features:
- **Color-coded urgency**: Red (urgent), Yellow (warning), Gray (normal)
- **Variant thumbnails**: Shows product image if variant has one
- **Live calculation**: Shows "→ X units" as you type
- **Smart button**: Only enabled when at least one amount is entered
- **Scrollable**: Handles products with many variants (10+)

### Technical Details:
- Parallel API calls for performance (Promise.all)
- After updating, refreshes entire product list from API to ensure consistency
- Maintains variant order and structure
- Cleans up state on close/cancel

## Database Structure
Variants are stored in `products.variants` JSON column:
```json
{
  "variants": [
    {
      "id": "var-red-small",
      "combination": { "Color": "Red", "Size": "Small" },
      "sku": "SKU-RED-SM",
      "price": 299.00,
      "stock": 15,
      "image": "data:image/jpeg;base64,..."
    }
  ]
}
```

When variant stock is updated:
1. Find variant by ID in array
2. Update `stock` field
3. Save entire `variants` JSON
4. Update parent product's `stock` = sum of all variant stocks

## Stock Logging
Each variant stock change creates a log entry:
```
Reason: "variant restock: var-red-small"
Reference ID: user_id
```

This allows tracking individual variant movements.

## Testing Checklist
- [ ] Products without variants use simple modal
- [ ] Products with variants show card list
- [ ] Out-of-stock variants show red styling
- [ ] Low-stock variants show yellow styling
- [ ] Input validation works (no negative, must be number)
- [ ] Live preview updates as you type
- [ ] Can update multiple variants at once
- [ ] Product list refreshes after restock
- [ ] Total stock recalculates correctly
- [ ] Status changes (out_of_stock → active) when restocking from 0
- [ ] Toast notification shows correct count
- [ ] Modal closes and clears state after success

## Example Scenarios

### Scenario 1: Restock out-of-stock variant
- Product: "T-Shirt" with Color/Size variants
- Red/Small: 0 units (OUT OF STOCK - red card)
- Blue/Small: 3 units (LOW STOCK - yellow card)
- Red/Large: 12 units (normal - gray card)

User adds 20 to Red/Small:
- Red/Small: 0 → 20 units
- Total product stock: 15 → 35 units
- Product status: may change to active if was out_of_stock

### Scenario 2: Bulk restock
User enters amounts for 5 variants:
- Red/S: +10
- Blue/S: +15
- Red/M: +10
- Blue/M: +15
- Red/L: +20

Click confirm → All 5 update in parallel → Toast: "Restocked 5 variants successfully!"

## Notes
- Variants without images show only text
- Empty inputs are ignored (won't send API call)
- Product refresh ensures UI matches database exactly
- Compatible with existing simple products (backward compatible)
