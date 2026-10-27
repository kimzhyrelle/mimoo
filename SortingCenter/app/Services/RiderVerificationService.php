<?php

namespace App\Services;

use App\Models\Rider;
use App\Models\RiderApplication;
use App\Models\RiderVerificationAudit;
use Illuminate\Support\Facades\Auth;

class RiderVerificationService
{
    public const OTP_TTL_MINUTES = 10;

    public const MAX_OTP_ATTEMPTS = 5;

    public const DISAPPROVE_REASONS = [
        'Incomplete documents',
        'Blurry ID',
        'Expired license',
        'Mismatched details',
        'Other',
    ];

    public function __construct(private SmsVerificationService $sms) {}

    public function normalizePhone(string $phone): string
    {
        return $this->sms->normalizePhone($phone);
    }

    public function isValidPhone(string $phone): bool
    {
        return $this->sms->isValidPhone($phone);
    }

    /**
     * Save staff-reviewed document checks and derive expiry/phone checks from their source data.
     *
     * @return array<string, mixed>
     */
    public function saveChecklist(RiderApplication $application, array $input): array
    {
        if (array_key_exists('or_cr_expires_on', $input)) {
            $application->or_cr_expires_on = $input['or_cr_expires_on'] ?: null;
        }

        if (array_key_exists('license_expires_on', $input)) {
            $application->license_expires_on = $input['license_expires_on'] ?: null;
        }

        if (array_key_exists('or_cr_verified', $input)) {
            $application->or_cr_verified = (bool) $input['or_cr_verified'];
        } elseif (array_key_exists('id_checked', $input)) {
            $application->or_cr_verified = filter_var($input['id_checked'], FILTER_VALIDATE_BOOLEAN);
        }

        if (array_key_exists('license_status', $input)) {
            $application->license_status = $input['license_status'];
        } elseif (array_key_exists('license_checked', $input)) {
            $application->license_status = filter_var($input['license_checked'], FILTER_VALIDATE_BOOLEAN) ? 'verified' : 'pending';
        }

        if (array_key_exists('documents_uploaded', $input)) {
            $application->checklist = array_merge($application->checklist ?? [], ['documents_uploaded' => (bool) $input['documents_uploaded']]);
        }

        $application->checklist = $application->checklistValue();
        $this->applyChecklistStatus($application, $application->checklistValue());
        $application->save();

        return $this->state($application->fresh());
    }

    /**
     * Staff changes the phone number; the rider gets a fresh SMS OTP.
     *
     * @return array{phone: string, expires_at: string}
     */
    public function changePhone(RiderApplication $application, string $phone): array
    {
        $result = $this->sms->changePhone($application, $phone);
        $this->audit($application, 'phone_changed');

        return ['phone' => $result['phone'], 'expires_at' => $result['expires_at']];
    }

    /**
     * @return array{phone: string, expires_at: string}
     */
    public function sendOtp(RiderApplication $application, string $phone): array
    {
        $normalized = $this->sms->normalizePhone($phone);
        $changed = $application->phone !== $normalized;
        $application->phone = $normalized;

        if ($changed) {
            $application->phone_verified_at = null;
        }

        $application->save();
        $result = $this->sms->send($application->fresh(), $normalized);

        return ['phone' => $result['phone'], 'expires_at' => $result['expires_at']];
    }

    /**
     * @return array<string, mixed>
     */
    public function verifyOtp(RiderApplication $application, string $code): array
    {
        $this->sms->verify($application, $code);
        $application->refresh();

        $checklist = $application->checklistValue();
        $checklist['phone_verified'] = true;
        $application->checklist = $checklist;
        $this->applyChecklistStatus($application, $checklist);
        $application->save();

        $this->audit($application, 'phone_verified');

        return $this->state($application->fresh());
    }

    /**
     * @return array<string, mixed>
     */
    public function state(RiderApplication $application): array
    {
        return [
            'checklist' => $application->checklistValue(),
            'verification' => $application->verificationChecklist(),
            'kyc_status' => $application->kyc_status,
            'kyc_label' => $application->kycStatusLabel(),
            'can_approve' => $application->canBeApproved(),
            'rejection_reason' => $application->rejection_reason,
        ];
    }

    public function approvalBlockReason(RiderApplication $application): ?string
    {
        if ($application->status !== 'pending') {
            return 'This application is no longer pending.';
        }

        if ($application->phone_verified_at === null) {
            return 'Approve is blocked: the rider must verify their phone number by SMS first.';
        }

        if ($application->kyc_status !== 'verified') {
            return 'KYC must be verified before this rider can be approved.';
        }

        if (! $application->isVerificationComplete()) {
            return 'Complete every verification checklist item before approving.';
        }

        if ($application->hasExpiredDocuments()) {
            return 'One or more submitted documents are expired.';
        }

        return null;
    }

    public function audit(RiderApplication $application, string $action, ?string $reason = null, ?Rider $rider = null): RiderVerificationAudit
    {
        return RiderVerificationAudit::create([
            'rider_id' => $rider?->id,
            'rider_application_id' => $application->id,
            'action' => $action,
            'reason' => $reason,
            'performed_by' => Auth::id(),
        ]);
    }

    /**
     * @param  array<string, bool>  $checklist
     */
    private function applyChecklistStatus(RiderApplication $application, array $checklist): void
    {
        $complete = ! in_array(false, $checklist, true);

        if ($application->kyc_status === 'rejected') {
            return;
        }

        if ($complete) {
            $application->kyc_status = 'verified';
            $application->verified_at ??= now();
            $application->verified_by ??= Auth::id();

            return;
        }

        $application->kyc_status = in_array(true, $checklist, true) ? 'under_review' : 'pending';
        $application->verified_at = null;
        $application->verified_by = null;
    }
}
