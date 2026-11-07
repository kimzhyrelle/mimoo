# Buyer Address Parsing Fix

## Problem
Yung buyer destination details sa courier's shipment details ay lahat **"Not provided"** kahit may address yung order.

### Before:
```
City: Not provided
Barangay: Not provided
Province: Not provided
Region: Not provided
```

## Root Cause
Ang IncomingShipmentsController ay nag-rely sa non-existent columns:
- `orders.shipping_municipality_city` ❌
- `orders.shipping_barangay` ❌
- `orders.shipping_province` ❌
- `orders.shipping_region` ❌

But yung actual address ay naka-store sa **`orders.shipping_address`** as full text string:
```
"092 Gen Luna St., Maracta (Pob.), Lumban, Laguna"
```

## Solution
Parse the `shipping_address` string to extract city, barangay, province, region.

### Updated Logic (IncomingShipmentsController.php)

**For show() method - Detailed view:**
```php
// Parse shipping address to extract components
$address = $order->shipping_address;
$parts = array_map('trim', explode(',', $address));

// Format: Street, Barangay, City, Province/Region
// Example: "092 Gen Luna St., Maracta (Pob.), Lumban, Laguna"

if (count($parts) >= 4) {
    // Extract barangay (usually 2nd part)
    $barangay = preg_replace('/^(Brgy\.?|Barangay)\s+/i', '', $parts[1]);
    
    // Extract city (second to last part)
    $city = preg_replace('/^(City of|Municipality of)\s+/i', '', $parts[count($parts) - 2]);
    
    // Extract province/region (last part)
    $provinceOrRegion = $parts[count($parts) - 1];
    
    if (preg_match('/(Region|NCR|CALABARZON|Metro Manila)/i', $provinceOrRegion)) {
        $region = $provinceOrRegion;
    } else {
        $province = $provinceOrRegion;
    }
}
```

**For index() method - List view:**
```php
// Show "City, Barangay" format
if (count($parts) >= 3) {
    $barangay = preg_replace('/^(Brgy\.?|Barangay)\s+/i', '', $parts[1]);
    $city = preg_replace('/^(City of|Municipality of)\s+/i', '', $parts[count($parts) - 2]);
    
    $destination = "$city, $barangay"; // "Lumban, Maracta (Pob.)"
}
```

## Test Results

### Sample Address:
```
"092 Gen Luna St., Maracta (Pob.), Lumban, Laguna"
```

### Parsed Output:
```
✅ City: Lumban
✅ Barangay: Maracta (Pob.)
✅ Province: Laguna
✅ Region: Not provided (correct - wala nga region sa address)
```

### In List View:
```
Destination: Lumban, Maracta (Pob.)
```

## Security & Privacy Note

Ang courier ay **limited info lang** ang nakikita:
- ✅ City
- ✅ Barangay
- ✅ Province
- ✅ Region

**HINDI nila makikita:**
- ❌ Full street address
- ❌ Buyer name
- ❌ Buyer phone
- ❌ Complete address

Ang full details ay available lang **AFTER acceptance** (future feature).

## Files Modified
- `app/Http/Controllers/Courier/IncomingShipmentsController.php`
  - Updated `show()` method - detailed destination parsing
  - Updated `index()` method - list destination parsing

## Date Fixed
November 7, 2026

---

## Quick Test

Login as Kimmi Courier → View shipment details:
```
Before: All "Not provided"
After:  City: Lumban
        Barangay: Maracta (Pob.)
        Province: Laguna
```

✅ Working na! 🎉
