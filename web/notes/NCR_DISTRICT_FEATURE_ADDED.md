# NCR District Field Implementation

## Overview
Added support for NCR (National Capital Region) district selection in all registration forms. NCR has a unique administrative structure with 4 legislative districts instead of provinces.

## Address Structure Comparison

### NCR (National Capital Region)
```
Region → District → City → Barangay → Street
```
- **Region**: National Capital Region (NCR)
- **Province**: N/A (auto-set)
- **District**: 1st-4th District (NEW FIELD)
- **City**: Manila, Quezon City, etc.
- **Barangay**: Barangay list
- **Street**: User input

### Other Regions
```
Region → Province → Municipality/City → Barangay → Street
```
- **Region**: e.g., Region I - Ilocos Region
- **Province**: e.g., Pangasinan
- **District**: Not applicable
- **City**: Municipality or City
- **Barangay**: Barangay list
- **Street**: User input

## NCR Districts

1. **1st District (Manila)** - Covers Manila city proper
2. **2nd District (Marikina, Pasig, Quezon City - East)** - Eastern Metro Manila
3. **3rd District (Caloocan, Malabon, Navotas, Valenzuela)** - Northern Metro Manila
4. **4th District (Las Piñas, Muntinlupa, Parañaque, Pasay, Taguig)** - Southern Metro Manila

## Changes Made

### 1. Database Migration
**File**: `database/migrations/2026_10_16_135532_add_district_to_users_table.php`
- Added `district` column (nullable string) after `province` column
- Migration executed successfully

### 2. Backend - User Model
**File**: `app/Models/User.php`
- Added `district` property to PHPDoc
- Added `district` to fillable array

### 3. Backend - User Registration
**File**: `app/Actions/Fortify/CreateNewUser.php`
- Added `district` validation rule (nullable)
- Added `district` to user creation data

### 4. Frontend - Buyer Registration
**File**: `resources/js/pages/Register.tsx`

**Changes**:
- Added `NCR_NAME` constant
- Added `NCR_DISTRICTS` array with 4 district options
- Added `district` field to `FormShape` type
- Added `district` to `FIELD_STEP` and `FIELD_LABEL` mappings
- Added district validation for NCR in step 2
- Added `district: ''` to form initial data
- Updated region onChange to reset district
- Added conditional district dropdown (shows only for NCR)

**UI Behavior**:
- District field appears only when NCR is selected
- Province field shows "N/A" and is disabled for NCR
- District is required when NCR is selected
- District resets when region changes

### 5. Frontend - Seller Registration
**File**: `resources/js/pages/seller/SellerRegistration.tsx`

**Changes**: Same as buyer registration
- Added NCR constants
- Added district to form type and mappings
- Added district validation
- Added conditional district field in Step 2
- Updated region onChange handler

### 6. Frontend - Logistics Registration
**File**: `resources/js/pages/logistics/LogisticsRegistration.tsx`

**Changes**: 
- Previously only loaded provinces directly
- **Updated to full region → province → city flow**
- Added regions state and loading
- Added NCR constants and district support
- Added Region field as first input in Step 2
- Added conditional District field
- Updated all address field logic to match other registration forms

**Major Refactor**: Changed from province-first to region-first flow to support NCR properly.

## Form Flow

### When User Selects NCR:
1. User selects "National Capital Region (NCR)" from Region dropdown
2. Province field auto-fills with "N/A" and becomes disabled
3. **District field appears** with 4 options
4. User selects district (1st-4th)
5. City dropdown loads NCR cities
6. User continues with barangay and street

### When User Selects Other Region:
1. User selects any non-NCR region
2. Province dropdown loads provinces for that region
3. District field **does not appear**
4. User selects province
5. Municipality/City dropdown loads
6. User continues with barangay and street

## Technical Implementation Details

### Validation Logic
```typescript
if (step === 2) {
    const isNCR = d.region === NCR_NAME;
    if (!d.region) e.region = 'Select a region.';
    if (!isNCR && !d.province) e.province = 'Select a province.';
    if (isNCR && !d.district) e.district = 'Select a district.'; // NEW
    if (!d.municipality_city) e.municipality_city = 'Select a municipality or city.';
    if (!d.barangay) e.barangay = 'Select a barangay.';
    if (!d.street_address.trim()) e.street_address = 'Enter your street and house number.';
}
```

### Data Reset Logic
When region changes:
```typescript
onChange={(e) => {
    const value = e.target.value;
    setData({
        ...data,
        region: value,
        province: value === NCR_NAME ? 'N/A' : '',
        district: '', // Reset district
        municipality_city: '',
        barangay: ''
    });
    setCities([]);
    setClientErrors((c) => ({ ...c, region: undefined, province: undefined, district: undefined }));
}}
```

## Testing Checklist

- [x] Database migration runs successfully
- [x] No TypeScript errors in all registration files
- [ ] Test buyer registration with NCR address
- [ ] Test buyer registration with non-NCR address
- [ ] Test seller registration with NCR address
- [ ] Test seller registration with non-NCR address
- [ ] Test logistics registration with NCR address
- [ ] Test logistics registration with non-NCR address
- [ ] Verify district field only shows for NCR
- [ ] Verify district is required for NCR
- [ ] Verify district is saved to database
- [ ] Verify province auto-fills to "N/A" for NCR
- [ ] Test form validation with and without district

## Notes

- District field is **nullable** in database (only NCR users will have values)
- For non-NCR users, district will be `null` or empty string
- Province field for NCR users is set to `'N/A'` to satisfy backend validation
- All three registration forms (Buyer, Seller, Logistics) now have consistent address field logic

## Future Enhancements

- Consider adding district-based city filtering (some cities belong to specific districts)
- Add district information to user profile display
- Consider district-based search or filtering features
- Add district data to address display components (Checkout, Orders, etc.)
