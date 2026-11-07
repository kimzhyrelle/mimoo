# PHASE 7 TESTING INSTRUCTIONS

## Step 1: Register New Courier

Go to: http://localhost:8000/register/courier

Fill in with these EXACT, DISTINCT values:

### Step 1: Company Information
- **Company Name:** TestCourier Express LLC
- **Trade Name:** TC Express
- **Company Logo:** [Upload any test image]
- **Registration Number:** SEC-TEST-2026-999
- **Business Address:** 456 Test Avenue, Building C, Unit 12, Barangay Testing, Pasig City, Metro Manila 1600
- **Business Email:** business@testcourier.ph
- **Contact Number:** 09171234567
- **Website:** https://testcourier.example.com

### Step 2: Authorized Representative
- **Full Name:** Maria Clara Reyes Santos
- **Position:** Chief Operations Officer
- **Email:** mreyes@testcourier.ph
- **Contact Number:** +639189876543
- **Username:** mcsantos_test
- **Password:** TestPass2024!@#
- **Confirm Password:** TestPass2024!@#

### Step 3: Documents
- **DTI/SEC Certificate:** [Upload test PDF]
- **Mayor's Permit:** [Upload test PDF]
- **Operating Permits:** [Upload test PDF]
- **Valid ID:** [Upload test PDF]
- **Proof of Address:** [Upload test PDF]

### Step 4: Service Coverage
- **Regions:** National Capital Region (NCR), Region IV-A (CALABARZON)
- **Provinces:** Rizal, Laguna
- **Cities:** 
  - From dropdown: Quezon City, Pasig, Makati
  - Custom with comma: "Taguig, BGC Area"
- **Delivery Services:** Same-Day Delivery, Next-Day Delivery, Standard Delivery
- **Pickup Available:** Yes
- **Estimated Delivery Time:** Same day for Metro Manila, 1-2 days for nearby provinces
- **Privacy Policy:** [Check]

Click **Submit Application**

Expected: Redirects to /register/pending

---

## Step 2: Verify Database

Run this after successful registration:

```php
php artisan tinker --execute="
\$user = DB::table('users')->where('email', 'mreyes@testcourier.ph')->first();
echo json_encode(\$user, JSON_PRETTY_PRINT);
"
```

Expected: All fields populated, NO NULL for required fields, arrays stored as JSON

---

## Step 3: Verify Model Cast

```php
php artisan tinker --execute="
\$user = App\Models\User::where('email', 'mreyes@testcourier.ph')->first();
echo json_encode(\$user->toArray(), JSON_PRETTY_PRINT);
"
```

Expected: regions_served, provinces_served, cities_served, delivery_services all returned as ARRAYS

---

## Step 4: Verify JSON Response

1. Login as logistics_staff user
2. Go to: http://localhost:8000/logistics/courier-applications
3. Click "View Details" on the test courier
4. Check browser DevTools Network tab for the JSON response

Expected: All fields present, arrays are arrays (not strings)

---

## Step 5: Verify View Details Screen

Check these fields are displayed correctly:
- ✅ Business Email: business@testcourier.ph
- ✅ Company Contact: 09171234567
- ✅ Rep Contact: +639189876543
- ✅ Username: mcsantos_test
- ✅ Full Name: Maria Clara Reyes Santos (NOT split)
- ✅ Business Address: Full address (not old street_address)
- ✅ Regions: NCR, Region IV-A (as separate items)
- ✅ Provinces: Rizal, Laguna (as separate items)
- ✅ Cities: Quezon City, Pasig, Makati, "Taguig, BGC Area" (comma preserved!)
- ✅ Delivery Services: Same-Day, Next-Day, Standard (as separate items)
- ✅ Operating Permits: File path shown

---

## Step 6: Comparison Table

| Field | What I Typed | Raw DB | Model Array | JSON | View Details |
|---|---|---|---|---|---|
| business_email | business@testcourier.ph | | | | |
| company_contact_number | 09171234567 | | | | |
| rep_contact_number | +639189876543 | | | | |
| username | mcsantos_test | | | | |
| rep_full_name | Maria Clara Reyes Santos | | | | |
| business_address | 456 Test Avenue... | | | | |
| regions_served | NCR, Region IV-A | | | | |
| provinces_served | Rizal, Laguna | | | | |
| cities_served | QC, Pasig, Makati, "Taguig, BGC" | | | | |
| delivery_services | Same-Day, Next-Day, Standard | | | | |
| operating_permits | [file] | | | | |

ALL ROWS MUST MATCH EXACTLY.

---

## Step 7: Old Record Test

Open View Details for user ID 10, 11, 12, etc.

Expected:
- ✅ Page loads without crash
- ✅ Missing fields show "Not provided"
- ✅ NO fake data (no 'male', no '1990-01-01', no 'NCR')
- ✅ service_areas_legacy shown if present

---

## Step 8: Failed Submit Test

Try to register again with duplicate email: mreyes@testcourier.ph

Expected:
- ✅ Returns 422
- ✅ Error shown inline under email field: "This email is already registered"
- ✅ Does NOT redirect to /register/pending
- ✅ Stays on Step 2 with error highlighted

---

## Step 9: Verify These Still Work

Test each page loads without errors:

1. ✅ Login with test courier: mreyes@testcourier.ph / TestPass2024!@#
   - Should redirect to /register/pending (status is pending)
   
2. ✅ Approve the courier (as logistics_staff)
   - Go to Courier Applications
   - Click Approve
   
3. ✅ Login again as approved courier
   - Should redirect to /courier/dashboard
   
4. ✅ Go to /logistics/incoming-parcels (as logistics_staff)
   - Page loads
   
5. ✅ Go to Sort & Assign
   - Test courier appears in dropdown
   - service_areas compatibility check

---

## FINAL CHECKLIST

- [ ] New registration completes successfully
- [ ] All 27 fields saved correctly (zero NULL, zero fake values)
- [ ] Arrays stored as JSON in DB
- [ ] Model cast returns arrays
- [ ] JSON response has arrays
- [ ] View Details displays all fields correctly
- [ ] Custom entry with comma preserved: "Taguig, BGC Area"
- [ ] Old records don't crash
- [ ] Duplicate email returns 422 inline error (NOT Pending)
- [ ] Login works
- [ ] Pending page works
- [ ] Incoming Shipments works
- [ ] Sort & Assign compatibility works
