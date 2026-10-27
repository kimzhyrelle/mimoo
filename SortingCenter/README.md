# Sorting Center — Laravel Backend (Stage 1–4)

This package contains the backend files for the Sorting Center module of
Mimoo, built to match the sc-*.html mockups. Stage 3 restyled the shared
layout and dashboard with Tailwind v4 using the shadcn-style design tokens
(`resources/css/app.css`). **Stage 4 removes login entirely** — every page
is now public, no session/auth required, so you can iterate on the UI
without setting up a database at all if you use the `/preview/*` routes
below, or against real data once you do migrate.

For the current page-by-page operations guide, parcel lifecycle, API
endpoints, and buyer/seller/admin integration boundaries, see
[WORKFLOW.md](./WORKFLOW.md).

Note: this sandbox can't reach `packagist.org` or the npm registry for
Tailwind's own package, so these files were written directly rather than
scaffolded with `composer create-project` / `npm install`. Drop them into
an existing Laravel 11 project (the same way you set up
`client-server-week02-laravel-setup`).

## 1. Copy files in

Copy each folder into the matching folder of your Laravel project, keeping
the paths as-is:

```
app/Models/{User,Area,Rider,Parcel,Activity,RiderApplication,HubProfile}.php
app/Http/Controllers/{DashboardController,IncomingParcelsController,SortParcelsController,DeliveryMonitoringController,RiderManagementController,AccountController,DevPreviewController}.php
database/migrations/2026_01_01_*.php
database/migrations/2026_01_02_*.php
database/migrations/2026_01_03_*.php
database/seeders/SortingCenterSeeder.php
resources/css/app.css
resources/js/app.js
resources/views/layouts/sorting.blade.php
resources/views/partials/legacy-styles.blade.php
resources/views/sorting/{dashboard,incoming-parcels,sort-parcels,delivery-monitoring,rider-management,account,coming-soon,preview-index}.blade.php
```

`resources/css/app.css` and `resources/js/app.js` — if you already have
these from your Laravel install, merge rather than overwrite: keep your
existing `bootstrap.js` import if you have one, and append the token
`:root`/`.dark` blocks and the "legacy compat" section at the bottom of
your `app.css`.

`app/Models/User.php` and `app/Http/Middleware/EnsureSortingRole.php` are
leftover from when this had login — they're unused now but harmless to
keep if you want to bring auth back later. Not required for anything
currently wired up.

`routes/web.php` in this package is NOT a drop-in replacement — copy the
route definitions inside it into your existing `routes/web.php`.

`vite.config.js` and `package.json` here are references, not drop-in
replacements — merge the `tailwindcss()` plugin and the `@tailwindcss/vite`
/ `tw-animate-css` dependencies into your existing project's copies.

## 2. Install the Tailwind v4 toolchain

```bash
npm install -D tailwindcss @tailwindcss/vite tw-animate-css
npm run build   # or `npm run dev` while working locally
```

## Just want to see the UI, no DB yet?

Copy in `app/Http/Controllers/DevPreviewController.php`,
`resources/views/sorting/preview-index.blade.php`, and the `preview.`
route group from `routes/web.php`. Visit `http://127.0.0.1:8000/preview`
for a landing page linking to all five pages:

- `/preview/dashboard`
- `/preview/parcels/incoming`
- `/preview/parcels/sort`
- `/preview/delivery/monitoring`
- `/preview/riders`
- `/preview/account`

Every one of these fakes its own data in memory (`new Model()->
forceFill()`, cross-linked with `setRelation()`) — no `migrate`, no
`db:seed`. The sidebar nav knows it's in preview mode and keeps you inside
`/preview/*` when you click between pages. The real pages (`/dashboard`,
`/parcels/incoming`, etc.) work too without any login — they just need a
migrated database, since they query real data.

Delete `DevPreviewController.php`, `preview-index.blade.php`, and the
`preview.` route group once you're working against real data — the real
routes render the exact same Blade views, just fed by the database
instead of fake objects.

## 3. Migrate and seed (only needed for the real pages, not /preview/*)

```bash
php artisan migrate
php artisan db:seed --class=SortingCenterSeeder
```

## 4. Run it

```bash
npm run dev        # keep this running in one terminal
php artisan serve  # and this in another
```

Visit `http://127.0.0.1:8000/` — it redirects straight to `/dashboard`,
no login screen in the way. Rider-specific views (a rider mobile app) are
a separate, not-yet-built piece — the `role` column on `users` is still
there if you want to bring back staff/rider login later.

## What's implemented


**Stage 1 — Dashboard**
- `users` gains a `role` (staff/rider) + `initials` — unused now, see Stage 4
- `areas`, `riders`, `parcels`, `activities` tables
- Dashboard fully wired to real queries — no more static numbers

**Stage 2 — Incoming Parcels, Sort Parcels, Delivery Monitoring, Rider Management**
- `parcels` gains a `dropped_off` stage (before `awaiting_sort`), `seller_name`,
  `dropped_off_by_rider_id`, `carrier`, `scanned_undelivered`, `eta_note`,
  `buyer_confirmed_at`
- `riders` gains `vehicle_type`, `plate_no`
- New `rider_applications` table (pending sign-up review)
- `areas` gains `pickup_window`, `cutoff_time`, `pickup_status` (for the
  Incoming Parcels schedule panel)
- **Incoming Parcels** — drop-off queue, scan one or scan all, moves a
  parcel from `dropped_off` → `awaiting_sort`
- **Sort Parcels** — "Determine area" matches the address text against
  known barangays; "Identify & assign rider" looks up that barangay's
  rider (one rider per barangay) and assigns the parcel to them; carrier
  can be set per parcel
- **Delivery Monitoring** — status rail (sorted/assigned/out/delivered/
  pending confirm/failed) computed live; "Mark out for delivery" and
  "Mark delivered" advance a parcel; a failed parcel can be scanned to
  confirm it's genuinely undelivered
- **Rider Management** — approve/disapprove applications (approving
  creates the `Rider` record); assign a barangay to a rider, which
  automatically frees that barangay from whoever had it before; toggle a
  rider on/off shift

One simplification from the original mockups: the click-to-see-a-spinner
AJAX animations are now plain form submits (POST → redirect back) rather
than `fetch()` calls, since the actual state changes now happen in the
database instead of a JS array. Happy to switch these to AJAX with Alpine
or Livewire in a later pass if you want that back.

**Stage 3 — Tailwind v4 / shadcn design tokens**
- Added `resources/css/app.css` (your provided tokens) + `vite.config.js` +
  `package.json` deps for `@tailwindcss/vite`
- Rebuilt with Tailwind utility classes: the shared layout (sidebar +
  topbar) and the dashboard — light/neutral palette
  (`bg-card`, `bg-primary`, `text-muted-foreground`, etc.) instead of the
  earlier purple theme
- The four Stage 2 pages (Incoming Parcels, Sort Parcels, Delivery
  Monitoring, Rider Management) are **not yet migrated** — they still use
  their original CSS, now loaded via `resources/views/partials/legacy-
  styles.blade.php` so nothing broke. Say the word and I'll convert those
  four the same way next.

**Stage 4 — Login removed**
- Deleted `SortingLoginController`, the login Blade view, and the
  `guest`/`auth`/`role:staff` route groups — every page is public now
- Root `/` redirects straight to `/dashboard`
- Sidebar footer is now a static "Laguna Hub — Sorting Staff" label
  instead of reading `auth()->user()`
- `users.role`, `EnsureSortingRole` middleware, and the seeded rider/staff
  logins are still in the codebase, just unused — wire them back up if you
  bring login back later

**Stage 5 — Account page**
- New `hub_profiles` table — a single row, since there's no per-user login
  anymore to hang settings off of
- **Account** — edit hub name, contact email/phone, address, and a
  coverage note; read-only list of barangays covered (managed from Rider
  Management, not here)
- Seeder creates the default row automatically via `HubProfile::current()`

## UI stabilization pass 1 - shared modals (2026-10-25)

Goal: one shared modal system, no modals visible on page load, and modals
only for actions that change data or status.

### Bugs found
- The custom `.confirm-modal-backdrop`, `.kyc-lightbox-backdrop`, and
  `.bulk-bar` classes forced `display:flex`, overriding Tailwind's
  `.hidden`. Every modal, the KYC lightbox, and the bulk action bar were
  visible on page load.
- The KYC lightbox backdrop was the cause of the "almost-black Disapprove
  modal with a floating X" bug.
- Modals had no shared Escape / backdrop / close-button handling, the body
  scroll lock was not always released, and focus was not returned to the
  button that opened the modal.
- Confirm buttons were not consistently solid, and confirm state was not
  synchronized for required checkboxes and selects.
- Bulk assign and bulk archive posted to the wrong routes.
- Sidebar Logout used `href="#"`.
- Dashboard loaded Chart.js `4.4.4` from cdnjs, which returns 404. This
  produced `ReferenceError: Chart is not defined` and a failed network
  request on every page.

### What changed
- Added `resources/js/modals.js`: `openModal`, `closeModal`,
  `closeAllModals`, `syncConfirmState`, and `updateConfirmButton`.
  Handles single-open enforcement, Escape, backdrop click, close buttons,
  scroll lock/release, focus restore, and submit loading state.
- `resources/js/app.js` now imports the modal module.
- Rewrote `components/confirm-modal.blade.php` as the single modal shell
  (title, labels, confirm style, required checkbox, form action/method).
- Rewrote `components/disapprove-modal.blade.php` to compose the shared
  shell; confirm stays disabled until a reason is selected.
- Removed the old per-page modal JavaScript from
  `layouts/sorting.blade.php`; the global scan modal now uses the shared
  module.
- Removed the KYC lightbox. Thumbnails now open in a new tab.
- Fixed bulk assign (`assignments.bulk`) and bulk archive
  (`parcels.sort.bulk-archive`) routes.
- Added solid `.btn-danger`, applied it to archive/disapprove confirms,
  and kept primary confirms solid.
- Account saves submit inline with a loading state. The confirm modal is
  only used when the hub/entity name changes.
- Logout is now a button that shows a toast instead of navigating to `#`.
- Dashboard Chart.js is now `4.5.1`, and chart initialization is guarded
  when the library is unavailable.

### Modal policy
- Keep modals for data/status changes: dispatch, push to loading docks,
  archive, checklist, advance, disapprove.
- Do not use modals for viewing details, sort/filter/search, Export,
  Generate Report, or normal Account saves.

### Verification
- `npm run build` passed.
- `php artisan optimize:clear` and `php artisan view:cache` passed.
- Headless Chrome/CDP check on every page: zero visible modals on load,
  correct titles, no whoops page, no console errors, no failed requests.
  Dashboard: Chart.js loaded and both chart canvases rendered.
- Same-origin iframe test harness: 26 checks passed, including
  X/Escape/backdrop close, scroll lock/release, focus restore, single-open
  enforcement, hidden bulk bar, solid danger buttons, and required-field
  confirm gating.
- `php artisan test --compact tests/Feature/AccountSettingsTest.php`:
  7 passed, 24 assertions.
- Assignment, Branch Checklist, Parcel Status, and Rider Verification
  feature tests: 34 passed, 299 assertions.

## UI stabilization pass 2 - Reports (2026-10-25)

Goal: make every report type render its own data, keep filters consistent
through navigation/export, and make exports actually download files.

### Bugs found
- The report page rendered the same four legacy parcel KPIs for every
  report type, ignoring the type-specific KPIs returned by
  `ReportService`.
- The chart was always the parcel daily-volume line chart, even for
  Delivery, Rider, Branch, and Overdue reports.
- Delivery, branch, and rider reports ignored the barangay/carrier
  filters when building area/rider rows.
- "Export PDF" opened the browser print dialog instead of downloading the
  server-generated PDF, even though the backend supports PDF export.
- Quick date presets used UTC (`toISOString`), so "Today" could select the
  previous day in Philippine time.
- Switching report type dropped unsaved date/barangay filter changes.
- Filter validation errors were flashed to the session but never shown.
- The chart loading overlay was positioned against the page instead of
  the chart card and was never triggered.

### What changed
- `ReportService` now applies barangay/carrier filters to delivery,
  branch, and rider datasets through a shared filtered-areas query.
- `ReportsController` now returns the structured report plus only the
  filter/date data needed by the view; previous-period delta is computed
  with the same filters.
- `reports.blade.php` now renders the report's own four KPIs, a line chart
  for Parcels and a bar chart for the other report types, and each
  report's own table rows.
- PDF/CSV export links use the active filters; Export PDF downloads the
  generated PDF. Print remains a separate action.
- Type switching and exports sync the current (possibly unsaved) filter
  values before navigating.
- Quick presets use local dates, validation errors show as an inline
  alert, the export menu closes on Escape/outside click, and Generate
  shows a loading state.
- Added `data-kpi` hooks for report smoke tests.

### Verification
- `php artisan view:cache` passed.
- `php artisan test --compact tests/Feature/ReportServiceTest.php`:
  13 passed, 110 assertions.
- Full suite: 91 passed, 839 assertions.
- PDF endpoint: `200 application/pdf`; CSV endpoint:
  `200 text/csv` with KPI and data sections.
- Headless Chrome across all five report types: correct KPI sets, correct
  chart type, no console/network errors, no visible modals.
- Filtered checks: Delivery/Branch show only Palasan for barangay R03 +
  J&T; Rider shows only A. Santos; chart labels match the filtered rows.
- Interaction checks: initial Generate disabled, presets enable it and use
  the local date, type/export links preserve current filters, export menu
  opens and closes with Escape, Generate shows the loading overlay, and an
  inverted date range shows the inline validation message.

## UI stabilization pass 3 - responsive layout and contrast (2026-10-26)

Goal: make the app usable from 390px phones through desktop, remove
horizontal overflow, and fix remaining dark-theme contrast problems.

### Bugs found
- The 256px app sidebar stayed in the layout below `lg`, leaving too little
  room for page content and causing horizontal overflow.
- Topbar search, breadcrumbs, and the notification dropdown did not fit on
  phones. The notification menu was wider than its trigger and could run
  past the viewport.
- Report filters and report tables overflowed narrow screens.
- Chat showed the conversation list and thread side by side at all widths,
  leaving both too narrow on phones. Several chat placeholders and dark
  metadata labels were also broken.
- The dark report KPI day count used a float day difference and could show
  one fewer day. The previous-period delta used mismatched range bounds.
- Solid danger buttons were red text on a red background in dark mode, and
  active sidebar/notification badges were too low-contrast.
- The Account landline hint rendered doubled parentheses.

### What changed
- Added `resources/js/sidebar.js`: below `lg`, the sidebar becomes an
  off-canvas drawer with a backdrop and close button. Escape, backdrop, and
  nav clicks close it; focus is moved into the drawer and restored on
  close; `aria-expanded`, `inert`, `aria-hidden`, and body scroll lock stay
  in sync. Desktop resets all drawer state.
- `layouts/sorting.blade.php` now has a mobile menu button, compact
  breadcrumbs, and a viewport-safe notification dropdown. Main page padding
  moved to `.app-main` with responsive `--app-main-x` / `--app-main-y`
  variables.
- Reports filters use a `1 / 2 / 4` column grid and Generate goes
  full-width when narrow. Report table headers wrap and tables scroll
  inside a `min-w-[720px]` wrapper instead of widening the page.
- Chat switches to a single pane below `sm`: the list by default, the
  thread when a conversation is selected, with a back button. Heights use
  `100dvh`; header, replies, and composer are responsive. Dark day labels,
  metadata, links, and placeholders were corrected.
- Dark contrast: `--primary` is `#3f68df` (white text 4.93:1),
  `.dark .text-primary` is `#8fb0ff` (7.84:1 on dark cards),
  `--destructive-foreground` is white, and `.dark .btn-danger` is
  `#b91c1c` (6.47:1). Nav badges use red-600, with a white/primary active
  badge.
- `ReportService` counts operating days inclusively without float drift:
  `max(1, startOfDay(from)->diffInDays(startOfDay(to)) + 1)`.
  `ReportsController` computes `prevFrom` with `subDays($daysCount - 1)`
  so both periods cover the same number of days.
- Added a regression test for the inclusive day count and the daily-average
  format.

### Verification
- `npm run build`, `php artisan view:cache`, and Pint passed.
- Full suite: 92 passed, 841 assertions. ReportService + Metrics: 23
  passed, 217 assertions.
- Layout audit at 1440/768/390 across all main pages: no document-level
  horizontal overflow; the only off-screen element is the intentionally
  hidden mobile sidebar.
- Sidebar interaction checks passed: open/close button, backdrop, Escape,
  nav close, focus restore, `inert`, scroll lock, desktop reset, and no
  console or network errors.
- Rechecked stage 1 pages after the layout changes: 13/13 pages clean, no
  visible modals on load, dashboard charts defined, zero errors/HTTP
  failures. Rechecked all 12 report states: correct KPIs/charts/tables,
  zero errors.
- Chat at 390px and dark desktop: single pane, working back button,
  viewport-contained composer, readable metadata, zero errors.
- Mobile danger confirmation: 358x273 dialog, 44px-high red/white confirm
  button, no overflow.

## UI stabilization pass 4 - full end-to-end self-test (2026-10-26)

Goal: exercise every main workflow and page against a clean, seeded
`laravel_testing` database at desktop, tablet, and phone widths.

### Bugs found
- Delivery Assignment at phone widths squeezed the four stat cards into one
  row, clipped the duty table, and let custom modals run nearly edge-to-edge.
- Two Stage 4 browser assertions checked the wrong thing: the drawer locks
  scrolling with `body.sidebar-locked` (not an inline `overflow` style), and
  "Scan all arrived" correctly stays visible but disabled after it empties the
  queue.

### What changed
- Delivery Assignment now uses `2 / 1` stat-card columns below 900px / 520px,
  a `min-width: 620px` duty table inside a horizontal scroll wrapper, and
  custom modals capped at `calc(100% - 2rem)`.
- The browser checks now verify the real drawer class/computed overflow and
  the post-scan state: disabled Scan All button, empty queue message, and
  "6 parcels scanned." flash.

### Verification
- Route audit: 20 pages x 3 viewports (60 rows), zero console errors, zero
  network/HTTP failures, zero document-level horizontal overflow.
- Workflow E2E: 35/35 checks passed, covering dashboard/scan search,
  notifications, incoming lookup + scan/scan-all, auto-clustering, assignment
  modals + dispatch, delivery confirmation, rider toggles/KYC gating, branch
  checklist submit, chat send, account save, mark-all-read, mobile drawer,
  and dark-mode persistence.
- Full suite: 92 passed, 841 assertions. Pint, `npm run build`, and
  `php artisan view:cache` all passed.

## Roadmap

- Stage 4 self-test is complete; no open regressions from the 60-row
  viewport audit or the 35-check workflow pass.
- Next: production hardening (authentication, deployment, backups) and real
  courier/notification integrations.

Say which one you want built next and I'll do it the same way: migrations
→ model → controller → routes → Blade view, styled with the Tailwind
tokens above (or the legacy CSS, your call).
