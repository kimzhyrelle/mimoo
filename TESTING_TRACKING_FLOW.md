# Testing Order Tracking Flow - Complete Guide

## Prerequisites

Before testing the tracking flow, you need:

1. ✅ **Seller account** with products
2. ✅ **Buyer account** with placed orders
3. ✅ **Logistics staff account** (admin role)
4. ✅ **Courier company account** (approved)
5. ✅ **Courier facilities** set up (Main Hub, Sorting Hubs, Branches)

---

## Step 0: Create Test Order

### As Buyer:
1. Browse products and add to cart
2. Checkout and place order
3. Note the order number (e.g., `MIM-00000026`)
4. Order status: `pending` or `paid`

### As Seller:
1. Go to Seller Orders
2. Find the order
3. Click "Generate Waybill" button
   - This creates waybill number like: `MIMWB-20261107-00000026-A4F2`
   - **IMPORTANT:** Copy this waybill number for scanning!
4. Mark order as "Packed" (ready for pickup)
   - Order status: `pending` → `packed`

**Verify:**
- Order has waybill number
- Order status is "packed"
- Seller can see "Awaiting pickup by logistics" status

---

## Step 1: Logistics Confirms Parcel Received

### As Logistics Staff:
1. Log in to logistics dashboard
2. Go to "Awaiting Pickup" or parcel list
3. Find the order by waybill number
4. Click "Confirm" or scan the parcel
   - API: `POST /api/logistics/parcel/{orderId}/confirm`

**Backend Changes:**
```php
// Order status: packed → shipped
// Tracking status: 'received_logistics'
// Tracking message: "Parcel received by logistics"
```

**Verify:**
- Order status changes to `shipped`
- Tracking message appears
- Timestamp is recorded

**Buyer Sees:**
- On MyPurchases: Purple box with "Parcel received by logistics"
- On OrderDetail: Tracking card with logistics confirmation

---

## Step 2: Logistics Assigns to Courier

### As Logistics Staff:
1. Go to "Sort & Assign" page
2. Select parcels to assign (check the order)
3. Choose courier company from dropdown
4. Click "Assign to Courier" button
   - API: `POST /api/logistics/assign-to-courier`

**Backend Changes:**
```php
// Order status: shipped → in_transit
// assigned_courier_id: [courier_id]
// courier_assigned_at: now()
// Tracking status: 'assigned_courier'
// Tracking message: "Parcel assigned to [Courier Name]"
```

**Verify:**
- Order shows assigned courier
- Assignment appears in courier's Incoming Shipments

**Buyer Sees:**
- "Parcel assigned to [Courier Company Name]"
- Courier company name displayed in order details

**Courier Sees:**
- New shipment in Incoming Shipments
- Status: "Pending" (needs to accept/reject)
- Shows seller pickup address and buyer destination

---

## Step 3: Courier Accepts Shipment

### As Courier:
1. Log in to courier dashboard
2. Go to "Incoming Shipments" page
3. Find the assignment
4. Click "View Details" to see full info
5. Click "Accept" button
   - API: `POST /api/courier/incoming-shipments/{assignmentId}/accept`

**Backend Changes:**
```php
// assignment_status: pending → accepted
// courier_accepted_at: now()
// Tracking status: 'accepted_courier'
// Tracking message: "Accepted by courier, in transit to nearest sorting hub"
```

**Verify:**
- Assignment status: "Accepted"
- Acceptance timestamp recorded
- Can now proceed to scan at facilities

**Buyer Sees:**
- "Accepted by courier, in transit to nearest sorting hub"

---

## Step 4: Scan at Facilities

### As Courier:
1. Go to "Parcel Scanning" page
2. Select facility from dropdown at top (e.g., "Metro Manila Main Hub")
3. Use scanner to scan waybill QR code/barcode
   - OR enter waybill manually: `MIMWB-20261107-00000026-A4F2`
4. Confirm scan
   - API: `POST /api/courier/scan-parcel`

**Backend Changes:**
```php
// ShipmentScan record created
// current_facility_id: [facility_id]
// Tracking status: 'scanned_at_facility'
// Tracking message: "Parcel scanned at [Facility Name] ([Type]) in [City]"
```

**Example Messages:**
- Main Hub: "Parcel scanned at Metro Manila Main Hub (Main Hub) in Quezon City"
- Sorting Hub: "Parcel scanned at Laguna Sorting Hub (Sorting Hub) in Santa Rosa"
- Branch: "Parcel scanned at Calamba Branch (Branch) in Calamba"

**Verify:**
- Scan record created in `shipment_scans` table
- Order `current_facility_id` updated
- Tracking message shows facility details

**Buyer Sees:**
- Tracking message updates to show current facility
- Facility name, type, and city displayed
- Timestamp of scan

**Courier Sees in Branch & Facility:**
1. Select the facility (gear icon)
2. Go to "Received Parcels" tab
3. See the parcel listed with details:
   - Waybill number
   - Buyer name and destination
   - Weight, payment method
   - Received timestamp
   - Scanned by (courier name)

---

## Step 5: Scan at Next Facility (Transfer)

### As Courier:
1. Go to "Parcel Scanning" page
2. Select different facility (e.g., "Laguna Sorting Hub")
3. Scan the same waybill number
4. Confirm scan

**Backend Changes:**
```php
// New ShipmentScan record created
// current_facility_id: [new_facility_id]
// Tracking message: "Parcel scanned at [New Facility]..."
```

**Archive Behavior:**
- Previous facility: Parcel moves from "Received Parcels" → "Archived Parcels"
- Shows: "Transferred to: [New Facility Name]" with timestamp
- New facility: Parcel appears in "Received Parcels"

**Buyer Sees:**
- Updated tracking message with new facility
- Current location changes to new facility

---

## Common Issues & Solutions

### Issue 1: "Parcel not found in system"

**Possible causes:**
1. ❌ Waybill not generated yet
   - **Solution:** Seller must generate waybill first
2. ❌ Wrong waybill number entered
   - **Solution:** Verify waybill number, check for typos
3. ❌ Order not in database
   - **Solution:** Check if order exists in database

**Debug command:**
```php
// Check if order has waybill
php artisan tinker
$order = Order::find(26);
echo $order->waybill_number;  // Should show: MIMWB-20261107-00000026-XXXX

// Check if waybill exists in system
Order::where('waybill_number', 'MIMWB-20261107-00000026-A4F2')->first();
```

### Issue 2: "This facility does not belong to your company"

**Cause:** Trying to scan at another courier's facility

**Solution:** Make sure you select a facility that belongs to your courier company

### Issue 3: "This parcel is assigned to a different courier company"

**Cause:** Parcel is assigned to another courier

**Solution:** 
- Check if logistics assigned to wrong courier
- Logistics needs to reassign to correct courier

### Issue 4: No tracking message showing on buyer side

**Possible causes:**
1. ❌ Frontend not rebuilt after changes
   - **Solution:** Run `npm run build`
2. ❌ Browser cache
   - **Solution:** Hard refresh (Ctrl+Shift+R) or clear cache
3. ❌ Tracking not updating
   - **Solution:** Click "Refresh" button on MyPurchases page

---

## Database Queries for Testing

### Check order tracking fields:
```sql
SELECT 
    id,
    waybill_number,
    status,
    tracking_status,
    tracking_message,
    tracking_updated_at,
    assigned_courier_id,
    current_facility_id
FROM orders 
WHERE id = 26;
```

### Check all scans for a waybill:
```sql
SELECT 
    s.*,
    f.facility_name,
    f.facility_type,
    f.municipality_city
FROM shipment_scans s
LEFT JOIN courier_facilities f ON s.facility_id = f.id
WHERE s.waybill_number = 'MIMWB-20261107-00000026-A4F2'
ORDER BY s.created_at ASC;
```

### Check shipment assignment:
```sql
SELECT 
    sa.*,
    c.company_name as courier_name,
    o.waybill_number
FROM shipment_assignments sa
LEFT JOIN users c ON sa.courier_id = c.id
LEFT JOIN orders o ON sa.order_id = o.id
WHERE sa.order_id = 26;
```

### List orders with waybills:
```sql
SELECT 
    id,
    waybill_number,
    status,
    tracking_message,
    created_at
FROM orders 
WHERE waybill_number IS NOT NULL
ORDER BY created_at DESC
LIMIT 10;
```

---

## Manual Testing Checklist

### Seller Flow:
- [ ] Place order as buyer
- [ ] Login as seller
- [ ] Generate waybill for order
- [ ] Copy waybill number
- [ ] Mark order as "packed"
- [ ] Verify status shows "Awaiting pickup"

### Logistics Flow:
- [ ] Login as logistics staff
- [ ] Find order in parcel list
- [ ] Confirm parcel received
- [ ] Verify tracking: "Parcel received by logistics"
- [ ] Go to Sort & Assign
- [ ] Assign to courier company
- [ ] Verify tracking: "Parcel assigned to [Courier]"

### Courier Flow:
- [ ] Login as courier
- [ ] Check Incoming Shipments
- [ ] View assignment details
- [ ] Accept assignment
- [ ] Verify tracking: "Accepted by courier..."
- [ ] Go to Parcel Scanning
- [ ] Select facility (Main Hub)
- [ ] Scan waybill number
- [ ] Verify scan successful
- [ ] Check Branch & Facility Management
- [ ] Select facility, see parcel in "Received Parcels"
- [ ] Scan at different facility (Sorting Hub)
- [ ] Check previous facility shows "Archived Parcels"
- [ ] Repeat for final destination (Branch)

### Buyer Flow:
- [ ] Login as buyer
- [ ] Go to My Purchases
- [ ] See waybill number displayed
- [ ] See tracking message in purple box
- [ ] See timestamp of last update
- [ ] Click "View details"
- [ ] See full tracking card
- [ ] See courier company name
- [ ] See current facility location
- [ ] Wait 30 seconds, verify auto-refresh
- [ ] Click "Refresh" button manually

---

## Expected Timeline

**Typical order journey:**
1. Order placed → 0 minutes
2. Seller generates waybill → +5 minutes
3. Seller packs order → +30 minutes
4. Logistics confirms → +2 hours
5. Logistics assigns → +1 hour
6. Courier accepts → +30 minutes
7. Scan at Main Hub → +1 hour
8. Scan at Sorting Hub → +6 hours
9. Scan at Branch → +12 hours
10. Out for delivery → +2 hours
11. Delivered → +1 hour

**Total:** ~26 hours from order to delivery (typical)

---

## Quick Test Script

Use this to quickly create and test a full order:

```bash
# 1. Create test order
php artisan tinker
$order = Order::create([...]);  # Or use UI

# 2. Generate waybill
$order->generateWaybillNumber();

# 3. Check waybill
echo $order->waybill_number;

# 4. Update to packed
$order->update(['status' => 'packed']);

# 5. Confirm at logistics
$order->update(['status' => 'shipped']);
$order->updateTracking('received_logistics', 'Parcel received by logistics');

# 6. Assign to courier (replace with actual courier ID)
$order->update([
    'status' => 'in_transit',
    'assigned_courier_id' => 10,
    'courier_assigned_at' => now()
]);
$order->updateTracking('assigned_courier', 'Parcel assigned to Test Courier');

# 7. Create scan record (replace with actual facility ID)
ShipmentScan::create([
    'order_id' => $order->id,
    'waybill_number' => $order->waybill_number,
    'courier_id' => 10,
    'facility_id' => 5,
    'scan_type' => 'received',
    'scanned_by' => 'Test Scanner'
]);

# 8. Update tracking with facility details
$facility = CourierFacility::find(5);
$order->update(['current_facility_id' => 5]);
$order->updateTracking('scanned_at_facility', "Parcel scanned at {$facility->facility_name}");

# 9. Check result
$order->refresh();
echo $order->tracking_message;
```

---

## Video Demo Script

**Scene 1: Seller (30 sec)**
- Show product listing
- Buyer places order
- Seller receives order notification
- Generate waybill (show the number)
- Mark as packed

**Scene 2: Logistics (45 sec)**
- Show parcel list
- Confirm parcel received
- Show tracking update
- Go to Sort & Assign
- Assign to courier
- Show tracking update

**Scene 3: Courier (60 sec)**
- Login to courier dashboard
- Show Incoming Shipments
- Accept assignment
- Go to Parcel Scanning
- Select Main Hub
- Scan waybill
- Show success message
- Check Branch & Facility
- See parcel in Received Parcels
- Scan at Sorting Hub
- Show archived at Main Hub
- Scan at Branch

**Scene 4: Buyer (30 sec)**
- Login as buyer
- Go to My Purchases
- Show tracking in purple box
- Click View details
- Show full tracking card
- Show courier and facility info
- Click Refresh
- Show auto-update after 30 seconds

**Total:** ~3 minutes
