# Mimoo Rider App — Audit Report

**Date:** November 1, 2026  
**Auditor:** Kiro (automated read-only audit)  
**Project:** `mimoo_rider` — Flutter rider/courier app for the Mimoo/Catoo logistics platform  
**Branch:** `main` (1 commit: `14bdee6`, Oct 28 2026)  
**Headline:** App is ~70% complete. UI is fully built; the data layer has been migrated from mock-only to a real HTTP API client backed by a Node.js/Express + MySQL server. `flutter analyze` passes with zero issues and 8/8 tests pass. The main gaps are in rider registration (non-functional), proof-of-delivery (photo not uploaded, OTP not verified), and several settings/profile flows still using mock `Future.delayed` stubs.

---

## 1. Summary

**Overall completion:** ~70%

| Bucket | Count |
|--------|-------|
| COMPLETED (UI + real data, no obvious bugs) | 18 |
| PARTIAL (UI exists, mock/TODO data or missing logic) | 11 |
| NOT STARTED | 2 |
| FAILING (compile/runtime errors) | 0 |

**Top 5 risks:**

1. **Proof-of-delivery is not legally valid** — The live camera photo is never uploaded to the server; only a local file path string is sent (`api_repositories.dart:299`). The buyer's 6-digit OTP is never verified server-side — `otp_verified` is sent as a client-set boolean, so any 6 digits "pass" (`pod_flow.dart:38`, `server.js:317`). This undermines the core proof-of-delivery use case for a logistics app.

2. **Rider registration is non-functional** — The 4-step wizard looks complete but captures nothing: `MField` widgets are used without controllers (input is discarded), address dropdowns use `onChanged: (_) {}` (selections thrown away), and document "uploads" are boolean toggles with no `image_picker` integration. Submit just navigates to `/pending` with no API call (`registration.dart`).

3. **Unhandled API exceptions in delivery flow** — `JobOfferScreen._accept` (`job_offer_screen.dart:40-45`), `DeliveryDetailScreen._fail` (`delivery_detail.dart:68`), and `PodFlow._finish` (`pod_flow.dart:103-128`) have no try/catch around API calls. A server 409 (expired offer, max jobs reached, transition conflict) throws an unhandled `ApiException` → red error screen.

4. **Mock saves silently discard user data** — Profile save (`profile_screen.dart:99`), change password (`change_password_screen.dart:47`), passkey registration (`passkey_screen.dart:13`), help center report (`help_center_screen.dart:207`), and vehicle document update (`vehicle_documents_screen.dart:39`) all use `Future.delayed` mocks with `TODO(api)` instead of real API calls. Users see success feedback but changes are lost on restart.

5. **No SMS phone verification** — No SMS/OTP phone verification exists anywhere in the codebase. Rider identity verification for onboarding is entirely missing. The activation flow is link-based (set password via email link), not phone-based.

---

## 2. Recent Changes (from git)

**Single commit:** `14bdee6` "feat: initial commit of Mimoo Rider app" (Oct 28, 2026, by BeltranJ)

**Uncommitted working changes** (36 tracked files modified, 20 untracked files added):

| Category | Files |
|----------|-------|
| **New (untracked)** | `AGENTS.md`, `backend/` (Node.js API + MySQL), `l10n.yaml`, `lib/core/auth_controller.dart`, `lib/core/rider_status_controller.dart`, `lib/core/settings_controller.dart`, `lib/core/app_info.dart`, `lib/data/db/`, `lib/data/models/`, `lib/data/parcel_flow.dart`, `lib/data/repositories/` (full API layer), `lib/l10n/` (ARB + generated), `lib/screens/settings/` (6 screens), `lib/screens/profile_screen.dart`, `lib/screens/vehicle_documents_screen.dart`, `lib/screens/help_center_screen.dart`, `lib/screens/history_job_detail.dart`, `lib/widgets/biometric_gate.dart`, `lib/widgets/custody_timeline.dart`, `lib/widgets/mimoo_drawer.dart`, `test/data/local_repositories_test.dart` |
| **Modified** | All 28 original `lib/` files (screens, widgets, core, data), `pubspec.yaml`, `pubspec.lock`, `README.md`, `android/app/build.gradle.kts`, platform plugin registrants |
| **Deleted** | `lib/screens/account_tab.dart` (replaced by drawer destinations) |
| **Net diff** | +5,501 insertions, −1,490 deletions |

**Key architectural change in working tree:** The app evolved from mock-only (per the initial commit) to a real HTTP API client (`api_client.dart` + `flutter_secure_storage` for token persistence) backed by a Node.js/Express server with MySQL. `mock_data.dart` is now dead code — no file imports it.

---

## 3. Completed Features

| Feature | File(s) | Notes |
|---------|---------|-------|
| Login | `lib/screens/login.dart`, `lib/data/repositories/api_client.dart` | Real `POST /api/auth/login` via `ApiClient`; token in `flutter_secure_storage`; loading/error states; `mounted` checks |
| Logout | `lib/screens/settings/settings_screen.dart` | Clears secure storage token, calls `AuthScope.signOut()`, navigates to `/login` |
| Session handling | `lib/data/repositories/api_repositories.dart:73-82`, `lib/main.dart:77-81` | `restoreSession()` checks storage, calls `GET /api/bootstrap`; 401 clears token |
| Dashboard (Home) | `lib/screens/home_tab.dart` | Stats from API-backed `parcels` repo; active dispatch card; recent activity; empty states |
| Scan waybill | `lib/screens/scan_waybill_screen.dart` | `mobile_scanner` QR; tracking match; manual entry fallback; camera permission handling; real `transition` API |
| Out for delivery | `lib/screens/out_for_delivery_screen.dart` | Lifecycle stepper; real multi-step `transition` API; POD lock until marked; try/catch on mark-out |
| Delivery detail | `lib/screens/delivery_detail.dart` | Failed-delivery sheet with 3 reasons; real `transition` API; call/message actions |
| Delivered screen | `lib/screens/delivered.dart` | Reads `parcels.byTracking` + `codOnHand` from repo; empty-state handling |
| Earnings dashboard | `lib/screens/earnings_tab.dart` | Today/Week filters; pickup/delivery breakdowns; COD-on-hand; all from `parcels` repo |
| Delivery history | `lib/screens/history_tab.dart`, `lib/screens/history_job_detail.dart` | API-backed status logs; read-only detail screen; route validates `HistoryJob` arg |
| COD remittance | `lib/screens/cod_remittance_screen.dart` | Real `POST /api/remittances`; short/over/match validation; reference 3-80 chars; busy state |
| Notifications | `lib/screens/notifications_screen.dart` | API-backed list; `POST /api/notifications/read-all`; kind-specific icons; offline pill; empty state |
| Chat (data layer) | `lib/screens/chat.dart` | Real `POST /api/conversations/{id}/messages`; conversation list from repo; try/catch on send |
| Settings (main) | `lib/screens/settings/settings_screen.dart` | Language/appearance/nav/notifications persist via `SettingsController` + `SharedPreferences`; biometric toggle via `local_auth`; logout with confirm |
| Emergency contacts | `lib/screens/settings/emergency_contacts_screen.dart` | Real `POST/PUT/DELETE /api/emergency-contacts`; max 2; add/edit/delete; empty state; try/catch |
| Permissions screen | `lib/screens/settings/permissions_screen.dart` | Real `Permission.location/camera/notification` status; refreshes on resume; "Open Settings" deep-link |
| Legal screens | `lib/screens/settings/legal_screen.dart` | Generic renderer for Privacy/Terms from l10n |
| About screen | `lib/screens/settings/about_screen.dart` | App version, support email (`mailto:`), licenses, kDebugMode reset |
| Biometric lock | `lib/widgets/biometric_gate.dart` | Mounted in `MaterialApp.builder`; locks on cold start + foreground return; retry + passcode fallback |
| Location permission | `lib/screens/location_permission_sheet.dart` | Real `Permission.location.request()`; denied/permanently-denied handling; "Open Settings" |
| Navigation & theme | `lib/main.dart`, `lib/core/theme.dart`, `lib/widgets/shell.dart` | 33 routes; 5-tab `IndexedStack` shell; purple/coral theme via `MimooColors` ThemeExtension; light/dark |
| Approval/rejected display | `lib/screens/registration_decision_screens.dart` | Complete display screens; fed hardcoded demo data from `main.dart` |
| Suspended screen | `lib/screens/suspended_screen.dart` | Complete display; real `tel:`/`sms:` launches; fed hardcoded demo data |
| Connectivity handling | `lib/core/connectivity.dart` | `ConnectivityMonitor` + `ConnectivityBanner` offline detection |

---

## 4. Unfinished Features

| Feature | What's missing | File(s) | Effort |
|---------|---------------|---------|--------|
| Rider self-registration | Form captures nothing — `MField` without controllers, dropdowns use `onChanged: (_) {}`, uploads are boolean toggles, no `image_picker`. No API call on submit. No field validations (email, phone, plate). Only age check works. | `lib/screens/registration.dart` | L |
| Phone verification (SMS) | No SMS/OTP phone verification exists anywhere. Not started. | — (missing) | M |
| Proof of delivery (photo) | Photo is never uploaded — only local path string sent to API. No multipart/binary upload. | `lib/data/repositories/api_repositories.dart:297`, `lib/screens/pod_flow.dart:88,109` | M |
| Proof of delivery (OTP) | OTP only checks `length == 6`; any 6 digits pass. `otp_verified` sent as client boolean. No server-side OTP value check. No lockout (3-vs-5 tries inconsistency flagged in AGENTS.md). | `lib/screens/pod_flow.dart:279`, `lib/data/repositories/api_repositories.dart:297` | M |
| 3 declines → Unassigned | No decline-counting client- or server-side. `setState('declined')` just marks state. | `lib/data/repositories/api_repositories.dart:275`, `backend/src/server.js:242` | S |
| Activation (set password) | Password UI + validation excellent, but `onActivate` is a `TODO(api)` no-op — navigates to success without using the password. | `lib/main.dart:481-484` | S |
| Activation (link expired) | `onRequestResend` is a 400ms mock `Future.delayed` with `TODO(api)`. | `lib/main.dart:494-497` | S |
| Profile save | `_save()` uses `Future.delayed(500ms)` mock instead of available `repositories.riderProfile.saveProfile()` API. Avatar not uploaded. Identity fields hardcoded. | `lib/screens/profile_screen.dart:86,99` | M |
| Change password | Strong validation UI, but `_submit()` is a mock `Future.delayed` with `TODO(api)`. | `lib/screens/settings/change_password_screen.dart:47` | S |
| Passkey | Local biometric placeholder only, not real WebAuthn. Create/remove are `TODO(api)`. | `lib/screens/settings/passkey_screen.dart:13,49`, `lib/core/settings_controller.dart:174` | M |
| Vehicle documents | Display-only with hardcoded data. "Update" button is a no-op SnackBar with `TODO(api)`. | `lib/screens/vehicle_documents_screen.dart:39` | S |
| Help center (report) | FAQ + call desk complete. "Report a Problem" uses mock `Future.delayed` with `TODO(api)`. Screenshot not uploaded. | `lib/screens/help_center_screen.dart:207` | S |
| Chat thread header | Thread header is hardcoded (`'Juan Miguel D.'`, `'TRK-0012345'`), ignores the selected conversation. Search field is decorative (no controller/onChanged). | `lib/screens/chat.dart:214,233` | S |
| History "Custom" filter | "Custom" range filter has no date picker — returns all logs. | `lib/screens/history_tab.dart` | S |
| Home header | Rider name `'Pedro Jose Garcia'` and fleet/hub hardcoded, though `MimooDrawer` reads them dynamically from `riderProfile.profile`. | `lib/screens/home_tab.dart:42-47` | S |
| Pickup detail data | `pay: 30`, `km: 2.4`, `etaMins: 15` hardcoded in route builder. `merchantId` set to tracking number (bug). `phone` uses buyer phone as seller contact (bug). | `lib/main.dart:340-347` | S |
| Delivery navigate data | Navigate uses hardcoded `etaMins: 12`, `km: 4.2`. | `lib/screens/delivery_detail.dart:286-287` | S |
| Go online/offline TODOs | `goOnline()` has a `Future.delayed(250ms)` with `TODO(api)` before the real `setCheckedIn` call. `goOffline()` has `TODO(api)` comment (but does call the real API). | `lib/core/rider_status_controller.dart:115,126` | S |
| Firebase push | `google-services.json`/`GoogleService-Info.plist` not present. FCM catches the error gracefully. `TODO(firebase)` in code. | `lib/core/job_offer_notifications.dart:45` | M |
| Failed delivery flow (client) | The delivery_detail `_fail` button calls the real API, but has no try/catch. The `if (!changed)` "can no longer be marked as failed" branch is dead code (API throws instead of returning false). | `lib/screens/delivery_detail.dart:68-77` | S |
| Job offer accept error handling | `_accept()` has no try/catch. `OfferResult.taken` only returned on `!context.mounted`, so the "Already assigned" card is unreachable. | `lib/screens/job_offer_screen.dart:40-45`, `lib/main.dart:311` | S |
| Out-for-delivery `_done` gate | `_done = widget.parcel.status != ParcelStatus.assigned` should be `== ParcelStatus.outForDelivery`. Currently works because screen is entered at `assigned`, but would wrongly unlock POD on re-entry at `atHub`. | `lib/screens/out_for_delivery_screen.dart:38` | S |

---

## 5. Failures and Bugs

| Severity | Issue | File:line | Suggested fix |
|----------|-------|-----------|---------------|
| **High** | POD photo not uploaded — only local path string sent. Delivery proof is not actual proof. | `api_repositories.dart:297-307`, `pod_flow.dart:88,109` | Implement multipart image upload; send photo bytes to server, store path. |
| **High** | OTP not verified — `otp_verified` is a client-set boolean. Any 6 digits pass. No server-side check. | `pod_flow.dart:38,279`, `server.js:317` | Send OTP digits to server; verify against stored code; implement lockout after N wrong tries. |
| **High** | Registration captures nothing — `MField` without controllers, dropdowns `onChanged: (_) {}`, uploads are boolean toggles. | `registration.dart` (all steps) | Add `TextEditingController` to each field; wire dropdown `onChanged` to state; integrate `image_picker` for uploads; add `POST /api/auth/register` endpoint. |
| **Med** | Unhandled `ApiException` in `_accept()` — server 409 (expired/max jobs) → red screen. | `job_offer_screen.dart:40-45` | Wrap in try/catch; show SnackBar on 409; handle `OfferResult.taken` properly. |
| **Med** | Unhandled `ApiException` in `_fail()` — server 409 (bad transition) → red screen. | `delivery_detail.dart:68` | Wrap in try/catch; show SnackBar on error. |
| **Med** | Unhandled `ApiException` in `_finish()` — server 409 (bad state/COD mismatch) → red screen. | `pod_flow.dart:103-128` | Add catch block; show SnackBar on error; handle stale state. |
| **Med** | `merchantId` set to tracking number (wrong data). | `main.dart:340` | Use real merchant ID from parcel data or add field to `Parcel` model. |
| **Med** | Seller contact phone uses buyer phone (wrong data). | `main.dart:343` | Add `sellerPhone` field to `Parcel` model and seed data. |
| **Med** | Chat thread header hardcoded — ignores selected conversation. | `chat.dart:214,233` | Pass conversation title/banner from the selected conversation. |
| **Med** | Profile save is a mock — user changes lost on restart despite available `saveProfile()` API. | `profile_screen.dart:99` | Replace `Future.delayed` with `repositories.riderProfile.saveProfile()`. |
| **Med** | Change password is a mock — password not actually changed. | `change_password_screen.dart:47` | Implement `PATCH /api/auth/password` endpoint and call it. |
| **Med** | Passkey is a local biometric placeholder, not real WebAuthn. | `passkey_screen.dart:13,49` | Implement WebAuthn registration/assertion with server ceremony. |
| **Low** | Dead `if (!changed)` branches — API always returns `true`; server rejections throw instead. | `delivery_detail.dart:73-77`, `pod_flow.dart:118-121` | Either catch the thrown exception (preferred) or make the API return false on soft failures. |
| **Low** | `OfferResult.taken` unreachable — only returned on `!context.mounted`. | `main.dart:311` | Check for 409 "already accepted" response and return `OfferResult.taken`. |
| **Low** | `_done` gate fragile — `!= assigned` should be `== outForDelivery`. | `out_for_delivery_screen.dart:38` | Change to `widget.parcel.status == ParcelStatus.outForDelivery`. |
| **Low** | Home header hardcodes rider name/fleet while drawer reads from profile. | `home_tab.dart:42-47` | Read from `RiderStatusScope`/`riderProfile.profile` like the drawer does. |
| **Low** | History "Custom" filter returns all logs (no date picker). | `history_tab.dart` | Add date range picker or relabel to "All time". |
| **Low** | Delivered screen hardcodes "+₱50.00" label instead of `parcel.deliveryFee`. | `delivered.dart:65` | Use `peso(parcel.deliveryFee)` from repo data. |
| **Low** | Chat search field is decorative (no controller/onChanged). | `chat.dart` (list screen) | Wire to filtering or remove. |
| **Low** | `goOnline()` has a 250ms `Future.delayed` with `TODO(api)` before the real API call. | `rider_status_controller.dart:115-117` | Remove the mock delay; the `setCheckedIn` call is already real. |
| **Low** | Hardcoded demo phone numbers (`+639171234567`, `+639181234567`, `+639191234567`) in `main.dart`, `app_info.dart`, `profile_screen.dart`. | Various | Source from rider profile / fleet config. |
| **Low** | `mock_data.dart` is dead code — no file imports it. Contains 7 `TODO(api)` comments. | `lib/data/mock_data.dart` | Remove or mark as deprecated once all flows are API-backed. |
| **Low** | Backend `.env` contains real MySQL password and JWT secret (gitignored, local only). | `backend/.env` | Acceptable for local dev; ensure `.env` stays gitignored; never commit. |

---

## 6. Test Case Results

**Test case spreadsheets** (`Mobile_Blackbox_Test_Cases_Account_Creation-Rider.xlsx`, `Mobile_Blackbox_Test_Cases_Rider_Modules.xlsx`, ERP components PDF) were **not found** in the workspace. No `.xlsx`, `.pdf`, or `.csv` files exist. The following assessment is inferred from the code and the business rules documented in `AGENTS.md` §5.

### Account Creation

| Case | Status | Reason |
|------|--------|--------|
| Personal info form entry | Likely FAIL | `MField` used without controllers — input is discarded (`registration.dart`) |
| Vehicle type selection | Likely PASS | Stored in local state (but never submitted) |
| Plate number entry | Likely FAIL | No controller — input discarded |
| OR/CR upload | Likely FAIL | Boolean toggle, no `image_picker` or file handling |
| ID/license upload | Likely FAIL | Boolean toggle, no `image_picker` or file handling |
| Province/City/Barangay cascade | Likely FAIL | `onChanged: (_) {}` — selections discarded (`registration.dart:177`) |
| Age validation (under 18) | Likely PASS | Implemented: `if (age != null && age! < 18)` (`registration.dart:145-150`) |
| Email/phone format validation | Likely FAIL | Not implemented — only comments exist |
| Terms agreement | Likely PASS | Checkbox tracked in state |
| Registration submit | Likely FAIL | Navigation-only, no API call |
| SMS phone verification | Likely FAIL | Not implemented — no SMS code exists |
| Pending status display | Likely PASS | `PendingApprovalScreen` exists with demo data |
| Approved status display | Likely PASS | `RegistrationApprovedScreen` exists |
| Rejected status display | Likely PASS | `RegistrationRejectedScreen` shows reason |
| Edit and resubmit from rejected | Likely PASS (navigates) | Routes to `/register`, but registration is broken |
| Activation set password | Likely PASS (UI) | Strong validation UI; but submit is `TODO(api)` mock (`main.dart:481`) |
| Activation link expired resend | Likely PASS (UI) | Mock `Future.delayed` with `TODO(api)` (`main.dart:494`) |

### R1 — Registration

| Case | Status | Reason |
|--------|--------|--------|
| All registration form fields | Likely FAIL | Fields don't capture data (no controllers) |
| Document uploads (image rules: JPG/PNG, max 5MB) | Likely FAIL | Uploads are boolean toggles; no file selection or size/type checks |
| Required field validation | Likely FAIL | No `Form`/`FormState`; "Next" always enabled on steps 0-2 |
| Submit to backend | Likely FAIL | No API call; `pushReplacementNamed('/pending')` only |

### R2 — Login / Activation

| Case | Status | Reason |
|--------|--------|--------|
| Login with valid credentials | Likely PASS | Real `POST /api/auth/login`; token persisted in secure storage |
| Login with invalid credentials | Likely PASS | Server returns 401; error shown in `InfoBanner` |
| Login with empty fields | Cannot verify | No client-side validation; depends on server 400 |
| Session restore on launch | Likely PASS | `restoreSession()` checks secure storage → `GET /api/bootstrap` |
| Logout | Likely PASS | Clears token, navigates to `/login` |
| Set password (activation) | Likely PASS (UI) | 5 password rules + confirm match; but submit is `TODO(api)` |
| Link expired resend | Likely PASS (UI) | UI + mock; `TODO(api)` |

### R3 — Dashboard / Home

| Case | Status | Reason |
|--------|--------|--------|
| View active jobs count (/3) | Likely PASS | `parcels.active.length` from API |
| View earnings today | Likely PASS | `parcels.earningsSince(startOfDay)` |
| View COD due | Likely PASS | `parcels.codOnHand` from API |
| Navigate to active pickup | Likely PASS | Pushes `/pickup-detail` or `/delivery` |
| Recent activity | Likely PASS | Filters `parcels.parcels` to delivered/failed |
| Go online | Likely PASS | Real `PUT /api/profile/check-in`; location permission check |
| Go offline with held parcels | Likely PASS | Warning dialog when `heldParcels > 0` (`shell.dart`) |
| Suspended rider cannot go online | Likely PASS | `riderStatusController` checks `_suspended` |

### R4 — Pickup

| Case | Status | Reason |
|--------|--------|--------|
| View pickup job detail | Likely PASS | Display from parcel data (hardcoded pay/km/ETA) |
| Scan waybill (QR) | Likely PASS | `mobile_scanner`; QR-only; alignment frame |
| Manual tracking entry | Likely PASS | Text field fallback when camera denied |
| Tracking mismatch rejected | Likely PASS | `ScanResult(ScanOutcome.mismatch, ...)` banner |
| Already picked up detection | Likely PASS | `ScanOutcome.alreadyPickedUp` banner |
| Confirm pickup | Likely PASS | Real `PATCH /api/parcels/{tracking}/status` → `picked_up` |
| Navigate to seller | Likely PASS | Google Maps/Waze picker via `url_launcher` |
| Camera permission denied handling | Likely PASS | "Open Settings" + manual fallback |

### R5 — Delivery / POD

| Case | Status | Reason |
|--------|--------|--------|
| View delivery detail | Likely PASS | Lifecycle stepper; recipient card; COD/Prepaid display |
| Mark out for delivery | Likely PASS | Multi-step `transition` API; no-skip enforced |
| POD Step 1: live camera photo | Likely PASS (UI) | `ImageSource.camera` only; JPG/PNG ≤5MB check; but photo NOT uploaded |
| POD Step 2: 6-digit OTP | Likely FAIL | Only `length == 6` checked; any 6 digits pass; no server verification |
| POD Step 3: exact COD (₱499.99 & ₱500.01 fail) | Likely PASS | Cent-based round; server `cents()` enforces; unit-tested |
| Prepaid skips COD step | Likely PASS | `stepCount = isCod ? 3 : 2` |
| Delivery failed (3 reasons) | Likely PASS | Server enforces valid reason; client shows 3-option sheet |
| Max 3 delivery attempts | Likely PASS | Server: `delivery_attempts >= 2` → `return_to_sender` |
| Refused → Return to Sender | Likely PASS | Server: `reason === 'Refused'` → `return_to_sender` |
| Rider pay ₱50 delivery / ₱30 pickup | Likely PASS | Server sets fees on transition; local tests verify |

### R6 — COD / Earnings

| Case | Status | Reason |
|--------|--------|--------|
| View COD on hand | Likely PASS | `parcels.codOnHand` subtracts pending remittances |
| Submit remittance (exact match) | Likely PASS | Real `POST /api/remittances`; server requires exact amount |
| Short/over/match detection | Likely PASS | Client-side + server-side validation |
| Pending remittance status | Likely PASS | `remittances` repo; status card |
| Earnings today/week breakdown | Likely PASS | From `parcels` repo fees; Today/Week filters |

### R7 — Settings / Profile

| Case | Status | Reason |
|--------|--------|--------|
| Change password | Likely FAIL | Mock `Future.delayed` with `TODO(api)` (`change_password_screen.dart:47`) |
| Passkey | Likely FAIL | Local biometric placeholder, not WebAuthn (`passkey_screen.dart:13`) |
| Emergency contacts CRUD | Likely PASS | Real `POST/PUT/DELETE /api/emergency-contacts`; max 2 |
| Biometric lock enable/disable | Likely PASS | `BiometricGate` + `local_auth`; requires biometric check to enable |
| Language switch (EN/Filipino) | Likely PASS | `SettingsController` + `SharedPreferences`; `MaterialApp.locale` |
| Theme switch (light/dark/system) | Likely PASS | `SettingsController.themeMode` |
| Notification toggles | Likely PASS | 5 toggles persist via `SharedPreferences` |
| Default nav app | Likely PASS | Google Maps/Waze/Ask preference |
| Permissions status view | Likely PASS | Real `Permission` status; "Open Settings" |
| Profile edit (phone/address) | Likely FAIL | Mock save with `TODO(api)` (`profile_screen.dart:99`) |
| Profile photo change | Likely FAIL | Captures bytes locally, never uploads (`profile_screen.dart:86`) |
| Vehicle documents view | Likely PASS | Display-only |
| Vehicle documents update | Likely FAIL | No-op SnackBar with `TODO(api)` (`vehicle_documents_screen.dart:39`) |

---

## 7. Recommended Next Steps (Priority Order)

1. **Fix POD photo upload + OTP verification** — Implement multipart image upload for the proof photo. Add a server-side OTP verification endpoint (generate code, verify digits, enforce lockout). This is the single most important gap for a logistics app. *(Effort: M)*

2. **Make registration functional** — Add `TextEditingController` to every field, wire dropdown `onChanged` callbacks, integrate `image_picker` for document uploads with size/type validation, implement `POST /api/auth/register` on the backend, and submit on the final step. *(Effort: L)*

3. **Add error handling to delivery flow API calls** — Wrap `_accept()`, `_fail()`, and `_finish()` in try/catch with user-facing SnackBar messages. Handle 409 responses gracefully (expired offer, max jobs, transition conflict). *(Effort: S)*

4. **Wire profile save to real API** — Replace the `Future.delayed` mock in `profile_screen.dart:99` with `repositories.riderProfile.saveProfile()`. Implement avatar upload via multipart. *(Effort: S)*

5. **Implement change password endpoint** — Add `PATCH /api/auth/password` to the backend and call it from `change_password_screen.dart:47`. *(Effort: S)*

6. **Add SMS phone verification** — Integrate an SMS provider, add a phone verification step to registration, and verify the code server-side. *(Effort: M)*

7. **Implement 3-declines → Unassigned logic** — Track decline count server-side; auto-set offer to `expired`/`unassigned` after 3 declines. *(Effort: S)*

8. **Fix chat thread header + search** — Pass conversation title from the selected conversation; wire search field to filter conversations. *(Effort: S)*

9. **Fix pickup detail data mapping** — Use real merchant ID and seller phone from parcel data instead of tracking number and buyer phone. Add `sellerPhone` field to the `Parcel` model. *(Effort: S)*

10. **Implement passkey (WebAuthn)** — Replace the local biometric placeholder with real WebAuthn registration/assertion. *(Effort: M)*

11. **Wire help center report + vehicle document update** — Implement backend endpoints and replace mock `Future.delayed` calls. *(Effort: S)*

12. **Add widget/golden tests** — The current test suite only covers the local SQLite repository layer (8 tests). Add tests for navigation, settings persistence, biometric gate, and the POD flow. *(Effort: M)*

---

## Appendix A: Project Structure

```
lib/
├── main.dart                    # Entry point, MimooRoutes, MaterialApp, route table
├── mimoo_screens.dart           # Barrel file re-exporting screens
├── core/
│   ├── app_info.dart            # kAppVersion, kSupportEmail, kBilisDeskPhone
│   ├── auth_controller.dart     # AuthController (ChangeNotifier), AuthScope
│   ├── connectivity.dart        # ConnectivityMonitor, ConnectivityBanner
│   ├── job_offer_notifications.dart  # Local + lockscreen notifications, FCM
│   ├── rider_status_controller.dart  # Online/offline/suspended state, location
│   ├── settings_controller.dart # SettingsController (ChangeNotifier), SettingsScope
│   └── theme.dart               # MC, MT, MimooColors (ThemeExtension), mimooTheme
├── data/
│   ├── db/app_database.dart     # SQLite schema + seed (test-only)
│   ├── models/entities.dart     # Parcel, RiderProfile, JobOffer, etc.
│   ├── mock_data.dart           # DEAD CODE — no file imports it
│   ├── parcel_flow.dart         # failureReasons, carrierLabels, clock(), DispatchStore (@Deprecated)
│   └── repositories/
│       ├── api_client.dart      # ApiClient (http + flutter_secure_storage)
│       ├── api_repositories.dart  # ApiRepositories (real HTTP impl)
│       ├── app_repositories_scope.dart  # InheritedWidget scope
│       ├── local_repositories.dart  # AppRepositories (SQLite, test-only)
│       ├── repository_bundle.dart  # RepositoryBundle interface
│       └── repository_interfaces.dart  # All repository interfaces
├── l10n/
│   ├── app_en.arb               # English strings
│   ├── app_fil.arb              # Filipino strings
│   └── generated/               # Generated localization files
├── screens/
│   ├── (26 screen files + settings/ subdirectory)
│   └── settings/                # 6 settings sub-screens
└── widgets/
    ├── biometric_gate.dart      # BiometricGate (MaterialApp.builder)
    ├── common.dart              # MChip, MField, IconDot, RouteTimeline, Keypad, MSegmentedControl
    ├── custody_timeline.dart    # Custody timeline renderer
    ├── mimoo_drawer.dart        # Navigation drawer
    └── shell.dart               # MimooAppBar, MimooBottomNav
```

## Appendix B: Technology Stack

| Layer | Technology |
|-------|-----------|
| Framework | Flutter / Dart (SDK >=3.3.0) |
| State management | `ChangeNotifier` + `InheritedNotifier` (no state package) |
| Navigation | Named routes via `MimooRoutes` in `main.dart` |
| HTTP | `package:http` + `flutter_secure_storage` for token |
| Local storage | `shared_preferences` (settings), `sqflite` (test-only local DB) |
| Backend | Node.js / Express + MySQL (NOT Laravel — audit request mentioned Laravel, but actual backend is Node.js) |
| Localization | `flutter_localizations` + ARB files (English + Filipino) |
| Auth | `local_auth` (biometric lock) |
| Camera | `mobile_scanner` (QR), `image_picker` (photos) |
| Permissions | `permission_handler` |
| Push | `firebase_core` + `firebase_messaging` (not configured — no google-services.json) |
| Notifications | `flutter_local_notifications` |
| Connectivity | `connectivity_plus` |

## Appendix C: Flutter Checks

| Check | Result |
|-------|--------|
| `flutter pub get` | ✅ Success — 43 packages have newer versions (pinned by constraints) |
| `flutter analyze` | ✅ No issues found (ran in 71.0s) |
| `flutter test` | ✅ 8/8 passed (local_repositories_test.dart — transition table, earnings, COD, failed delivery, RTS) |
| `flutter build apk --debug` | ✅ Built `build\app\outputs\flutter-apk\app-debug.apk` (360s). Warnings: `mobile_scanner` uses Kotlin Gradle Plugin (future Flutter may break); deprecated API usage (cosmetic). No errors. |

## Appendix D: Endpoint Mismatch Analysis

The audit request mentions a "Laravel JSON API," but the actual backend is **Node.js/Express** (not Laravel). All Flutter API calls match the backend routes exactly:

| Flutter call | Backend route | Match |
|-------------|---------------|-------|
| `POST /api/auth/login` | ✅ Same | ✓ |
| `GET /api/bootstrap` | ✅ Same | ✓ |
| `PATCH /api/profile` | ✅ Same | ✓ |
| `PUT /api/profile/check-in` | ✅ Same | ✓ |
| `PATCH /api/offers/:id` | ✅ Same | ✓ |
| `PATCH /api/parcels/:tracking/status` | ✅ Same | ✓ |
| `POST /api/parcels/:tracking/delivery` | ✅ Same | ✓ |
| `POST /api/remittances` | ✅ Same | ✓ |
| `POST /api/notifications/read-all` | ✅ Same | ✓ |
| `POST /api/conversations/:id/messages` | ✅ Same | ✓ |
| `POST /api/emergency-contacts` | ✅ Same | ✓ |
| `PUT /api/emergency-contacts/:id` | ✅ Same | ✓ |
| `DELETE /api/emergency-contacts/:id` | ✅ Same | ✓ |
| `POST /api/dev/reset` | ✅ Same | ✓ |

**Missing backend endpoints** (TODO(api) flows not yet implemented):
- `POST /api/auth/register` (rider self-registration)
- `POST /api/activation/set-password` (activation)
- `POST /api/activation/resend` (link expired resend)
- `PATCH /api/auth/password` (change password)
- `POST /api/passkey` + `DELETE /api/passkey` (WebAuthn passkey)
- `POST /api/documents/upload` (vehicle docs / avatar)
- `POST /api/support/report` (help center report)
- `POST /api/parcels/:tracking/otp/verify` (OTP verification)

## Appendix E: TODO/FIXME Inventory

**No FIXME comments** found in the codebase. **24 TODO comments** across 14 files:

| File | Line | TODO text |
|------|------|-----------|
| `main.dart` | 481 | `TODO(api): submit the password to the activation endpoint.` |
| `main.dart` | 494 | `TODO(api): ask the logistics company to resend the invitation.` |
| `settings_controller.dart` | 165 | `TODO(api): sync the emergency contacts with the rider profile endpoint.` |
| `settings_controller.dart` | 174 | `TODO(api): replace with a real passkey / WebAuthn registration + assertion.` |
| `mock_data.dart` | 13 | `TODO(api): replace with the status returned by the auth endpoint.` |
| `mock_data.dart` | 16 | `TODO: replace with real API response` |
| `mock_data.dart` | 19 | `TODO(api): resolve from GPS / reverse geocode.` |
| `mock_data.dart` | 23 | `TODO(api): replace with the rider earnings endpoint.` |
| `mock_data.dart` | 63 | `TODO(api): replace with the job-history endpoint` |
| `mock_data.dart` | 106 | `TODO(api): read from the inbox and support threads` |
| `api_repositories.dart` | 297 | `TODO(api): upload the live camera photo and verify the buyer OTP server-side.` |
| `rider_status_controller.dart` | 115 | `TODO(api): check in with the dispatch server before accepting offers.` |
| `rider_status_controller.dart` | 126 | `TODO(api): persist the rider's check-out with the dispatch server.` |
| `job_offer_notifications.dart` | 45 | `TODO(firebase): add google-services.json` |
| `help_center_screen.dart` | 207 | `TODO(api): submit the support report with category, description and media.` |
| `profile_screen.dart` | 86 | `TODO(api): upload the new avatar and persist it on the rider profile.` |
| `profile_screen.dart` | 99 | `TODO(api): PATCH the rider contact number and residential address.` |
| `pod_flow.dart` | 88 | `TODO: upload _photoPath (captured proof photo) before enabling step 2.` |
| `pod_flow.dart` | 109 | `TODO(api): upload the proof photo and validate the OTP with the backend.` |
| `pod_flow.dart` | 279 | `TODO: validate against backend; after 3 wrong tries show 15-min lockout banner.` |
| `vehicle_documents_screen.dart` | 39 | `TODO(api): upload replacement license / OR-CR for verification.` |
| `registration.dart` | 177 | `TODO: wire Province → City → Barangay to PSGC data` |
| `change_password_screen.dart` | 47 | `TODO(api): verify the current password and persist the new credential.` |
| `passkey_screen.dart` | 13 | `TODO(api): register a real WebAuthn credential` |
| `passkey_screen.dart` | 49 | `TODO(api): revoke the registered passkey on the server.` |
