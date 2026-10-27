<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class IssueReport extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'ticket_no', 'category', 'related_reference', 'contact_email',
        'contact_phone', 'priority', 'subject', 'description', 'status', 'resolution',
        'closed_at', 'last_replied_at', 'idempotency_key',
    ];

    public const CATEGORIES = [
        'Parcel issue' => 'Parcel issue',
        'Rider issue' => 'Rider issue',
        'System bug' => 'System bug',
        'COD / Remittance' => 'COD / Remittance',
        'Other' => 'Other',
    ];

    public const PRIORITIES = ['Low' => 'Low', 'Normal' => 'Normal', 'High' => 'High', 'Urgent' => 'Urgent'];

    public const STATUSES = [
        'Open' => 'Open',
        'In Progress' => 'In Progress',
        'Resolved' => 'Resolved',
        'Closed' => 'Closed',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
        'last_replied_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $report) {
            if (empty($report->ticket_no)) {
                $report->ticket_no = static::nextReference();
            }
        });
    }

    public static function nextReference(): string
    {
        $last = static::withoutGlobalScopes()->orderByDesc('id')->value('ticket_no');
        $next = 1;

        if ($last && preg_match('/RPT-(\d+)/', (string) $last, $match)) {
            $next = (int) $match[1] + 1;
        }

        return 'RPT-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function replies()
    {
        return $this->hasMany(IssueReportReply::class)->orderBy('id');
    }

    public function attachments()
    {
        return $this->hasMany(IssueReportAttachment::class);
    }
}
