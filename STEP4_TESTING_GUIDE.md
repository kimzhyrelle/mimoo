# Step 4 Service Coverage - Testing Guide

## 🎯 Quick Test Checklist

Use this guide to quickly verify that the redesigned Step 4 works correctly.

---

## ⚡ FAST AUTO-FILL TEST

1. Navigate to: `http://localhost/register/courier`
2. Scroll down and click the **"Auto-Fill (Testing)"** button
3. Click **"Continue"** three times to reach Step 4
4. **Verify you see:**
   - ✅ "Where you deliver" section header
   - ✅ "How you deliver" section header
   - ✅ Purple chips showing selected regions, provinces, cities
   - ✅ Purple chips showing selected delivery services
   - ✅ "Yes, we offer pickup" card is selected
   - ✅ Estimated delivery time is filled
5. Click **"Complete Registration"**
6. **Expected Result:** Redirected to `/register/pending` page

---

## 📝 MANUAL INPUT TEST

### Test 1: Regions → Provinces → Cities (Cascading)

1. Navigate to Step 4
2. **Click "Regions Served" input**
   - ✅ Dropdown opens with all 17 regions
   - ✅ "Select All" and "Clear" buttons visible
3. **Select "National Capital Region (NCR)"**
   - ✅ Purple chip appears with "National Capital Region (NCR)"
   - ✅ Shows "1 selected" badge
4. **Click "Provinces Served" input**
   - ✅ Dropdown should show only: "Metro Manila"
   - ✅ (Because NCR only has Metro Manila)
5. **Select "Metro Manila"**
   - ✅ Purple chip appears
6. **Click "Cities / Municipalities Served" input**
   - ✅ Dropdown should show only NCR cities: Caloocan, Las Piñas, Makati, Malabon, etc.
   - ✅ Should NOT show cities from other regions
7. **Select "Makati" and "Taguig"**
   - ✅ Two purple chips appear
   - ✅ Shows "2 selected" badge

### Test 2: Search Functionality

1. In "Regions Served", click the input
2. **Type "vis"** in the search box
   - ✅ Should filter to only: Western Visayas, Central Visayas, Eastern Visayas
   - ✅ Other regions should be hidden
3. **Clear the search**
   - ✅ All regions appear again

### Test 3: Select All & Clear

1. Click "Regions Served" input
2. Click **"Select All"**
   - ✅ All 17 regions added as chips
   - ✅ Badge shows "17 selected"
3. Click "Regions Served" input again
4. Click **"Clear"**
   - ✅ All chips removed
   - ✅ Badge disappears

### Test 4: Custom Entry

1. Click "Regions Served" input
2. Click **"+ Add custom / not listed"**
   - ✅ Input box appears
3. Type **"Custom Region Name"** and click **"Add"**
   - ✅ Purple chip appears with "Custom Region Name"
4. Click the **X** on the custom chip
   - ✅ Chip is removed

### Test 5: Delivery Services

1. **Click chips:** Same-Day Delivery, Next-Day Delivery, COD Service
   - ✅ Each clicked chip turns purple (selected)
   - ✅ Click again to deselect (turns gray)
2. Click **"+ Add other service"**
3. Type **"Pallet Shipping"** and click **"Add"**
   - ✅ Purple chip with lighter shade appears
   - ✅ Shows X button to remove

### Test 6: Pickup Availability (Radio Cards)

1. **Click "Yes, we offer pickup" card**
   - ✅ Card border turns purple
   - ✅ Radio dot fills in
2. **Click "No, drop-off only" card**
   - ✅ Previous card deselects
   - ✅ New card selects with purple border
3. **Click "Selected areas only" card**
   - ✅ Works the same way

### Test 7: Estimated Delivery Time

1. **Click "Same day" quick-pick chip**
   - ✅ Chip turns purple
   - ✅ Input box below auto-fills with "Same day"
2. **Click "3-5 days" quick-pick chip**
   - ✅ Input box updates to "3-5 days"
3. **Type manually:** "1-3 business days for standard, same day for express"
   - ✅ Text appears in input
   - ✅ All quick-pick chips turn gray (deselected)

---

## 🔄 DATA PERSISTENCE TEST

1. Complete Step 4 with some selections
2. Click **"Back"** to Step 3
3. Click **"Continue"** to return to Step 4
4. **Verify:**
   - ✅ All previously selected chips are still there
   - ✅ Custom entries are still there
   - ✅ Pickup selection is still there
   - ✅ Estimated delivery time is still there

---

## 📤 SUBMISSION & DISPLAY TEST

1. Complete all 4 steps
2. Click **"Complete Registration"**
3. **Expected:** Redirected to `/register/pending`
4. Open a new tab as admin
5. Navigate to **"Courier Applications"** page
6. **Verify the submitted data:**
   - ✅ Regions column shows comma-separated values (e.g., "NCR, Region III")
   - ✅ Provinces column shows comma-separated values
   - ✅ Cities column shows comma-separated values
   - ✅ Services column shows comma-separated values
   - ✅ Custom entries are included
   - ✅ All data is readable and correct

---

## 📱 MOBILE RESPONSIVE TEST

1. Open browser DevTools (F12)
2. Toggle device toolbar (Ctrl+Shift+M)
3. Select **"iPhone 12 Pro"** or similar
4. Navigate to Step 4
5. **Verify:**
   - ✅ Sections stack vertically
   - ✅ Chips wrap to multiple lines
   - ✅ Radio cards stack in single column on small screens
   - ✅ Dropdowns are easy to tap
   - ✅ Text is readable
   - ✅ No horizontal scrolling

---

## ❌ ERROR HANDLING TEST (After Re-enabling Validation)

1. Navigate to Step 4
2. Leave all required fields empty
3. Click **"Complete Registration"**
4. **Verify:**
   - ✅ Red error messages appear under empty required fields
   - ✅ Form does not submit
   - ✅ Page scrolls to first error
5. Fill one field at a time
6. **Verify:**
   - ✅ Error disappears when field is filled
   - ✅ Can only submit when all required fields are filled

---

## 🐛 KNOWN ISSUES TO WATCH FOR

### Issue 1: Cascading Filter Not Working
- **Symptom:** Selecting regions doesn't filter provinces
- **Check:** Console for JavaScript errors
- **Fix:** Verify `phLocations` data structure matches component expectations

### Issue 2: Chips Not Removable
- **Symptom:** Clicking X on chip doesn't remove it
- **Check:** Click handler is firing (add console.log)
- **Fix:** Verify `onChange` is updating form state

### Issue 3: Data Not Persisting on Back/Next
- **Symptom:** Selections disappear when navigating steps
- **Check:** Form state is not being cleared
- **Fix:** Verify `set()` function is updating Inertia form data

### Issue 4: Custom Entries Lost on Submit
- **Symptom:** Custom entries don't appear in Courier Applications
- **Check:** Console log the FormData before submit
- **Fix:** Verify `.join(', ')` is including custom entries

### Issue 5: Comma in Custom Entry Breaks Data
- **Symptom:** Custom entry "ABC, Inc." splits into two values
- **Check:** How custom entries are stored
- **Fix:** May need to escape commas or use different separator

---

## 📊 SUCCESS CRITERIA

All these must pass before considering Step 4 complete:

- ✅ Visual design matches requirements (sections, chips, cards)
- ✅ Cascading filters work (regions → provinces → cities)
- ✅ Search filtering works
- ✅ "Select All" and "Clear" buttons work
- ✅ Custom entries can be added and removed
- ✅ Service chips toggle correctly
- ✅ Radio cards select/deselect correctly
- ✅ Quick-pick delivery time works
- ✅ Data persists when navigating Back/Next
- ✅ Auto-fill populates all fields correctly
- ✅ Manual input works correctly
- ✅ Data submits successfully
- ✅ Data appears correctly in Courier Applications list
- ✅ Custom entries are included in submission
- ✅ Mobile layout is responsive
- ✅ Validation shows errors when re-enabled
- ✅ No console errors
- ✅ Build completes successfully

---

## 🚀 QUICK TEST SCRIPT

Copy and paste this into browser console for quick data verification:

```javascript
// After filling Step 4, run this in console:
console.log('=== STEP 4 DATA ===');
console.log('Regions:', document.querySelector('#regions_served')?.value);
console.log('Provinces:', document.querySelector('#provinces_served')?.value);
console.log('Cities:', document.querySelector('#cities_served')?.value);
console.log('Services:', document.querySelector('#delivery_services')?.value);
console.log('Pickup:', document.querySelector('#pickup_available')?.value);
console.log('Delivery Time:', document.querySelector('#estimated_delivery_time')?.value);
```

**Expected Output:**
- All values should be comma-separated strings
- Custom entries should be included
- No `undefined` or `null` values (except estimated_delivery_time if empty)

---

## 📞 SUPPORT

If tests fail:
1. Check browser console for errors
2. Verify `npm run build` completed successfully
3. Hard refresh browser (Ctrl+Shift+R)
4. Clear browser cache
5. Check `TASK3_STEP4_REDESIGN_SUMMARY.md` for implementation details

---

**Last Updated**: November 6, 2026  
**Version**: 1.0  
**Status**: Ready for Testing
