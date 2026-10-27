<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Area;
use App\Models\Branch;
use App\Models\Conversation;
use App\Models\HubProfile;
use App\Models\IssueReport;
use App\Models\Message;
use App\Models\Parcel;
use App\Models\ParcelStatusLog;
use App\Models\Rider;
use App\Models\RiderApplication;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SortingCenterSeeder extends Seeder
{
    public function run(): void
    {
        // --- Hub profile (Account page) ---
        HubProfile::current();

        // --- Staff logins ---
        User::firstOrCreate(
            ['email' => 'hub@santacruzhub.ph'],
            ['name' => 'Laguna Hub', 'password' => Hash::make('password'), 'role' => 'staff', 'initials' => 'LG']
        );
        User::firstOrCreate(
            ['email' => 'supervisor@santacruzhub.ph'],
            ['name' => 'Ana Reyes', 'password' => Hash::make('password'), 'role' => 'staff', 'initials' => 'AR']
        );
        User::firstOrCreate(
            ['email' => 'sorter1@santacruzhub.ph'],
            ['name' => 'Ben Torres', 'password' => Hash::make('password'), 'role' => 'staff', 'initials' => 'BT']
        );

        // --- Areas (barangays), with today's pickup schedule ---
        $areasData = [
            ['code' => 'R01', 'name' => 'Poblacion I',    'color' => '#6C4FD8', 'window' => '9:00 AM – 11:00 AM',  'cutoff' => '10:30 AM', 'status' => 'On schedule'],
            ['code' => 'R02', 'name' => 'Bubukal',        'color' => '#2A8FBD', 'window' => '9:30 AM – 11:30 AM',  'cutoff' => '11:00 AM', 'status' => 'On schedule'],
            ['code' => 'R03', 'name' => 'Palasan',        'color' => '#1FA391', 'window' => '10:00 AM – 12:00 PM', 'cutoff' => '11:30 AM', 'status' => '2 sellers not ready'],
            ['code' => 'R04', 'name' => 'Gatid',          'color' => '#5B2FC7', 'window' => '10:30 AM – 12:30 PM', 'cutoff' => '12:00 PM', 'status' => 'On schedule'],
            ['code' => 'R05', 'name' => 'Duhat',          'color' => '#C0432F', 'window' => '11:00 AM – 1:00 PM',  'cutoff' => '12:30 PM', 'status' => 'On schedule'],
            ['code' => 'R06', 'name' => 'Buenavista',     'color' => '#D97706', 'window' => '11:30 AM – 1:30 PM',  'cutoff' => '1:00 PM',  'status' => 'On schedule'],
            ['code' => 'R07', 'name' => 'Magsaysay',      'color' => '#059669', 'window' => '12:00 PM – 2:00 PM',  'cutoff' => '1:30 PM',  'status' => 'On schedule'],
            ['code' => 'R08', 'name' => 'Nanhaya',        'color' => '#DB2777', 'window' => '12:30 PM – 2:30 PM',  'cutoff' => '2:00 PM',  'status' => 'On schedule'],
            ['code' => 'R09', 'name' => 'Bagong Pook',    'color' => '#0284C7', 'window' => '1:00 PM – 3:00 PM',   'cutoff' => '2:30 PM',  'status' => 'On schedule'],
            ['code' => 'R10', 'name' => 'Lumban',         'color' => '#7C3AED', 'window' => '1:30 PM – 3:30 PM',   'cutoff' => '3:00 PM',  'status' => 'On schedule'],
            ['code' => 'R11', 'name' => 'Poblacion II',   'color' => '#B45309', 'window' => '2:00 PM – 4:00 PM',   'cutoff' => '3:30 PM',  'status' => 'On schedule'],
        ];
        $areas = collect($areasData)->mapWithKeys(fn ($a) => [$a['code'] => Area::updateOrCreate(
            ['code' => $a['code']],
            ['name' => $a['name'], 'chart_color' => $a['color'], 'pickup_window' => $a['window'], 'cutoff_time' => $a['cutoff'], 'pickup_status' => $a['status']]
        )]);

        // --- Branch scoping ---
        $hub = Branch::withoutGlobalScopes()->where('code', 'HUB-001')->first();

        // --- Shipping zones and rates ---
        $zonesData = [
            [
                'name' => 'Santa Cruz North',
                'region' => 'Laguna',
                'areas' => ['R01', 'R02', 'R03', 'R04', 'R05', 'R06'],
                'rates' => [
                    ['name' => 'Standard', 'base_fee' => 45, 'per_kg_fee' => 12, 'min_weight_kg' => 0.01, 'max_weight_kg' => 5],
                    ['name' => 'Heavy', 'base_fee' => 85, 'per_kg_fee' => 18, 'min_weight_kg' => 5.01, 'max_weight_kg' => 50],
                ],
            ],
            [
                'name' => 'Santa Cruz South',
                'region' => 'Laguna',
                'areas' => ['R07', 'R08', 'R09', 'R10', 'R11'],
                'rates' => [
                    ['name' => 'Standard', 'base_fee' => 55, 'per_kg_fee' => 14, 'min_weight_kg' => 0.01, 'max_weight_kg' => 5],
                    ['name' => 'Heavy', 'base_fee' => 95, 'per_kg_fee' => 20, 'min_weight_kg' => 5.01, 'max_weight_kg' => 50],
                ],
            ],
        ];

        foreach ($zonesData as $z) {
            $zone = ShippingZone::updateOrCreate(
                ['company_id' => 1, 'name' => $z['name']],
                ['region' => $z['region'], 'areas' => $z['areas'], 'is_serviceable' => true]
            );

            foreach ($z['rates'] as $rate) {
                ShippingRate::updateOrCreate(
                    ['shipping_zone_id' => $zone->id, 'name' => $rate['name']],
                    $rate + ['company_id' => 1, 'is_active' => true, 'effective_from' => now()->subDays(30)]
                );
            }
        }

        // --- Riders (each with their own login + vehicle + duty status) ---
        // 13 Active riders, 2 Inactive (R4, R14).
        // 11 On Duty (checked in today): R1, R2, R3, R5, R6, R7, R8, R9, R10, R11, R12.
        // 5 In Route (busy with out_for_delivery parcels): R1, R2, R3, R6, R8.
        // 6 Idle (available on duty): R5, R7, R9, R10, R11, R12.
        // 2 Active but Off Duty: R13, R15.
        // Idle (6) + In Route (5) = On Duty (11).
        $checkinTime = Carbon::today()->setTime(7, 30);
        $ridersData = [
            ['name' => 'J. Ramos',        'initials' => 'R1',  'area' => 'R01', 'status' => 'busy',      'is_active' => true,  'checked_in_at' => $checkinTime, 'vehicle' => 'Motorcycle', 'plate' => 'NBC 1234', 'email' => 'j.ramos@santacruzhub.ph'],
            ['name' => 'M. Cruz',         'initials' => 'R2',  'area' => 'R02', 'status' => 'busy',      'is_active' => true,  'checked_in_at' => $checkinTime, 'vehicle' => 'Motorcycle', 'plate' => 'NDC 4471', 'email' => 'm.cruz@santacruzhub.ph'],
            ['name' => 'A. Santos',       'initials' => 'R3',  'area' => 'R03', 'status' => 'busy',      'is_active' => true,  'checked_in_at' => $checkinTime, 'vehicle' => 'Tricycle',   'plate' => 'TRY 0092', 'email' => 'a.santos@santacruzhub.ph'],
            ['name' => 'P. Reyes',        'initials' => 'R4',  'area' => null,  'status' => 'offline',   'is_active' => false, 'checked_in_at' => null,         'vehicle' => 'Motorcycle', 'plate' => 'NEF 8820', 'email' => 'p.reyes@santacruzhub.ph'],
            ['name' => 'K. Villar',       'initials' => 'R5',  'area' => 'R05', 'status' => 'available', 'is_active' => true,  'checked_in_at' => $checkinTime, 'vehicle' => 'Motorcycle', 'plate' => 'NGH 5510', 'email' => 'k.villar@santacruzhub.ph'],
            ['name' => 'B. Flores',       'initials' => 'R6',  'area' => 'R06', 'status' => 'busy',      'is_active' => true,  'checked_in_at' => $checkinTime, 'vehicle' => 'Motorcycle', 'plate' => 'NMB 2231', 'email' => 'b.flores@santacruzhub.ph'],
            ['name' => 'E. Navarro',      'initials' => 'R7',  'area' => 'R07', 'status' => 'available', 'is_active' => true,  'checked_in_at' => $checkinTime, 'vehicle' => 'Motorcycle', 'plate' => 'NJD 9901', 'email' => 'e.navarro@santacruzhub.ph'],
            ['name' => 'T. Lim',          'initials' => 'R8',  'area' => 'R08', 'status' => 'busy',      'is_active' => true,  'checked_in_at' => $checkinTime, 'vehicle' => 'Tricycle',   'plate' => 'TRY 1133', 'email' => 't.lim@santacruzhub.ph'],
            ['name' => 'C. Delos Santos', 'initials' => 'R9',  'area' => 'R09', 'status' => 'available', 'is_active' => true,  'checked_in_at' => $checkinTime, 'vehicle' => 'Motorcycle', 'plate' => 'NKP 4420', 'email' => 'c.delossantos@santacruzhub.ph'],
            ['name' => 'R. Bautista',     'initials' => 'R10', 'area' => null,  'status' => 'available', 'is_active' => true,  'checked_in_at' => $checkinTime, 'vehicle' => 'Motorcycle', 'plate' => 'NLP 7784', 'email' => 'r.bautista@santacruzhub.ph'],
            ['name' => 'M. Aquino',       'initials' => 'R11', 'area' => 'R11', 'status' => 'available', 'is_active' => true,  'checked_in_at' => $checkinTime, 'vehicle' => 'Motorcycle', 'plate' => 'NMR 3309', 'email' => 'm.aquino@santacruzhub.ph'],
            ['name' => 'F. Dela Cruz',    'initials' => 'R12', 'area' => 'R04', 'status' => 'available', 'is_active' => true,  'checked_in_at' => $checkinTime, 'vehicle' => 'Motorcycle', 'plate' => 'NBS 6612', 'email' => 'f.delacruz@santacruzhub.ph'],
            ['name' => 'G. Mendoza',      'initials' => 'R13', 'area' => null,  'status' => 'offline',   'is_active' => true,  'checked_in_at' => null,         'vehicle' => 'Motorcycle', 'plate' => 'NCP 8841', 'email' => 'g.mendoza@santacruzhub.ph'],
            ['name' => 'L. Ocampo',       'initials' => 'R14', 'area' => null,  'status' => 'offline',   'is_active' => false, 'checked_in_at' => null,         'vehicle' => 'Tricycle',   'plate' => 'TRY 2204', 'email' => 'l.ocampo@santacruzhub.ph'],
            ['name' => 'N. Pascual',      'initials' => 'R15', 'area' => null,  'status' => 'offline',   'is_active' => true,  'checked_in_at' => null,         'vehicle' => 'Motorcycle', 'plate' => 'NDT 5567', 'email' => 'n.pascual@santacruzhub.ph'],
        ];

        $riders = collect();
        foreach ($ridersData as $r) {
            $user = User::firstOrCreate(
                ['email' => $r['email']],
                ['name' => $r['name'], 'password' => Hash::make('password'), 'role' => 'rider', 'initials' => $r['initials']]
            );

            $rider = Rider::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'company_id' => 1,
                    'branch_id' => $hub?->id,
                    'name' => $r['name'],
                    'initials' => $r['initials'],
                    'area_id' => isset($r['area']) ? ($areas[$r['area']]->id ?? null) : null,
                    'status' => $r['status'],
                    'is_active' => $r['is_active'],
                    'checked_in_at' => $r['checked_in_at'],
                    'vehicle_type' => $r['vehicle'],
                    'plate_no' => $r['plate'],
                ]
            );
            $riders[$r['initials']] = $rider;
        }

        // --- Pending rider applications ---
        $futureExpiry = Carbon::today()->addYear();
        $applications = [
            [
                'name' => 'D. Villanueva', 'vehicle_type' => 'Motorcycle', 'plate_no' => 'NGH 3391',
                'or_cr_verified' => true,  'license_status' => 'verified', 'applied_on' => Carbon::today()->subDays(2),  'status' => 'pending',
                'phone' => '09171234567', 'kyc_status' => 'verified',
                'checklist' => ['id_checked' => true, 'license_checked' => true, 'phone_verified' => true, 'docs_not_expired' => true],
                'or_cr_expires_on' => $futureExpiry, 'license_expires_on' => $futureExpiry,
                'phone_verified_at' => now(), 'verified_at' => now(),
            ],
            [
                'name' => 'K. Torres',     'vehicle_type' => 'Motorcycle', 'plate_no' => 'NKL 7742',
                'or_cr_verified' => true,  'license_status' => 'pending',  'applied_on' => Carbon::today()->subDay(),    'status' => 'pending',
                'phone' => '09182223333', 'kyc_status' => 'under_review',
                'checklist' => ['id_checked' => true, 'license_checked' => false, 'phone_verified' => false, 'docs_not_expired' => true],
                'or_cr_expires_on' => $futureExpiry, 'license_expires_on' => $futureExpiry,
            ],
            [
                'name' => 'R. Manalo',     'vehicle_type' => 'Tricycle',   'plate_no' => 'TRY 0511',
                'or_cr_verified' => false, 'license_status' => 'pending',  'applied_on' => Carbon::today(),              'status' => 'pending',
                'phone' => '09193334444', 'kyc_status' => 'pending',
                'checklist' => ['id_checked' => false, 'license_checked' => false, 'phone_verified' => false, 'docs_not_expired' => false],
            ],
            [
                'name' => 'S. Garcia',     'vehicle_type' => 'Motorcycle', 'plate_no' => 'NPR 4418',
                'or_cr_verified' => true,  'license_status' => 'verified', 'applied_on' => Carbon::today()->subDays(4),  'status' => 'pending',
                'phone' => '09204445555', 'kyc_status' => 'verified',
                'checklist' => ['id_checked' => true, 'license_checked' => true, 'phone_verified' => true, 'docs_not_expired' => true],
                'or_cr_expires_on' => $futureExpiry, 'license_expires_on' => $futureExpiry,
                'phone_verified_at' => now(), 'verified_at' => now(),
            ],
            [
                'name' => 'V. Hernandez',  'vehicle_type' => 'Motorcycle', 'plate_no' => 'NQS 8824',
                'or_cr_verified' => false, 'license_status' => 'pending',  'applied_on' => Carbon::today()->subDays(3),  'status' => 'pending',
                'phone' => '09215556666', 'kyc_status' => 'under_review',
                'checklist' => ['id_checked' => true, 'license_checked' => false, 'phone_verified' => false, 'docs_not_expired' => false],
            ],
            [
                'name' => 'O. Castillo',   'vehicle_type' => 'Tricycle',   'plate_no' => 'TRY 3317',
                'or_cr_verified' => true,  'license_status' => 'verified', 'applied_on' => Carbon::today()->subDays(5),  'status' => 'pending',
                'phone' => '09226667777', 'kyc_status' => 'verified',
                'checklist' => ['id_checked' => true, 'license_checked' => true, 'phone_verified' => true, 'docs_not_expired' => true],
                'or_cr_expires_on' => $futureExpiry, 'license_expires_on' => $futureExpiry,
                'phone_verified_at' => now(), 'verified_at' => now(),
            ],
        ];
        foreach ($applications as $a) {
            $existing = RiderApplication::firstOrCreate(['name' => $a['name']], $a);

            if (! $existing->wasRecentlyCreated) {
                $existing->update(array_intersect_key($a, array_flip([
                    'phone', 'kyc_status', 'checklist', 'or_cr_expires_on', 'license_expires_on',
                    'phone_verified_at', 'verified_at',
                ])));
            }
        }

        // --- Buyers pool ---
        $buyers = [
            'Maria Clara Santos',    'Eduardo Dela Cruz',  'Liza Soberano',      'Ramon Bautista Jr.',
            'Grace Poe',             'Jose Rizal Mercado', 'Andrea Villanueva',  'Paolo Reyes',
            'Carmela Aquino',        'Miguel Navarro',     'Theresa Castillo',   'Rolando Mendoza',
            'Imelda Ocampo',         'Fernando Pascual',   'Natividad Garcia',   'Simplicio Hernandez',
            'Corazon Flores',        'Benigno Lim',        'Estrella Manalo',    'Artemio Torres',
        ];

        $today = Carbon::today();

        // -------------------------------------------------------------------------
        // Parcels: spans every stage across all 11 barangays
        // -------------------------------------------------------------------------
        $sample = [
            // === Incoming — dropped off, awaiting scan ===
            ['no' => '#1011', 'addr' => '12 Rizal St., Purok 3, Poblacion I',       'seller' => 'Nilo\'s Sari-Sari',    'via' => 'R1',  'status' => 'dropped_off',     'minsAgo' => 12, 'carrier' => 'jnt',      'kg' => 0.8],
            ['no' => '#1012', 'addr' => '45 Mabini Ave., Zone 1, Bubukal',          'seller' => 'CraftedByLen',          'via' => 'R2',  'status' => 'dropped_off',     'minsAgo' => 6,  'carrier' => 'lbc',      'kg' => 1.2],
            ['no' => '#1013', 'addr' => 'Sitio Ilaya, Brgy. Bubukal',               'seller' => 'Bahay Kubo Snacks',     'via' => 'R3',  'status' => 'dropped_off',     'minsAgo' => 3,  'carrier' => 'jnt',      'kg' => 0.5],
            ['no' => '#1035', 'addr' => 'Purok 5, Brgy. Duhat',                     'seller' => 'TechZone Laguna',       'via' => 'R5',  'status' => 'dropped_off',     'minsAgo' => 9,  'carrier' => 'jnt',      'kg' => 2.3],
            ['no' => '#1036', 'addr' => 'Zone 1, Brgy. Gatid',                      'seller' => 'PH Clothing Co.',       'via' => 'R12', 'status' => 'dropped_off',     'minsAgo' => 4,  'carrier' => 'lalamove', 'kg' => 0.9],
            ['no' => '#1037', 'addr' => '3 Bonifacio St., Magsaysay',               'seller' => 'Farmville Produce',     'via' => 'R7',  'status' => 'dropped_off',     'minsAgo' => 14, 'carrier' => 'jnt',      'kg' => 3.1],
            ['no' => '#1038', 'addr' => 'Purok 2, Brgy. Lumban',                    'seller' => 'Garden Fresh PH',       'via' => 'R10', 'status' => 'dropped_off',     'minsAgo' => 2,  'carrier' => 'lbc',      'kg' => 1.5],

            // === Sort Parcels queue — awaiting_sort ===
            ['no' => '#0994', 'addr' => 'Purok 2, Brgy. Palasan',                   'seller' => 'RTW Junction',          'area' => 'R03', 'status' => 'awaiting_sort', 'minsAgo' => 38, 'carrier' => 'jnt',      'kg' => 1.0],
            ['no' => '#0996', 'addr' => 'Zone 2, Brgy. Poblacion I',                'seller' => 'MJ Gadget Store',       'area' => 'R01', 'status' => 'awaiting_sort', 'minsAgo' => 29, 'carrier' => 'lbc',      'kg' => 2.5],
            ['no' => '#1039', 'addr' => '7 Quezon St., Brgy. Nanhaya',              'seller' => 'Aling Rosa\'s Store',   'area' => 'R08', 'status' => 'awaiting_sort', 'minsAgo' => 22, 'carrier' => 'jnt',      'kg' => 0.7],
            ['no' => '#1040', 'addr' => 'Sitio Centro, Brgy. Bagong Pook',          'seller' => 'PetCare Corner',        'area' => 'R09', 'status' => 'awaiting_sort', 'minsAgo' => 17, 'carrier' => 'lalamove', 'kg' => 1.8],
            ['no' => '#1041', 'addr' => 'Zone 3, Brgy. Duhat',                      'seller' => 'Toybox Manila',         'area' => 'R05', 'status' => 'awaiting_sort', 'minsAgo' => 11, 'carrier' => 'jnt',      'kg' => 0.6],
            ['no' => '#1042', 'addr' => 'Purok 1, Brgy. Buenavista',                'seller' => 'Home Essentials PH',    'area' => 'R06', 'status' => 'awaiting_sort', 'minsAgo' => 8,  'carrier' => 'lbc',      'kg' => 4.0],
            ['no' => '#1043', 'addr' => 'Purok 3, Brgy. Poblacion II',              'seller' => 'Baker\'s Nook',         'area' => 'R11', 'status' => 'awaiting_sort', 'minsAgo' => 5,  'carrier' => 'jnt',      'kg' => 1.1],
            ['no' => '#1044', 'addr' => '22 Rizal St., Brgy. Lumban',               'seller' => 'Lakeview Produce',      'area' => 'R10', 'status' => 'awaiting_sort', 'minsAgo' => 45, 'carrier' => 'jnt',      'kg' => 2.2],
            ['no' => '#1045', 'addr' => 'Purok 4, Brgy. Magsaysay',                 'seller' => 'Rosales Dry Goods',     'area' => 'R07', 'status' => 'awaiting_sort', 'minsAgo' => 33, 'carrier' => 'lalamove', 'kg' => 0.9],

            // === Sorted — staged for dispatch ===
            ['no' => '#1001', 'addr' => '7 Aguinaldo St., Brgy. Bubukal',           'seller' => 'Home Essentials PH',    'area' => 'R02', 'status' => 'sorted',        'minsAgo' => 20, 'carrier' => 'jnt',      'kg' => 1.4],
            ['no' => '#1002', 'addr' => 'Purok 1, Brgy. Poblacion I',               'seller' => 'Aling Rosa\'s Store',   'area' => 'R01', 'status' => 'sorted',        'minsAgo' => 15, 'carrier' => 'lbc',      'kg' => 0.8],
            ['no' => '#1003', 'addr' => 'Purok 4, Brgy. Gatid',                     'seller' => 'Lakeview Produce',      'area' => 'R04', 'status' => 'sorted',        'minsAgo' => 10, 'carrier' => 'jnt',      'kg' => 3.2],
            ['no' => '#1005', 'addr' => 'Zone 2, Brgy. Poblacion I',                'seller' => 'Baker\'s Nook',         'area' => 'R01', 'status' => 'sorted',        'minsAgo' => 8,  'carrier' => 'lalamove', 'kg' => 1.7],
            ['no' => '#1006', 'addr' => 'Sitio Centro, Brgy. Palasan',              'seller' => 'Rosales Dry Goods',     'area' => 'R03', 'status' => 'sorted',        'minsAgo' => 5,  'carrier' => 'jnt',      'kg' => 2.0],
            ['no' => '#1020', 'addr' => '3 Kalayaan St., Buenavista',               'seller' => 'Garden Fresh PH',       'area' => 'R06', 'status' => 'sorted',        'minsAgo' => 18, 'carrier' => 'jnt',      'kg' => 1.3],
            ['no' => '#1021', 'addr' => 'Purok 2, Buenavista',                      'seller' => 'TechZone Laguna',       'area' => 'R06', 'status' => 'sorted',        'minsAgo' => 12, 'carrier' => 'lbc',      'kg' => 0.6],
            ['no' => '#1022', 'addr' => '10 Rizal Ave., Magsaysay',                 'seller' => 'PH Clothing Co.',       'area' => 'R07', 'status' => 'sorted',        'minsAgo' => 25, 'carrier' => 'jnt',      'kg' => 2.8],
            ['no' => '#1023', 'addr' => 'Zone 3, Brgy. Magsaysay',                  'seller' => 'Nilo\'s Sari-Sari',     'area' => 'R07', 'status' => 'sorted',        'minsAgo' => 14, 'carrier' => 'lalamove', 'kg' => 1.0],
            ['no' => '#1024', 'addr' => '5 Bonifacio St., Magsaysay',               'seller' => 'Bahay Kubo Snacks',     'area' => 'R07', 'status' => 'sorted',        'minsAgo' => 9,  'carrier' => 'jnt',      'kg' => 0.5],
            ['no' => '#1025', 'addr' => 'Sitio Ilaya, Brgy. Nanhaya',               'seller' => 'Home Essentials PH',    'area' => 'R08', 'status' => 'sorted',        'minsAgo' => 30, 'carrier' => 'jnt',      'kg' => 1.9],
            ['no' => '#1026', 'addr' => 'Purok 1, Brgy. Nanhaya',                   'seller' => 'RTW Junction',          'area' => 'R08', 'status' => 'sorted',        'minsAgo' => 11, 'carrier' => 'lbc',      'kg' => 2.1],
            ['no' => '#1027', 'addr' => '22 Mabini St., Bagong Pook',               'seller' => 'MJ Gadget Store',       'area' => 'R09', 'status' => 'sorted',        'minsAgo' => 22, 'carrier' => 'lalamove', 'kg' => 0.7],
            ['no' => '#1028', 'addr' => 'Zone 1, Brgy. Bagong Pook',                'seller' => 'Lakeview Produce',      'area' => 'R09', 'status' => 'sorted',        'minsAgo' => 8,  'carrier' => 'jnt',      'kg' => 3.5],
            ['no' => '#1029', 'addr' => 'Purok 4, Brgy. Bagong Pook',               'seller' => 'Rosales Dry Goods',     'area' => 'R09', 'status' => 'sorted',        'minsAgo' => 5,  'carrier' => 'jnt',      'kg' => 1.6],
            ['no' => '#1030', 'addr' => '8 Quezon St., Lumban',                     'seller' => 'Toybox Manila',         'area' => 'R10', 'status' => 'sorted',        'minsAgo' => 35, 'carrier' => 'jnt',      'kg' => 2.4],
            ['no' => '#1031', 'addr' => 'Purok 3, Brgy. Lumban',                    'seller' => 'Farmville Produce',     'area' => 'R10', 'status' => 'sorted',        'minsAgo' => 17, 'carrier' => 'lbc',      'kg' => 0.9],
            ['no' => '#1032', 'addr' => 'Sitio Centro, Lumban',                     'seller' => 'Baker\'s Nook',         'area' => 'R10', 'status' => 'sorted',        'minsAgo' => 7,  'carrier' => 'lalamove', 'kg' => 1.2],
            ['no' => '#1033', 'addr' => '14 Aguinaldo St., Poblacion II',            'seller' => 'CraftedByLen',          'area' => 'R11', 'status' => 'sorted',        'minsAgo' => 28, 'carrier' => 'jnt',      'kg' => 2.7],
            ['no' => '#1034', 'addr' => 'Zone 2, Brgy. Poblacion II',               'seller' => 'PetCare Corner',        'area' => 'R11', 'status' => 'sorted',        'minsAgo' => 13, 'carrier' => 'jnt',      'kg' => 1.1],
            ['no' => '#1046', 'addr' => 'Purok 2, Brgy. Gatid',                     'seller' => 'MJ Gadget Store',       'area' => 'R04', 'status' => 'sorted',        'minsAgo' => 40, 'carrier' => 'jnt',      'kg' => 3.8],
            ['no' => '#1047', 'addr' => 'Zone 3, Brgy. Gatid',                      'seller' => 'Toybox Manila',         'area' => 'R04', 'status' => 'sorted',        'minsAgo' => 23, 'carrier' => 'lbc',      'kg' => 0.8],
            ['no' => '#1048', 'addr' => '9 Mabini St., Brgy. Duhat',               'seller' => 'TechZone Laguna',       'area' => 'R05', 'status' => 'sorted',        'minsAgo' => 16, 'carrier' => 'jnt',      'kg' => 1.5],
            ['no' => '#1049', 'addr' => 'Sitio Ilaya, Brgy. Duhat',                 'seller' => 'Farmville Produce',     'area' => 'R05', 'status' => 'sorted',        'minsAgo' => 6,  'carrier' => 'lalamove', 'kg' => 2.0],

            // === Assigned — dispatch bay, rider en route to pick up ===
            ['no' => '#1004', 'addr' => '12 Matini St., Poblacion I',               'seller' => 'Baker\'s Nook',         'area' => 'R01', 'rider' => 'R1',  'status' => 'assigned', 'minsAgo' => 2,  'carrier' => 'jnt',      'kg' => 1.4],
            ['no' => '#1050', 'addr' => 'Zone 2, Brgy. Palasan',                    'seller' => 'Bahay Kubo Snacks',     'area' => 'R03', 'rider' => 'R3',  'status' => 'assigned', 'minsAgo' => 4,  'carrier' => 'lbc',      'kg' => 0.9],
            ['no' => '#1051', 'addr' => 'Purok 3, Brgy. Magsaysay',                 'seller' => 'Garden Fresh PH',       'area' => 'R07', 'rider' => 'R7',  'status' => 'assigned', 'minsAgo' => 6,  'carrier' => 'jnt',      'kg' => 2.1],
            ['no' => '#1052', 'addr' => '5 Rizal St., Brgy. Bagong Pook',           'seller' => 'PH Clothing Co.',       'area' => 'R09', 'rider' => 'R9',  'status' => 'assigned', 'minsAgo' => 3,  'carrier' => 'lalamove', 'kg' => 1.6],
            ['no' => '#1053', 'addr' => 'Purok 1, Brgy. Duhat',                     'seller' => 'Nilo\'s Sari-Sari',     'area' => 'R05', 'rider' => 'R5',  'status' => 'assigned', 'minsAgo' => 7,  'carrier' => 'jnt',      'kg' => 0.7],

            // === Out for delivery (exactly 5 riders: R1, R2, R3, R6, R8) ===
            ['no' => '#1008', 'addr' => 'Purok 1, Brgy. Palasan',                   'seller' => 'Toybox Manila',         'area' => 'R03', 'rider' => 'R3',  'status' => 'out_for_delivery', 'minsAgo' => 6,  'eta' => '18 min', 'carrier' => 'lalamove', 'kg' => 1.7],
            ['no' => '#1054', 'addr' => '18 Quezon Ave., Brgy. Bubukal',            'seller' => 'RTW Junction',          'area' => 'R02', 'rider' => 'R2',  'status' => 'out_for_delivery', 'minsAgo' => 9,  'eta' => '12 min', 'carrier' => 'jnt',      'kg' => 2.3],
            ['no' => '#1055', 'addr' => 'Sitio Wawa, Brgy. Gatid',                  'seller' => 'MJ Gadget Store',       'area' => 'R04', 'rider' => 'R1',  'status' => 'out_for_delivery', 'minsAgo' => 14, 'eta' => '25 min', 'carrier' => 'lbc',      'kg' => 0.6],
            ['no' => '#1056', 'addr' => '7 Bonifacio St., Brgy. Nanhaya',           'seller' => 'PetCare Corner',        'area' => 'R08', 'rider' => 'R8',  'status' => 'out_for_delivery', 'minsAgo' => 5,  'eta' => '8 min',  'carrier' => 'jnt',      'kg' => 3.0],
            ['no' => '#1057', 'addr' => 'Purok 5, Brgy. Lumban',                    'seller' => 'Lakewave Produce',      'area' => 'R10', 'rider' => 'R1',  'status' => 'out_for_delivery', 'minsAgo' => 20, 'eta' => '30 min', 'carrier' => 'lalamove', 'kg' => 1.0],
            ['no' => '#1058', 'addr' => 'Zone 1, Brgy. Poblacion II',               'seller' => 'CraftedByLen',          'area' => 'R11', 'rider' => 'R2',  'status' => 'out_for_delivery', 'minsAgo' => 11, 'eta' => '20 min', 'carrier' => 'jnt',      'kg' => 2.5],
            ['no' => '#1059', 'addr' => 'Purok 2, Brgy. Buenavista',                'seller' => 'Home Essentials PH',    'area' => 'R06', 'rider' => 'R6',  'status' => 'out_for_delivery', 'minsAgo' => 7,  'eta' => '15 min', 'carrier' => 'lbc',      'kg' => 1.8],

            // === Delivered (today) ===
            ['no' => '#1009', 'addr' => '7 Aguinaldo St., Brgy. Palasan',           'seller' => 'Farmville Produce',     'area' => 'R03', 'rider' => 'R3',  'status' => 'delivered', 'minsAgo' => 40,  'carrier' => 'jnt',      'kg' => 1.2],
            ['no' => '#1060', 'addr' => '3 Mabini Rd., Brgy. Poblacion I',          'seller' => 'Aling Rosa\'s Store',   'area' => 'R01', 'rider' => 'R1',  'status' => 'delivered', 'minsAgo' => 55,  'carrier' => 'lbc',      'kg' => 0.8],
            ['no' => '#1061', 'addr' => 'Zone 1, Brgy. Bubukal',                    'seller' => 'Baker\'s Nook',         'area' => 'R02', 'rider' => 'R2',  'status' => 'delivered', 'minsAgo' => 70,  'carrier' => 'jnt',      'kg' => 2.0],
            ['no' => '#1062', 'addr' => 'Purok 3, Brgy. Gatid',                     'seller' => 'TechZone Laguna',       'area' => 'R04', 'rider' => 'R12', 'status' => 'delivered', 'minsAgo' => 90,  'carrier' => 'lalamove', 'kg' => 3.3],
            ['no' => '#1063', 'addr' => '11 Rizal St., Brgy. Duhat',                'seller' => 'PH Clothing Co.',       'area' => 'R05', 'rider' => 'R5',  'status' => 'delivered', 'minsAgo' => 110, 'carrier' => 'jnt',      'kg' => 1.5],
            ['no' => '#1064', 'addr' => 'Sitio Centro, Brgy. Buenavista',           'seller' => 'RTW Junction',          'area' => 'R06', 'rider' => 'R6',  'status' => 'delivered', 'minsAgo' => 45,  'carrier' => 'jnt',      'kg' => 0.7],
            ['no' => '#1065', 'addr' => 'Zone 2, Brgy. Magsaysay',                  'seller' => 'Garden Fresh PH',       'area' => 'R07', 'rider' => 'R7',  'status' => 'delivered', 'minsAgo' => 65,  'carrier' => 'lbc',      'kg' => 2.6],
            ['no' => '#1066', 'addr' => 'Purok 4, Brgy. Nanhaya',                   'seller' => 'Lakeview Produce',      'area' => 'R08', 'rider' => 'R8',  'status' => 'delivered', 'minsAgo' => 80,  'carrier' => 'jnt',      'kg' => 1.9],
            ['no' => '#1067', 'addr' => '9 Quezon St., Brgy. Bagong Pook',          'seller' => 'Toybox Manila',         'area' => 'R09', 'rider' => 'R9',  'status' => 'delivered', 'minsAgo' => 100, 'carrier' => 'lalamove', 'kg' => 0.5],
            ['no' => '#1068', 'addr' => 'Sitio Wawa, Brgy. Lumban',                 'seller' => 'Nilo\'s Sari-Sari',     'area' => 'R10', 'rider' => 'R1',  'status' => 'delivered', 'minsAgo' => 120, 'carrier' => 'jnt',      'kg' => 3.0],
            ['no' => '#1069', 'addr' => 'Zone 3, Brgy. Poblacion II',               'seller' => 'Home Essentials PH',    'area' => 'R11', 'rider' => 'R11', 'status' => 'delivered', 'minsAgo' => 135, 'carrier' => 'jnt',      'kg' => 1.4],
            ['no' => '#1070', 'addr' => 'Purok 2, Brgy. Poblacion I',               'seller' => 'MJ Gadget Store',       'area' => 'R01', 'rider' => 'R12', 'status' => 'delivered', 'minsAgo' => 50,  'carrier' => 'lbc',      'kg' => 2.2],
            ['no' => '#1071', 'addr' => 'Sitio Ilaya, Brgy. Magsaysay',             'seller' => 'Farmville Produce',     'area' => 'R07', 'rider' => 'R7',  'status' => 'delivered', 'minsAgo' => 150, 'carrier' => 'jnt',      'kg' => 0.9],
            ['no' => '#1072', 'addr' => 'Zone 1, Brgy. Nanhaya',                    'seller' => 'CraftedByLen',          'area' => 'R08', 'rider' => 'R8',  'status' => 'delivered', 'minsAgo' => 160, 'carrier' => 'jnt',      'kg' => 1.1],

            // === Failed / Exception ===
            ['no' => '#0997', 'addr' => 'Purok 4, Brgy. Gatid',                     'seller' => 'PetCare Corner',        'area' => 'R04', 'rider' => 'R12', 'status' => 'failed', 'minsAgo' => 22,  'carrier' => 'jnt',      'kg' => 2.0],
            ['no' => '#1073', 'addr' => '6 Rizal St., Brgy. Lumban',                'seller' => 'Rosales Dry Goods',     'area' => 'R10', 'rider' => 'R7',  'status' => 'failed', 'minsAgo' => 95,  'carrier' => 'lalamove', 'kg' => 1.3],
            ['no' => '#1074', 'addr' => 'Zone 2, Brgy. Duhat',                      'seller' => 'Bahay Kubo Snacks',     'area' => 'R05', 'rider' => 'R5',  'status' => 'failed', 'minsAgo' => 130, 'carrier' => 'lbc',      'kg' => 0.8],
            ['no' => '#1075', 'addr' => 'Purok 1, Brgy. Bagong Pook',               'seller' => 'Baker\'s Nook',         'area' => 'R09', 'rider' => 'R9',  'status' => 'failed', 'minsAgo' => 75,  'carrier' => 'jnt',      'kg' => 2.4],
            ['no' => '#1076', 'addr' => 'Sitio Centro, Brgy. Poblacion II',         'seller' => 'TechZone Laguna',       'area' => 'R11', 'rider' => 'R11', 'status' => 'failed', 'minsAgo' => 45,  'carrier' => 'jnt',      'kg' => 1.7],
        ];

        foreach ($sample as $i => $s) {
            $parcel = Parcel::updateOrCreate(
                ['tracking_no' => $s['no']],
                [
                    'company_id' => 1,
                    'branch_id' => $hub?->id,
                    'address' => $s['addr'],
                    'seller_name' => $s['seller'],
                    'buyer_name' => $buyers[$i % count($buyers)],
                    'buyer_phone' => sprintf('09%02d %03d %04d', 17 + ($i % 8), 100 + (($i * 37) % 800), 1000 + (($i * 1319) % 8999)),
                    'weight_kg' => $s['kg'],
                    'cod_amount' => $s['status'] === 'delivered' ? 199 + ($i % 5) * 50 : 0,
                    'cod_collected' => $s['status'] === 'delivered' ? 199 + ($i % 5) * 50 : 0,
                    'cod_remitted' => $s['status'] === 'delivered' && ($i % 4 === 0),
                    'is_prepaid' => $s['status'] !== 'delivered',
                    'area_id' => isset($s['area']) ? ($areas[$s['area']]->id ?? null) : null,
                    'rider_id' => isset($s['rider']) ? ($riders[$s['rider']]->id ?? null) : null,
                    'dropped_off_by_rider_id' => isset($s['via']) ? ($riders[$s['via']]->id ?? null) : null,
                    'status' => $s['status'],
                    'carrier' => $s['carrier'],
                    'eta_note' => $s['eta'] ?? null,
                    'received_at' => now()->subMinutes($s['minsAgo'] + 15),
                    'sorted_at' => in_array($s['status'], ['sorted', 'assigned', 'out_for_delivery', 'delivered', 'failed'])
                        ? now()->subMinutes($s['minsAgo']) : null,
                    'assigned_at' => in_array($s['status'], ['assigned', 'out_for_delivery', 'delivered', 'failed'])
                        ? now()->subMinutes($s['minsAgo']) : null,
                    'delivered_at' => $s['status'] === 'delivered' ? now()->subMinutes($s['minsAgo']) : null,
                    'buyer_confirmed_at' => ($s['status'] === 'delivered' && ($i % 3 === 0))
                        ? now()->subMinutes((int) ($s['minsAgo'] / 2)) : null,
                    'sla_due_at' => $s['status'] === 'failed'
                        ? now()->subMinutes($s['minsAgo'] + 30)
                        : now()->addHours(24)->subMinutes($s['minsAgo']),
                    'scan_count' => match ($s['status']) {
                        'delivered' => 4,
                        'out_for_delivery', 'assigned', 'failed' => 3,
                        'sorted' => 2,
                        default => 1,
                    },
                    'last_scanned_at' => now()->subMinutes($s['minsAgo']),
                ]
            );

            // Seed status audit trail for active/delivered/failed parcels
            $logTime = now()->subMinutes($s['minsAgo']);
            ParcelStatusLog::firstOrCreate(
                ['parcel_id' => $parcel->id, 'to' => $s['status']],
                [
                    'from' => match ($s['status']) {
                        'delivered', 'failed' => 'out_for_delivery',
                        'out_for_delivery' => 'assigned',
                        'assigned' => 'sorted',
                        'sorted' => 'awaiting_sort',
                        'awaiting_sort' => 'dropped_off',
                        default => null,
                    },
                    'user_id' => null,
                    'reason' => 'Seeded status progression',
                    'undo_token' => Str::random(40),
                    'created_at' => $logTime,
                    'updated_at' => $logTime,
                ]
            );
        }

        // --- Support tickets ---
        $tickets = [
            ['ticket_no' => 'RPT-0001', 'category' => 'Parcel issue', 'priority' => 'High', 'subject' => 'Parcel #1009 marked delivered but buyer has not received it', 'related_reference' => '#1009', 'status' => 'Open'],
            ['ticket_no' => 'RPT-0002', 'category' => 'Rider issue', 'priority' => 'Normal', 'subject' => 'Rider R5 reported an unresponsive buyer phone number', 'related_reference' => 'R5', 'status' => 'In Progress'],
            ['ticket_no' => 'RPT-0003', 'category' => 'COD / Remittance', 'priority' => 'Urgent', 'subject' => 'COD remittance for R3 does not match the collected amount', 'related_reference' => 'R3', 'status' => 'Open'],
        ];

        foreach ($tickets as $ticket) {
            IssueReport::updateOrCreate(
                ['ticket_no' => $ticket['ticket_no']],
                $ticket + [
                    'company_id' => 1,
                    'description' => $ticket['subject'].'. Please investigate and update this ticket with the outcome.',
                    'contact_email' => 'hub@santacruzhub.ph',
                    'contact_phone' => '09171234567',
                ]
            );
        }

        // --- Activity feed (rich, timestamped entries) ---
        $activities = [
            // Deliveries & outcomes
            ['icon' => 'green',  'description' => '<b>#1009</b> delivered — Brgy. Palasan, signed by Farmville Produce', 'minsAgo' => 40],
            ['icon' => 'green',  'description' => '<b>#1060</b> delivered — Brgy. Poblacion I, signed by receiver',       'minsAgo' => 55],
            ['icon' => 'green',  'description' => '<b>#1061</b> delivered — Brgy. Bubukal, confirmed by buyer',           'minsAgo' => 70],
            ['icon' => 'green',  'description' => '<b>#1062</b> delivered — Brgy. Gatid, left with neighbor (noted)',     'minsAgo' => 90],
            ['icon' => 'green',  'description' => '<b>#1063</b> delivered — Brgy. Duhat, signed by Maria Clara Santos',  'minsAgo' => 110],
            ['icon' => 'red',    'description' => '<b>#0997</b> delivery failed — no answer at Brgy. Gatid address',     'minsAgo' => 22],
            ['icon' => 'red',    'description' => '<b>#1073</b> delivery failed — address not found in Lumban',           'minsAgo' => 95],
            ['icon' => 'red',    'description' => '<b>#1074</b> delivery failed — buyer refused package, Brgy. Duhat',    'minsAgo' => 130],
            // Assignments
            ['icon' => 'violet', 'description' => '<b>#1004</b> assigned to J. Ramos (R1) — Brgy. Poblacion I',          'minsAgo' => 2],
            ['icon' => 'violet', 'description' => '<b>#1050</b> assigned to A. Santos (R3) — Brgy. Palasan',             'minsAgo' => 4],
            ['icon' => 'violet', 'description' => '<b>#1051</b> assigned to E. Navarro (R7) — Brgy. Magsaysay',          'minsAgo' => 6],
            ['icon' => 'violet', 'description' => '<b>#1052</b> assigned to C. Delos Santos (R9) — Brgy. Bagong Pook',  'minsAgo' => 3],
            // Sort events
            ['icon' => 'violet', 'description' => '<b>#1001</b> sorted → BIN-R02 (Bubukal)',                              'minsAgo' => 20],
            ['icon' => 'violet', 'description' => '<b>#1003</b> sorted → BIN-R04 (Gatid)',                                'minsAgo' => 10],
            ['icon' => 'violet', 'description' => '<b>#1022</b> sorted → BIN-R07 (Magsaysay)',                            'minsAgo' => 25],
            // Drop-offs
            ['icon' => 'accent', 'description' => 'Seller drop-off received — <b>7 parcels</b> awaiting scan (Inbound Dock)', 'minsAgo' => 5],
            ['icon' => 'accent', 'description' => '<b>#1012</b> dropped off by M. Cruz (R2) — 3 items from CraftedByLen',     'minsAgo' => 6],
            ['icon' => 'accent', 'description' => '<b>#1037</b> dropped off by E. Navarro (R7) — Farmville Produce batch',    'minsAgo' => 14],
            // System / admin
            ['icon' => 'accent', 'description' => 'Rider application received — <b>D. Villanueva</b> (Motorcycle · NGH 3391)', 'minsAgo' => 2880],
            ['icon' => 'accent', 'description' => 'Pickup schedule updated — Brgy. Palasan cutoff extended by 30 min',         'minsAgo' => 1440],
        ];

        foreach ($activities as $a) {
            Activity::firstOrCreate(
                ['description' => $a['description']],
                [
                    'icon' => $a['icon'],
                    'created_at' => now()->subMinutes($a['minsAgo']),
                    'updated_at' => now()->subMinutes($a['minsAgo']),
                ]
            );
        }

        // --- Messages (only seed once so local replies aren't overwritten) ---
        if (Conversation::count() === 0) {
            $parcelIds = Parcel::query()
                ->whereIn('tracking_no', ['#1004', '#1045', '#0997', '#1044'])
                ->pluck('id', 'tracking_no');

            $threads = [
                [
                    'type' => 'rider',
                    'name' => 'J. Ramos',
                    'initials' => 'R1',
                    'online' => true,
                    'parcel' => '#1004',
                    'messages' => [
                        ['them', 'Sir, ready na po ba yung parcel sa Poblacion I?', 38, true],
                        ['me', 'Opo, nasa Bay 2 na. Pwede na kunin.', 37, true],
                        ['them', 'Salamat po, papasok na ako.', 35, false],
                    ],
                ],
                [
                    'type' => 'seller',
                    'name' => 'Rosales Dry Goods',
                    'initials' => 'RD',
                    'online' => false,
                    'parcel' => '#1045',
                    'messages' => [
                        ['them', 'Parcel is packed and ready for pickup.', 55, true],
                        ['them', 'Can we move the pickup to 1 PM?', 54, false],
                        ['them', 'Please confirm when the rider is on the way.', 53, false],
                    ],
                ],
                [
                    'type' => 'rider',
                    'name' => 'M. Cruz',
                    'initials' => 'R2',
                    'online' => false,
                    'parcel' => '#0997',
                    'messages' => [
                        ['them', 'Hindi ko po na-deliver yung parcel, may road closure.', 118, true],
                        ['me', 'Noted. Please bring it back to the hub for re-dispatch.', 116, true],
                    ],
                ],
                [
                    'type' => 'admin',
                    'name' => 'Admin - Platform Ops',
                    'initials' => 'AD',
                    'online' => false,
                    'parcel' => null,
                    'messages' => [
                        ['them', 'Reminder: submit the weekly report before Friday 5 PM.', 1440, false],
                    ],
                ],
                [
                    'type' => 'seller',
                    'name' => 'Lakeview Produce',
                    'initials' => 'LP',
                    'online' => false,
                    'parcel' => '#1044',
                    'messages' => [
                        ['them', 'Can we reschedule pickup to 1 PM?', 2880, true],
                        ['me', 'Yes, the rider will arrive after lunch.', 2875, true],
                    ],
                ],
            ];

            foreach ($threads as $thread) {
                $conversation = Conversation::create([
                    'type' => $thread['type'],
                    'name' => $thread['name'],
                    'initials' => $thread['initials'],
                    'online' => $thread['online'],
                    'parcel_id' => $thread['parcel'] ? ($parcelIds[$thread['parcel']] ?? null) : null,
                    'last_message_at' => now()->subMinutes(collect($thread['messages'])->min(fn ($m) => $m[2])),
                ]);

                foreach ($thread['messages'] as [$sender, $body, $minsAgo, $read]) {
                    Message::create([
                        'conversation_id' => $conversation->id,
                        'sender' => $sender,
                        'body' => $body,
                        'parcel_id' => $conversation->parcel_id,
                        'read_at' => $read || $sender === 'me' ? now()->subMinutes($minsAgo) : null,
                        'created_at' => now()->subMinutes($minsAgo),
                        'updated_at' => now()->subMinutes($minsAgo),
                    ]);
                }

                $conversation->update([
                    'last_message_at' => now()->subMinutes(collect($thread['messages'])->min(fn ($m) => $m[2])),
                ]);
            }
        }
    }
}
