# Mimoo Sorting Center Workflow Guide

This guide explains what each Sorting Center page does, where its actions
lead, how a parcel moves through the hub, and what currently connects to the
buyer, seller, rider, and administrator experiences.

## At a glance

The Sorting Center is the operations workspace for parcels already recorded in
its `parcels` table. Staff use it to receive, sort, weigh, assign, dispatch,
track, and reconcile parcels. The main operational path is:

```text
Parcel record exists
  -> Incoming Parcels: scan arrival at hub
  -> Sort & Assign: determine barangay, choose carrier, assign route/rider
  -> Staging Bay: review parcels and record weights
  -> Dispatch List: save/print the planned dispatch manifest
  -> Loading Docks / Dispatch: push eligible parcels to their riders
  -> Delivery Monitoring: progress delivery or record an unsuccessful attempt
  -> Delivered: collect COD and reconcile rider remittance when applicable
```

Operational lists, dashboard metrics, and reports read the parcel records.
Status logs and activity records provide internal history. Some shipment
workflow paths also create separate shipment-event and notification records
for API consumers. These are distinct records; do not assume every change made
through a web page is automatically visible in the buyer tracking API.

## Pages in the Sorting Center

The sidebar links to the following pages. The route is included to make it
clear where each page lives.

| Page | Route | What it is for |
|---|---|---|
| Dashboard | `/dashboard` | Hub overview: parcel and rider metrics, recent shipments, activity, and links into work queues. It summarizes live operational data; it is not a separate order-entry page. |
| Incoming Parcels | `/parcels/incoming` | Lists parcels in the drop-off queue. Staff scan a parcel or scan the queue, moving parcels from `dropped_off` to `awaiting_sort`. Search, filters, selected-parcel details, and the recent scans help locate work. |
| Sort & Assign | `/parcels/sort` | Displays parcels awaiting sort and sorted/assigned area clusters. Staff match the address to a known barangay, correct the area when needed, select a carrier, and assign the parcel to the rider for that route. Bulk assignment and archive controls are also available where applicable. |
| Dispatch Assignment / Loading Docks | `/delivery/assignment` | Shows sorted parcels grouped by destination area and rider. Staff can inspect a route, dispatch its parcels or eligible parcels across areas, and use the loading-dock confirmation before dispatch. A route without an active rider is not dispatchable. |
| Dispatch List | `/delivery/assignment/dispatch-list` | Generates a saved, printable dispatch manifest from eligible sorted parcels. A manifest is a snapshot for review/printing; generating it does not itself dispatch parcels. |
| Staging Bay Weigh-ins | `/delivery/assignment/weigh-ins` | Lists sorted parcels awaiting dispatch. Staff enter a parcel weight; the current parcel weight is updated and a separate weigh-in history record is saved. Only parcels still in the sorted staging state can be weighed here. |
| Delivery Monitoring | `/delivery/monitoring` | Tracks dispatched/in-delivery/delivered/failed or archived parcels. Staff can advance a parcel through delivery, record an undelivered scan, inspect custody history, and archive/restore where available. |
| Riders | `/riders` | Reviews rider applications and manages the approved fleet. Staff manually verify ID/license and enter document expiry dates, verify the applicant's phone by OTP, approve/disapprove applications, assign one rider to a barangay, change shift availability, or suspend/reactivate a rider. |
| Branches & Checklists | `/branches` | Shows configured branch/hub locations and shift-based operational checklists. Staff can add notes/photos and submit a checklist; a submitted checklist is locked for that shift. |
| Zones & Rates | `/zones` | Maintains serviceable zones, regions, listed barangays/areas, and weight-based shipping fee tiers. These are read by the shipping quote endpoint. |
| COD & Remittance | `/cod` | Groups delivered, collected, non-prepaid COD by rider. Staff record the amount collected and a reference, then confirm a remittance. Confirmation marks eligible parcels as remitted and reconciles the rider balance. |
| Reports | `/reports` | Builds parcel, delivery, rider, branch, and overdue reports over a date range with optional barangay/carrier filters. Reports can be exported as CSV, XLSX, or PDF. |
| Support | `/support` | Creates and manages issue tickets, including parcel/rider/system/COD concerns. Staff can reply, update ticket state, and add a resolution note when closing. |
| Messages | `/chat` | Internal conversation inbox for the conversation records already in this application. Staff can read and reply; it is not currently a live bridge to buyer/seller messaging in the marketplace app. |
| Account | `/account` | Updates the Sorting Center hub's contact and profile information. Covered barangays come from area/rider configuration rather than this form. |

### Global layout functions

- **Search** finds records supported by the search controller and takes staff to
  the relevant operational page.
- **Scan** in the top bar can look up a tracking number and link into the
  relevant parcel queue.
- **Notifications/activity** summarize operational alerts and recent recorded
  activity. Shipment notifications are stored in the application; they are not
  proof that an external SMS, email, or push message was delivered.
- **Preview pages** under `/preview/*` use sample data for UI review. They do
  not write real parcel, rider, or checklist records.

## Parcel process in detail

### 1. Parcel enters the Sorting Center

Incoming Parcels expects the parcel to already exist in the Sorting Center's
database with tracking number, recipient/address, seller/buyer details as
available, and initial status `dropped_off`. Scanning it at the inbound dock
records arrival and moves it to `awaiting_sort`. Scan-all applies the same
transition to the current drop-off queue.

The current Sorting Center API does not expose a parcel-creation endpoint.
Therefore an outside system or an operational data-entry process must create
the parcel record before it appears in this queue.

### 2. Sort to a destination and assign a rider

On Sort & Assign, staff determine a destination barangay from the address or
set the area manually. The system uses the configured areas and rider routes;
the intended setup is one rider per barangay. Staff may set the shipping
carrier and assign a suitable rider. Parcels that have been sorted appear in
the staging/cluster view.

### 3. Weigh and prepare dispatch

In the Staging Bay, staff review sorted parcels and record measured weights.
Each weigh-in keeps the latest parcel weight and an individual historical
record. Staff can generate a dispatch list to save a printable manifest grouped
by destination/rider. This manifest supports review and hand-off but does not
change the parcels to dispatched.

### 4. Push parcels to loading docks / dispatch

Staff dispatch a route or eligible routes from the Dispatch Assignment page.
Dispatch checks that a route has an active rider, then changes the selected
sorted parcels to `assigned`, records assignment time and history, and makes
them available to the delivery workflow. If a rider is inactive or missing,
the affected route must be corrected before dispatch.

### 5. Delivery and exceptions

Delivery Monitoring supports the normal sequence:

```text
assigned -> out_for_delivery -> delivered
```

If delivery fails, staff record the failed attempt/reason according to the
available action. Parcels can be retried or returned/handled through the
shipment workflow. Custody events and status logs keep the recorded trail.
Delivery OTP verification has an API endpoint, but this codebase does not
currently provide a buyer-facing delivery-code sending flow.

### 6. COD settlement

Delivered COD parcels are grouped by their assigned rider. Recording a
remittance creates a pending remittance entry with expected amount, collected
amount, difference, and reference. A staff confirmation then marks the
eligible parcels as remitted and updates the rider's COD balance.

## Buyer and seller connection

There are two applications in this repository:

1. **`bsa`** is the marketplace application. Buyers can use its cart and
   checkout; sellers manage products and seller order records there.
2. **`SortingCenter`** is the hub operations application. Its logistics pages
   operate on Sorting Center `parcels`, areas, riders, events, and manifests.

The Sorting Center exposes these public API integration points:

| Endpoint | Use |
|---|---|
| `GET` or `POST /api/shipping/quote` | Given an address/region and parcel weight, calculate a quote from the configured serviceable zones and weight tiers. |
| `GET /api/shipments/{trackingNo}/tracking` | Return the parcel tracking summary and recorded shipment events. |
| `POST /api/shipments/{trackingNo}/otp` | Validate a delivery OTP supplied for a parcel. |

Protected Sorting Center APIs (parcel operations, riders, courier actions,
dispatch, activities, and audit data) are grouped behind Laravel Sanctum token
authentication. The public quote/tracking endpoints are not token-protected.

**Important current integration gap:** no code was found that sends a `bsa`
checkout order to Sorting Center to create a parcel, or that calls the
Sorting Center quote endpoint from the marketplace checkout. The marketplace
stores buyer orders in its `orders` workflow; the Sorting Center pages read
from their own `parcels` workflow. No automatic order-to-parcel sync or shared
buyer/seller shipment-status display is currently wired. As a result:

- A buyer checkout does not automatically create a parcel in the Sorting
  Center.
- A seller's order update does not automatically move a Sorting Center parcel.
- Buyer-facing shipment notifications/tracking are not automatically attached
  to marketplace orders.

To connect them end to end, an integration still needs to call the shipping
quote during checkout, create or transmit a parcel after an order is accepted,
persist a durable mapping between marketplace order ID and Sorting Center
parcel/tracking number, and expose tracking/status updates in buyer and seller
order views. That integration should be implemented using authenticated,
validated server-to-server calls (or a shared service/event queue), not by
trusting browser-submitted price or status values.

## Intended end-to-end flow by role

The flow below describes how the marketplace and hub are supposed to work
together once the integration is built. It is a target workflow, not a claim
that these cross-application handoffs are already active.

### Buyer

1. The buyer browses active products, adds items to the cart, and enters a
   delivery address and phone number at checkout.
2. Checkout should request a shipping quote for the destination and the
   shipment's chargeable weight. The buyer should see item subtotal, shipping
   fee, payment method, and final total before placing the order.
3. After the order is accepted, the buyer should see an order number and,
   once a parcel is created, its tracking number and shipment progress.
4. The buyer follows the tracking page as the parcel is received, sorted,
   dispatched, and delivered. If a delivery confirmation code is required, the
   buyer provides that code to the courier; the current project has an OTP
   verification endpoint but no complete buyer code-delivery flow.

**Current behavior:** marketplace checkout creates a marketplace `Order` and
clears the buyer's cart. It does not call the Sorting Center quote endpoint,
create a Sorting Center parcel, or provide a connected shipment timeline in
the buyer order view.

### Seller

1. The seller must first be approved in the marketplace admin area and list
   products that are approved/available for purchase.
2. When a buyer purchases the seller's item, the order appears in the seller
   order list with buyer delivery information and the seller's own line items.
3. The seller reviews the order, accepts it, prepares/packs it, and hands it to
   the pickup courier. Seller order actions currently include accept/pack and
   pickup/shipped updates.
4. At hand-off, the intended integration creates or updates the linked
   Sorting Center parcel and shares its tracking number with the seller and
   buyer. From that point, delivery progress should come from the courier and
   Sorting Center, rather than a seller manually claiming delivery is complete.
5. If a parcel is returned, the seller should receive the return status and
   confirm receipt so inventory/refund handling can finish where applicable.

**Current behavior:** seller actions update the marketplace order only.
They do not create or update a parcel in the Sorting Center. Return receipt
handling exists in the Sorting Center shipment service, but it is not shown as
a seller-side marketplace handoff.

### Marketplace admin

1. The marketplace admin reviews marketplace registrations and seller
   applications, approves/disapproves sellers, and reviews products/reports.
2. Admin activity governs who may sell and what products can be listed.
3. If an order or seller needs operational investigation, admin can review
   marketplace records, but this admin area does not currently dispatch
   parcels or manage hub riders.

The marketplace admin is separate from Sorting Center operations. A future
integration may provide a shared order/parcel lookup, but the current admin
routes do not provide that cross-system view.

### Sorting Center staff / hub operator

1. **Rider onboarding:** staff review the rider's ID and license, enter expiry
   dates, verify the rider's phone, then approve or reject the rider.
2. **Inbound:** a parcel record must reach the Sorting Center first. Staff scan
   it in Incoming Parcels to acknowledge hub arrival.
3. **Sorting:** staff determine the barangay, choose the carrier, and assign
   the parcel to the rider for that route.
4. **Staging:** staff weigh the parcel and review the staging list. The
   dispatch manifest is prepared for hand-off; generating it alone does not
   dispatch the parcel.
5. **Loading dock:** staff confirm dispatch to an active route/rider. The
   parcel becomes assigned and should then appear in that courier's workload.
6. **Delivery oversight:** staff monitor progress, record delivery exceptions,
   and handle return/lost/damaged workflows. Delivered COD collections are
   reconciled through COD & Remittance.

**Current behavior:** these steps work on records already in the Sorting
Center database. The marketplace does not automatically create those parcel
records today.

### Rider / courier

The Sorting Center has protected courier API endpoints, but no complete
rider-facing mobile/web application is included in this repository. A future
courier client is intended to:

1. Sign in and request the rider's authorized workload.
2. Show parcels assigned to that rider, including tracking number, destination,
   and delivery information needed to complete the route.
3. Claim an eligible parcel when allowed. The API checks that the rider is
   active, that another rider does not own the parcel, and that its barangay
   matches the rider's route when the route is configured.
4. Advance a parcel from assigned to out-for-delivery, then to delivered.
   Failed attempts need a reason and may be retried or returned according to
   the shipment rules.
5. Send each accepted change to the Sorting Center API so the parcel history
   and operations views can be updated. The buyer/seller should then see those
   updates only after the missing marketplace integration consumes and
   displays them.

Courier API routes are `GET /api/courier/{rider}`,
`POST /api/courier/{rider}/claim`, and
`POST /api/courier/{rider}/advance`; they require Sanctum authentication.
The rider authorization check limits a rider-role token to its linked rider
record. These endpoints are not themselves a rider app, and the current
default SMS sender logs verification codes instead of sending real SMS.

### Handoff/data that the integration needs

For a reliable connected flow, the marketplace and Sorting Center need a
stable relationship between an order and one or more parcels:

1. Marketplace checkout obtains the authoritative shipping quote server-side.
2. After seller acceptance/pickup readiness, a trusted server-side integration
   creates the parcel record(s), copying the agreed buyer contact/destination,
   seller/order reference, payment/COD details, and chargeable weight.
3. It stores the resulting parcel ID and tracking number against the
   marketplace order (or seller-specific order item group). Decide explicitly
   whether a multi-seller marketplace order becomes one shipment or several.
4. Courier and hub updates are exposed to buyer and seller order views using
   the stored mapping. Retried requests must be idempotent so they do not
   create duplicate parcels or duplicate charges.
5. Real SMS/email/push delivery, buyer delivery-code issuance, access controls,
   and reconciliation/error handling must be configured before this is used
   as a production logistics connection.

## Admin and access boundaries

The Sorting Center sidebar is an operations workspace for hub staff. Its pages
include rider verification, dispatch, reports, support, and hub setup, but
there is no separate role-specific admin UI inside that sidebar. In the
current web routes, the Sorting Center pages themselves are not grouped behind
an authentication middleware; the `SIGN_IN_ENABLED` flag controls whether
login/logout routes are exposed, but it does not by itself protect the
operations pages. Do not treat this configuration as production access
control.

The marketplace has a separate `/admin` area. It uses its own master-code
verification/session and covers marketplace registrations, seller approval,
product approval, and product reports. It does not automatically administer
Sorting Center parcels, riders, zones, or COD remittances.

The Sort Center rider SMS sender currently logs OTP messages through
`LogSmsSender`; a real SMS provider must be configured for actual delivery.
Likewise, stored shipment notifications and API support for tracking do not
alone deliver SMS/email/push or connect those notices to marketplace accounts.

## Useful route references

- Web page/actions: `SortingCenter/routes/web.php`
- API endpoints: `SortingCenter/routes/api.php`
- Marketplace buyer/seller/admin routes: `bsa/routes/web.php`
- Parcel processing rules and history: `SortingCenter/app/Services/ParcelStatusService.php`
- Shipment events and notifications: `SortingCenter/app/Services/ShipmentStatusService.php`
- Shipping quote calculation: `SortingCenter/app/Services/ShippingQuoteService.php`
