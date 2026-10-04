<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Area;
use App\Models\HubProfile;
use App\Models\Parcel;
use App\Models\Rider;
use App\Models\RiderApplication;

/**
 * UI-only preview routes for all five Sorting Center pages. Every "model"
 * here is built in memory with `new` + forceFill()/setRelation() and never
 * touches the database — no migrate, no seed needed. See routes/web.php
 * for the /preview/* group. Delete this whole controller + route group
 * once you're working against real data again.
 */
class DevPreviewController extends Controller
{
    // Shared fake barangays + riders, cross-linked with setRelation() so
    // $area->rider and $rider->area both work without a DB relation query.
    private function fakeWorld(): array
    {
        $areaDefs = [
            ['name' => 'Brgy. Poblacion I', 'code' => 'R01', 'chart_color' => '#6C4FD8'],
            ['name' => 'Brgy. Bubukal', 'code' => 'R02', 'chart_color' => '#2A8FBD'],
            ['name' => 'Brgy. Palasan', 'code' => 'R03', 'chart_color' => '#1FA391'],
            ['name' => 'Brgy. Gatid', 'code' => 'R04', 'chart_color' => '#5B2FC7'],
        ];
        $schedule = [
            ['window' => '9:00 AM – 11:00 AM', 'cutoff' => '10:30 AM', 'status' => 'On schedule'],
            ['window' => '9:30 AM – 11:30 AM', 'cutoff' => '11:00 AM', 'status' => 'On schedule'],
            ['window' => '10:00 AM – 12:00 PM', 'cutoff' => '11:30 AM', 'status' => '2 sellers not ready'],
            ['window' => '10:30 AM – 12:30 PM', 'cutoff' => '12:00 PM', 'status' => 'On schedule'],
        ];
        $riderDefs = [
            ['name' => 'J. Ramos', 'initials' => 'R1', 'status' => 'busy', 'vehicle_type' => 'Motorcycle', 'plate_no' => 'NBC 1234'],
            ['name' => 'M. Cruz', 'initials' => 'R2', 'status' => 'available', 'vehicle_type' => 'Motorcycle', 'plate_no' => 'NDC 4471'],
            ['name' => 'A. Santos', 'initials' => 'R3', 'status' => 'busy', 'vehicle_type' => 'Tricycle', 'plate_no' => 'TRY 0092'],
            ['name' => 'P. Reyes', 'initials' => 'R4', 'status' => 'offline', 'vehicle_type' => 'Motorcycle', 'plate_no' => 'NEF 8820'],
        ];

        $areas = collect($areaDefs)->map(fn ($a, $i) => (new Area())->forceFill($a + $schedule[$i])->forceFill(['id' => $i + 1]));
        $riders = collect($riderDefs)->map(function ($r, $i) use ($areas) {
            $rider = (new Rider())->forceFill($r + ['id' => $i + 1, 'area_id' => $i + 1])->setRelation('area', $areas[$i]);
            $areas[$i]->setRelation('rider', $rider);
            return $rider;
        });

        return [$areas, $riders];
    }

    public function index()
    {
        return view('sorting.preview-index');
    }

    public function dashboard()
    {
        [$areas, $riders] = $this->fakeWorld();
        $areas[0]->parcel_count = 9;
        $areas[1]->parcel_count = 6;
        $areas[2]->parcel_count = 4;
        $areas[3]->parcel_count = 3;

        $activityDefs = [
            ['icon' => 'green', 'description' => '<b>#1004</b> assigned to Rider 01', 'mins' => 2],
            ['icon' => 'violet', 'description' => '<b>#1008</b> sorted to Area B', 'mins' => 6],
            ['icon' => 'accent', 'description' => 'Seller drop-off received, <b>3 parcels</b> awaiting scan', 'mins' => 12],
            ['icon' => 'red', 'description' => '<b>#0997</b> delivery failed — no answer', 'mins' => 22],
        ];
        $recentActivity = collect($activityDefs)->map(fn ($a) => (new Activity())->forceFill([
            'icon' => $a['icon'], 'description' => $a['description'], 'created_at' => now()->subMinutes($a['mins']),
        ]));

        $needsDefs = [
            ['no' => '#0994', 'area' => 2, 'status' => 'awaiting_sort', 'mins' => 38],
            ['no' => '#0996', 'area' => 0, 'status' => 'awaiting_sort', 'mins' => 29],
            ['no' => '#1001', 'area' => 1, 'status' => 'sorted', 'mins' => 20],
            ['no' => '#1002', 'area' => 0, 'status' => 'sorted', 'mins' => 15],
        ];
        $needsAttention = collect($needsDefs)->map(fn ($p, $i) => (new Parcel())->forceFill([
            'id' => $i + 1, 'tracking_no' => $p['no'], 'status' => $p['status'], 'received_at' => now()->subMinutes($p['mins']),
        ])->setRelation('area', $areas[$p['area']]));

        return view('sorting.dashboard', [
            'receivedToday' => 22, 'receivedYesterday' => 18,
            'awaitingSort' => 7, 'assignedToRiders' => 5, 'deliveryFailed' => 1,
            'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'received' => [14, 19, 16, 21, 18, 9, 4],
            'assigned' => [10, 15, 13, 17, 14, 6, 2],
            'areas' => $areas, 'riders' => $riders,
            'recentActivity' => $recentActivity, 'needsAttention' => $needsAttention,
        ]);
    }

    public function incomingParcels()
    {
        [$areas, $riders] = $this->fakeWorld();

        $defs = [
            ['no' => '#1011', 'seller' => 'Nilo\'s Sari-Sari', 'via' => 0, 'mins' => 12],
            ['no' => '#1012', 'seller' => 'CraftedByLen', 'via' => 1, 'mins' => 6],
            ['no' => '#1013', 'seller' => 'Bahay Kubo Snacks', 'via' => 2, 'mins' => 3],
        ];
        $parcels = collect($defs)->map(fn ($p, $i) => (new Parcel())->forceFill([
            'id' => $i + 1, 'tracking_no' => $p['no'], 'seller_name' => $p['seller'], 'received_at' => now()->subMinutes($p['mins']),
        ])->setRelation('droppedOffBy', $riders[$p['via']]));

        return view('sorting.incoming-parcels', [
            'parcels' => $parcels, 'awaitingScan' => $parcels->count(), 'scannedToday' => 5,
            'ridersDroppingOff' => 3, 'areas' => $areas, 'q' => null,
        ]);
    }

    public function sortParcels()
    {
        [$areas, $riders] = $this->fakeWorld();

        $defs = [
            ['no' => '#0994', 'addr' => 'Brgy. Palasan', 'status' => 'awaiting_sort'],
            ['no' => '#0996', 'addr' => 'Brgy. Poblacion I', 'status' => 'awaiting_sort'],
            ['no' => '#1001', 'addr' => 'Brgy. Bubukal', 'area' => 1, 'status' => 'sorted'],
            ['no' => '#1002', 'addr' => 'Brgy. Poblacion I', 'area' => 0, 'status' => 'sorted'],
            ['no' => '#1004', 'addr' => 'Brgy. Poblacion I', 'area' => 0, 'rider' => 0, 'status' => 'assigned'],
        ];
        $parcels = collect($defs)->map(function ($p, $i) use ($areas, $riders) {
            $parcel = (new Parcel())->forceFill(['id' => $i + 1, 'tracking_no' => $p['no'], 'address' => $p['addr'], 'status' => $p['status'], 'carrier' => 'jnt']);
            if (isset($p['area'])) $parcel->setRelation('area', $areas[$p['area']]);
            if (isset($p['rider'])) $parcel->setRelation('rider', $riders[$p['rider']]);
            return $parcel;
        });

        return view('sorting.sort-parcels', [
            'parcels' => $parcels, 'areas' => $areas,
            'awaitingSort' => 2, 'areaDetermined' => 2, 'assignedToday' => 1, 'ridersOnShift' => 3, 'q' => null,
        ]);
    }

    public function deliveryMonitoring()
    {
        [$areas, $riders] = $this->fakeWorld();

        $defs = [
            ['no' => '#1004', 'addr' => 'Brgy. Poblacion I', 'area' => 0, 'rider' => 0, 'status' => 'assigned'],
            ['no' => '#1008', 'addr' => 'Brgy. Bubukal', 'area' => 1, 'rider' => 1, 'status' => 'out_for_delivery', 'eta' => '18 min'],
            ['no' => '#1009', 'addr' => 'Brgy. Palasan', 'area' => 2, 'rider' => 2, 'status' => 'delivered'],
            ['no' => '#0997', 'addr' => 'Brgy. Gatid', 'area' => 3, 'rider' => 3, 'status' => 'failed'],
        ];
        $parcels = collect($defs)->map(fn ($p, $i) => (new Parcel())->forceFill([
            'id' => $i + 1, 'tracking_no' => $p['no'], 'address' => $p['addr'], 'status' => $p['status'],
            'eta_note' => $p['eta'] ?? null, 'scanned_undelivered' => false,
        ])->setRelation('area', $areas[$p['area']])->setRelation('rider', $riders[$p['rider']]));

        return view('sorting.delivery-monitoring', [
            'parcels' => $parcels,
            'rail' => ['sorted' => 2, 'assigned' => 1, 'out' => 1, 'delivered' => 1, 'pending_confirm' => 1, 'failed' => 1],
            'filter' => 'all', 'q' => null,
        ]);
    }

    public function riderManagement()
    {
        [$areas, $riders] = $this->fakeWorld();

        $appDefs = [
            ['name' => 'D. Villanueva', 'vehicle_type' => 'Motorcycle', 'plate_no' => 'NGH 3391', 'or_cr_verified' => true, 'license_status' => 'verified', 'days' => 2],
            ['name' => 'K. Torres', 'vehicle_type' => 'Motorcycle', 'plate_no' => 'NKL 7742', 'or_cr_verified' => true, 'license_status' => 'pending', 'days' => 1],
            ['name' => 'R. Manalo', 'vehicle_type' => 'Tricycle', 'plate_no' => 'TRY 0511', 'or_cr_verified' => false, 'license_status' => 'pending', 'days' => 0],
        ];
        $pending = collect($appDefs)->map(fn ($a, $i) => (new RiderApplication())->forceFill([
            'id' => $i + 1, 'name' => $a['name'], 'vehicle_type' => $a['vehicle_type'], 'plate_no' => $a['plate_no'],
            'or_cr_verified' => $a['or_cr_verified'], 'license_status' => $a['license_status'],
            'applied_on' => now()->subDays($a['days']),
        ]));

        return view('sorting.rider-management', [
            'pending' => $pending, 'riders' => $riders, 'areas' => $areas,
            'covered' => 4, 'unassigned' => 0, 'onShift' => 3, 'q' => null,
        ]);
    }

    public function account()
    {
        [$areas] = $this->fakeWorld();

        $hub = (new HubProfile())->forceFill([
            'id' => 1, 'name' => 'Laguna Hub', 'contact_email' => 'hub@santacruzhub.ph',
            'contact_phone' => '(049) 000-0000', 'address' => 'Santa Cruz, Laguna',
            'coverage_note' => 'Covers Santa Cruz, Laguna, sorted barangay by barangay.',
        ]);

        return view('sorting.account', ['hub' => $hub, 'areas' => $areas]);
    }
}
