# Mimoo Rider

Flutter rider app for pickup, delivery, COD remittance, and proof-of-delivery
workflows. Runs **fully offline with local SQLite data** — no backend server
required. The UI uses a single 5-tab shell, a persistent preferences layer,
light/dark themes, English and Filipino localization, and a biometric launch lock.

## Stack

- Flutter / Dart
- SQLite (`sqflite`) for all persistent data — fully offline
- `shared_preferences` for user settings
- `crypto` for password hashing (SHA-256)
- `flutter_localizations` + ARB files for English and Filipino
- `local_auth` for biometric lock and passkey gating
- `image_picker` for profile photos, proof-of-delivery photos, and document uploads
- `mobile_scanner` for waybill QR scanning
- `permission_handler` for location, camera, and notification status
- `url_launcher` for calls, email, and maps
- `connectivity_plus` for offline detection
- `flutter_local_notifications` for lockscreen job-offer notifications

## Running the app

```bash
flutter pub get
flutter gen-l10n
flutter run
```

No backend server needed. The app seeds realistic demo data on first launch:
- Rider account: `rider@mimoo.ph` / `password`
- Active parcels: pickup, delivery, job offer
- Chat conversations, history, earnings, COD records

Run `flutter gen-l10n` after editing `lib/l10n/app_en.arb` or `lib/l10n/app_fil.arb`.

## Reset demo data

In Settings → About, tap **Reset demo data** (debug builds only) to wipe and
re-seed all local data. Or call `AppDatabase.resetLocalData()` programmatically.

## Quality checks

```bash
flutter analyze    # must stay at 0 issues
flutter test       # 27 tests — all must pass
```

## Work completed

### Stage 1 (new) — Frontend-only, fully offline

- **Removed** `api_client.dart`, `api_repositories.dart`, `http` and
  `flutter_secure_storage` packages, Firebase, and all remote calls.
- **`AppRepositories`** (local SQLite) now backs every repository interface.
  Screens and business logic are unchanged — only the data layer swapped.
- **Local login:** SHA-256 password stored in `rider_accounts` table; validated
  in `AppRepositories.signIn()`. Demo: `rider@mimoo.ph` / `password`.
- **Local changePassword:** compares and updates the hash in the same table.
- **Local OTP (registration):** random 6-digit code generated and shown in a
  debug banner (no SMS provider needed for local demo).
- **Avatar + documents:** saved to `getApplicationDocumentsDirectory()` — no
  upload endpoint needed.
- **Help center report:** saved to `issue_reports` SQLite table.
- **Vehicle document update:** copies file to app documents directory.
- **Passkey:** local biometric-only (no server ceremony).
- **Seeder redesigned:** 3 active parcels (1 assigned + 1 outForDelivery + 1
  pickedUp), history, bulk earnings entries, COD remittance history, chat.
- **27 tests pass** (`flutter analyze` clean, `flutter test` all green).

### Navigation shell, theme, localization, biometric lock, POD, registration, settings

(All described in AUDIT_REPORT.md — implemented across prior stages.)

---

## API limitations (now resolved — offline only)

- All data is stored locally in SQLite. No backend is required.
- Push notifications use `flutter_local_notifications` only (no FCM).
- "Sync to server" is stubbed via the outbox table; real sync can be added later.

## Known remaining work

- Stages 2-6 from the original prompt: drawer name fix, profile card navigation,
  Active/Offline switch, theme switching, widget tests.
- Translate legal body copy and FAQ answer copy.
- Migrate legacy screens to the theme-aware palette.
- Rework the History tab filters and link to history detail.
- Full widget/golden tests.
- Verify biometric lock on a physical device.

## Screen routes

Declared in `MimooRoutes` in `lib/main.dart`. The route table covers login,
registration (5-step), activation decisions, suspension, job offers, pickup and
delivery, waybill scanning, POD, COD remittance, notifications, vehicle
documents, Help Center, Profile, Settings, legal pages, About, and history
job detail.

`showLocationPermissionSheet` is a modal helper. `LocationOffBanner`,
`OfflineBanner`, and `SystemStateView` are inline widgets, not routes.

## Progress log

- [x] Stage 1: Backend removed. App runs fully offline with SQLite. Local login,
  OTP, avatar, docs, help center, passkey all working locally. 27/27 tests pass.
- [x] Stage 2: Single profile state — drawer, home header, and profile screen all
  read from `repositories.riderProfile` (live via `ListenableBuilder`). Drawer
  shows real name with "Rider" fallback, real avatar if available, and fleet·hub.
- [x] Stage 3: Home header profile card is tappable (navigates to `/profile`).
  Drawer header already navigated to `/profile`. Profile screen shows live name
  and fleet from the repository.
- [x] Stage 4: Active/Offline switch fully functional. Drawer chip reads
  `RiderStatusScope.isOnline` live. Jobs tab hides offers and shows an offline
  banner when offline. Accepting an offer while offline is blocked with a clear
  SnackBar. Online status persists via `checked_in_at` in SQLite.
- [x] Stage 5: Light/Dark/System theme switching wired end-to-end via
  `SettingsController` + `AnimatedBuilder` on `MaterialApp`. Login screen always
  uses `ThemeMode.light`. No restart needed to apply the change.
- [x] UI remediation Stage 1: Repaired mojibake in ARB copy and delivery screens
  (`…`, `·`, curly quotes, `₱`), added the shared `kDot` separator, and repaired
  previously stored text via database schema v3. Analyze clean; 27/27 tests pass.
- [x] UI remediation Stage 2: Login card centered and capped at 480dp with the
  `mimoo rider` wordmark header; inline dismissible errors via `friendlyError`,
  loading state on the button, passkey guidance, and debug tools hidden until a
  five-times long-press on the wordmark.
- [x] UI remediation Stage 3: One delivery-detail layout for at-hub, out-for-
  delivery, failed and delivered states via shared `delivery_sections.dart`
  (progress, recipient + Call/Message, drop-off, note, parcel, payment, custody).
- [x] UI remediation Stage 4: COD Balance keeps the summary pinned while the
  collected list scrolls, adds `remitCollected` (marks parcels remitted and
  records a confirmed remittance), a GCash/Hub Remit sheet, due-cutoff chip,
    remitted history and a ₱0.00 empty state. Analyze clean; 27/27 tests pass.
  - [x] UI remediation Stage 5: App-wide `MimooScrollBehavior` hides scrollbars
    while keeping touch, mouse and stylus dragging.
  - [x] UI remediation Stage 6: Shared widgets, shell, drawer and the Home, Jobs,
    Earnings and History tabs now resolve light/dark tokens via `MC.of(context)`;
    `MimooColors` gained amber/coral/tertiary/secondary tokens, and legacy
    `MC.*` arguments are mapped to the active palette.
  - [x] UI remediation Stage 7: Reusable `PasswordField` with show/hide plus an
    inline "Current password is incorrect." check in Change password.
