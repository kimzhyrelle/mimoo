<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiderApplication extends Model
{
    use BelongsToCompany, HasFactory;

    public const CHECKLIST_KEYS = [
        'id_checked',
        'license_checked',
        'phone_verified',
        'docs_not_expired',
    ];

    protected $fillable = [
        'company_id', 'name', 'phone', 'vehicle_type', 'plate_no', 'or_cr_verified',
        'license_status', 'applied_on', 'status', 'approved_rider_id',
        'kyc_status', 'verified_by', 'verified_at', 'rejection_reason',
        'checklist', 'or_cr_expires_on', 'license_expires_on',
        'otp_hash', 'otp_expires_at', 'otp_attempts', 'phone_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'or_cr_verified' => 'boolean',
            'applied_on' => 'date',
            'verified_at' => 'datetime',
            'checklist' => 'array',
            'or_cr_expires_on' => 'date',
            'license_expires_on' => 'date',
            'otp_expires_at' => 'datetime',
            'otp_attempts' => 'integer',
            'phone_verified_at' => 'datetime',
        ];
    }

    public function approvedRider()
    {
        return $this->belongsTo(Rider::class, 'approved_rider_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function verificationAudits()
    {
        return $this->hasMany(RiderVerificationAudit::class);
    }

    /**
     * Computed from data only. Staff cannot toggle verification items.
     *
     * @return array<string, bool>
     */
    public function checklistValue(): array
    {
        return [
            'phone_verified' => $this->phone_verified_at !== null,
            'documents_uploaded' => (bool) $this->or_cr_verified || $this->license_status === 'verified',
            'license_checked' => $this->license_status === 'verified',
            'id_checked' => (bool) $this->or_cr_verified,
            'docs_not_expired' => $this->or_cr_expires_on !== null
                && $this->license_expires_on !== null
                && $this->or_cr_expires_on->gte(today())
                && $this->license_expires_on->gte(today()),
        ];
    }

    /**
     * @return array<int, array{key: string, label: string, ok: bool}>
     */
    public function verificationChecklist(): array
    {
        $values = $this->checklistValue();

        return [
            ['key' => 'phone_verified', 'label' => 'Phone verified by SMS', 'ok' => $values['phone_verified']],
            ['key' => 'documents_uploaded', 'label' => 'Documents uploaded', 'ok' => $values['documents_uploaded']],
            ['key' => 'license_checked', 'label' => 'Driver license on file', 'ok' => $values['license_checked']],
            ['key' => 'id_checked', 'label' => 'OR/CR on file', 'ok' => $values['id_checked']],
            ['key' => 'docs_not_expired', 'label' => 'Documents not expired', 'ok' => $values['docs_not_expired']],
        ];
    }

    public function isVerificationComplete(): bool
    {
        return ! in_array(false, $this->checklistValue(), true);
    }

    public function hasExpiredDocuments(): bool
    {
        foreach ([$this->or_cr_expires_on, $this->license_expires_on] as $expiry) {
            if ($expiry !== null && $expiry->lt(today())) {
                return true;
            }
        }

        return false;
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'pending'
            && $this->kyc_status === 'verified'
            && $this->isVerificationComplete()
            && ! $this->hasExpiredDocuments();
    }

    public function kycStatusLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->kyc_status ?: 'pending'));
    }

    public function docsLabel(): string
    {
        $orCr = $this->or_cr_verified ? 'OR/CR ok' : 'OR/CR pending';
        $license = $this->license_status === 'verified' ? 'License ok' : 'License pending';

        return "{$orCr} / {$license}";
    }
}
