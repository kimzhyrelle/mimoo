# Quick Order Status Check

## Problem: "Parcel not found in system"

### Root Cause:
**Seller hasn't clicked the "Pack" button yet!**

When seller clicks "Pack":
- ✅ Waybill is AUTO-GENERATED
- ✅ Order status: `paid` → `packed`
- ✅ Order appears in Logistics "Incoming Parcels"

---

## How to Fix (Seller Side):

### Step 1: Login as Seller
```
Email: [seller email]
Password: [password]
```

### Step 2: Go to Seller Orders
- Click "Orders" in sidebar
- Or go to: `http://127.0.0.1:8000/seller/orders`

### Step 3: Find the Order
- Look for order number (e.g., MIM-00000026)
- Check current status

### Step 4: Click "Pack" Button
- Find the order in the list
- Click the **"Pack"** button (purple button)
- **This automatically generates the waybill!** ✓

### Step 5: Verify Waybill Generated
- Look for waybill number (format: MIMWB-YYYYMMDD-ORDERID-XXXX)
- Example: `MIMWB-20261107-00000026-A4F2`
- Copy this for scanning/testing

---

## Check via Browser (Quick Test)

### 1. Check all waybills in system:
```
http://127.0.0.1:8000/api/debug/waybills
```

This will show:
```json
{
    "count": 5,
    "orders": [
        {
            "id": 26,
            "order_number": "MIM-00000026",
            "waybill_number": "MIMWB-20261107-00000026-A4F2",
            "status": "packed",
            "waybill_generated_at": "2026-11-07T14:30:00.000000Z",
            "waybill_printed_at": null
        }
    ]
}
```

### 2. Try scanning a known waybill:
Pick any waybill from the debug list and use it for testing.

---

## Check via Database (Tinker)

```bash
php artisan tinker

# Check specific order
$order = Order::find(26);  # Replace with your order ID
echo "Status: " . $order->status . "\n";
echo "Waybill: " . ($order->waybill_number ?? "NOT GENERATED") . "\n";

# If waybill is null, generate it manually:
if (!$order->waybill_number) {
    $waybill = $order->generateWaybillNumber();
    echo "Generated: $waybill\n";
}

# List all orders with waybills
Order::whereNotNull('waybill_number')
    ->get(['id', 'waybill_number', 'status'])
    ->each(function($o) {
        echo "ID: {$o->id} | Waybill: {$o->waybill_number} | Status: {$o->status}\n";
    });
```

---

## Order Status Flow (Seller Side)

### Status Progression:
1. **pending** → Order placed by buyer
   - ❌ No waybill yet
   - Seller action: Accept order

2. **paid** → Order accepted/paid
   - ❌ No waybill yet
   - Seller action: **Click "Pack" button** ⬅️ MUST DO THIS!

3. **packed** → Order packed (ready for pickup)
   - ✅ Waybill AUTO-GENERATED!
   - Now visible in Logistics "Incoming Parcels"
   - Can be scanned by logistics/courier

4. **shipped** → Logistics confirmed received
   - ✅ Can assign to courier

5. **in_transit** → Assigned to courier
   - ✅ Courier can scan at facilities

6. **completed** → Delivered to buyer
   - ✅ Journey complete

---

## Common Mistakes

### ❌ Mistake 1: Skipping "Pack" Step
**Problem:** Seller marks order as "accepted" but doesn't pack it
**Result:** No waybill generated
**Fix:** Click the "Pack" button!

### ❌ Mistake 2: Trying to Scan Before Packing
**Problem:** Logistics tries to scan order that's still "paid" status
**Result:** "Parcel not found"
**Fix:** Wait for seller to pack first

### ❌ Mistake 3: Wrong Order Selected
**Problem:** Scanning wrong order's barcode
**Result:** "Parcel not found"
**Fix:** Verify order number matches

---

## Testing Workflow (Complete)

### Phase 1: Order Creation (Buyer)
```
1. Login as buyer
2. Add product to cart
3. Checkout
4. Order created → Status: pending/paid
```

### Phase 2: Order Packing (Seller) ⬅️ CRITICAL STEP!
```
5. Login as seller
6. Go to Seller Orders
7. Find the order
8. Click "Pack" button ⬅️ THIS GENERATES WAYBILL!
9. Waybill appears on screen
10. Status changes to: packed
```

### Phase 3: Logistics Scanning
```
11. Login as logistics
12. Go to Incoming Parcels
13. Scan the waybill (now it exists!)
14. Confirm parcel received
```

### Phase 4: Courier Assignment
```
15. Assign to courier company
16. Courier accepts in Incoming Shipments
```

### Phase 5: Facility Scanning
```
17. Courier scans at facilities
18. Tracking updates for buyer
```

---

## Quick Verification Checklist

Before attempting to scan:
- [ ] Order exists in database
- [ ] Seller has clicked "Pack" button
- [ ] Order status is "packed" (not "paid")
- [ ] Waybill number is visible in seller order details
- [ ] Waybill exists in `/api/debug/waybills`
- [ ] Waybill format is correct: `MIMWB-YYYYMMDD-ORDERID-XXXX`

If all checked ✓, scanning should work!

---

## Emergency Fix (If Seller Can't Access)

If seller account is unavailable, admin can manually fix:

```bash
php artisan tinker

# Force generate waybill for order
$order = Order::find(26);  # Replace with order ID
$order->update(['status' => 'packed']);
$waybill = $order->generateWaybillNumber();
echo "Generated waybill: $waybill\n";
echo "Order is now ready for scanning!\n";
```

---

## Success Indicators

### ✅ When Everything Works:

**In Seller Orders:**
```
Order MIM-00000026
Status: Packed ✓
Waybill: MIMWB-20261107-00000026-A4F2
[Print Waybill] [Mark as Shipped]
```

**In Logistics Incoming Parcels:**
```
Scan Parcel: MIMWB-20261107-00000026-A4F2
[Parcel Details Appear]
Buyer: John Doe
Seller: Test Shop
Amount: ₱1,299.00
[Confirm] [Report Problem]
```

**In Courier Scanner:**
```
Scan Parcel: MIMWB-20261107-00000026-A4F2
✓ Parcel received successfully at Metro Manila Main Hub
```

---

## Still Not Working?

Check logs:
```bash
tail -f storage/logs/laravel.log
```

Look for:
```
Scanned waybill number: MIMWB-20261107-00000026-A4F2
All waybill numbers in DB: [...]
```

If waybill is NOT in the list → Order not packed yet!
If waybill IS in the list but still error → Different issue (check typo in scanned number)
