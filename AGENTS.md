# AGENTS.md: Mimoo Rider (Flutter)

Instructions for AI coding agents (GitHub Copilot, opencode, etc.). Read this fully before editing. The app is **front-end complete with mock data**. Your job is almost always to make a *small, targeted* change, not to rewrite things.

## 0. Golden rules

1. **Smallest diff that solves the task.** Touch only the files the task needs. No drive-by refactors, renames, reformatting, import reordering or "cleanup" of unrelated code.
2. **Never delete or rename** a file, route, class, ARB key or public widget unless the task says so. Never delete a legacy screen because it looks unused; check usage with a search first, and ask if unsure.
3. **One concern per change.** UI tweak, new feature and migration do not go in the same diff.
4. **Plan first.** For anything touching more than 3 files, post a short plan (files, what changes, risks) and wait for approval.
5. **Prove it builds.** Run `flutter pub get`, `flutter gen-l10n` (if ARB changed) and `flutter analyze` before saying you are done. Analyze must stay at **zero issues**. Report anything you could not run.
6. **If a request conflicts with this file, stop and ask.** Do not silently pick a side.
7. **Never fabricate.** Do not invent endpoints, packages, API fields or business rules. Use `TODO(api)` instead.

## 1. Project map (`lib/`)

| Path | Contents |
|---|---|
| `main.dart` | `main()`, `MimooRoutes`, `MaterialApp`, route table, notification action handler |
| `mimoo_screens.dart` | Barrel file that re-exports screens. Add an `export` here when you add a screen that other files import via the barrel |
| `core/theme.dart` | Design tokens: `MC` (colors), `MT` (text styles), `MimooColors` (`ThemeExtension`), `mimooTheme({Brightness})` |
| `core/connectivity.dart` | `ConnectivityMonitor`, `ConnectivityBanner` / offline handling |
| `core/job_offer_notifications.dart` | Local and lock-screen job-offer notifications |
| `data/mock_data.dart` | **All** mock data (jobs, parcels, notifications, activity, rider identity) |
| `screens/` | One file per screen or tab (see §2) |
| `widgets/shell.dart` | `MimooAppBar`, `MimooBottomNav`, drawer |
| `widgets/common.dart` | Shared UI: `MCard`, `MChip`, `MField`, `IconDot`, `PillButton`, `InfoBanner`, `RouteTimeline`, `Keypad`, `SystemStateView` |
| `l10n/` | `app_en.arb`, `app_fil.arb`; generated files in `l10n/generated/` (checked in) |

> Before you assume a file exists, **look**. Some files named in this document may live in a different folder in your checkout. Search first, and match the layout you find.

## 2. App structure (do not change without being asked)

- **Shell:** 5 bottom-nav slots **Home · Jobs · Scan · Earnings · History**, with a hamburger drawer and a notification bell. `Scan` is a raised center **action** (pushes the scanner, never selected). The shell uses `IndexedStack` so tab state survives. Do not add or reorder tabs.
- **Drawer:** rider header, Vehicle & Documents, COD Remittance, Messages, Notifications, Settings, Help Center, app version.
- **Profile** = personal information only (photo, contact number, address, read-only verified identity). **Settings** = Account management (change password, passkey, emergency contacts, biometric), Preferences (language, appearance, notifications, default nav app, permissions), About (privacy, terms, About Mimoo, licenses), then Log out. Keep them separate. Do not move items between them.
- **Routes** are declared in `MimooRoutes` (`main.dart`). Add a new route as a `static const` there and register it in the table. Do not rename existing route strings. Routes that need data validate the argument type and show a clear error when it is missing. Keep that behavior.
- **Settings persistence:** `SettingsController` (`ChangeNotifier`) + `SharedPreferences`, exposed via `SettingsScope`. New preferences go through it. Do not call `SharedPreferences` directly from widgets.
- **Biometric lock:** `BiometricGate` is mounted in `MaterialApp.builder`. Do not move it or change the lock/unlock lifecycle without being asked.

## 3. Theming and design rules

- **New or edited screens must use the theme-aware palette:** `final c = MC.of(context);` then `c.primary`, `c.surface`, etc. No hardcoded `Color(0x…)` or `Colors.white/black` for surfaces or text.
- **Legacy screens** still use static `MC.*` / `MT.*`: auth/registration, activation, suspension, chat, notifications, scan, pickup detail, job offer, COD remittance, delivered, out-for-delivery, registration decision. **Leave them on static tokens unless the task is "migrate X".** Do not half-migrate a screen. Migrate a whole screen in one change and test it in light **and** dark.
- Reuse shared widgets (`MCard`, `PillButton`, `MChip`, `MField`, `InfoBanner`, …). Do not create a one-off copy of a shared widget. If a shared widget needs a new option, add an optional named parameter with a default that keeps current behavior.
- Typography: nothing below **12sp**. Tap targets at least **48dp**. Check both themes for contrast.
- Respect safe areas on bottom bars and sheets.
- No new packages for UI.

## 4. Localization

- All user-visible strings in new or edited migrated screens go in **both** `app_en.arb` and `app_fil.arb` with the same key. Then run `flutter gen-l10n`.
- Never hand-edit files in `l10n/generated/`. Regenerate them.
- Do not rename or remove existing ARB keys.
- Legal body text and FAQ answers are intentionally English-only for now. Do not machine-translate them.
- Legacy screens may still contain hardcoded English. Don't "fix" them as a side effect.

## 5. Business rules (protected, do not change in code)

These come from the QA assumptions and the test workbooks. Changing them breaks test cases.

- **Job offer:** expires after **60 s**; declined or expired offers go to the next nearest rider; **3 declines** → Unassigned. Max **3 active jobs** per rider.
- **Pickup:** confirm by scanning the waybill **or** typing the tracking number; a mismatched tracking number is rejected ("tracking number does not match this job").
- **Delivery lifecycle:** Assigned → Picked Up → At Hub → Out for Delivery → Delivered. No skipping states.
- **Proof of delivery (3 steps, order fixed):** (1) **live camera photo**, gallery uploads are not allowed; (2) buyer's **6-digit OTP**; (3) **exact COD amount** (₱499.99 and ₱500.01 must both fail for ₱500.00). Prepaid orders have COD ₱0 and skip step 3.
- **Image rules:** JPG/PNG, max 5 MB.
- **Failed delivery:** reason required (Buyer not home / Wrong address / Refused); max 3 attempts; "Refused" and the 3rd failure → Return to Sender.
- **Rider pay:** ₱50.00 per successful delivery, ₱30.00 per completed pickup, ₱0 for a failed attempt.
- **COD:** rider remits the full collected COD to the logistics company by end of day; the balance stays pending until Logistics confirms.
- **Going offline** is allowed while holding a picked-up parcel (warn that it must still be delivered). Only warn when a parcel really is held.
- **Suspended riders** cannot go online or receive offers.

Known inconsistency: the code TODO says OTP lockout after **3** wrong tries for 15 min, while the test case says **5**. Do **not** pick one. Ask.

## 6. Mock data and backend

- All fake data lives in `data/mock_data.dart`. Do not scatter literals across screens, and do not duplicate the same number in two places (earnings, job counts and COD must come from one source).
- Backend does not exist. Every unimplemented call gets a `// TODO(api): …` comment and a mock `Future` with a small delay. Do not add HTTP clients, Firebase calls, or real endpoints.
- Do not commit secrets or config: `google-services.json`, `GoogleService-Info.plist`, keys, tokens. Do not create them.
- Debug-only widgets must stay behind `kDebugMode`.

## 7. Dependencies and platform

- Allowed already: `shared_preferences`, `flutter_localizations`/`intl`, `local_auth`, `image_picker`, `mobile_scanner`, `permission_handler`, `url_launcher`, `connectivity_plus`, `firebase_core`, `firebase_messaging`, `flutter_local_notifications`.
- **Do not add, upgrade or remove packages** (`pubspec.yaml`) without asking. Never run `flutter pub upgrade` or `flutter pub add` on your own.
- Do not edit `android/`, `ios/`, Gradle, Podfile or manifests unless the task is about a permission or platform setting, and then explain exactly what and why.
- Request permissions only when first needed, and handle the denied and permanently-denied states with an "Open Settings" action (follow the existing camera/location pattern).

## 8. Code style

- Dart/Flutter idioms, `const` constructors where possible, keep widgets small. Follow the style of the surrounding file.
- Always check `mounted` / `context.mounted` after an `await` before using `context`.
- Dispose controllers, timers and observers.
- Prefer `Navigator.pushNamed` with a `MimooRoutes` constant, not a raw string, in new code.
- Keep comments short and useful. Keep existing `TODO(api)` / `TODO(l10n)` markers; never delete one unless you actually implemented it.
- No `print`; use `debugPrint`, and only when needed.

## 9. Workflow for each task

1. Restate the task in 1–2 lines. List the files you expect to touch.
2. Search for existing usages (`grep`) before changing a shared widget, route, token or ARB key.
3. Make the change.
4. Run: `flutter pub get` → `flutter gen-l10n` (if ARB changed) → `flutter analyze`.
5. Manually reason through: light mode, dark mode, English, Filipino, small phone (about 5.5"), offline state, and the back button.
6. Finish with a summary: files changed, behavior changed, `TODO`s added, anything you did **not** do or could not verify, and which test cases might be affected (IDs like `RID-PICK-002`).

## 10. Ask before you…

- change a route string, add or remove a bottom-nav tab, or restructure the shell or drawer;
- touch POD, job offer, COD remittance or biometric lock logic;
- change any rule in §5;
- migrate more than one legacy screen at once;
- add or upgrade a dependency;
- edit platform folders or Firebase config;
- delete anything.

## 11. Known remaining work (do only when asked)

Connect `TODO(api)` flows · translate legal and FAQ copy · migrate legacy screens to `MC.of(context)` · failed-delivery flow (the button is currently a no-op) · offline queue for scan and delivery · real OTP lockout · real WebAuthn passkeys · widget/golden tests for navigation, settings persistence and the biometric gate · verify the biometric lock on a physical device.
