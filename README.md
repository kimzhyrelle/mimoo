# Mimoo Rider

Flutter rider app for pickup, delivery, COD remittance, and proof-of-delivery
workflows. The UI was converted from the Stitch HTML designs and now uses a
single 5-tab shell, a persistent preferences layer, light/dark themes, English
and Filipino localization, and a biometric launch lock.

The app uses a Node.js API backed by MySQL. Flutter web and mobile clients
connect to the API over HTTP; the client does not connect directly to MySQL.
Rider accounts are provisioned by an administrator through the API CLI.

## Stack

- Flutter / Dart
- Node.js / Express API with MySQL
- `http` and `flutter_secure_storage` for authenticated API access
- `shared_preferences` for local settings
- `flutter_localizations` + ARB files for English and Filipino
- `local_auth` for biometric unlock
- `image_picker` for profile photos, proof-of-delivery photos, and report screenshots
- `mobile_scanner` for waybill scanning
- `permission_handler` for location, camera, and notification status
- `url_launcher` for calls, email, maps, and system settings
- `connectivity_plus` for offline detection
- `firebase_core`, `firebase_messaging`, `flutter_local_notifications` for push wiring

## Run the API

1. Install Node.js 20+ and MySQL 8+, then create a local MySQL user/database
   with permission to create tables.
2. From `backend`, copy `.env.example` to `.env` and set the MySQL connection
   values and a random `JWT_SECRET` of at least 32 characters. Keep `.env`
   private.
3. Install dependencies and initialize the schema:

   ```bash
   npm install
   npm run db:migrate
   ```

4. For local demo data, run `npm run db:seed`. This creates the demo rider
   profile `RDR-2026-0842`. Set `RIDER_PASSWORD` in the environment to a unique
   password of at least 12 characters, then provision its login:

   ```bash
   npm run rider:create -- rider@example.com RDR-2026-0842
   ```

5. Start the API with `npm start`. It listens on `127.0.0.1:8080` by default.
   Set `HOST=0.0.0.0` only when clients on another device need to reach it, and
   configure `CORS_ORIGINS` for the exact web origins in use.

For another rider, load an approved rider profile before running the provisioning
command with that profile's user ID. The API has no default credentials.

## Run Flutter

```bash
flutter pub get
flutter gen-l10n
flutter run
```

For web or a physical device, override the API URL with the server's reachable
address, for example:

```bash
flutter run -d chrome --dart-define=MIMOO_API_BASE_URL=http://localhost:8080
flutter run --dart-define=MIMOO_API_BASE_URL=http://192.168.1.20:8080
```

Android emulators default to `http://10.0.2.2:8080`; web defaults to
`http://localhost:8080`; other native platforms default to
`http://127.0.0.1:8080`. The app requires connectivity for sign-in and data
operations.

Run `flutter gen-l10n` after editing `lib/l10n/app_en.arb` or
`lib/l10n/app_fil.arb`. Generated localization files live in
`lib/l10n/generated/` and are checked in.

## Quality checks

```bash
flutter analyze
```

The project currently has no automated widget/golden tests. `flutter analyze`
passes with no issues.

## Work completed

### Navigation shell

- Replaced the old tab set with five bottom-navigation slots: Home, Jobs, Scan,
  Earnings, and History.
- Made Scan a raised center action that pushes the scanner and is never marked
  selected.
- Removed the old Account tab; account-related destinations now live in the
  drawer.
- Added safe-area handling to the bottom bar.
- Added a notification bell with an unread badge and a hamburger drawer.

### Drawer and Help Center

- Added a drawer header with rider identity, active status, unread indicators,
  vehicle/documents, COD remittance, messages, notifications, settings, Help
  Center, and app version.
- Added a Help Center with searchable FAQ categories, empty-search state, a
  Bilis Desk call action, and a report-problem sheet.
- The report sheet supports category selection, description, optional gallery
  screenshot, and a submitted confirmation.
- FAQ content and category labels are localized; FAQ answer copy remains
  English pending content/legal review.

### Profile

- Added photo selection from camera or gallery.
- Kept verified identity fields read-only: full legal name, date of birth, and
  license number.
- Made contact number and residential address editable with dirty-state
  detection, save action, and discard confirmation.
- Added localized success/error feedback.

### Settings

- Added account management: change password, passkey, emergency contacts, and
  biometric unlock.
- Change password includes live rules for length, uppercase, number/special
  character, and matching confirmation.
- Added a max-two emergency-contact editor with add, edit, remove, and
  persistence.
- Added a passkey screen with local biometric enrollment placeholder and
  removal confirmation.
- Added preferences: language, appearance, notification toggles, default
  navigation app, and location/permissions.
- Added legal screens, About, support email, licenses, and logout confirmation.
- Persisted settings with `SettingsController` and `SharedPreferences`, exposed
  through `SettingsScope`.

### Home, Jobs, and History

- Reworked Home with the rider header, online toggle, offline warning, single
  source of earnings stats, active dispatch card, and recent activity.
- Offline mode only blocks going offline when a parcel is held; the dialog still
  warns that held parcels must be delivered.
- Added the Jobs carousel under For Pickup and wired Accept Pickup to the mock
  accepted state while card taps open job detail.
- Added a read-only history job detail screen and registered its route; the
  History tab still uses the older list and is not wired to it yet.

### Earnings and delivery flow

- Rewrote Earnings with Today, This week, and Custom range filters, pickup and
  delivery breakdowns, COD-on-hand card, and mock daily bars.
- Cleaned up delivery detail chips, copy, lifecycle, and COD display.
- Reworked proof of delivery into three steps: live photo, buyer 6-digit code,
  and exact COD amount.
- Removed duplicate COD amount entry from the final proof-of-delivery step.
- Added clearer invalid-code and amount-mismatch states.
- Kept mock submission paths marked for backend replacement.

### Themes, contrast, and accessibility

- Introduced `MimooColors` as a `ThemeExtension` with `MC.of(context)` and
  `mimooTheme({Brightness brightness})`.
- Migrated all new-IA screens, including Profile and Vehicle & Documents, to the
  theme-aware palette.
- Preserved static `MC.*`/`MT.*` tokens for legacy screens.
- Fixed dark-mode contrast in banners, fields, keypad, route timeline, dialogs,
  sheets, delivery chips, history banners, passkey banners, and POD placeholders.
- Raised the minimum body text to 12sp and increased tap targets to at least
  48dp where needed.
- Fixed segmented-control, delivery-button, and jobs accept-button sizing.
- Updated contrast tokens: light green `#15803D`, dark primary container
  `#7C3AED`, and accent text generally uses `c.primary`.
- Verified the route graph: 33 declared routes and no missing push targets.

### Biometric launch lock

- Added `BiometricGate`, mounted through `MaterialApp.builder`, keeping the
  navigator alive beneath an opaque lock overlay.
- Locks on cold start and on returning to the foreground when biometric unlock
  is enabled.
- Supports retry, device-passcode fallback, and a short post-unlock resume guard
  so the authentication prompt itself does not re-lock the app.
- Enabling biometric unlock requires a successful biometric check, preventing
  unsupported devices from being locked out.
- Added localized lock, unlock, passcode, reason, and unavailable strings.

### Localization

- Added English and Filipino ARB resources and generated localizations.
- Localized navigation, drawer, profile, settings, Help Center, vehicle
  documents, biometric lock, and the new home/jobs/history/earnings flows.
- Localized settings sub-screens: change password, passkey, emergency contacts,
  permissions, About, and legal-screen chrome.
- Added localized report-problem sheet copy and category names.
- Wired the app locale to system default, English, or Filipino through
  `SettingsController` and `MaterialApp`.
- Legal body copy and FAQ answer copy remain English pending final content.

## API limitations

- Proof-of-delivery currently sends a local photo path and the client's OTP
  verification result. A real photo upload and server-side OTP verification
  still need to be implemented before treating this as production proof.
- Password changes, passkeys, push notifications, scan validation, and document
  uploads are not yet connected to the API.
- Settings, locale, theme mode, biometric preference, and passkey flag persist
  locally through `shared_preferences`.

## Known remaining work

- Connect the `TODO(api)` flows to real endpoints.
- Translate legal body copy and FAQ answer copy.
- Migrate legacy screens to the theme-aware palette: auth/registration,
  activation, suspension, chat, notifications, scan, pickup detail, job offer,
  COD remittance, delivered, out-for-delivery, and registration decision
  screens.
- Rework the History tab (All/Pickup/Delivery filters, empty states) and link
  past records to the history job detail screen.
- Add widget/golden tests for navigation, settings persistence, and the
  biometric gate.
- Verify biometric lock behavior on a biometric-capable physical device.

## Screen routes

Routes are declared in `MimooRoutes` in `lib/main.dart`. Screens that need data
or callbacks accept them as constructor arguments, so push a configured screen
when a route requires an argument:

```dart
Navigator.pushNamed(
  context,
  MimooRoutes.notifications,
  arguments: NotificationsScreen(items: notifications),
);
```

The route table covers activation, registration decisions, suspension, job
offers, pickup and delivery, waybill scanning, COD remittance, notifications,
vehicle documents, Help Center, Profile, Settings, legal pages, About, and
history job detail. Each route validates that its argument has the expected
screen type and reports a clear error when the argument is missing or incorrect.

`showLocationPermissionSheet` is a modal helper. `LocationOffBanner`,
`OfflineBanner`, and `SystemStateView` are inline widgets rather than routes.
