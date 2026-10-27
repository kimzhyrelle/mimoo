<?php

namespace App\Http\Controllers;

use App\Models\IssueReport;
use App\Models\IssueReportAttachment;
use App\Models\IssueReportReply;
use App\Services\AuditLogger;
use App\Support\CompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:120',
            'status' => ['nullable', Rule::in(array_keys(IssueReport::STATUSES))],
            'category' => ['nullable', Rule::in(array_keys(IssueReport::CATEGORIES))],
            'priority' => ['nullable', Rule::in(array_keys(IssueReport::PRIORITIES))],
        ]);

        $reports = IssueReport::query()
            ->when($filters['q'] ?? null, function ($query, $q) {
                $like = '%'.addcslashes($q, '%_\\').'%';
                $query->where(fn ($query) => $query
                    ->where('ticket_no', 'like', $like)
                    ->orWhere('subject', 'like', $like)
                    ->orWhere('description', 'like', $like));
            })
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['category'] ?? null, fn ($q, $category) => $q->where('category', $category))
            ->when($filters['priority'] ?? null, fn ($q, $priority) => $q->where('priority', $priority))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('sorting.support.index', [
            'reports' => $reports,
            'filters' => $filters,
            'statuses' => IssueReport::STATUSES,
            'categories' => IssueReport::CATEGORIES,
            'priorities' => IssueReport::PRIORITIES,
        ]);
    }

    public function create(): View
    {
        return view('sorting.support.create', [
            'categories' => IssueReport::CATEGORIES,
            'priorities' => IssueReport::PRIORITIES,
        ]);
    }

    public function store(Request $request, AuditLogger $audit): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'category' => ['required', Rule::in(array_keys(IssueReport::CATEGORIES))],
            'priority' => ['nullable', Rule::in(array_keys(IssueReport::PRIORITIES))],
            'subject' => 'required|string|min:5|max:150',
            'description' => 'required|string|min:20|max:3000',
            'related_reference' => 'nullable|string|max:40',
            'contact_email' => 'nullable|email|max:150',
            'contact_phone' => 'nullable|string|max:20',
            'attachments' => 'nullable|array|max:3',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $report = IssueReport::create([
            'company_id' => CompanyContext::id(),
            'ticket_no' => IssueReport::nextReference(),
            'category' => $data['category'],
            'priority' => $data['priority'] ?? 'Normal',
            'subject' => $data['subject'],
            'description' => $data['description'],
            'related_reference' => $data['related_reference'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'status' => 'Open',
        ]);

        foreach ($request->file('attachments', []) as $file) {
            $path = $file->store('support-attachments', 'public');
            IssueReportAttachment::create([
                'company_id' => $report->company_id,
                'issue_report_id' => $report->id,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        $audit->log('support.created', $report, [], ['ticket_no' => $report->ticket_no], 'Issue report submitted');

        $message = "Issue report {$report->ticket_no} submitted. Our team will reply here.";

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => $message, 'report' => $report, 'redirect' => route('support.show', $report)]);
        }

        return redirect()->route('support.show', $report)->with('toast', $message)->with('toast_type', 'success');
    }

    public function show(IssueReport $report): View
    {
        $report->load(['replies.author', 'attachments']);

        return view('sorting.support.show', [
            'report' => $report,
            'statuses' => IssueReport::STATUSES,
        ]);
    }

    public function reply(Request $request, IssueReport $report, AuditLogger $audit): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'body' => 'required|string|min:2|max:2000',
            'is_internal' => 'nullable|boolean',
        ]);

        if ($report->status === 'Closed') {
            $message = 'This report is closed. Reopen it before replying.';

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => $message], 422);
            }

            return back()->withErrors(['body' => $message]);
        }

        $reply = IssueReportReply::create([
            'company_id' => $report->company_id,
            'issue_report_id' => $report->id,
            'user_id' => auth()->id(),
            'author_name' => auth()->user()?->name ?? 'Logistics Staff',
            'body' => $data['body'],
            'is_internal' => (bool) ($data['is_internal'] ?? false),
        ]);

        $report->update(['last_replied_at' => now()]);
        $audit->log('support.replied', $report, [], ['reply_id' => $reply->id], 'Reply added');

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Reply added.', 'reply' => $reply]);
        }

        return back()->with('toast', 'Reply added.')->with('toast_type', 'success');
    }

    public function updateStatus(Request $request, IssueReport $report, AuditLogger $audit): JsonResponse|RedirectResponse
    {
        try {
            $data = $request->validate([
                'status' => ['required', Rule::in(array_keys(IssueReport::STATUSES))],
                'reason' => 'nullable|string|max:300',
            ]);
        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'errors' => $e->errors()], 422);
            }
            throw $e;
        }

        if ($data['status'] === 'Closed' && mb_strlen(trim((string) ($data['reason'] ?? ''))) === 0) {
            $message = 'Add a short resolution note before closing the report.';

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => $message, 'errors' => ['reason' => [$message]]], 422);
            }

            return back()->withErrors(['reason' => $message]);
        }

        $old = $report->status;
        $report->update([
            'status' => $data['status'],
            'resolution' => $data['reason'] ?? $report->resolution,
            'closed_at' => $data['status'] === 'Closed' ? now() : null,
        ]);

        $audit->log('support.status_changed', $report, ['status' => $old], ['status' => $data['status']], $data['reason'] ?? null);

        $message = "Report {$report->ticket_no} marked {$data['status']}.";

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return back()->with('toast', $message)->with('toast_type', 'success');
    }
}
