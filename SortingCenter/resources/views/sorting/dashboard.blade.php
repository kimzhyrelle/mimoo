@extends('layouts.sorting')

@section('title', 'Dashboard')
@section('subtitle', "Santa Cruz, Laguna Hub — today's parcel flow at a glance")

@php
  $card = 'rounded-lg border border-border bg-card p-4 shadow-sm';
  $panel = 'rounded-lg border border-border bg-card overflow-hidden';
  $panelHead = 'flex flex-wrap items-center justify-between gap-3 border-b border-border p-4';
  $th = 'border-b border-border bg-muted/40 px-4 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground';
  $td = 'border-b border-border px-4 py-3 text-sm font-medium';
  $badge = 'inline-block rounded border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider';
@endphp

@section('content')

  <div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">

    <div class="rounded-lg border border-primary bg-primary p-5 shadow-sm">
      <div class="mb-3 flex items-center gap-2">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.7)" stroke-width="1.5"><path d="M21 8v8a2 2 0 0 1-1 1.73l-7 4a2 2 0 0 1-2 0l-7-4A2 2 0 0 1 3 16V8"/><path d="M3.27 6.96 12 12l8.73-5.04"/><path d="M12 22V12"/></svg>
        <div class="text-[11px] font-semibold uppercase tracking-wider text-primary-foreground/70">Received today</div>
      </div>
      <div class="flex items-end gap-2">
        <div class="text-2xl font-bold text-primary-foreground leading-none">{{ $receivedToday }}</div>
        @php $delta = $receivedYesterday > 0 ? round((($receivedToday - $receivedYesterday) / $receivedYesterday) * 100) : 0; @endphp
        <span class="mb-0.5 inline-flex items-center gap-1 rounded border border-primary-foreground/20 bg-primary-foreground/15 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-primary-foreground">
          {{ $delta >= 0 ? '▲' : '▼' }} {{ abs($delta) }}%
        </span>
      </div>
      <div class="mt-2 text-[11px] text-primary-foreground/50">Last week: {{ $receivedYesterday }}</div>
    </div>

    <div class="{{ $card }}">
      <div class="mb-3 flex items-center gap-2">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-amber-500"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
        <div class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Awaiting sort</div>
      </div>
      <div class="flex items-end gap-2">
        <div class="text-2xl font-bold leading-none">{{ $awaitingSort }}</div>
      </div>
      <div class="mt-2 text-[11px] text-muted-foreground">In queue right now</div>
    </div>

    <div class="{{ $card }}">
      <div class="mb-3 flex items-center gap-2">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-emerald-500"><path d="M20 6 9 17l-5-5"/></svg>
        <div class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Assigned to riders</div>
      </div>
      <div class="flex items-end gap-2">
        <div class="text-2xl font-bold leading-none">{{ $assignedToRiders }}</div>
        @if ($assignedToRiders > 0)
          <span class="mb-0.5 inline-block rounded border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-600">▲ On the way</span>
        @endif
      </div>
      <div class="mt-2 text-[11px] text-muted-foreground">This shift</div>
    </div>

    <div class="{{ $card }}">
      <div class="mb-3 flex items-center gap-2">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-destructive"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/></svg>
        <div class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Delivery failed</div>
      </div>
      <div class="flex items-end gap-2">
        <div class="text-2xl font-bold leading-none">{{ $deliveryFailed }}</div>
        @if ($deliveryFailed > 0)
          <span class="mb-0.5 inline-block rounded border border-red-200 bg-red-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-red-600">▼ Needs action</span>
        @endif
      </div>
      <div class="mt-2 text-[11px] text-muted-foreground">Needs reschedule</div>
    </div>

  </div>

  <div class="mb-4 grid grid-cols-1 gap-4 lg:grid-cols-[1.4fr_1fr]">
    <div class="{{ $panel }}">
      <div class="{{ $panelHead }}">
        <div><h2 class="text-sm font-semibold">Parcel volume — this week</h2><div class="mt-0.5 text-xs text-muted-foreground">Received vs. assigned to rider</div></div>
        <div class="flex gap-1 rounded-md border border-border bg-muted/40 p-0.5">
          <div class="rounded px-2.5 py-1 text-[11.5px] font-medium bg-background shadow-sm">Week</div>
          <div class="rounded px-2.5 py-1 text-[11.5px] font-medium text-muted-foreground">Month</div>
        </div>
      </div>
      <div class="p-4"><div class="relative h-56"><canvas id="volumeChart"></canvas></div></div>
    </div>

    <div class="{{ $panel }}">
      <div class="{{ $panelHead }}">
        <div><h2 class="text-sm font-semibold">Parcels by barangay</h2><div class="mt-0.5 text-xs text-muted-foreground">Today's sorted distribution within Santa Cruz</div></div>
      </div>
      <div class="p-4">
        {{-- Pie chart --}}
        <div class="relative mx-auto" style="height:220px;max-width:220px;">
          <canvas id="areaChart"></canvas>
        </div>
        {{-- Legend --}}
        <div class="mt-4 grid grid-cols-2 gap-x-4 gap-y-1.5">
          @foreach ($areas as $area)
            <div class="flex items-center gap-2 min-w-0">
              <span class="size-2 shrink-0 rounded-full" style="background:{{ $area->chart_color }}"></span>
              <span class="truncate text-[11px] text-muted-foreground">{{ $area->name }}</span>
              <span class="ml-auto text-[12px] font-bold tabular-nums {{ $area->parcel_count > 0 ? 'text-foreground' : 'text-muted-foreground' }}">{{ $area->parcel_count }}</span>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 xl:grid-cols-3">
    <div class="{{ $panel }}">
      <div class="{{ $panelHead }}">
        <div><h2 class="text-sm font-semibold">Rider status</h2><div class="mt-0.5 text-xs text-muted-foreground">On shift right now</div></div>
        <button onclick="openRiderModal()" class="text-xs font-semibold text-foreground hover:underline">View assignments →</button>
      </div>
      {{-- Rider list --}}
      <div class="overflow-y-auto" style="max-height:320px;">
        <div class="flex flex-col px-4 py-2">
          @foreach ($riders as $rider)
            <div class="flex items-center gap-2.5 border-b border-border py-2.5 last:border-none">
              <div class="flex size-7.5 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">{{ $rider->initials }}</div>
              <div class="min-w-0 flex-1"><div class="text-[12.5px] font-semibold">{{ $rider->name }}</div><div class="text-[11px] text-muted-foreground">{{ $rider->area->name ?? '—' }}</div></div>
              @if ($rider->status === 'available')
                <span class="{{ $badge }} border-emerald-200 bg-emerald-50 text-emerald-600">{{ $rider->statusLabel() }}</span>
              @elseif ($rider->status === 'busy')
                <span class="{{ $badge }} border-amber-200 bg-amber-50 text-amber-600">{{ $rider->statusLabel() }}</span>
              @else
                <span class="{{ $badge }} border-border bg-muted text-muted-foreground">{{ $rider->statusLabel() }}</span>
              @endif
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <div class="{{ $panel }}">
      <div class="{{ $panelHead }}">
        <div><h2 class="text-sm font-semibold">Recent activity</h2><div class="mt-0.5 text-xs text-muted-foreground">Live sorting hub events</div></div>
      </div>
      {{-- Activity feed --}}
      <div class="overflow-y-auto" style="max-height:320px;">
        <div class="flex flex-col px-4 py-2">
          @foreach ($recentActivity as $a)
            <div class="flex gap-2.5 border-b border-border py-2.5 last:border-none">
              @php
                $iconClasses = match($a->icon) {
                  'green' => 'bg-emerald-50 text-emerald-600',
                  'red' => 'bg-destructive/10 text-destructive',
                  default => 'bg-accent text-accent-foreground',
                };
              @endphp
              <div class="flex size-7 shrink-0 items-center justify-center rounded-full {{ $iconClasses }}">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/></svg>
              </div>
              <div>
                <div class="text-[12.5px] leading-snug">{!! $a->description !!}</div>
                <div class="mt-0.5 text-[11px] text-muted-foreground">{{ $a->created_at->diffForHumans() }}</div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <div class="{{ $panel }}">
      <div class="{{ $panelHead }}">
        <div><h2 class="text-sm font-semibold">Needs attention</h2><div class="mt-0.5 text-xs text-muted-foreground">Waiting the longest</div></div>
        <a href="{{ route('parcels.incoming') }}" class="text-xs font-semibold text-foreground hover:underline">View queue →</a>
      </div>
      <div class="overflow-y-auto" style="max-height:320px;">
        <table class="w-full border-collapse">
          <thead class="sticky top-0 bg-card"><tr><th class="{{ $th }}">Parcel</th><th class="{{ $th }}">Address</th><th class="{{ $th }}">Status</th></tr></thead>
        <tbody>
          @foreach ($needsAttention as $p)
            <tr>
              <td class="{{ $td }} font-mono font-semibold">{{ $p->tracking_no }}</td>
              <td class="{{ $td }}">{{ $p->area->name ?? $p->address }}</td>
              <td class="{{ $td }}">
                @if ($p->status === 'awaiting_sort')
                  <span class="{{ $badge }} border-amber-200 bg-amber-50 text-amber-600">{{ $p->waitingMinutes() }} min</span>
                @else
                  <span class="{{ $badge }} border-emerald-200 bg-emerald-50 text-emerald-600">Sorted</span>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- ── Rider Assignment Modal ── --}}
  <div id="riderModalBackdrop"
       onclick="if(event.target===this)closeRiderModal()"
       class="hidden fixed inset-0 z-50 flex items-center justify-center"
       style="background:rgba(0,0,0,.45);">

    <div class="relative w-full max-w-3xl max-h-[85vh] flex flex-col rounded-2xl bg-card shadow-2xl overflow-hidden"
         style="animation:riderSlideUp .18s ease-out;">

      {{-- Header --}}
      <div class="flex items-center justify-between border-b border-border px-6 py-4 flex-shrink-0">
        <div>
          <div class="text-sm font-semibold">Active Riders — Barangay Assignment</div>
          <div class="mt-0.5 text-[12px] text-primary">One rider per barangay, per the sorting center's coverage area</div>
        </div>
        <div class="flex items-center gap-3">
          {{-- Search --}}
          <div class="flex items-center gap-2 rounded-lg border border-border bg-background px-3 py-2">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-muted-foreground"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" id="riderSearch" oninput="filterRiders(this.value)" placeholder="Search rider…"
              class="border-none bg-transparent text-[12.5px] outline-none w-36 placeholder:text-muted-foreground">
          </div>
          <button onclick="closeRiderModal()" class="rounded-full p-1.5 text-muted-foreground hover:bg-muted hover:text-foreground transition-colors">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
      </div>

      {{-- Table --}}
      <div class="overflow-y-auto flex-1">
        <table class="w-full" style="border-collapse:collapse;">
          <thead class="sticky top-0 bg-muted/60 backdrop-blur-sm">
            <tr>
              <th class="px-5 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Rider</th>
              <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Vehicle</th>
              <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Assigned barangay</th>
              <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Status</th>
              <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Active</th>
            </tr>
          </thead>
          <tbody id="riderModalBody">
            @foreach ($riders as $r)
              <tr class="rider-modal-row border-b border-border hover:bg-muted/20 transition-colors"
                  data-name="{{ strtolower($r->name) }}">
                {{-- Rider --}}
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-2.5">
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">{{ $r->initials }}</div>
                    <div>
                      <div class="text-[13px] font-semibold">{{ $r->name }}</div>
                      <div class="text-[11px] text-muted-foreground">{{ $r->initials }}</div>
                    </div>
                  </div>
                </td>
                {{-- Vehicle --}}
                <td class="px-4 py-3.5">
                  <div class="text-[12.5px] text-primary font-semibold">{{ $r->vehicleLabel() ?: '—' }}</div>
                </td>
                {{-- Barangay dropdown --}}
                <td class="px-4 py-3.5 text-center">
                  <form method="POST" action="{{ route('riders.barangay', $r) }}">
                    @csrf
                    <select name="area_id" onchange="this.form.submit()"
                      class="rounded-lg border border-border bg-background px-3 py-2 text-[12.5px] font-semibold text-foreground cursor-pointer outline-none focus:border-ring min-w-[140px]">
                      <option value="">— Unassigned —</option>
                      @foreach ($areas as $area)
                        @php $takenBy = $area->rider && $area->rider->id !== $r->id ? $area->rider : null; @endphp
                        <option value="{{ $area->id }}" {{ $r->area_id === $area->id ? 'selected' : '' }}>
                          {{ $area->name }}{{ $takenBy ? ' (taken by '.$takenBy->initials.')' : '' }}
                        </option>
                      @endforeach
                    </select>
                  </form>
                </td>
                {{-- Status --}}
                <td class="px-4 py-3.5 text-center">
                  @if ($r->isActive())
                    <span class="inline-block rounded border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-600">
                      On shift
                    </span>
                  @else
                    <span class="inline-block rounded border border-border bg-muted px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
                      Off shift
                    </span>
                  @endif
                </td>
                {{-- Toggle --}}
                <td class="px-4 py-3.5 text-center">
                  <form method="POST" action="{{ route('riders.toggle-active', $r) }}">
                    @csrf
                    <button type="submit"
                      class="relative inline-flex h-6 w-11 cursor-pointer rounded-full border-2 border-transparent transition-colors {{ $r->isActive() ? 'bg-emerald-500' : 'bg-muted' }}">
                      <span class="inline-block size-5 rounded-full bg-white shadow transition-transform {{ $r->isActive() ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{-- Footer --}}
      <div class="flex items-center justify-between border-t border-border px-6 py-3 flex-shrink-0 bg-muted/30">
        <div class="text-[12px] text-muted-foreground">{{ $riders->count() }} riders · {{ $areas->count() }} barangays</div>
        <a href="{{ route('riders.index') }}" class="text-[12px] font-semibold text-primary hover:underline">Open full Rider Management →</a>
      </div>

    </div>
  </div>

@endsection

@push('styles')
<style>
@keyframes riderSlideUp {
  from { opacity:0; transform:translateY(14px); }
  to   { opacity:1; transform:translateY(0); }
}
</style>
@endpush

@push('scripts')
<script>
// ── Rider assignment modal ──
function openRiderModal() {
  document.getElementById('riderModalBackdrop').classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}
function closeRiderModal() {
  document.getElementById('riderModalBackdrop').classList.add('hidden');
  document.body.style.overflow = '';
}
function filterRiders(val) {
  const q = val.toLowerCase();
  document.querySelectorAll('.rider-modal-row').forEach(row => {
    row.style.display = row.dataset.name.includes(q) ? '' : 'none';
  });
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeRiderModal(); });
const primary = 'oklch(0.205 0 0)';
const receivedBar = 'oklch(0.87 0 0)';
const border = 'oklch(0.922 0 0)';
const mutedFg = 'oklch(0.556 0 0)';
const chart = ['oklch(0.646 0.222 41.116)', 'oklch(0.6 0.118 184.704)', 'oklch(0.398 0.07 227.392)', 'oklch(0.828 0.189 84.429)', 'oklch(0.769 0.188 70.08)'];

new Chart(document.getElementById('volumeChart'), {
  type: 'bar',
  data: {
    labels: @json($labels),
    datasets: [
      { label:'Received', data: @json($received), backgroundColor: receivedBar, borderRadius:5, barThickness:14 },
      { label:'Assigned', data: @json($assigned), backgroundColor: primary, borderRadius:5, barThickness:14 }
    ]
  },
  options: {
    responsive:true, maintainAspectRatio:false,
    plugins:{ legend:{ position:'top', align:'end', labels:{ boxWidth:8, boxHeight:8, usePointStyle:true, font:{ size:11, family:'Instrument Sans' }, color: mutedFg } } },
    scales:{
      x:{ grid:{ display:false }, ticks:{ font:{ size:11, family:'Instrument Sans' }, color: mutedFg } },
      y:{ grid:{ color: border }, ticks:{ font:{ size:11, family:'Instrument Sans' }, color: mutedFg }, beginAtZero:true }
    }
  }
});

new Chart(document.getElementById('areaChart'), {
  type: 'pie',
  data: {
    labels: @json($areas->pluck('name')),
    datasets: [{
      data: @json($areas->pluck('parcel_count')),
      backgroundColor: @json($areas->pluck('chart_color')),
      borderWidth: 2,
      borderColor: document.documentElement.classList.contains('dark') ? '#1e1a2e' : '#fff',
      hoverOffset: 8,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: ctx => ` ${ctx.label}: ${ctx.parsed} parcel${ctx.parsed !== 1 ? 's' : ''}`
        }
      }
    }
  }
});
</script>
@endpush
