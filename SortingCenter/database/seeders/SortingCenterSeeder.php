<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Area;
use App\Models\HubProfile;
use App\Models\Parcel;
use App\Models\Rider;
use App\Models\RiderApplication;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class SortingCenterSeeder extends Seeder
{
    public function run(): void
    {
        // --- Hub profile (Account page) ---
        HubProfile::current();

        // --- Staff login (Laguna Hub) ---
        User::firstOrCreate(
            ['email' => 'hub@santacruzhub.ph'],
            ['name' => 'Laguna Hub', 'password' => Hash::make('password'), 'role' => 'staff', 'initials' => 'LG']
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

        // --- Riders (each with their own login + vehicle) ---
        $ridersData = [
            ['name' => 'J. Ramos',    'initials' => 'R1',  'area' => 'R01', 'status' => 'busy',      'vehicle' => 'Motorcycle', 'plate' => 'NBC 1234', 'email' => 'j.ramos@santacruzhub.ph'],
            ['name' => 'M. Cruz',     'initials' => 'R2',  'area' => 'R02', 'status' => 'available',  'vehicle' => 'Motorcycle', 'plate' => 'NDC 4471', 'email' => 'm.cruz@santacruzhub.ph'],
            ['name' => 'A. Santos',   'initials' => 'R3',  'area' => 'R03', 'status' => 'busy',       'vehicle' => 'Tricycle',   'plate' => 'TRY 0092', 'email' => 'a.santos@santacruzhub.ph'],
            ['name' => 'P. Reyes',    'initials' => 'R4',  'area' => 'R04', 'status' => 'offline',    'vehicle' => 'Motorcycle', 'plate' => 'NEF 8820', 'email' => 'p.reyes@santacruzhub.ph'],
            ['name' => 'K. Villar',   'initials' => 'R5',  'area' => 'R05', 'status' => 'available',  'vehicle' => 'Motorcycle', 'plate' => 'NGH 5510', 'email' => 'k.villar@santacruzhub.ph'],
            ['name' => 'B. Flores',   'initials' => 'R6',  'area' => 'R06', 'status' => 'available',  'vehicle' => 'Motorcycle', 'plate' => 'NMB 2231', 'email' => 'b.flores@santacruzhub.ph'],
            ['name' => 'E. Navarro',  'initials' => 'R7',  'area' => 'R07', 'status' => 'busy',       'vehicle' => 'Motorcycle', 'plate' => 'NJD 9901', 'email' => 'e.navarro@santacruzhub.ph'],
            ['name' => 'T. Lim',      'initials' => 'R8',  'area' => 'R08', 'status' => 'available',  'vehicle' => 'Tricycle',   'plate' => 'TRY 1133', 'email' => 't.lim@santacruzhub.ph'],
            ['name' => 'C. Delos Santos', 'initials' => 'R9',  'area' => 'R09', 'status' => 'busy',   'vehicle' => 'Motorcycle', 'plate' => 'NKP 4420', 'email' => 'c.delossantos@santacruzhub.ph'],
            ['name' => 'R. Bautista', 'initials' => 'R10', 'area' => 'R10', 'status' => 'available',  'vehicle' => 'Motorcycle', 'plate' => 'NLP 7784', 'email' => 'r.bautista@santacruzhub.ph'],
            ['name' => 'M. Aquino',   'initials' => 'R11', 'area' => 'R11', 'status' => 'available',  'vehicle' => 'Motorcycle', 'plate' => 'NMR 3309', 'email' => 'm.aquino@santacruzhub.ph'],
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
                    'name' => $r['name'], 'initials' => $r['initials'], 'area_id' => $areas[$r['area']]->id,
                    'status' => $r['status'], 'vehicle_type' => $r['vehicle'], 'plate_no' => $r['plate'],
                ]
            );
            $riders[$r['initials']] = $rider;
        }

        // --- Pending rider applications ---
        $applications = [
            ['name' => 'D. Villanueva', 'vehicle_type' => 'Motorcycle', 'plate_no' => 'NGH 3391', 'or_cr_verified' => true, 'license_status' => 'verified', 'applied_on' => Carbon::today()->subDays(2)],
            ['name' => 'K. Torres', 'vehicle_type' => 'Motorcycle', 'plate_no' => 'NKL 7742', 'or_cr_verified' => true, 'license_status' => 'pending', 'applied_on' => Carbon::today()->subDay()],
            ['name' => 'R. Manalo', 'vehicle_type' => 'Tricycle', 'plate_no' => 'TRY 0511', 'or_cr_verified' => false, 'license_status' => 'pending', 'applied_on' => Carbon::today()],
        ];
        foreach ($applications as $a) {
            RiderApplication::firstOrCreate(['name' => $a['name']], $a + ['status' => 'pending']);
        }

        // --- Parcels: spans every stage, from just-dropped-off to delivered/failed ---
        $today = Carbon::today();
        $sample = [
            // Incoming — still needs scanning
            ['no' => '#1011', 'addr' => '12 Rizal St., Purok 3',       'seller' => 'Nilo\'s Sari-Sari',    'via' => 'R1', 'status' => 'dropped_off',     'minsAgo' => 12],
            ['no' => '#1012', 'addr' => '45 Mabini Ave., Zone 1',      'seller' => 'CraftedByLen',          'via' => 'R2', 'status' => 'dropped_off',     'minsAgo' => 6],
            ['no' => '#1013', 'addr' => 'Sitio Ilaya, Brgy. Bubukal',  'seller' => 'Bahay Kubo Snacks',     'via' => 'R3', 'status' => 'dropped_off',     'minsAgo' => 3],

            // Sort Parcels queue
            ['no' => '#0994', 'addr' => 'Purok 2, Brgy. Palasan',      'seller' => 'RTW Junction',          'area' => 'R03', 'status' => 'awaiting_sort', 'minsAgo' => 38],
            ['no' => '#0996', 'addr' => 'Zone 2, Brgy. Poblacion I',   'seller' => 'MJ Gadget Store',       'area' => 'R01', 'status' => 'awaiting_sort', 'minsAgo' => 29],
            ['no' => '#1001', 'addr' => '7 Aguinaldo St., Brgy. Bubukal', 'seller' => 'Home Essentials PH', 'area' => 'R02', 'status' => 'sorted',        'minsAgo' => 20, 'carrier' => 'jnt'],
            ['no' => '#1002', 'addr' => 'Purok 1, Brgy. Poblacion I',  'seller' => 'Aling Rosa\'s Store',   'area' => 'R01', 'status' => 'sorted',        'minsAgo' => 15, 'carrier' => 'lbc'],
            ['no' => '#1003', 'addr' => 'Purok 4, Brgy. Gatid',        'seller' => 'Lakeview Produce',      'area' => 'R04', 'status' => 'sorted',        'minsAgo' => 10, 'carrier' => 'jnt'],
            ['no' => '#1005', 'addr' => 'Zone 2, Brgy. Poblacion I',   'seller' => 'Baker\'s Nook',         'area' => 'R01', 'status' => 'sorted',        'minsAgo' => 8,  'carrier' => 'lalamove'],
            ['no' => '#1006', 'addr' => 'Sitio Centro, Brgy. Palasan', 'seller' => 'Rosales Dry Goods',     'area' => 'R03', 'status' => 'sorted',        'minsAgo' => 5,  'carrier' => 'jnt'],

            // Delivery monitoring
            ['no' => '#1004', 'addr' => '12 Matini St., Poblacion I',  'seller' => 'Baker\'s Nook',         'area' => 'R01', 'rider' => 'R1', 'status' => 'assigned',          'minsAgo' => 2,  'carrier' => 'jnt'],
            ['no' => '#1008', 'addr' => 'Purok 1, Brgy. Palasan',      'seller' => 'Toybox Manila',         'area' => 'R03', 'rider' => 'R3', 'status' => 'out_for_delivery',  'minsAgo' => 6,  'eta' => '18 min', 'carrier' => 'lalamove'],
            ['no' => '#1009', 'addr' => '7 Aguinaldo St., Brgy. Palasan', 'seller' => 'Farmville Produce',  'area' => 'R03', 'rider' => 'R3', 'status' => 'delivered',         'minsAgo' => 40, 'carrier' => 'jnt'],
            ['no' => '#0997', 'addr' => 'Purok 4, Brgy. Gatid',        'seller' => 'PetCare Corner',        'area' => 'R04', 'rider' => 'R4', 'status' => 'failed',            'minsAgo' => 22, 'carrier' => 'jnt'],

            // Dispatch board — new area sorted parcels (R06–R11)
            ['no' => '#1020', 'addr' => '3 Kalayaan St., Buenavista',      'seller' => 'Garden Fresh PH',      'area' => 'R06', 'status' => 'sorted', 'minsAgo' => 18, 'carrier' => 'jnt'],
            ['no' => '#1021', 'addr' => 'Purok 2, Buenavista',             'seller' => 'TechZone Laguna',      'area' => 'R06', 'status' => 'sorted', 'minsAgo' => 12, 'carrier' => 'lbc'],
            ['no' => '#1022', 'addr' => '10 Rizal Ave., Magsaysay',        'seller' => 'PH Clothing Co.',      'area' => 'R07', 'status' => 'sorted', 'minsAgo' => 25, 'carrier' => 'jnt'],
            ['no' => '#1023', 'addr' => 'Zone 3, Brgy. Magsaysay',         'seller' => 'Nilo\'s Sari-Sari',    'area' => 'R07', 'status' => 'sorted', 'minsAgo' => 14, 'carrier' => 'lalamove'],
            ['no' => '#1024', 'addr' => '5 Bonifacio St., Magsaysay',      'seller' => 'Bahay Kubo Snacks',    'area' => 'R07', 'status' => 'sorted', 'minsAgo' => 9,  'carrier' => 'jnt'],
            ['no' => '#1025', 'addr' => 'Sitio Ilaya, Brgy. Nanhaya',      'seller' => 'Home Essentials PH',   'area' => 'R08', 'status' => 'sorted', 'minsAgo' => 30, 'carrier' => 'jnt'],
            ['no' => '#1026', 'addr' => 'Purok 1, Brgy. Nanhaya',          'seller' => 'RTW Junction',         'area' => 'R08', 'status' => 'sorted', 'minsAgo' => 11, 'carrier' => 'lbc'],
            ['no' => '#1027', 'addr' => '22 Mabini St., Bagong Pook',      'seller' => 'MJ Gadget Store',      'area' => 'R09', 'status' => 'sorted', 'minsAgo' => 22, 'carrier' => 'lalamove'],
            ['no' => '#1028', 'addr' => 'Zone 1, Brgy. Bagong Pook',       'seller' => 'Lakeview Produce',     'area' => 'R09', 'status' => 'sorted', 'minsAgo' => 8,  'carrier' => 'jnt'],
            ['no' => '#1029', 'addr' => 'Purok 4, Brgy. Bagong Pook',      'seller' => 'Rosales Dry Goods',    'area' => 'R09', 'status' => 'sorted', 'minsAgo' => 5,  'carrier' => 'jnt'],
            ['no' => '#1030', 'addr' => '8 Quezon St., Lumban',             'seller' => 'Toybox Manila',        'area' => 'R10', 'status' => 'sorted', 'minsAgo' => 35, 'carrier' => 'jnt'],
            ['no' => '#1031', 'addr' => 'Purok 3, Brgy. Lumban',           'seller' => 'Farmville Produce',    'area' => 'R10', 'status' => 'sorted', 'minsAgo' => 17, 'carrier' => 'lbc'],
            ['no' => '#1032', 'addr' => 'Sitio Centro, Lumban',             'seller' => 'Baker\'s Nook',        'area' => 'R10', 'status' => 'sorted', 'minsAgo' => 7,  'carrier' => 'lalamove'],
            ['no' => '#1033', 'addr' => '14 Aguinaldo St., Poblacion II',   'seller' => 'CraftedByLen',         'area' => 'R11', 'status' => 'sorted', 'minsAgo' => 28, 'carrier' => 'jnt'],
            ['no' => '#1034', 'addr' => 'Zone 2, Brgy. Poblacion II',       'seller' => 'PetCare Corner',       'area' => 'R11', 'status' => 'sorted', 'minsAgo' => 13, 'carrier' => 'jnt'],
        ];

        foreach ($sample as $s) {
            Parcel::updateOrCreate(
                ['tracking_no' => $s['no']],
                [
                    'address'               => $s['addr'],
                    'seller_name'           => $s['seller'],
                    'area_id'               => isset($s['area']) ? $areas[$s['area']]->id : null,
                    'rider_id'              => isset($s['rider']) ? $riders[$s['rider']]->id : null,
                    'dropped_off_by_rider_id' => isset($s['via']) ? $riders[$s['via']]->id : null,
                    'status'                => $s['status'],
                    'carrier'               => $s['carrier'] ?? 'jnt',
                    'eta_note'              => $s['eta'] ?? null,
                    'received_at'           => $today->copy()->addMinutes(8 * 60 - $s['minsAgo']),
                    'sorted_at'             => in_array($s['status'], ['sorted', 'assigned', 'out_for_delivery', 'delivered']) ? now()->subMinutes($s['minsAgo']) : null,
                    'assigned_at'           => in_array($s['status'], ['assigned', 'out_for_delivery', 'delivered']) ? now()->subMinutes($s['minsAgo']) : null,
                    'delivered_at'          => $s['status'] === 'delivered' ? now()->subMinutes($s['minsAgo']) : null,
                ]
            );
        }

        // --- Activity feed ---
        $activities = [
            ['icon' => 'green', 'description' => '<b>#1004</b> assigned to Rider 01'],
            ['icon' => 'violet', 'description' => '<b>#1008</b> sorted to Area B'],
            ['icon' => 'accent', 'description' => 'Seller drop-off received, <b>3 parcels</b> awaiting scan'],
            ['icon' => 'red', 'description' => '<b>#0997</b> delivery failed — no answer'],
        ];
        foreach ($activities as $a) {
            Activity::firstOrCreate($a);
        }
    }
}
