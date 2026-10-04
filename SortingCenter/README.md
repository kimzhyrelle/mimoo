# Sorting Center — Laravel Backend (Stage 1–4)

This package contains the backend files for the Sorting Center module of
Catoo, built to match the sc-*.html mockups. Stage 3 restyled the shared
layout and dashboard with Tailwind v4 using the shadcn-style design tokens
(`resources/css/app.css`). **Stage 4 removes login entirely** — every page
is now public, no session/auth required, so you can iterate on the UI
without setting up a database at all if you use the `/preview/*` routes
below, or against real data once you do migrate.

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

## Roadmap

- Delivery Assignment (as its own standalone page, if still needed
  alongside Sort Parcels)
- Reports
- Chat / Messaging

Say which one you want built next and I'll do it the same way: migrations
→ model → controller → routes → Blade view, styled with the Tailwind
tokens above (or the legacy CSS, your call).
