# Buyer Tracking Status - What They See

## My Purchases Page

### Order Card with Tracking
```
┌─────────────────────────────────────────────────────────────┐
│ MIM-00000026    [🔵 Preparing to ship]        Total: ₱798.00│
│                                                               │
│ 1 item                                                        │
│ Placed on Nov 07, 2026                                       │
│ MIMWB-20261107-00000026-A4F2  (waybill number)              │
│                                                               │
│ ┌───────────────────────────────────────────────────────┐   │
│ │ 🚚 Parcel received by logistics                       │   │
│ │    Nov 07, 2026 2:30 PM                               │   │
│ └───────────────────────────────────────────────────────┘   │
│                                                               │
│ [Product card with image]                                    │
│ Dress                                                         │
│ Seller: Kim Zhyrelle Del Mundo                               │
│ Qty: 2    ₱399.00                                            │
│                                                               │
│ [View details]  [Cancel order]                               │
└─────────────────────────────────────────────────────────────┘
```

### Tracking Status Updates (as they happen)

**Stage 1: No tracking yet**
```
1 item
Placed on Nov 07, 2026
```

**Stage 2: Logistics confirms**
```
1 item
Placed on Nov 07, 2026
MIMWB-20261107-00000026-A4F2

┌─────────────────────────────────────────────┐
│ 🚚 Parcel received by logistics             │
│    Nov 07, 2026 2:30 PM                     │
└─────────────────────────────────────────────┘
```

**Stage 3: Assigned to courier**
```
1 item
Placed on Nov 07, 2026
MIMWB-20261107-00000026-A4F2

┌──────────────────────────────────────────────────┐
│ 🚚 Parcel assigned to Swift Express Logistics   │
│    Nov 07, 2026 3:15 PM                          │
└──────────────────────────────────────────────────┘
```

**Stage 4: Courier accepts**
```
1 item
Placed on Nov 07, 2026
MIMWB-20261107-00000026-A4F2

┌──────────────────────────────────────────────────────────┐
│ 🚚 Accepted by courier, in transit to nearest sorting hub│
│    Nov 07, 2026 4:00 PM                                  │
└──────────────────────────────────────────────────────────┘
```

**Stage 5: Scanned at Main Hub**
```
1 item
Placed on Nov 07, 2026
MIMWB-20261107-00000026-A4F2

┌──────────────────────────────────────────────────────────────┐
│ 🚚 Parcel scanned at Metro Manila Main Hub (Main Hub)       │
│    in Quezon City                                            │
│    Nov 07, 2026 5:30 PM                                      │
└──────────────────────────────────────────────────────────────┘
```

**Stage 6: Scanned at Sorting Hub**
```
1 item
Placed on Nov 07, 2026
MIMWB-20261107-00000026-A4F2

┌──────────────────────────────────────────────────────────────┐
│ 🚚 Parcel scanned at Laguna Sorting Hub (Sorting Hub)       │
│    in Santa Rosa                                             │
│    Nov 07, 2026 7:45 PM                                      │
└──────────────────────────────────────────────────────────────┘
```

**Stage 7: Scanned at Branch (near destination)**
```
1 item
Placed on Nov 07, 2026
MIMWB-20261107-00000026-A4F2

┌──────────────────────────────────────────────────────────────┐
│ 🚚 Parcel scanned at Calamba Branch (Branch) in Calamba    │
│    Nov 08, 2026 9:00 AM                                      │
└──────────────────────────────────────────────────────────────┘
```

---

## Order Detail Page (Click "View details")

### Full Tracking Information Card

```
┌─────────────────────────────────────────────────────────────┐
│ 📍 Tracking Information                                      │
│                                                               │
│ ┌───────────────────────────────────────────────────────┐   │
│ │ ● Parcel scanned at Calamba Branch (Branch)          │   │
│ │   in Calamba                                          │   │
│ │   🕐 Nov 08, 2026 9:00 AM                            │   │
│ └───────────────────────────────────────────────────────┘   │
│                                                               │
│ Courier Company           Current Location                   │
│ 🚚 Swift Express         📍 Calamba Branch                  │
│    Logistics                                                 │
└─────────────────────────────────────────────────────────────┘
```

### Complete Order Detail Page Layout

```
┌─────────────────────────────────────────────────────────────┐
│ ← Back to My Purchases                                       │
│                                                               │
│ MIM-00000026                        [🟢 In Transit]          │
│ Placed on Nov 07, 2026                                       │
│                                                               │
│ 📦 Waybill: MIMWB-20261107-00000026-A4F2                    │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ 📍 Tracking Information                                      │
│                                                               │
│ ┌───────────────────────────────────────────────────────┐   │
│ │ ● Parcel scanned at Calamba Branch (Branch)          │   │
│ │   in Calamba                                          │   │
│ │   🕐 Nov 08, 2026 9:00 AM                            │   │
│ └───────────────────────────────────────────────────────┘   │
│                                                               │
│ Courier Company           Current Location                   │
│ 🚚 Swift Express         📍 Calamba Branch                  │
│    Logistics                                                 │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ Delivery Address                                             │
│                                                               │
│ Miguel Angelo Cruz                                           │
│ 0918 552 7734                                                │
│ 3 Purok 5, Apokon, Tagum City, Davao del Norte             │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ Order Items                                                  │
│                                                               │
│ [Image] Dress                                 ₱399.00        │
│         Quantity: 2                           ₱798.00        │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ Order Summary                                                │
│                                                               │
│ Payment Method                            GCASH              │
│ Payment Status                            Paid               │
│ ─────────────────────────────────────────────────────       │
│ Total Amount                              ₱798.00            │
│                                                               │
│ [Cancel Order]  (if eligible)                                │
└─────────────────────────────────────────────────────────────┘
```

---

## How Tracking Updates

### Automatic Updates
- **MyPurchases page:** Auto-refreshes every 30 seconds
- **OrderDetail page:** Refresh browser to see latest updates
- **Toast notification:** "Orders updated" when refresh completes

### Manual Refresh
Click the "Refresh" button on MyPurchases page to get latest tracking immediately.

---

## Visual Styling

### Tracking Status Box
- **Background:** Light purple (`bg-purple-50`)
- **Border:** Purple (`border-purple-200`)
- **Text:** Purple (`text-[#4B2E7E]`)
- **Icon:** Truck icon (`🚚`)
- **Font:** Bold for message, smaller for timestamp

### Waybill Number
- **Font:** Monospace font for easy reading
- **Color:** Purple (`text-[#4B2E7E]`)
- **Format:** `MIMWB-YYYYMMDD-ORDERID-XXXX`

### Order Detail Tracking Card
- **Header:** "📍 Tracking Information" with MapPin icon
- **Current status:** Highlighted with purple left border
- **Additional info:** Grid layout showing courier and facility
- **Icons:** Truck for courier, MapPin for location

---

## Testing Checklist for Buyers

### On MyPurchases Page
- [ ] Can see waybill number once generated
- [ ] Tracking message appears in purple box
- [ ] Timestamp shows when last updated
- [ ] Message updates when refreshing (manual or auto)
- [ ] "View details" button works
- [ ] Can see tracking for multiple orders

### On OrderDetail Page
- [ ] Full tracking card is visible
- [ ] Current tracking message is displayed
- [ ] Last updated time is shown
- [ ] Courier company name appears (after assignment)
- [ ] Current facility location appears (after first scan)
- [ ] All order details are displayed correctly
- [ ] Back button returns to MyPurchases

### Tracking Updates Flow
- [ ] No tracking before logistics confirms
- [ ] "Parcel received by logistics" after confirmation
- [ ] "Parcel assigned to [Courier]" after assignment
- [ ] "Accepted by courier..." after acceptance
- [ ] "Parcel scanned at [Facility]..." for each scan
- [ ] Facility type shows correctly (Main Hub, Sorting Hub, Branch)
- [ ] City shows in tracking message

---

## What Buyers Can Track

✅ When parcel was received by logistics
✅ Which courier company has their parcel
✅ When courier accepted the shipment
✅ Which facility the parcel is currently at
✅ Facility type (Main Hub, Sorting Hub, or Branch)
✅ City/location of current facility
✅ Timestamp of each tracking update
✅ Complete order details and delivery address

---

## Mobile Responsive

All tracking displays are mobile-friendly:
- Tracking boxes wrap properly on small screens
- Text is readable at all screen sizes
- Icons scale appropriately
- Touch-friendly buttons and links
