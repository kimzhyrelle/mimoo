<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListFilterRequest;
use App\Http\Resources\RiderResource;
use App\Models\Area;
use App\Models\Rider;
use App\Models\RiderApplication;
use App\Models\User;
use App\Services\AssignmentService;
use App\Services\MetricsService;
use App\Services\RiderVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RiderController extends Controller
{
    /** GET /api/riders */
    public function index(ListFilterRequest $request, MetricsService $metrics): JsonResponse
    {
        $riders = Rider::with('area')
            ->withCount([
                'parcels as open_parcels_count' => fn ($query) => $query->whereIn('status', ['assigned', 'out_for_delivery']),
                'parcels as out_for_delivery_count' => fn ($query) => $query->where('status', 'out_for_delivery'),
                'parcels as delivered_today_count' => fn ($query) => $query->where('status', 'delivered')->whereDate('delivered_at', today()),
            ])
            ->listFilters($request->validated())
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => RiderResource::collection($riders),
            'meta' => [
                'fleet' => [
                    'riders_on_duty' => $metrics->ridersOnDutyValue(),
                    'riders_active' => $metrics->ridersActiveValue(),
                    'riders_idle' => $metrics->ridersIdleValue(),
                    'riders_in_route' => $metrics->ridersInRouteValue(),
                ],
            ],
        ]);
    }

    /** GET /api/riders/{rider} */
    public function show(Rider $rider): JsonResponse
    {
        $rider->load('area');

        return response()->json(['data' => new RiderResource($rider)]);
    }

    /** GET /api/riders/load */
    public function load(AssignmentService $assignmentService): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'data' => $assignmentService->getRidersLoad(),
        ]);
    }

    /** PATCH /api/riders/{rider}/toggle-active */
    public function toggleActive(Request $request, Rider $rider, AssignmentService $assignmentService): JsonResponse
    {
        $currentActive = $rider->isActive();
        $targetActive = $request->has('is_active') ? $request->boolean('is_active') : ! $currentActive;

        if ($currentActive && ! $targetActive) {
            $sync = $assignmentService->reassignOrBlock($rider, $request->input('reassign_to'));

            if ($sync && ! empty($sync['blocked'])) {
                return response()->json([
                    'ok' => false,
                    'message' => $sync['message'],
                    'open_parcels' => $sync['open_parcels'],
                    'count' => $sync['count'],
                ], 409);
            }
        }

        $rider->update([
            'is_active' => $targetActive,
            'status' => $targetActive ? 'available' : 'offline',
            'checked_in_at' => $targetActive ? now() : null,
        ]);

        return response()->json([
            'ok' => true,
            'data' => new RiderResource($rider->load('area')),
            'message' => 'Rider is now '.($targetActive ? 'active' : 'inactive').'.',
        ]);
    }

    /** PATCH /api/riders/{rider}/barangay */
    public function setBarangay(Request $request, Rider $rider, AssignmentService $assignmentService): JsonResponse
    {
        $data = $request->validate([
            'area_id' => 'nullable|exists:areas,id',
            'reassign_to' => 'nullable|exists:riders,id',
        ]);

        $newAreaId = $data['area_id'] ?? null;

        if ($newAreaId != $rider->area_id) {
            $sync = $assignmentService->reassignOrBlock($rider, $data['reassign_to'] ?? null);

            if ($sync && ! empty($sync['blocked'])) {
                return response()->json([
                    'ok' => false,
                    'message' => $sync['message'],
                    'open_parcels' => $sync['open_parcels'],
                    'count' => $sync['count'],
                ], 409);
            }
        }

        // Unlink any other rider currently assigned to this area
        if (! empty($newAreaId)) {
            Rider::where('area_id', $newAreaId)
                ->where('id', '!=', $rider->id)
                ->update(['area_id' => null]);
        }

        $rider->update(['area_id' => $newAreaId]);

        $areaName = $rider->fresh('area')->area?->name ?? 'unassigned';

        return response()->json([
            'ok' => true,
            'data' => new RiderResource($rider->fresh('area')),
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
            'data' => $applications->map(fn ($a) => $this->applicationPayload($a)),
        ]);
    }

    /** GET /api/rider-applications/{application} */
    public function showApplication(RiderApplication $application): JsonResponse
    {
        return response()->json(['data' => $this->applicationPayload($application)]);
    }

    /** POST /api/rider-applications/{application}/approve */
    public function approveApplication(RiderApplication $application, RiderVerificationService $verification): JsonResponse
    {
        $block = $verification->approvalBlockReason($application);

        if ($block !== null) {
            return response()->json(['ok' => false, 'message' => $block], 422);
        }

        $user = User::firstOrCreate(
            ['email' => strtolower(str_replace(' ', '.', $application->name)).'@santacruzhub.ph'],
            [
                'name' => $application->name,
                'password' => bcrypt('password'),
                'role' => 'rider',
                'initials' => collect(explode(' ', $application->name))
                    ->map(fn ($w) => strtoupper($w[0]))->implode(''),
            ]
        );

        $rider = Rider::create([
            'user_id' => $user->id,
            'name' => $application->name,
            'initials' => $user->initials,
            'vehicle_type' => $application->vehicle_type,
            'plate_no' => $application->plate_no,
            'phone' => $application->phone,
            'status' => 'available',
            'kyc_status' => 'verified',
            'verified_by' => $application->verified_by,
            'verified_at' => $application->verified_at,
            'checklist' => $application->checklist,
            'or_cr_expires_on' => $application->or_cr_expires_on,
            'license_expires_on' => $application->license_expires_on,
        ]);

        $application->update([
            'status' => 'approved',
            'approved_rider_id' => $rider->id,
            'rejection_reason' => null,
        ]);

        $verification->audit($application, 'approved', null, $rider);

        return response()->json([
            'ok' => true,
            'data' => new RiderResource($rider),
            'message' => "{$rider->name} approved as rider.",
        ], 201);
    }

    /** POST /api/rider-applications/{application}/disapprove */
    public function disapproveApplication(Request $request, RiderApplication $application, RiderVerificationService $verification): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', Rule::in(RiderVerificationService::DISAPPROVE_REASONS)],
            'reason_detail' => ['nullable', 'string', 'max:500', 'required_if:reason,Other'],
        ]);

        $reason = $data['reason'] === 'Other'
            ? 'Other: '.trim((string) ($data['reason_detail'] ?? ''))
            : $data['reason'];

        $application->update([
            'status' => 'rejected',
            'kyc_status' => 'rejected',
            'rejection_reason' => $reason,
            'verified_at' => null,
            'verified_by' => null,
        ]);

        $verification->audit($application, 'rejected', $reason);

        return response()->json([
            'ok' => true,
            'message' => 'Application disapproved.',
            'reason' => $reason,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function applicationPayload(RiderApplication $application): array
    {
        return [
            'id' => $application->id,
            'name' => $application->name,
            'phone' => $application->phone,
            'vehicle_type' => $application->vehicle_type,
            'plate_no' => $application->plate_no,
            'or_cr_verified' => $application->or_cr_verified,
            'license_status' => $application->license_status,
            'docs_label' => $application->docsLabel(),
            'applied_on' => $application->applied_on?->format('M j, Y'),
            'status' => $application->status,
            'kyc_status' => $application->kyc_status,
            'kyc_status_label' => $application->kycStatusLabel(),
            'checklist' => $application->checklistValue(),
            'verified_at' => $application->verified_at?->toIso8601String(),
            'phone_verified_at' => $application->phone_verified_at?->toIso8601String(),
            'rejection_reason' => $application->rejection_reason,
            'or_cr_expires_on' => $application->or_cr_expires_on?->toDateString(),
            'license_expires_on' => $application->license_expires_on?->toDateString(),
            'can_be_approved' => $application->canBeApproved(),
        ];
    }
}
