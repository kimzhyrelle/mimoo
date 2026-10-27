{{-- Rows for the rider tables. Expects $rows (riders). --}}
@foreach ($rows as $r)
  @php
    $isSuspended = $r->status === 'suspended';
    $isActive = $r->isActive();
    $isOnline = $r->isOnline();
    $jobCount = $r->active_jobs_count ?? $r->activeJobsCount();
  @endphp
  <tr class="h-11 transition-colors hover:bg-secondary/60">
    <td>
      <span class="flex items-center gap-3">
        <span class="avatar size-8 rounded-xl text-[10.5px]">{{ $r->initials }}</span>
        <span>
          <span class="block font-bold">{{ $r->name }}</span>
          <span class="block text-[11px] text-muted-foreground">MIM-RDR-{{ str_pad((string) $r->id, 4, '0', STR_PAD_LEFT) }} - {{ $r->branch?->code ?? 'NO-BRANCH' }}</span>
          <span class="block text-[11px] text-muted-foreground">{{ $jobCount }}/3 jobs</span>
        </span>
      </span>
    </td>
    <td>
      <span class="block font-bold">{{ $r->plate_no ?: '-' }}</span>
      <span class="block text-[11.5px] text-muted-foreground">{{ $r->vehicle_type ?: '-' }}</span>
    </td>
    <td>
      <span class="block font-semibold">{{ $r->area->name ?? '—' }}</span>
      <span class="block text-[10.5px] text-muted-foreground">{{ $r->area?->code ?? '—' }}</span>
    </td>
    <td class="text-right">
      <x-row-view-button :modal="'view-rider-'.$r->id" :label="'View rider '.$r->name" />
    </td>
  </tr>
@endforeach
