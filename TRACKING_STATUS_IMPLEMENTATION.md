# Order Tracking Status Implementation

## Overview
Implemented detailed tracking status updates visible to buyers throughout the entire shipping journey, from seller packing to final delivery.

## Database Schema
Added tracking fields to `orders` table via migration `2026_11_07_144905_add_tracking_status_to_orders_table.php`:
- `tracking_status` - Status code (e.g., 'received_logistics', 'assigned_courier', 'scanned_at_facility')
- `tracking_message` - Human-readable message shown to buyers
- `tracking_updated_at` - Timestamp of last update
- `assigned_courier_id` - Which courier company has the parcel
- `courier_assigned_at` - When assigned to courier
- `courier_accepted_at` - When courier accepted the shipment
- `current_facility_id` - Current location (facility ID)

## Tracking Status Flow

### 1. Seller marks "Awaiting Pickup" (packed)
**Status:** `packed`
**Buyer sees:** "Awaiting pickup by logistics"
**Implementation:** Already handled by seller order status update

### 2. Logistics scans/confirms parcel received
**API:** `POST /api/logistics/parcel/{id}/confirm`
**Status:** `received_logistics`
**Buyer sees:** "Parcel received by logistics"
**Implementation:**
```php
$order->updateTracking('received_logistics', 'Parcel received by logistics');
```
**File:** `routes/web.php` (line ~621)

### 3. Logistics assigns to courier company
**API:** `POST /api/logistics/assign-to-courier`
**Status:** `assigned_courier`
**Buyer sees:** "Parcel assigned to [Courier Company Name]"
**Implementation:**
```php
$order->update([
    'assigned_courier_id' => $courierId,
    'courier_assigned_at' => now(),
]);
$order->updateTracking('assigned_courier', "Parcel assigned to {$courierName}");
```
**File:** `routes/web.php` (line ~1090)

### 4. Courier accepts in Incoming Shipments
**API:** `POST /api/courier/incoming-shipments/{id}/accept`
**Status:** `accepted_courier`
**Buyer sees:** "Accepted by courier, in transit to nearest sorting hub"
**Implementation:**
```php
$order->update(['courier_accepted_at' => now()]);
$order->updateTracking('accepted_courier', 'Accepted by courier, in transit to nearest sorting hub');
```
**File:** `app/Http/Controllers/Courier/IncomingShipmentsController.php` (accept method)

### 5. Parcel scanned at facilities
**API:** `POST /api/courier/scan-parcel`
**Status:** `scanned_at_facility`
**Buyer sees:** "Parcel scanned at [Facility Name] ([Type]) in [City]"
**Examples:**
- "Parcel scanned at Metro Manila Main Hub (Main Hub) in Quezon City"
- "Parcel scanned at Laguna Sorting Hub (Sorting Hub) in Santa Rosa"
- "Parcel scanned at Calamba Branch (Branch) in Calamba"

**Implementation:**
```php
$facilityType = match($facility->facility_type) {
    'main_hub' => 'Main Hub',
    'sorting_hub' => 'Sorting Hub',
    'branch' => 'Branch',
    default => ucfirst(str_replace('_', ' ', $facilityType)),
};

$trackingMessage = "Parcel scanned at {$facility->facility_name} ({$facilityTypeLabel})";
if ($facility->municipality_city) {
    $trackingMessage .= " in {$facility->municipality_city}";
}

$order->update(['current_facility_id' => $validated['facility_id']]);
$order->updateTracking('scanned_at_facility', $trackingMessage);
```
**File:** `routes/web.php` (line ~260)

## Order Model Helper Method

Added `updateTracking()` helper method to Order model:
```php
public function updateTracking(string $status, string $message): void
{
    $this->update([
        'tracking_status' => $status,
        'tracking_message' => $message,
        'tracking_updated_at' => now(),
    ]);
}
```
**File:** `app/Models/Order.php`

## Buyer-Facing Pages

### OrderDetail Page (NEW)
**Route:** `GET /orders/{id}`
**Page:** `resources/js/pages/OrderDetail.tsx`
**Features:**
- Displays order summary with status badge
- Shows waybill number
- **Tracking Information Card:**
  - Current tracking message (highlighted in purple)
  - Last updated timestamp
  - Courier company name
  - Current facility location
- Delivery address
- Order items list with images
- Order summary with payment details
- Cancel order button (if eligible)

### MyPurchases Page (UPDATED)
**Route:** `GET /orders`
**Features:**
- Auto-refreshes every 30 seconds
- Now includes tracking information in order data:
  - `tracking_message`
  - `tracking_updated_at`
  - `assigned_courier_name`
  - `waybill_number`

## API Updates

### OrderController
**Method:** `getOrdersData()` - Updated to include tracking fields
**Method:** `show()` - Updated to load tracking relationships and pass to frontend

**Relationships loaded:**
- `assignedCourier` - Courier company details
- `currentFacility` - Current facility location

**File:** `app/Http/Controllers/OrderController.php`

## Testing Checklist

### 1. Logistics confirms parcel
- [ ] Seller marks order as "packed"
- [ ] Logistics scans parcel at hub
- [ ] Buyer sees "Parcel received by logistics"

### 2. Assign to courier
- [ ] Logistics assigns parcel to courier company
- [ ] Buyer sees "Parcel assigned to [Courier Name]"
- [ ] `assigned_courier_id` and `courier_assigned_at` are set

### 3. Courier accepts
- [ ] Courier views in Incoming Shipments
- [ ] Courier clicks "Accept" button
- [ ] Buyer sees "Accepted by courier, in transit to nearest sorting hub"
- [ ] `courier_accepted_at` is set

### 4. Scan at facilities
- [ ] Courier scans parcel at Main Hub
- [ ] Buyer sees facility name, type, and city
- [ ] `current_facility_id` is updated
- [ ] Scan again at different facility
- [ ] Buyer sees new location
- [ ] Original facility shows parcel in "Archived Parcels"

### 5. View order details
- [ ] Buyer navigates to My Purchases
- [ ] Clicks on an order
- [ ] Views OrderDetail page
- [ ] Sees tracking information card
- [ ] Sees courier and facility info (if assigned)

## Related Files

### Backend
- `database/migrations/2026_11_07_144905_add_tracking_status_to_orders_table.php`
- `app/Models/Order.php`
- `app/Http/Controllers/OrderController.php`
- `app/Http/Controllers/Courier/IncomingShipmentsController.php`
- `routes/web.php`

### Frontend
- `resources/js/pages/OrderDetail.tsx` (NEW)
- `resources/js/pages/MyPurchases.tsx` (already exists, data updated)

### Models Referenced
- `Order` - Main order model with tracking fields
- `ShipmentScan` - Tracks parcel scans at facilities
- `ShipmentAssignment` - Courier assignment records
- `CourierFacility` - Facility information
- `User` - Courier company details

## Notes
- Tracking messages are automatically updated at each stage
- Buyers can view real-time tracking on OrderDetail page
- MyPurchases auto-refreshes every 30 seconds to show latest status
- All tracking updates include timestamps
- Facility scans show specific location details (name, type, city)
- Each scan at a different facility archives the parcel at previous facility
