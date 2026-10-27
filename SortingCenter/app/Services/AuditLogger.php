<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Support\CompanyContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * @param  array<string, mixed>|null  $old
     * @param  array<string, mixed>|null  $new
     */
    public function log(
        string $action,
        Model|string $subject,
        ?array $old = null,
        ?array $new = null,
        ?string $reason = null,
        ?int $userId = null,
    ): AuditLog {
        $subjectType = $subject instanceof Model ? $subject::class : $subject;
        $subjectId = $subject instanceof Model ? (int) $subject->getKey() : 0;

        return AuditLog::create([
            'company_id' => CompanyContext::id(),
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'old_value' => $old,
            'new_value' => $new,
            'reason' => $reason,
        ]);
    }
}
