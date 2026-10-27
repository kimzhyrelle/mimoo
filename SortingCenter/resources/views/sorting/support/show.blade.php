@extends('layouts.sorting')

@section('title', $report->ticket_no)

@section('content')
  <x-page-head eyebrow="Support Ticket" :title="$report->ticket_no" :sub="$report->subject">
    <a href="{{ route('support.index') }}" class="btn-soft"><x-icon name="arrow-right" :size="16" class="rotate-180" />All Reports</a>
  </x-page-head>

  @include('partials.flash')

  <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1.4fr_1fr]">
    <div class="space-y-5">
      <section class="card space-y-4 p-6">
        <div class="flex flex-wrap items-center gap-2">
          <span class="pill-slate px-2.5 py-1 text-[11px] font-semibold">{{ $report->priority }} priority</span>
          <span class="pill-slate px-2.5 py-1 text-[11px] font-semibold">{{ $report->category }}</span>
          @if ($report->related_reference)<span class="pill-slate px-2.5 py-1 text-[11px] font-semibold">{{ $report->related_reference }}</span>@endif
        </div>
        <p class="whitespace-pre-line text-[13.5px] leading-relaxed">{{ $report->description }}</p>

        @if ($report->contact_email || $report->contact_phone)
          <div class="flex flex-wrap gap-4 border-t border-border pt-4 text-[12px] text-muted-foreground">
            @if ($report->contact_email)<span class="flex items-center gap-1.5"><x-icon name="mail" :size="14" />{{ $report->contact_email }}</span>@endif
            @if ($report->contact_phone)<span class="flex items-center gap-1.5"><x-icon name="phone" :size="14" />{{ $report->contact_phone }}</span>@endif
          </div>
        @endif

        @if ($report->attachments->isNotEmpty())
          <div class="border-t border-border pt-4">
            <div class="mb-2 text-[12px] font-semibold text-muted-foreground">Attachments</div>
            <ul class="space-y-1.5 text-[12.5px]">
              @foreach ($report->attachments as $attachment)
                <li><a href="{{ Storage::url($attachment->path) }}" target="_blank" rel="noopener" class="text-primary underline-offset-2 hover:underline">{{ $attachment->original_name }}</a></li>
              @endforeach
            </ul>
          </div>
        @endif
      </section>

      <section class="card p-6">
        <h2 class="h-display mb-4 text-[17px]">Timeline</h2>
        <ol class="space-y-4">
          <li class="flex gap-3">
            <span class="mt-1 size-2.5 shrink-0 rounded-full bg-primary"></span>
            <div>
              <div class="text-[13px] font-semibold">Report submitted</div>
              <div class="text-[11.5px] text-muted-foreground">{{ $report->created_at->format('M j, Y g:i A') }}</div>
            </div>
          </li>
          @foreach ($report->replies as $reply)
            <li class="flex gap-3">
              <span class="mt-1 size-2.5 shrink-0 rounded-full {{ $reply->is_internal ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
              <div class="flex-1">
                <div class="flex flex-wrap items-baseline gap-2">
                  <span class="text-[13px] font-semibold">{{ $reply->author_name ?: 'Logistics Staff' }}</span>
                  @if ($reply->is_internal)<span class="pill-amber px-2 py-0.5 text-[10px]">Internal</span>@endif
                  <span class="text-[11.5px] text-muted-foreground">{{ $reply->created_at->format('M j, Y g:i A') }}</span>
                </div>
                <p class="mt-1 whitespace-pre-line text-[13px] leading-relaxed">{{ $reply->body }}</p>
              </div>
            </li>
          @endforeach
        </ol>

        @if ($report->status !== 'Closed')
          <form method="POST" action="{{ route('support.replies.store', $report) }}" class="mt-5 space-y-3 border-t border-border pt-5">
            @csrf
            <label class="block text-[12px] font-semibold text-muted-foreground">Reply
              <textarea name="body" required minlength="2" maxlength="2000" rows="3" class="field mt-1 w-full" placeholder="Add an update for the reporter..."></textarea>
              @error('body')<span class="mt-1 block text-[11.5px] text-red-600">{{ $message }}</span>@enderror
            </label>
            <div class="flex flex-wrap items-center justify-between gap-3">
              <label class="flex items-center gap-2 text-[12px] text-muted-foreground"><input type="checkbox" name="is_internal" value="1" class="rounded border-border"> Internal note (hidden from reporter)</label>
              <button type="submit" class="btn-primary">Send Reply</button>
            </div>
          </form>
        @endif
      </section>
    </div>

    <div class="space-y-5">
      <section class="card space-y-4 p-6">
        <h2 class="h-display text-[17px]">Ticket Details</h2>
        <dl class="space-y-3 text-[12.5px]">
          <div class="flex justify-between gap-4"><dt class="text-muted-foreground">Ticket</dt><dd class="font-semibold">{{ $report->ticket_no }}</dd></div>
          <div class="flex justify-between gap-4"><dt class="text-muted-foreground">Opened</dt><dd>{{ $report->created_at->format('M j, Y g:i A') }}</dd></div>
          <div class="flex justify-between gap-4"><dt class="text-muted-foreground">Last reply</dt><dd>{{ optional($report->last_replied_at)->diffForHumans() ?? '—' }}</dd></div>
          @if ($report->closed_at)<div class="flex justify-between gap-4"><dt class="text-muted-foreground">Closed</dt><dd>{{ $report->closed_at->format('M j, Y g:i A') }}</dd></div>@endif
        </dl>
      </section>

      <section class="card p-6">
        <h2 class="h-display mb-4 text-[17px]">Update Status</h2>
        <form method="POST" action="{{ route('support.status', $report) }}" class="space-y-3">
          @csrf
          <select name="status" class="field w-full">
            @foreach ($statuses as $key => $label)
              <option value="{{ $key }}" @selected($report->status === $key)>{{ $label }}</option>
            @endforeach
          </select>
          <textarea name="reason" rows="3" class="field w-full" placeholder="Resolution note (required to close)">{{ $report->resolution }}</textarea>
          @error('reason')<span class="mt-1 block text-[11.5px] text-red-600">{{ $message }}</span>@enderror
          <button type="submit" class="btn-primary w-full">Save Status</button>
        </form>
      </section>
    </div>
  </div>
@endsection
