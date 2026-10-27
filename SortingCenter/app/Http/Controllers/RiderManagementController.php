<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListFilterRequest;
use App\Models\Area;
use App\Models\Rider;
use App\Models\RiderApplication;
use App\Services\AssignmentService;
use App\Services\AuditLogger;
use App\Services\DispatchService;
use App\Services\MetricsService;
use App\Services\RiderVerificationService;
use App\Services\SmsVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RiderManagementController extends Controller
{
    public function index(ListFilterRequest $request, MetricsService $metrics)
    {
        $filters = $request->validated();
        $q = $filters['q'] ?? null;

        $pending = RiderApplication::with('verificationAudits')->where('status', 'pending')->orderBy('applied_on')->get();

        $riders = Rider::with('area', 'branch')
            ->withCount(['parcels as active_jobs_count' => fn ($q) => $q->whereIn('status', ['assigned', 'picked_up', 'out_for_delivery'])])
            ->withCount(['parcels as completed_today' => fn ($q) => $q->where('status', 'delivered')->whereDate('delivered_at', today())])
            ->listFilters($filters)
            ->orderBy('name')
            ->get();

        $areas = Area::with('rider')->orderBy('name')->get();

        return view('sorting.rider-management', [
            'pending' => $pending,
            'riders' => $riders,
            'areas' => $areas,
            'covered' => $metrics->coveredAreasCount(),
            'unassigned' => $metrics->unassignedAreasCount(),
            'onShift' => $metrics->ridersOnDutyValue(),
            'q' => $q,
        ]);
    }

    public function updateVerification(Request $request, RiderApplication $application, RiderVerificationService $verification): JsonResponse|RedirectResponse
    {
        try {
            $data = $request->validate([
                'id_checked' => 'sometimes|boolean',
                'license_checked' => 'sometimes|boolean',
                'or_cr_expires_on' => 'nullable|date',
                'license_expires_on' => 'nullable|date',
            ]);
            $state = $verification->saveChecklist($application, $data);
        } catch (ValidationException $e) {
            return $this->respondValidation($request, $e);
        }

        return $this->respond($request, array_merge([
            'ok' => true,
            'message' => 'Verification checklist saved.',
        ], $state));
    }

    public function changePhone(Request $request, Rider $rider, SmsVerificationService $sms, AuditLogger $audit): JsonResponse|RedirectResponse
    {
        try {
            $data = $request->validate(['phone' => 'required|string|max:20']);
            $exists = Rider::where('phone', $sms->normalizePhone($data['phone']))->whereKeyNot($rider->id)->exists();

            if ($exists) {
                throw ValidationException::withMessages(['phone' => 'This phone number is already used by another rider.']);
            }

            $result = $sms->changePhone($rider, $data['phone']);
            $audit->log('rider.phone_changed', $rider, [], ['phone' => $result['phone']], 'Phone changed; SMS verification required again');
        } catch (ValidationException $e) {
            return $this->respondValidation($request, $e);
        }

        return $this->respond($request, [
            'ok' => true,
            'message' => 'Phone updated. An SMS code was sent to the rider.',
            'phone' => $result['phone'],
            'expires_at' => $result['expires_at'],
        ]);
    }

    public function sendRiderOtp(Request $request, Rider $rider, SmsVerificationService $sms): JsonResponse|RedirectResponse
    {
        try {
            $data = $request->validate(['phone' => 'nullable|string|max:20']);
            $result = $sms->send($rider, $data['phone'] ?? (string) $rider->phone);
        } catch (ValidationException $e) {
            return $this->respondValidation($request, $e);
        }

        return $this->respond($request, ['ok' => true, 'message' => 'SMS code sent.', 'expires_at' => $result['expires_at']]);
    }

    public function verifyRiderOtp(Request $request, Rider $rider, SmsVerificationService $sms): JsonResponse|RedirectResponse
    {
        try {
            $data = $request->validate(['code' => 'required|digits:6']);
            $sms->verify($rider, $data['code']);
        } catch (ValidationException $e) {
            return $this->respondValidation($request, $e);
        }

        return $this->respond($request, ['ok' => true, 'message' => 'Phone verified by SMS.']);
    }

    public function suspend(Request $request, Rider $rider, DispatchService $dispatch): JsonResponse|RedirectResponse
    {
        try {
            $data = $request->validate(['reason' => 'required|string|min:10|max:300']);
            $returned = $dispatch->suspend($rider, $data['reason']);
        } catch (ValidationException $e) {
            return $this->respondValidation($request, $e);
        }

        return $this->respond($request, [
            'ok' => true,
            'message' => "Rider suspended. {$returned} not-yet-picked-up jobs returned to the Unassigned queue.",
            'returned_jobs' => $returned,
        ]);
    }

    public function reactivate(Request $request, Rider $rider, DispatchService $dispatch): JsonResponse|RedirectResponse
    {
        try {
            $data = $request->validate(['reason' => 'required|string|min:10|max:300']);
            $dispatch->reactivate($rider, $data['reason']);
        } catch (ValidationException $e) {
            return $this->respondValidation($request, $e);
        }

        return $this->respond($request, ['ok' => true, 'message' => 'Rider reactivated.']);
    }

    public function approveRider(Request $request, Rider $rider, AuditLogger $audit): JsonResponse|RedirectResponse
    {
        if (! $rider->hasPhoneVerified()) {
            return $this->respond($request, [
                'ok' => false,
                'message' => 'Approve is blocked: the rider must verify their phone number by SMS first.',
                'errors' => ['phone' => ['Approve is blocked: the rider must verify their phone number by SMS first.']],
            ], 422);
        }

        $old = ['status' => $rider->status, 'kyc_status' => $rider->kyc_status];
        $rider->update(['status' => 'available', 'is_active' => true, 'checked_in_at' => now(), 'kyc_status' => 'verified', 'verified_at' => now()]);
        $audit->log('rider.approved', $rider, $old, ['status' => 'active', 'kyc_status' => 'verified'], $request->input('reason'));

        return $this->respond($request, ['ok' => true, 'message' => 'Rider approved.']);
    }

    public function sendOtp(Request $request, RiderApplication $application, RiderVerificationService $verification): JsonResponse|RedirectResponse
    {
        try {
            $data = $request->validate([
                'phone' => 'required|string|max:20',
            ]);
            $result = $verification->sendOtp($application, $data['phone']);
        } catch (ValidationException $e) {
            return $this->respondValidation($request, $e);
        }

        return $this->respond($request, array_merge([
            'ok' => true,
            'message' => 'Verification code sent to '.$this->maskPhone($result['phone']).'.',
            'phone' => $result['phone'],
            'expires_at' => $result['expires_at'],
            'dev_hint' => 'Local development: the code is written to storage/logs/laravel.log.',
        ], $verification->state($application->fresh())));
    }

    public function verifyOtp(Request $request, RiderApplication $application, RiderVerificationService $verification): JsonResponse|RedirectResponse
    {
        try {
            $data = $request->validate([
                'code' => 'required|digits:6',
            ]);
            $state = $verification->verifyOtp($application, $data['code']);
        } catch (ValidationException $e) {
            return $this->respondValidation($request, $e);
        }

        return $this->respond($request, array_merge([
            'ok' => true,
            'message' => 'Phone number verified.',
        ], $state));
    }

    public function approve(Request $request, RiderApplication $application, RiderVerificationService $verification): JsonResponse|RedirectResponse
    {
        $block = $verification->approvalBlockReason($application);

        if ($block !== null) {
            return $this->respond($request, [
                'ok' => false,
                'message' => $block,
                'errors' => ['verification' => [$block]],
            ], 422);
        }

        $rider = DB::transaction(function () use ($application) {
            $initials = 'R'.str_pad((string) (Rider::withTrashed()->count() + 1), 2, '0', STR_PAD_LEFT);

            $rider = Rider::create([
                'company_id' => $application->company_id,
                'name' => $application->name,
                'initials' => $initials,
                'vehicle_type' => $application->vehicle_type,
                'plate_no' => $application->plate_no,
                'phone' => $application->phone,
                'status' => 'available',
                'is_active' => true,
                'checked_in_at' => now(),
                'kyc_status' => 'verified',
                'verified_by' => $application->verified_by,
                'verified_at' => $application->verified_at,
                'checklist' => $application->checklist,
                'or_cr_expires_on' => $application->or_cr_expires_on,
                'license_expires_on' => $application->license_expires_on,
                'phone_verified_at' => $application->phone_verified_at,
                'or_cr_verified' => $application->or_cr_verified,
                'documents_uploaded' => true,
            ]);

            $application->update([
                'status' => 'approved',
                'approved_rider_id' => $rider->id,
                'rejection_reason' => null,
            ]);

            return $rider;
        });

        $verification->audit($application, 'approved', null, $rider);

        return $this->respond($request, [
            'ok' => true,
            'message' => "{$application->name} approved as {$rider->initials}. Assign a barangay below.",
            'rider_id' => $rider->id,
        ]);
    }

    public function disapprove(Request $request, RiderApplication $application, RiderVerificationService $verification): JsonResponse|RedirectResponse
    {
        try {
            $data = $request->validate([
                'reason' => ['required', 'string', Rule::in(RiderVerificationService::DISAPPROVE_REASONS)],
                'reason_detail' => ['nullable', 'string', 'max:500', 'required_if:reason,Other'],
            ]);
        } catch (ValidationException $e) {
            return $this->respondValidation($request, $e);
        }

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

        return $this->respond($request, [
            'ok' => true,
            'message' => "{$application->name}'s application was disapproved.",
            'reason' => $reason,
        ]);
    }

    // One rider per barangay — reassigning frees it from whoever had it before.
    // Blocks with 409 if rider holds open parcels without reassign_to.
    public function setBarangay(Request $request, Rider $rider, AssignmentService $assignmentService): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'area_id' => 'nullable|exists:areas,id',
            'reassign_to' => 'nullable|exists:riders,id',
        ]);

        $newAreaId = $data['area_id'] ?? null;

        // If removing or changing route area, check if rider holds open parcels
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

        DB::transaction(function () use ($newAreaId, $rider) {
            if (! empty($newAreaId)) {
                Rider::where('area_id', $newAreaId)->where('id', '!=', $rider->id)->update(['area_id' => null]);
            }
            $rider->update(['area_id' => $newAreaId]);
        });

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Barangay route updated successfully.',
                'rider' => $rider->fresh('area'),
            ]);
        }

        $message = 'Barangay route updated successfully.';

        return back()->with('status', $message)->with('toast', $message);
    }

    // Toggle rider active status.
    // Blocks with 409 if rider holds open parcels when being deactivated without reassign_to.
    public function toggleActive(Request $request, Rider $rider, AssignmentService $assignmentService): JsonResponse|RedirectResponse
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

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Rider is now '.($targetActive ? 'active' : 'inactive').'.',
                'rider' => $rider->fresh('area'),
            ]);
        }

        $message = 'Rider is now '.($targetActive ? 'active' : 'inactive').'.';

        return back()->with('status', $message)->with('toast', $message);
    }

    /**
     * GET /riders/load
     * Rider load for dropdowns: [{id, label, open_parcels, status, disabled}]
     */
    public function load(AssignmentService $assignmentService): JsonResponse
    {
        return response()->json($assignmentService->getRidersLoad());
    }

    private function respond(Request $request, array $payload, int $status = 200): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json($payload, $status);
        }

        if ($status >= 400) {
            return back()->with('error', $payload['message'] ?? 'Something went wrong.')->withInput();
        }

        $message = $payload['message'] ?? 'Saved.';

        return back()->with('status', $message)->with('toast', $message);
    }

    private function respondValidation(Request $request, ValidationException $exception): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
                'errors' => $exception->errors(),
            ], 422);
        }

        throw $exception;
    }

    private function maskPhone(string $phone): string
    {
        return strlen($phone) >= 7
            ? substr($phone, 0, 4).' *** '.substr($phone, -3)
            : $phone;
    }
}
