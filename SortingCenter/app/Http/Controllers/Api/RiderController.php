<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RiderResource;
use App\Models\Area;
use App\Models\Rider;
use App\Models\RiderApplication;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RiderController extends Controller
{
    /** GET /api/riders */
    public function index(Request $request): JsonResponse
    {
        $riders = Rider::with('area')
            ->when($request->q, fn ($q, $search) =>
                $q->where('name', 'like', "%{$search}%")
            )
            ->orderBy('name')
            ->get();

        return response()->json(['data' => RiderResource::collection($riders)]);
    }

    /** GET /api/riders/{rider} */
    public function show(Rider $rider): JsonResponse
    {
        $rider->load('area');
        return response()->json(['data' => new RiderResource($rider)]);
    }

    /** PATCH /api/riders/{rider}/toggle-active */
    public function toggleActive(Rider $rider): JsonResponse
    {
        $rider->status = $rider->isActive() ? 'offline' : 'available';
        $rider->save();

        return response()->json([
            'data'    => new RiderResource($rider->load('area')),
            'message' => "Rider is now {$rider->status}.",
        ]);
    }

    /** PATCH /api/riders/{rider}/barangay */
    public function setBarangay(Request $request, Rider $rider): JsonResponse
    {
        $data = $request->validate([
            'area_id' => 'nullable|exists:areas,id',
        ]);

        // Unlink any other rider currently assigned to this area
        if ($data['area_id']) {
            Rider::where('area_id', $data['area_id'])
                ->where('id', '!=', $rider->id)
                ->update(['area_id' => null]);
        }

        $rider->update(['area_id' => $data['area_id'] ?: null]);

        $areaName = $rider->area?->name ?? 'unassigned';

        return response()->json([
            'data'    => new RiderResource($rider->load('area')),
            'message' => "Rider assigned to {$areaName}.",
        ]);
    }

    /** GET /api/rider-applications */
    public function applications(): JsonResponse
    {
        $applications = RiderApplication::where('status', 'pending')
            ->orderBy('applied_on')
            ->get();

        return response()->json([
            'data' => $applications->map(fn ($a) => [
                'id'              => $a->id,
                'name'            => $a->name,
                'vehicle_type'    => $a->vehicle_type,
                'plate_no'        => $a->plate_no,
                'or_cr_verified'  => $a->or_cr_verified,
                'license_status'  => $a->license_status,
                'docs_label'      => $a->docsLabel(),
                'applied_on'      => $a->applied_on?->format('M j, Y'),
                'status'          => $a->status,
            ]),
        ]);
    }

    /** POST /api/rider-applications/{application}/approve */
    public function approveApplication(RiderApplication $application): JsonResponse
    {
        if ($application->status !== 'pending') {
            return response()->json(['message' => 'Application is not pending.'], 422);
        }

        $user = User::firstOrCreate(
            ['email' => strtolower(str_replace(' ', '.', $application->name)) . '@santacruzhub.ph'],
            [
                'name'     => $application->name,
                'password' => bcrypt('password'),
                'role'     => 'rider',
                'initials' => collect(explode(' ', $application->name))
                    ->map(fn ($w) => strtoupper($w[0]))->implode(''),
            ]
        );

        $rider = Rider::create([
            'user_id'      => $user->id,
            'name'         => $application->name,
            'initials'     => $user->initials,
            'vehicle_type' => $application->vehicle_type,
            'plate_no'     => $application->plate_no,
            'status'       => 'available',
        ]);

        $application->update([
            'status'            => 'approved',
            'approved_rider_id' => $rider->id,
        ]);

        return response()->json([
            'data'    => new RiderResource($rider),
            'message' => "{$rider->name} approved as rider.",
        ], 201);
    }

    /** POST /api/rider-applications/{application}/disapprove */
    public function disapproveApplication(RiderApplication $application): JsonResponse
    {
        $application->update(['status' => 'disapproved']);

        return response()->json(['message' => 'Application disapproved.']);
    }
}
