# Order Tracking Test Scenario

## Complete Order Journey with Tracking Updates

### Setup
1. **Buyer:** Create an order with at least one product
2. **Seller:** Mark order as "packed" (ready for pickup)
3. **Logistics:** Has access to scan parcels and assign to couriers
4. **Courier:** Has Main Hub, Sorting Hubs, and Branches set up

---

## Test Flow

### Step 1: Seller Prepares Order
**Action:** Seller marks order as "packed"
```
Status: pending → packed
```

**Buyer sees on OrderDetail page:**
- Status: "Packed"
- No tracking message yet (waybill not generated)

---

### Step 2: Logistics Confirms Parcel Received
**Action:** Logistics scans/confirms parcel at their hub

**API Call:**
```http
POST /api/logistics/parcel/{orderId}/confirm
```

**Backend Update:**
```php
// Order status: packed → shipped
// Tracking: 'received_logistics'
$order->updateTracking('received_logistics', 'Parcel received by logistics');
```

**Buyer sees on OrderDetail page:**
- Status: "Shipped"
- Tracking Message: **"Parcel received by logistics"**
- Last Updated: [timestamp]

---

### Step 3: Logistics Assigns to Courier
**Action:** Logistics assigns parcel to a courier company (e.g., "Swift Express Logistics")

**API Call:**
```http
POST /api/logistics/assign-to-courier
Body: {
  "parcel_ids": [123],
  "courier_id": 45
}
```

**Backend Update:**
```php
// Order status: shipped → in_transit
// assigned_courier_id: 45
// courier_assigned_at: now()
$order->updateTracking('assigned_courier', 'Parcel assigned to Swift Express Logistics');
```

**Buyer sees on OrderDetail page:**
- Status: "In Transit"
- Tracking Message: **"Parcel assigned to Swift Express Logistics"**
- Courier Company: "Swift Express Logistics"
- Last Updated: [timestamp]

**Courier sees in Incoming Shipments:**
- New shipment assignment appears
- Status: "Pending" (needs to accept/reject)

---

### Step 4: Courier Accepts Shipment
**Action:** Courier clicks "Accept" in Incoming Shipments page

**API Call:**
```http
POST /api/courier/incoming-shipments/{assignmentId}/accept
```

**Backend Update:**
```php
// assignment_status: pending → accepted
// courier_accepted_at: now()
$order->updateTracking('accepted_courier', 'Accepted by courier, in transit to nearest sorting hub');
```

**Buyer sees on OrderDetail page:**
- Status: "In Transit"
- Tracking Message: **"Accepted by courier, in transit to nearest sorting hub"**
- Courier Company: "Swift Express Logistics"
- Last Updated: [timestamp]

**Courier sees in Incoming Shipments:**
- Assignment status: "Accepted"
- Can now pick up parcel from logistics hub

---

### Step 5: Scan at Main Hub
**Action:** Courier scans parcel at "Metro Manila Main Hub" facility

**API Call:**
```http
POST /api/courier/scan-parcel
Body: {
  "waybill_number": "MIMWB-20261107-00000123-A4F2",
  "facility_id": 10
}
```

**Backend Update:**
```php
// current_facility_id: 10
// ShipmentScan record created
$order->updateTracking('scanned_at_facility', 'Parcel scanned at Metro Manila Main Hub (Main Hub) in Quezon City');
```

**Buyer sees on OrderDetail page:**
- Status: "In Transit"
- Tracking Message: **"Parcel scanned at Metro Manila Main Hub (Main Hub) in Quezon City"**
- Current Location: "Metro Manila Main Hub"
- Courier Company: "Swift Express Logistics"
- Last Updated: [timestamp]

**Courier sees in Branch & Facility Management:**
- Parcel appears in "Received Parcels" tab for Metro Manila Main Hub
- Shows: waybill, buyer, destination, weight, payment method, received timestamp

---

### Step 6: Scan at Sorting Hub
**Action:** Courier scans parcel at "Laguna Sorting Hub" facility

**API Call:**
```http
POST /api/courier/scan-parcel
Body: {
  "waybill_number": "MIMWB-20261107-00000123-A4F2",
  "facility_id": 15
}
```

**Backend Update:**
```php
// current_facility_id: 15
// ShipmentScan record created
$order->updateTracking('scanned_at_facility', 'Parcel scanned at Laguna Sorting Hub (Sorting Hub) in Santa Rosa');
```

**Buyer sees on OrderDetail page:**
- Status: "In Transit"
- Tracking Message: **"Parcel scanned at Laguna Sorting Hub (Sorting Hub) in Santa Rosa"**
- Current Location: "Laguna Sorting Hub"
- Last Updated: [newer timestamp]

**Courier sees in Branch & Facility Management:**
- **Metro Manila Main Hub:**
  - Parcel moved from "Received Parcels" → "Archived Parcels"
  - Shows: "Transferred to: Laguna Sorting Hub" with transfer timestamp
- **Laguna Sorting Hub:**
  - Parcel appears in "Received Parcels" tab
  - Shows current parcel details

---

### Step 7: Scan at Final Branch (Destination)
**Action:** Courier scans parcel at "Calamba Branch" facility (near buyer's address)

**API Call:**
```http
POST /api/courier/scan-parcel
Body: {
  "waybill_number": "MIMWB-20261107-00000123-A4F2",
  "facility_id": 22
}
```

**Backend Update:**
```php
// current_facility_id: 22
// ShipmentScan record created
$order->updateTracking('scanned_at_facility', 'Parcel scanned at Calamba Branch (Branch) in Calamba');
```

**Buyer sees on OrderDetail page:**
- Status: "In Transit"
- Tracking Message: **"Parcel scanned at Calamba Branch (Branch) in Calamba"**
- Current Location: "Calamba Branch"
- Last Updated: [newest timestamp]

**Courier sees in Branch & Facility Management:**
- **Laguna Sorting Hub:**
  - Parcel moved to "Archived Parcels"
  - Shows: "Transferred to: Calamba Branch"
- **Calamba Branch:**
  - Parcel appears in "Received Parcels" tab
  - Ready for final delivery to buyer

---

## Verification Points

### For Buyers (OrderDetail Page)
✅ Order header shows order number and status badge
✅ Waybill number is displayed
✅ Tracking Information card is visible
✅ Tracking message updates at each stage
✅ Timestamps show when tracking was last updated
✅ Courier company name is shown (after assignment)
✅ Current facility location is shown (after first scan)
✅ Delivery address is displayed
✅ Order items list with images
✅ Payment summary with total amount

### For Logistics
✅ Can confirm parcel received
✅ Can assign to courier companies
✅ Parcel status updates after assignment

### For Couriers
✅ Receives shipment assignment in Incoming Shipments
✅ Can accept/reject assignments
✅ Can scan parcels at any facility
✅ Parcels move between facilities correctly
✅ Archived parcels show transfer details

### Database Checks
✅ `orders.tracking_status` updates with status code
✅ `orders.tracking_message` updates with human-readable message
✅ `orders.tracking_updated_at` updates with timestamp
✅ `orders.assigned_courier_id` set when assigned
✅ `orders.courier_assigned_at` set when assigned
✅ `orders.courier_accepted_at` set when accepted
✅ `orders.current_facility_id` updates with each scan
✅ `shipment_scans` table records all scan events

---

## Expected Tracking Messages

| Stage | Status Code | Message |
|-------|-------------|---------|
| Logistics confirms | `received_logistics` | "Parcel received by logistics" |
| Assigned to courier | `assigned_courier` | "Parcel assigned to [Courier Name]" |
| Courier accepts | `accepted_courier` | "Accepted by courier, in transit to nearest sorting hub" |
| Scanned at main hub | `scanned_at_facility` | "Parcel scanned at [Facility Name] (Main Hub) in [City]" |
| Scanned at sorting hub | `scanned_at_facility` | "Parcel scanned at [Facility Name] (Sorting Hub) in [City]" |
| Scanned at branch | `scanned_at_facility` | "Parcel scanned at [Facility Name] (Branch) in [City]" |

---

## Refresh Behavior

### MyPurchases Page
- Auto-refreshes every 30 seconds
- Fetches latest order data including tracking information
- Shows toast notification: "Orders updated"

### OrderDetail Page
- Manual refresh via browser
- Loads latest tracking information from database

---

## Edge Cases to Test

1. **Multiple scans at same facility**
   - Should update tracking message with new timestamp
   - Should not create duplicate in "Received Parcels"

2. **Scan without courier assignment**
   - Should still work if scanned before assignment
   - Tracking message shows facility without courier name

3. **Rejected shipment assignment**
   - Logistics can reassign to different courier
   - Tracking should update with new courier name

4. **Order cancellation**
   - Cancel button only shows if `can_cancel` is true
   - Tracking stops updating after cancellation

---

## Quick Test Commands

```bash
# Check order tracking fields
php artisan tinker
$order = Order::find(123);
$order->tracking_status;
$order->tracking_message;
$order->tracking_updated_at;
$order->assignedCourier->company_name;
$order->currentFacility->facility_name;

# View all scans for a waybill
ShipmentScan::where('waybill_number', 'MIMWB-20261107-00000123-A4F2')->get();

# Check latest scan location
$order->currentFacility;
```
