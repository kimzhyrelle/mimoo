# Waybill System Implementation

Nag-implement ako ng complete waybill system para sa MIMOO logistics tracking.

## Features

### 1. **Unique Waybill Numbers**
- Format: `MIMWB-YYYYMMDD-ORDERID-RANDOM`
- Example: `MIMWB-20261103-00000123-A4F2`
- Unique per order, automatically generated

### 2. **Database Fields** (Already migrated)
```php
- waybill_number (string, unique, nullable)
- waybill_generated_at (timestamp, nullable)
- waybill_printed_at (timestamp, nullable)
```

### 3. **Backend Routes**
```php
// Generate waybill (POST)
/api/orders/{id}/waybill/generate

// Get waybill details (GET)
/api/orders/{id}/waybill

// Mark as printed (POST)
/api/orders/{id}/waybill/mark-printed

// QR Code public endpoint (GET)
/waybill/{waybillNumber}/qr
```

### 4. **Automatic Generation**
- Waybill automatically generated when order status changes to "packed"
- Called via `$order->generateWaybillNumber()`
- Idempotent - won't regenerate if already exists

### 5. **QR Code & Barcode**
- QR Code: Uses `chillerlan/php-qrcode` package
- SVG format for scalability
- Barcode: Code 39 format (already implemented in SellerOrders.tsx)

### 6. **Frontend Component**
- Created: `resources/js/components/WaybillPreview.tsx`
- Features:
  - Preview before printing
  - Print button (triggers browser print dialog)
  - Download as PDF option
  - QR code display
  - Barcode display
  - Complete shipping information
  - Item list
  - Signature sections for courier & recipient

## Usage

### For Sellers:

1. **Accept Order** → Status: "new" → "to_prepare"
2. **Pack Items** → Status: "to_prepare" → "packed" 
   - Waybill automatically generated here
3. **Print Waybill** → Click "Print waybill" button
   - Shows preview with QR code and barcode
   - Can print or save as PDF
4. **Hand to Courier** → Status: "packed" → "shipped"
   - Confirm pickup with courier details

### For Logistics:

Same waybill endpoints accessible to logistics users:
- Can view waybill of all orders
- Can scan QR codes for tracking
- Can print waybills for parcels

## API Examples

### Generate Waybill
```javascript
POST /api/orders/123/waybill/generate

Response:
{
  "waybill": {
    "number": "MIMWB-20261103-00000123-A4F2",
    "order_id": 123,
    "order_number": "MIM-00000123",
    "generated_at": "Nov 3, 2026 10:30 AM",
    "printed_at": null
  }
}
```

### Get Waybill Details
```javascript
GET /api/orders/123/waybill

Response:
{
  "waybill": {
    "number": "MIMWB-20261103-00000123-A4F2",
    "order_number": "MIM-00000123",
    "generated_at": "Nov 3, 2026 10:30 AM",
    "printed_at": "Nov 3, 2026 10:35 AM",
    "status": "packed",
    "payment_method": "cod",
    "shipping": {
      "name": "Juan Dela Cruz",
      "phone": "09171234567",
      "address": "123 Main St, Brgy. Sample, Manila"
    },
    "items": [
      {
        "name": "Product A",
        "quantity": 2,
        "variant": {"Size": "Large", "Color": "Red"}
      }
    ],
    "total_amount": "1,250.00",
    "item_count": 2,
    "total_quantity": 3
  }
}
```

### Mark as Printed
```javascript
POST /api/orders/123/waybill/mark-printed

Response:
{
  "message": "Waybill marked as printed.",
  "printed_at": "Nov 3, 2026 10:35 AM"
}
```

## Security

- **Seller Access**: Can only access waybills for orders containing their products
- **Logistics Access**: Can access all waybills
- **Public QR Endpoint**: Only returns SVG, validates waybill exists
- **CSRF Protection**: All POST/PATCH routes protected

## Model Methods

```php
// In Order model
$order->generateWaybillNumber();  // Generate unique waybill
$order->markWaybillPrinted();     // Mark as printed
$order->hasWaybill();             // Check if has waybill
```

## Integration with Existing SellerOrders

The current SellerOrders.tsx already has:
- Code 39 barcode rendering
- Waybill preview modal
- Print functionality

The new system adds:
- Backend persistence (database storage)
- Unique number generation
- QR code generation
- API endpoints for logistics tracking
- Timestamp tracking (generated_at, printed_at)

## Next Steps (Optional Enhancements)

1. **Add waybill scanning interface for logistics**
   - Scan QR code to update parcel status
   - Track parcel movement

2. **Add tracking page for buyers**
   - Public tracking with waybill number
   - Real-time status updates

3. **Add batch waybill printing**
   - Print multiple waybills at once
   - Export as PDF

4. **Add waybill analytics**
   - Track printing time vs pickup time
   - Efficiency metrics

## Testing

To test:
1. Login as seller
2. Go to Orders page
3. Accept a new order
4. Pack items
5. Click "Print waybill" - should show unique waybill number
6. Print or download
7. Confirm pickup

The waybill number will be stored in database and can be used for logistics tracking.
