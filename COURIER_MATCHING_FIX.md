# Courier Matching Fix - Municipality/City Level

## Problem
Ang courier dropdown sa Sort & Assign ay dapat mag-match based sa **municipality/city** level, hindi barangay-specific. Kailangan kung saklaw ng courier yung city (e.g., "Lumban"), dapat eligible sila kahit anong barangay sa loob ng city na yun.

## What Was Wrong (Before)
- Matching was done at **barangay level** ❌
- Example: Parcel destination = "Brgy. Maracta, Lumban"
- Kimmi service areas include "Lumban" pero di nag-mmatch kasi nag-hahanap ng "Maracta" (barangay)
- Result: Hindi lumalabas si Kimmi kahit covered naman niya yung Lumban

## What I Fixed (Now)
Changed matching to **municipality/city level** ✅

### Backend Changes (routes/web.php)
1. **Extract both barangay AND city** from shipping address
2. **Pass city to frontend** for proper matching

```php
// Extract city/municipality (usually the second-to-last or last part before region)
$addressParts = array_map('trim', explode(',', $address));
if (count($addressParts) >= 2) {
    // Format: Street, Barangay, City, Province/Region
    $city = $addressParts[count($addressParts) - 2] ?? 'Unknown';
    
    // Clean up: Remove "City of" or "Municipality of"
    $city = preg_replace('/^(City of|Municipality of)\s+/i', '', $city);
    $city = trim($city);
}

return [
    // ... other fields
    'barangay' => $barangay,
    'city' => $city,  // ✅ NEW: Add city for matching
];
```

### Frontend Changes (SortAssign.tsx)

1. **Updated ParcelData interface**:
```typescript
interface ParcelData {
    // ... other fields
    barangay: string;
    city: string; // ✅ NEW: Municipality/City for courier matching
}
```

2. **Updated matching logic**:
```typescript
const getEligibleCouriers = () => {
    if (selectedParcels.length === 0) {
        return couriers; // Show all if no parcels selected
    }

    // Get cities/municipalities of selected parcels (NOT barangays)
    const selectedCities = new Set<string>();
    parcels.forEach(parcel => {
        if (selectedParcels.includes(parcel.id)) {
            selectedCities.add(parcel.city); // ✅ Use city, not barangay
        }
    });

    // Filter couriers by city/municipality coverage
    const eligible = couriers.filter(courier => {
        if (!courier.service_areas || courier.service_areas.length === 0) {
            return true; // No restrictions = covers everywhere
        }

        // Match by city/municipality (case-insensitive, flexible)
        return Array.from(selectedCities).some(city => 
            courier.service_areas.some(area => {
                const areaLower = area.toLowerCase();
                const cityLower = city.toLowerCase();
                
                // Flexible matching:
                // - "City of Calamba" matches "Calamba"
                // - "Santa Cruz" matches "City of Santa Cruz"
                return areaLower.includes(cityLower) || cityLower.includes(areaLower);
            })
        );
    });

    return eligible;
};
```

## How It Works Now

### Example 1: Parcel to Lumban
**Address**: "092 Gen Luna St., Maracta (Pob.), Lumban, Laguna"

**Extraction**:
- Barangay: "Maracta (Pob.)"
- City: **"Lumban"** ✅

**Courier Matching**:
- Kimmi service areas include "Lumban" → ✅ **MATCH**
- Zhy has no service areas → ✅ **MATCH** (covers everywhere)
- **Result**: Both couriers appear in dropdown

### Example 2: Parcel to Calamba
**Address**: "123 Main St., Brgy. Batino, City of Calamba, Laguna"

**Extraction**:
- Barangay: "Batino"
- City: **"Calamba"** (cleaned from "City of Calamba") ✅

**Courier Matching**:
- Kimmi service areas include "City of Calamba" → ✅ **MATCH** ("Calamba" matches "City of Calamba")
- Zhy has no service areas → ✅ **MATCH**
- **Result**: Both couriers appear

### Example 3: Parcel to Unknown/Unsupported City
**Address**: "456 Road, Brgy. Test, Davao City, Davao del Sur"

**Extraction**:
- Barangay: "Test"
- City: **"Davao City"** ✅

**Courier Matching**:
- Kimmi service areas = Laguna/NCR only → ❌ **NO MATCH**
- Zhy has no service areas → ✅ **MATCH** (covers everywhere)
- **Result**: Only Zhy appears (correct behavior!)

## Benefits
✅ **Flexible matching** - "City of Calamba" matches "Calamba", etc.
✅ **Municipality level** - All barangays within covered city are eligible
✅ **Fallback support** - Couriers without service_areas cover everywhere
✅ **Case-insensitive** - Works with any capitalization

## Testing
```bash
php test-city-matching.php
```

**Result**:
```
Sample Order: Lumban address
Extracted City: Lumban
Kimmi Service Areas: includes "Lumban"
✅ MATCH: 'Lumban' <=> 'Lumban'
```

## Date Fixed
November 7, 2026

## Files Modified
1. `routes/web.php` - Added city extraction and parsing
2. `resources/js/pages/logistics/SortAssign.tsx` - Updated matching logic to use city instead of barangay
