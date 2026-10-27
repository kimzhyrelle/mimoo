<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasListFilters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rider extends Model
{
    use BelongsToCompany, HasFactory, HasListFilters, SoftDeletes;

    protected $fillable = [
        'company_id', 'branch_id', 'user_id', 'name', 'initials', 'vehicle_type', 'plate_no',
        'area_id', 'status', 'is_active', 'checked_in_at',
        'archived_at', 'archived_by',
        'phone', 'kyc_status', 'verified_by', 'verified_at', 'rejection_reason',
        'checklist', 'or_cr_expires_on', 'license_expires_on',
        'phone_verified_at', 'otp_hash', 'otp_expires_at', 'otp_attempts',
        'otp_locked_until', 'otp_sent_at', 'cod_balance', 'or_cr_verified',
        'documents_uploaded', 'suspended_at', 'suspension_reason',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'checked_in_at' => 'datetime',
            'archived_at' => 'datetime',
            'verified_at' => 'datetime',
            'checklist' => 'array',
            'or_cr_expires_on' => 'date',
            'license_expires_on' => 'date',
            'phone_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'otp_locked_until' => 'datetime',
            'otp_sent_at' => 'datetime',
            'otp_attempts' => 'integer',
            'cod_balance' => 'decimal:2',
            'or_cr_verified' => 'boolean',
            'documents_uploaded' => 'boolean',
            'suspended_at' => 'datetime',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function activeJobsCount(): int
    {
        return $this->parcels()
            ->whereIn('status', ['assigned', 'picked_up', 'out_for_delivery'])
            ->count();
    }

    public function completedTodayCount(): int
    {
        return $this->parcels()
            ->where('status', 'delivered')
            ->whereDate('delivered_at', today())
            ->count();
    }

    public function hasPhoneVerified(): bool
    {
        return $this->phone_verified_at !== null;
    }

    public function archivedBy()
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null || $this->trashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function parcels()
    {
        return $this->hasMany(Parcel::class);
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
     * The checklist is computed from data only. Staff can never toggle it.
     *
     * @return array<string, bool>
     */
    public function checklistValue(): array
    {
        return [
            'phone_verified' => $this->phone_verified_at !== null,
            'documents_uploaded' => (bool) $this->documents_uploaded,
            'license_checked' => $this->kyc_status === 'verified' && $this->license_expires_on !== null,
            'or_cr_checked' => (bool) $this->or_cr_verified,
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
            ['key' => 'or_cr_checked', 'label' => 'OR/CR on file', 'ok' => $values['or_cr_checked']],
            ['key' => 'docs_not_expired', 'label' => 'Documents not expired', 'ok' => $values['docs_not_expired']],
        ];
    }

    public function isVerificationComplete(): bool
    {
        return ! in_array(false, $this->checklistValue(), true);
    }

    public function kycStatusLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->kyc_status ?: 'pending'));
    }

    // Label shown as the pill on the dashboard (e.g. "Out for delivery")
    public function statusLabel(): string
    {
        if (! $this->isActive()) {
            return 'Inactive';
        }

        if (! $this->isOnDuty()) {
            return 'Off shift';
        }

        return $this->isInRoute() ? 'Out for delivery' : 'Available';
    }

    public function isActive(): bool
    {
        if ($this->status === 'suspended') {
            return false;
        }

        return (bool) ($this->is_active ?? ($this->status !== 'offline'));
    }

    public function isOnline(): bool
    {
        return $this->isActive() && $this->isCheckedIn();
    }

    public function canAcceptJobs(): bool
    {
        return $this->isOnline() && $this->activeJobsCount() < 3;
    }

    public function isCheckedIn(): bool
    {
        return $this->checked_in_at !== null && $this->checked_in_at->isToday();
    }

    public function isOnDuty(): bool
    {
        return $this->isActive() && $this->isCheckedIn();
    }

    public function isInRoute(): bool
    {
        if (! $this->isOnDuty()) {
            return false;
        }

        return $this->parcels()->where('status', 'out_for_delivery')->exists();
    }

    public function isIdle(): bool
    {
        return $this->isOnDuty() && ! $this->isInRoute();
    }

    public function vehicleLabel(): string
    {
        return trim($this->vehicle_type.($this->plate_no ? ' · '.$this->plate_no : ''));
    }

    /**
     * @return array<int, string>
     */
    protected function listSearchable(): array
    {
        return ['name', 'initials', 'phone', 'plate_no', 'vehicle_type'];
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function listSearchRelations(): array
    {
        return [
            'area' => ['name', 'code'],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    protected function listSortable(): array
    {
        return [
            'name' => ['name', 'asc'],
            'newest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'checked_in' => ['checked_in_at', 'desc'],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function listStatusFilters(): array
    {
        return [
            'active' => ['is_active' => true],
            'inactive' => ['is_active' => false],
        ];
    }

    protected function listAreaColumn(): string
    {
        return 'area_id';
    }
}
