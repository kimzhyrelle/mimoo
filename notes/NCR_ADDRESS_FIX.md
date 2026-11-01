# NCR Address Dropdown Fix

## Problem
NCR (National Capital Region) has no provinces - it contains cities directly. The previous implementation required selecting a province before selecting a city, which blocked NCR users from proceeding.

## Solution Applied

### Files Updated:
1. ✅ **Checkout.tsx** - Added region dropdown & NCR handling
2. ✅ **Register.tsx** - Fixed NCR province bypass
3. ✅ **SellerRegistration.tsx** - Fixed NCR province bypass

### Changes Made:

#### 1. Added Region Types
```typescript
interface Region {
  code: string;
  name: string;
}
```

#### 2. Added `isNCR` Detection
```typescript
const isNCR = data.region === "National Capital Region (NCR)";
```

#### 3. Conditional Province Loading
- **For NCR**: Load cities directly from region API endpoint
- **For Other Regions**: Load provinces first, then cities from province

#### 4. Conditional Province Field Rendering
```tsx
{!isNCR && (
  <Field label="Province" htmlFor="province" required>
    {/* Province dropdown only shown when NOT NCR */}
  </Field>
)}
```

#### 5. Smart City Dropdown Logic
- **Disabled when**: 
  - NCR: No region selected
  - Others: No province selected
- **Placeholder text**: Adapts based on NCR or not

#### 6. Validation Update
```typescript
if (step === 2) {
  const isNCR = d.region === "National Capital Region (NCR)";
  if (!d.region) e.region = 'Select a region.';
  if (!isNCR && !d.province) e.province = 'Select a province.'; // Skip for NCR
  // ... other validations
}
```

## How It Works Now:

### For NCR Users:
1. Select **Region** → "National Capital Region (NCR)"
2. **Province field disappears** ✨
3. Select **City** → (Quezon City, Manila, Makati, etc.)
4. Select **Barangay**
5. Enter **Street Address**

### For Other Regions:
1. Select **Region** → (e.g., Calabarzon, Central Luzon)
2. Select **Province** → (appears after region selection)
3. Select **City** → (loads from province)
4. Select **Barangay**
5. Enter **Street Address**

## API Endpoints Used:
- Regions: `https://psgc.gitlab.io/api/regions/`
- Provinces by Region: `https://psgc.gitlab.io/api/regions/{code}/provinces/`
- Cities by Region (NCR): `https://psgc.gitlab.io/api/regions/{code}/cities-municipalities/`
- Cities by Province: `https://psgc.gitlab.io/api/provinces/{code}/cities-municipalities/`
- Barangays by City: `https://psgc.gitlab.io/api/cities-municipalities/{code}/barangays/`

## Note on LogisticsRegistration.tsx
This file was **NOT modified** because it uses a different approach:
- Loads all provinces directly (no region dropdown)
- Works fine for all regions including NCR since provinces are universal

## Testing Checklist:
- [ ] NCR users can select cities without province
- [ ] Non-NCR users must select province before city
- [ ] Province field hides when NCR is selected
- [ ] Province field shows when switching from NCR to another region
- [ ] Validation allows submission for NCR without province
- [ ] Address is formatted correctly for both NCR and non-NCR

## Date: October 16, 2026
