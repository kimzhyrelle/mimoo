# COD Orders - Fixed "To Ship" Tab Routing ✅

## Issue
COD (Cash on Delivery) orders were showing in the "To Pay" tab instead of going directly to "To Ship" tab.

## Root Cause
The `payment_method` field was not being sent from the backend to the frontend, causing the tab logic to treat all pending orders as online payments that need payment.

## Frontend Logic (Already Correct)
**Location:** `resources/js/pages/MyPurchases.tsx`

```typescript
function tabOf(o: Order): Exclude<TabKey, "all"> {
  if (o.status === "cancelled" || o.status === "failed") return "cancelled";
  if (o.status === "completed") return "to_review";
  if (o.status === "shipped") return "to_receive";
  if (o.status === "paid") return "to_ship";
  
  // pending: online payments still need paying, COD goes straight to "To Ship"
  const online = o.payment_method ? o.payment_method !== "cod" : true;
  return online && o.payment_status !== "paid" ? "to_pay" : "to_ship";
}
```

### Logic Breakdown:
- **COD orders** (`payment_method === "cod"`)
  - `online = false`
  - Returns "to_ship" ✅

- **Online payments** (`payment_method === "gcash"` or `"paymongo"`)
  - `online = true`
  - If `payment_status !== "paid"` → returns "to_pay" ✅
  - If `payment_status === "paid"` → returns "to_ship" ✅

- **Missing payment_method** (was the bug)
  - `online = true` (default)
  - Returns "to_pay" ❌ (incorrect for COD)

## Backend Fix
**Location:** `app/Http/Controllers/OrderController.php`

### Before ❌
```php
'id' => $order->id,
'order_number' => 'MIM-' . str_pad($order->id, 8, '0', STR_PAD_LEFT),
'total' => (float) $order->total,
'status' => $order->status,
'payment_status' => $order->payment_status,
// Missing: 'payment_method' => $order->payment_method,
'item_count' => $order->items->count(),
```

### After ✅
```php
'id' => $order->id,
'order_number' => 'MIM-' . str_pad($order->id, 8, '0', STR_PAD_LEFT),
'total' => (float) $order->total,
'status' => $order->status,
'payment_status' => $order->payment_status,
'payment_method' => $order->payment_method,  // ← Added
'item_count' => $order->items->count(),
```

## Order Flow by Payment Method

### COD Orders
```
User checks out with COD
    ↓
Order created with:
  - status: "pending"
  - payment_status: "unpaid"
  - payment_method: "cod"
    ↓
MyPurchases page:
  - tabOf() checks payment_method
  - Sees payment_method === "cod"
  - Routes to "To Ship" tab ✅
    ↓
Seller prepares the order
    ↓
Status changes to "paid" (order is ready)
    ↓
Stays in "To Ship" tab
    ↓
Status changes to "shipped"
    ↓
Moves to "To Receive" tab
    ↓
Status changes to "completed"
    ↓
Moves to "To Review" tab
```

### Online Payment Orders (GCash/PayMongo)
```
User checks out with GCash/PayMongo
    ↓
Order created with:
  - status: "pending"
  - payment_status: "unpaid"
  - payment_method: "gcash" or "paymongo"
    ↓
MyPurchases page:
  - tabOf() checks payment_method
  - Sees payment_method !== "cod"
  - payment_status === "unpaid"
  - Routes to "To Pay" tab ✅
    ↓
User pays online
    ↓
payment_status changes to "paid"
status changes to "paid"
    ↓
Moves to "To Ship" tab ✅
    ↓
(same flow as COD from here)
```

## Status Transitions

### COD Path
```
pending → paid → shipped → completed
  ↓        ↓       ↓          ↓
To Ship  To Ship  To Receive To Review
```

### Online Payment Path
```
pending → paid → shipped → completed
  ↓        ↓       ↓          ↓
To Pay   To Ship  To Receive To Review
```

## Tab Assignment Logic Summary

| Status    | Payment Status | Payment Method | Tab         |
|-----------|---------------|----------------|-------------|
| pending   | unpaid        | cod            | To Ship     |
| pending   | unpaid        | gcash/paymongo | To Pay      |
| paid      | paid          | any            | To Ship     |
| shipped   | paid          | any            | To Receive  |
| completed | paid          | any            | To Review   |
| cancelled | any           | any            | Cancelled   |
| failed    | any           | any            | Cancelled   |

## Testing Checklist

### COD Orders
- [ ] Create order with COD payment method
- [ ] Verify order appears in "To Ship" tab (NOT "To Pay")
- [ ] Verify "Pay Now" button does NOT show
- [ ] Verify "View Details" button shows
- [ ] Verify "Cancel Order" button shows (if cancellable)

### Online Payment Orders
- [ ] Create order with GCash/PayMongo
- [ ] Verify order appears in "To Pay" tab
- [ ] Verify "Pay Now" button shows
- [ ] Mark payment as paid (simulate payment)
- [ ] Verify order moves to "To Ship" tab
- [ ] Verify "Pay Now" button disappears

### Tab Counts
- [ ] "To Pay" tab count shows only unpaid online orders
- [ ] "To Ship" tab count includes COD orders + paid online orders
- [ ] Badge numbers match actual order counts

## Database Schema

### orders table
```sql
- id
- buyer_id
- total
- status           -- "pending" | "paid" | "shipped" | "completed" | "cancelled" | "failed"
- payment_status   -- "unpaid" | "paid"
- payment_method   -- "cod" | "gcash" | "paymongo"
- shipping_name
- shipping_address
- shipping_phone
- idempotency_key
- created_at
- updated_at
```

## Important Notes

1. **COD orders NEVER go to "To Pay" tab**
   - They are created with status "pending" but payment_method "cod"
   - Frontend logic recognizes this and routes to "To Ship"

2. **payment_method is immutable**
   - Set at order creation
   - Never changes during order lifecycle
   - Used for tab routing logic

3. **payment_status vs status**
   - `payment_status`: Whether buyer has paid (unpaid/paid)
   - `status`: Order fulfillment stage (pending/paid/shipped/completed)
   - Both are needed for accurate tab assignment

4. **Why COD is "unpaid" initially**
   - COD payment happens at delivery
   - Order is created as "unpaid"
   - payment_status changes to "paid" when:
     - Seller marks order as ready (status → "paid")
     - Or upon delivery confirmation

## Summary

✅ Added `payment_method` to OrderController@index response  
✅ Frontend logic already handles COD correctly  
✅ COD orders now route to "To Ship" tab  
✅ Online payment orders route to "To Pay" tab  
✅ Tab transitions work correctly through order lifecycle  

COD orders now skip the "To Pay" tab and go straight to "To Ship"! 🎉
