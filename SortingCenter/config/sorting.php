<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sorting Center SLA Configuration
    |--------------------------------------------------------------------------
    | Configurable hours from received_at before a parcel is considered overdue.
    */
    'sla_hours' => (int) env('SORTING_SLA_HOURS', 24),

    /*
    |--------------------------------------------------------------------------
    | Current company (no login)
    |--------------------------------------------------------------------------
    | The app has no authentication by design. Every record route is scoped to
    | the current company stored in the session, falling back to Logistics 1.
    */
    'default_company_id' => (int) env('SORTING_DEFAULT_COMPANY_ID', 1),

    'sms' => [
        'driver' => env('SMS_DRIVER', 'log'),
        'from' => env('SMS_FROM', 'Mimoo'),
        'semaphore_key' => env('SEMAPHORE_API_KEY'),
        'twilio_sid' => env('TWILIO_ACCOUNT_SID'),
        'twilio_token' => env('TWILIO_AUTH_TOKEN'),
        'twilio_from' => env('TWILIO_FROM'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Hub Identity
    |--------------------------------------------------------------------------
    | Single default used when the hub profile row is created. The Account page
    | remains the editable source of truth (hub_profiles.name) once it exists.
    */
    'hub_name' => env('SORTING_HUB_NAME', 'Laguna Hub (Santa Cruz)'),

    /*
    |--------------------------------------------------------------------------
    | Shift Windows
    |--------------------------------------------------------------------------
    | Checklists are created per shift and lock once submitted. The scheduler
    | calls checklist:reset at each shift boundary to prepare the next list.
    */
    'shifts' => [
        'morning' => ['label' => 'Morning (06:00 - 14:00)', 'start' => '06:00', 'end' => '14:00'],
        'afternoon' => ['label' => 'Afternoon (14:00 - 22:00)', 'start' => '14:00', 'end' => '22:00'],
        'night' => ['label' => 'Night (22:00 - 06:00)', 'start' => '22:00', 'end' => '06:00'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Branch Checklist Definitions
    |--------------------------------------------------------------------------
    | Items are seeded into the database the first time a shift is opened so
    | notes, photos, completion, and lock state can be tracked per submission.
    */
    'branch_checklists' => [
        'inbound' => [
            'title' => 'Inbound Manifest Triage',
            'station' => 'Bay 4 Inbound',
            'items' => [
                ['key' => 'truck_seal', 'title' => 'Verify truck seal integrity', 'body' => 'Check that container seal numbers match the linehaul cargo manifest prior to unloading.'],
                ['key' => 'qr_scan', 'title' => "Scan every parcel's QR code", 'body' => 'Ensure all inbound packages are verified with optical QR scan against the electronic waybill (NO barcodes).'],
                ['key' => 'damage_weight', 'title' => 'Inspect for damage and weight differences', 'body' => 'Record any crushed boxes or tare discrepancies (>0.05kg) in the triage log before sorting line clearance.'],
            ],
        ],
        'outbound' => [
            'title' => 'Outbound Dispatch Triage',
            'station' => 'Dock B Outbound',
            'items' => [
                ['key' => 'manifest_qr', 'title' => 'Confirm manifest QR matches loaded bin', 'body' => 'Scan each cluster manifest QR and confirm the parcel count against the loading sheet.'],
                ['key' => 'courier_assignment', 'title' => 'Verify courier assignment per route', 'body' => 'Every destination cluster must have one assigned courier before the dock gate opens.'],
                ['key' => 'seal_log', 'title' => 'Seal and log outbound vehicle', 'body' => 'Apply a numbered seal, record it on the dispatch ledger and photograph the closed load.'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Expected Custody Scan Points
    |--------------------------------------------------------------------------
    | Standard custody checkpoints:
    | 1. Inbound Dock Scan
    | 2. Sorting & Bin Allocation Scan
    | 3. Dispatch Bay Hand-off Scan
    | 4. Delivery Hand-off / Confirmation Scan
    */
    'expected_scans' => 4,

    /*
    |--------------------------------------------------------------------------
    | Sign-in Feature Flag
    |--------------------------------------------------------------------------
    | Login is disabled on this public hub. Set SIGN_IN_ENABLED=true to bring
    | the sorting-center login routes back. When the flag is absent it defaults
    | to ON only in production.
    */
    'sign_in_enabled' => filter_var(
        env('SIGN_IN_ENABLED', env('APP_ENV', 'production') === 'production'),
        FILTER_VALIDATE_BOOLEAN
    ),

    /*
    |--------------------------------------------------------------------------
    | Rider Approval Authorization Hook
    |--------------------------------------------------------------------------
    | TODO(admin-auth): flip this to true once admin roles are restored.
    */
    'require_admin_for_rider_approval' => filter_var(
        env('SORTING_REQUIRE_ADMIN_FOR_RIDER_APPROVAL', false),
        FILTER_VALIDATE_BOOLEAN
    ),
];
