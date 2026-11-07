# 🚀 QUICK START - Courier Registration Testing

**Status:** ✅ All implementation complete, ready for testing

---

## 1️⃣ VERIFY SYSTEM IS READY

```bash
# Check all 10 new database columns exist
php check_courier_fields.php

# Check build is successful
npm run build
```

Expected: ✅ All green, no errors

---

## 2️⃣ REGISTER NEW COURIER

**URL:** http://localhost:8000/register/courier

**Use these EXACT values:**

### Step 1: Company Info
```
Company Name: TestCourier Express LLC
Trade Name: TC Express
Registration Number: SEC-TEST-2026-999
Business Address: 456 Test Avenue, Building C, Unit 12
Business Email: business@testcourier.ph
Contact Number: 09171234567
Website: https://testcourier.example.com
```

### Step 2: Representative
```
Full Name: Maria Clara Reyes Santos
Position: Chief Operations Officer
Email: mreyes@testcourier.ph
Contact Number: +639189876543
Username: mcsantos_test
Password: TestPass2024!@#
```

### Step 3: Documents
Upload 5 test PDFs (any test files)

### Step 4: Service Coverage
```
Regions: NCR, Region IV-A (CALABARZON)
Provinces: Rizal, Laguna
Cities: Quezon City, Pasig, Makati, "Taguig, BGC Area" ← MUST include this!
Delivery Services: Same-Day, Next-Day, Standard (all 3)
Pickup: Yes
Delivery Time: Same day for Metro Manila
```

**Expected:** Redirects to `/register/pending`

---

## 3️⃣ VERIFY DATA SAVED CORRECTLY

```bash
# Run automated verification
php test_courier_data.php mreyes@testcourier.ph
```

**Check output shows:**
- ✅ All fields populated (no NULL for required fields)
- ✅ Arrays are type "array" (not "string")
- ✅ "Taguig, BGC Area" appears as ONE item with comma preserved
- ✅ All 3 delivery services saved

---

## 4️⃣ VERIFY OLD RECORDS DON'T CRASH

1. Login as logistics_staff
2. Go to: http://localhost:8000/logistics/courier-applications
3. Click "View Details" on courier ID 10, 11, 12, etc.

**Expected:**
- ✅ Page loads without errors
- ✅ Shows "Not provided" for missing fields
- ✅ NO fake data (no 'male', no '1990-01-01')

---

## 5️⃣ VERIFY DUPLICATE EMAIL ERROR

Try to register again with email: `mreyes@testcourier.ph`

**Expected:**
- ✅ Shows inline error: "This email is already registered"
- ✅ Does NOT go to /register/pending
- ✅ Stays on Step 2

---

## 6️⃣ VERIFY PAGES STILL WORK

- [ ] Login with new courier
- [ ] Pending page shows
- [ ] Approve the courier (as logistics_staff)
- [ ] Login again → goes to courier dashboard
- [ ] Incoming Shipments loads
- [ ] Sort & Assign shows test courier

---

## ✅ ALL TESTS PASS?

If all 6 tests pass, the implementation is complete!

---

## 🔍 COMPARISON TABLE (Fill this in)

| Field | What I Typed | Database | Model | Display |
|---|---|---|---|---|
| business_email | business@testcourier.ph | | | |
| company_contact_number | 09171234567 | | | |
| rep_contact_number | +639189876543 | | | |
| username | mcsantos_test | | | |
| rep_full_name | Maria Clara Reyes Santos | | | |
| regions_served | NCR, Region IV-A | | | |
| cities_served | QC, Pasig, Makati, "Taguig, BGC Area" | | | |
| delivery_services | Same-Day, Next-Day, Standard | | | |

**ALL ROWS MUST MATCH EXACTLY**

---

## 📁 FULL DOCUMENTATION

- `READY_FOR_TESTING.md` - Complete testing checklist
- `FINAL_REPORT.md` - Comprehensive summary
- `test_registration.md` - Detailed testing steps

---

## 🆘 IF SOMETHING FAILS

1. Check the browser console for errors
2. Check Laravel logs: `storage/logs/laravel.log`
3. Run the test script: `php test_courier_data.php mreyes@testcourier.ph`
4. Report which test failed and what the error was

---

**Ready to test!** Start with Test 1 (verify system) then Test 2 (register).
