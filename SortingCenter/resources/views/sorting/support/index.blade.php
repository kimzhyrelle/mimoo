@extends('layouts.sorting')

@section('title', 'Support & Issue Reports')

@section('content')
  <x-page-head eyebrow="Help Desk" title="Support & Issue Reports" sub="Track logistics issues from report to resolution with a full audit trail.">
    <a href="{{ route('support.create') }}" class="btn-primary"><x-icon name="plus" class="size-4" />New Report</a>
  </x-page-head>

  @include('partials.flash')

  <form method="GET" action="{{ route('support.index') }}" class="card mb-5 flex flex-wrap items-end gap-3 p-4">
    <label class="min-w-[220px] flex-1 text-[12px] font-semibold text-muted-foreground">Search
      <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Ticket no, subject or description" class="field mt-1 w-full">
    </label>
    <label class="text-[12px] font-semibold text-muted-foreground">Status
      <select name="status" class="field mt-1">
        <option value="">All statuses</option>
        @foreach ($statuses as $key => $label)
          <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
        @endforeach
      </select>
    </label>
    <label class="text-[12px] font-semibold text-muted-foreground">Category
      <select name="category" class="field mt-1">
        <option value="">All categories</option>
        @foreach ($categories as $key => $label)
          <option value="{{ $key }}" @selected(($filters['category'] ?? '') === $key)>{{ $label }}</option>
        @endforeach
      </select>
    </label>
    <label class="text-[12px] font-semibold text-muted-foreground">Priority
      <select name="priority" class="field mt-1">
        <option value="">All priorities</option>
        @foreach ($priorities as $key => $label)
          <option value="{{ $key }}" @selected(($filters['priority'] ?? '') === $key)>{{ $label }}</option>
        @endforeach
      </select>
    </label>
    <button type="submit" class="btn-soft">Filter</button>
    <a href="{{ route('support.index') }}" class="btn-ghost">Reset</a>
  </form>

  <section class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="tbl w-full text-left text-[13px]" data-row-details="off">
        <thead>
          <tr>
            <th>Ticket</th>
            <th>Subject</th>
            <th>Category</th>
            <th>Priority</th>
            <th>Updated</th>
            <th class="text-right">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($reports as $report)
            <tr>
              <td class="font-semibold">{{ $report->ticket_no }}</td>
              <td class="max-w-[280px]">
                <div class="truncate font-medium">{{ $report->subject }}</div>
                <div class="truncate text-[11.5px] text-muted-foreground">{{ $report->related_reference ?: 'No reference' }}</div>
              </td>
              <td>{{ $categories[$report->category] ?? $report->category }}</td>
              <td>{{ $priorities[$report->priority] ?? $report->priority }}</td>
              <td>{{ optional($report->last_replied_at ?? $report->updated_at)->diffForHumans() }}</td>
              <td class="text-right">
                <a href="{{ route('support.show', $report) }}" class="row-view-btn" aria-label="View ticket {{ $report->ticket_no }}">View</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="py-10 text-center text-muted-foreground">No issue reports yet. Create one to start a ticket.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="border-t border-border p-4">{{ $reports->links() }}</div>
  </section>
@endsection
