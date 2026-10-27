<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssueReportReply extends Model
{
    protected $fillable = [
        'company_id', 'issue_report_id', 'author_role', 'user_id', 'author_name',
        'body', 'is_internal', 'status_from', 'status_to',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
    ];

    public function report()
    {
        return $this->belongsTo(IssueReport::class, 'issue_report_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
