<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssueReportAttachment extends Model
{
    protected $fillable = [
        'company_id', 'issue_report_id', 'path', 'original_name', 'mime_type', 'size',
    ];

    public function report()
    {
        return $this->belongsTo(IssueReport::class, 'issue_report_id');
    }
}
