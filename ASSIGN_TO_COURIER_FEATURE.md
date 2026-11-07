# Assign to Courier Feature - Complete Implementation

## Overview
Implemented the "Assign to Courier" functionality sa Sort & Assign page. Ngayon pwede na mag-assign ng parcels from logistics hub to approved courier companies.

## Flow

### 1. Logistics Staff selects parcels
- Check boxes to select one or more parcels
- Select a courier from dropdown (filtered by service areas)
- Click "Assign to Courier" button

### 2. Backend creates shipment assignment
- Creates `ShipmentAssignment` record
- Status: `pending` (courier needs to accept/reject)
- Pickup Status: `pending_pickup`
- Acceptance Deadline: 24 hours from assignment
- Updates order status: `shipped` → `in_transit`

### 3. Courier receives assignment
- Assignment appears in courier's "Incoming Shipments" page
- Courier can accept or reject within 24 hours
- After acceptance, courier can schedule pickup

## API Endpoint

### POST /api/logistics/assign-to-courier

**Request Body:**
```json
{
  "parcel_ids": [4, 5, 6],
  "courier_id": 20
}
```

**Validation:**
- `parcel_ids`: required, array, min 1 item
- `parcel_ids.*`: must be valid order ID
- `courier_id`: required, must be approved courier (role='courier')

**Success Response (200):**
```json
{
  "success": true,
  "message": "Successfully assigned 3 parcel(s) to Kimmi Courier Services Inc.",
  "assigned_count": 3,
  "skipped_count": 0,
  "errors": []
}
```

**Partial Success Response (200):**
```json
{
  "success": true,
  "message": "Successfully assigned 2 parcel(s) to Kimmi Courier Services Inc. (1 skipped)",
  "assigned_count": 2,
  "skipped_count": 1,
  "errors": [
    "Order MIMWB-20261107-00000026-EB3F is already assigned"
  ]
}
```

**Error Response (400/500):**
```json
{
  "error": "Invalid courier selected"
}
```

## What Happens After Assignment

### Database Changes

**1. New `shipment_assignments` record:**
```
- order_id: 4
- courier_id: 20
- assigned_by: 10 (logistics staff)
- assigned_at: 2026-11-07 10:30:00
- assignment_status: 'pending'
- acceptance_deadline: 2026-11-08 10:30:00 (24 hrs)
- pickup_status: 'pending_pickup'
- weight: 0.6 (estimated)
```

**2. `shipment_assignment_history` record:**
```
- assignment_id: 1
- action: 'assigned'
- performed_by: 10
- notes: "Assigned to Kimmi Courier Services Inc."
- created_at: 2026-11-07 10:30:00
```

**3. Order status updated:**
```
- orders.status: 'shipped' → 'in_transit'
```

### Frontend Changes

**Sort & Assign page:**
- Selected parcels removed from list (status changed)
- Success message displayed
- Page reloads to show updated parcel list

**Courier Incoming Shipments page:**
- New assignment appears with status "Pending"
- Shows acceptance deadline countdown
- "Accept" and "Reject" buttons enabled

## Validation & Error Handling

### Checks Performed:
1. ✅ Courier must exist and be approved (role='courier', status='approved')
2. ✅ Order must be in 'shipped' status
3. ✅ Order must not have existing active assignment
4. ✅ All validations wrapped in DB transaction

### Skipped Scenarios:
- Order not found
- Order status is not 'shipped'
- Order already has active assignment

### Error Handling:
- Individual order errors are collected but don't stop batch assignment
- Returns partial success with error details
- Database transaction rollback on critical errors
- Detailed logging for debugging

## Frontend Implementation

**Location:** `resources/js/pages/logistics/SortAssign.tsx`

**Key Changes:**

1. **handleAssign function** - Replaced stub with actual API call
2. **Confirmation dialog** - Shows courier name and parcel count
3. **Success handling** - Shows message and reloads page
4. **Error handling** - Displays error messages

**Code:**
```typescript
const handleAssign = async () => {
    if (selectedParcels.length === 0) {
        alert('Please select at least one parcel');
        return;
    }
    if (!selectedCourier) {
        alert('Please select a courier');
        return;
    }
    
    const courierName = eligibleCouriers.find(c => c.id.toString() === selectedCourier)?.name || 'courier';
    
    if (!confirm(`Assign ${selectedParcels.length} parcel(s) to ${courierName}?`)) {
        return;
    }
    
    try {
        const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;
        const response = await fetch('/api/logistics/assign-to-courier', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
            },
            body: JSON.stringify({
                parcel_ids: selectedParcels,
                courier_id: parseInt(selectedCourier),
            }),
        });
        
        const data = await response.json();
        
        if (response.ok) {
            alert(data.message || 'Parcels assigned successfully!');
            
            // Show errors if any
            if (data.errors && data.errors.length > 0) {
                console.warn('Assignment errors:', data.errors);
            }
            
            // Reload page to refresh parcel list
            router.reload();
        } else {
            alert(data.error || 'Failed to assign parcels');
        }
    } catch (error) {
        console.error('Assignment error:', error);
        alert('Failed to assign parcels. Please try again.');
    }
};
```

## Status Flow

```
Order Status:
┌─────────┐      ┌─────────┐      ┌────────────┐
│ shipped │ ---> │in_transit│ ---> │ delivered │
└─────────┘      └─────────┘      └────────────┘
    ↑                 │
    │                 │ (after assignment)
    │                 ↓
Confirmed at    Assigned to courier
logistics hub   (pending acceptance)


Assignment Status:
┌─────────┐      ┌──────────┐      ┌──────────────┐
│ pending │ ---> │ accepted │ ---> │ picked_up    │
└─────────┘      └──────────┘      └──────────────┘
    │
    ├─> rejected (courier can't handle)
    └─> expired (deadline passed)


Pickup Status:
┌───────────────┐   ┌───────────┐   ┌───────────┐   ┌──────────┐
│pending_pickup │-->│ scheduled │-->│ picked_up │   │ delivered│
└───────────────┘   └───────────┘   └───────────┘   └──────────┘
                         │
                         ├─> failed (retry possible)
                         └─> cancelled
```

## Testing

### Manual Test:
1. Login as logistics staff
2. Go to Sort & Assign page
3. Select 1 or more parcels (status must be 'shipped')
4. Select a courier from dropdown
5. Click "Assign to Courier"
6. Confirm the assignment
7. ✅ Success message should appear
8. ✅ Page reloads with updated parcel list

### Verify Database:
```sql
-- Check shipment assignment
SELECT * FROM shipment_assignments WHERE order_id = 4;

-- Check history
SELECT * FROM shipment_assignment_history WHERE assignment_id = 1;

-- Check order status
SELECT id, waybill_number, status FROM orders WHERE id = 4;
```

### Expected Results:
```
shipment_assignments:
- assignment_status: 'pending'
- pickup_status: 'pending_pickup'
- acceptance_deadline: +24 hours

orders:
- status: 'in_transit'
```

## Next Steps / Courier Side

After assignment, the courier can:
1. **View assignment** in "Incoming Shipments" page
2. **Accept or Reject** within 24 hours
3. **Schedule pickup** after acceptance
4. **Assign personnel** for pickup
5. **Confirm pickup** when parcel is collected

## Files Modified

1. **routes/web.php** - Added POST `/api/logistics/assign-to-courier`
2. **resources/js/pages/logistics/SortAssign.tsx** - Implemented handleAssign function

## Date Implemented
November 7, 2026

## Dependencies
- ✅ ShipmentAssignment model
- ✅ ShipmentAssignmentHistory model
- ✅ Courier approval system
- ✅ Service area matching logic

---

## Quick Reference

**Assign parcels:**
```
POST /api/logistics/assign-to-courier
Body: { parcel_ids: [1,2,3], courier_id: 20 }
```

**Check assignment:**
```sql
SELECT * FROM shipment_assignments WHERE courier_id = 20;
```

**View courier's incoming:**
```
GET /courier/incoming-shipments
```
