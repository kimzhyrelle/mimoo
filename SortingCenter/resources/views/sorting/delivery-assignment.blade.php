@extends('layouts.sorting')

@section('title', 'Delivery Assignment')
@section('subtitle', "Hand off sorted parcels to each barangay's rider for today's delivery run.")

@push('styles')
<style>
/* ── Top status chips ── */
.dispatch-status-chips {
  display: flex;
  align-items: center;
  gap: 8px;
}
.status-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border: 1px solid var(--color-border);
  border-radius: 20px;
  padding: 5px 12px;
  font-size: 12px;
  font-weight: 600;
  background: var(--color-background);
  color: var(--color-foreground);
}
.status-chip .chip-dot {
  width: 7px; height: 7px;
  border-radius: 50%;
}
.chip-dot.green  { background: #16a34a; }
.chip-dot.blue   { background: #2563eb; }

/* ── Stat cards ── */
.stat-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
  margin-bottom: 18px;
}
.stat-card {
  background: var(--color-card);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 18px 20px;
}
.stat-card.primary {
  background: #4B2E7E;
  border-color: #4B2E7E;
}
.stat-card .card-top { display: flex; align-items: center; gap: 6px; margin-bottom: 10px; }
.stat-card .card-top svg { flex-shrink: 0; opacity: .65; }
.stat-card.primary .card-top svg { opacity: .8; }
.stat-card .k { font-size: 11.5px; font-weight: 600; color: var(--color-muted-foreground); line-height: 1; }
.stat-card.primary .k { color: rgba(255,255,255,.75); }
.stat-card .val-row { display: flex; align-items: flex-end; gap: 8px; }
.stat-card .v { font-size: 32px; font-weight: 800; letter-spacing: -0.5px; color: var(--color-foreground); line-height: 1; font-family: 'Poppins', sans-serif; }
.stat-card.primary .v { color: #fff; }
.stat-card .delta-pill { display: inline-flex; align-items: center; gap: 3px; border-radius: 6px; padding: 2px 7px; font-size: 10.5px; font-weight: 700; margin-bottom: 2px; }
.stat-card .delta-pill.up   { background: #e8f5ee; color: #16a34a; }
.stat-card .delta-pill.down { background: #fef2f2; color: #dc2626; }
.stat-card .delta-pill.neutral { background: var(--color-muted); color: var(--color-muted-foreground); }
.stat-card.primary .delta-pill.up { background: rgba(255,255,255,.2); color: #fff; }
.stat-card .sub { font-size: 11px; color: var(--color-muted-foreground); margin-top: 8px; }
.stat-card.primary .sub { color: rgba(255,255,255,.5); }

/* ── Info banner ── */
.info-banner {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  background: #fffbeb;
  border: 1px solid #fde68a;
  border-radius: 10px;
  padding: 10px 14px;
  font-size: 12px;
  color: #92400e;
  margin-bottom: 20px;
  line-height: 1.5;
}
.info-banner a { color: var(--color-primary); font-weight: 700; text-decoration: underline; }

/* ── Board header ── */
.board-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 14px;
}
.board-header h2 { font-size: 14px; font-weight: 700; }
.board-header .sub { font-size: 12px; color: var(--color-muted-foreground); margin-top: 2px; }
.board-actions { display: flex; gap: 8px; }
.btn-refresh {
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 7px 14px;
  font-size: 12.5px;
  font-weight: 600;
  background: var(--color-background);
  color: var(--color-foreground);
  cursor: pointer;
  font-family: inherit;
  transition: background .15s;
}
.btn-refresh:hover { background: var(--color-muted); }
.btn-dispatch-all {
  border: none;
  border-radius: 8px;
  padding: 7px 16px;
  font-size: 12.5px;
  font-weight: 700;
  background: var(--color-primary);
  color: var(--color-primary-foreground);
  cursor: pointer;
  font-family: inherit;
  transition: opacity .15s;
}
.btn-dispatch-all:hover { opacity: .88; }

/* ── Kanban board ── */
.dispatch-board {
  display: flex;
  gap: 14px;
  overflow-x: auto;
  padding-bottom: 12px;
  /* Custom scrollbar */
  scrollbar-width: thin;
  scrollbar-color: var(--color-border) transparent;
}
.dispatch-board::-webkit-scrollbar { height: 6px; }
.dispatch-board::-webkit-scrollbar-track { background: transparent; }
.dispatch-board::-webkit-scrollbar-thumb { background: var(--color-border); border-radius: 3px; }

/* ── Column card ── */
.dispatch-col {
  flex: 0 0 220px;
  width: 220px;
  min-width: 220px;
  display: flex;
  flex-direction: column;
  background: var(--color-card);
  border: 1px solid var(--color-border);
  border-radius: 14px;
  overflow: hidden;
}

/* Column header */
.col-head {
  padding: 13px 14px 10px;
  border-bottom: 1px solid var(--color-border);
  flex-shrink: 0;
}
.col-brgy {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12.5px;
  font-weight: 700;
  margin-bottom: 7px;
}
.brgy-dot {
  width: 10px; height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
}
.col-rider {
  display: flex;
  align-items: center;
  gap: 7px;
  margin-bottom: 8px;
}
.rider-av {
  width: 26px; height: 26px;
  border-radius: 50%;
  background: #2A1B4D;
  color: #fff;
  font-size: 9px;
  font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.rider-name { font-size: 12.5px; font-weight: 700; color: var(--color-foreground); }
.parcel-count {
  font-size: 11.5px;
  font-weight: 600;
  color: var(--color-primary);
  margin-bottom: 6px;
}
.col-progress-track {
  height: 3px;
  background: var(--color-muted);
  border-radius: 2px;
  overflow: hidden;
}
.col-progress-fill {
  height: 100%;
  border-radius: 2px;
  transition: width .4s ease;
}

/* Scrollable parcel list */
.parcel-list {
  flex: 1;
  overflow-y: auto;
  padding: 10px 10px 6px;
  display: flex;
  flex-direction: column;
  gap: 7px;
  max-height: 280px;
  min-height: 80px;
  /* Scrollbar */
  scrollbar-width: thin;
  scrollbar-color: var(--color-border) transparent;
}
.parcel-list::-webkit-scrollbar { width: 4px; }
.parcel-list::-webkit-scrollbar-thumb { background: var(--color-border); border-radius: 2px; }

/* Parcel card */
.parcel-card {
  background: var(--color-background);
  border: 1px solid var(--color-border);
  border-radius: 9px;
  padding: 9px 10px;
  flex-shrink: 0;
}
.parcel-no {
  font-size: 12px;
  font-weight: 700;
  color: var(--color-foreground);
  margin-bottom: 2px;
}
.parcel-addr {
  font-size: 11px;
  color: var(--color-muted-foreground);
  margin-bottom: 5px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.carrier-pill {
  display: inline-block;
  font-size: 10.5px;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 4px;
}
.carrier-pill.jnt      { background: #fdeded; color: #c81e1e; }
.carrier-pill.lbc      { background: #eaf4fb; color: #2a6fbd; }
.carrier-pill.lalamove { background: #f1eafe; color: #7c4fe0; }
.carrier-pill.default  { background: var(--color-muted); color: var(--color-muted-foreground); }

.empty-col {
  font-size: 11.5px;
  color: var(--color-muted-foreground);
  text-align: center;
  padding: 18px 10px;
  font-style: italic;
}

/* Column footer dispatch button */
.col-foot {
  padding: 10px;
  border-top: 1px solid var(--color-border);
  flex-shrink: 0;
}
.btn-dispatch-col {
  width: 100%;
  border: none;
  border-radius: 8px;
  padding: 9px;
  font-size: 12px;
  font-weight: 700;
  font-family: inherit;
  cursor: pointer;
  transition: opacity .15s;
  background: var(--color-primary);
  color: var(--color-primary-foreground);
}
.btn-dispatch-col:hover { opacity: .88; }
.btn-dispatch-col:disabled {
  background: var(--color-muted);
  color: var(--color-muted-foreground);
  cursor: not-allowed;
  opacity: 1;
}

/* No-rider column */
.col-no-rider .col-head { background: #f5f4fb; }
.no-rider-label {
  font-size: 11.5px;
  color: var(--color-muted-foreground);
  font-style: italic;
  margin-bottom: 4px;
}
</style>
@endpush

@section('content')

{{-- Session flash --}}
@if (session('status'))
  <div class="mb-4 flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-[13px] font-semibold text-emerald-700">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
    {{ session('status') }}
  </div>
@endif
@if (session('error'))
  <div class="mb-4 flex items-center gap-2 rounded-lg border border-destructive/30 bg-destructive/10 px-4 py-2.5 text-[13px] font-semibold text-destructive">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    {{ session('error') }}
  </div>
@endif

{{-- ── Stat cards ── --}}
<div class="stat-row">

  <div class="stat-card primary">
    <div class="card-top">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
      <div class="k">Sorted, awaiting dispatch</div>
    </div>
    <div class="val-row">
      <div class="v">{{ $readyToDispatch }}</div>
      @if ($readyToDispatch > 0)
        <span class="delta-pill up">▲ {{ $readyToDispatch }} ready</span>
      @endif
    </div>
    <div class="sub">Parcels in queue</div>
  </div>

  <div class="stat-card">
    <div class="card-top">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
      <div class="k">Dispatched today</div>
    </div>
    <div class="val-row">
      <div class="v">{{ $dispatchedToday }}</div>
      @if ($dispatchedToday > 0)
        <span class="delta-pill up">▲ {{ $dispatchedToday }} sent</span>
      @endif
    </div>
    <div class="sub">Assigned to riders</div>
  </div>

  <div class="stat-card">
    <div class="card-top">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#7c4fe0" stroke-width="2"><circle cx="8" cy="8" r="4"/><path d="M2 21v-2a5 5 0 0 1 5-5h2a5 5 0 0 1 5 5v2"/><circle cx="17.5" cy="9.5" r="2.5"/><path d="M22 21v-1.5a4 4 0 0 0-3-3.87"/></svg>
      <div class="k">Riders with a load</div>
    </div>
    <div class="val-row">
      <div class="v">{{ $ridersWithLoad }} / {{ $totalRiders }}</div>
    </div>
    <div class="sub">Active routes today</div>
  </div>

  <div class="stat-card">
    <div class="card-top">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#b8791a" stroke-width="2"><path d="M3 3h18"/><path d="M8 3v4a4 4 0 0 0 8 0V3"/><path d="M12 14v7"/><path d="M9 21h6"/></svg>
      <div class="k">Avg. load per rider</div>
    </div>
    <div class="val-row">
      <div class="v">{{ $avgLoad }}</div>
      <span class="delta-pill neutral">parcels</span>
    </div>
    <div class="sub">Per active rider</div>
  </div>

</div>

{{-- ── Info banner ── --}}
<div class="info-banner">
  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  <span>Each column is one barangay's rider — the same 1-rider-per-barangay assignment set in <a href="{{ route('riders.index') }}">Rider Management</a>. Dispatch sends every sorted parcel in that column to the rider at once.</span>
</div>

{{-- ── Board header ── --}}
<div class="board-header">
  <div>
    <h2>Dispatch board</h2>
    <div class="sub">Sorted parcels grouped by barangay, ready to hand to the assigned rider</div>
  </div>
  <div class="board-actions">
    <button class="btn-refresh" onclick="window.location.reload()">Refresh</button>
    <form method="POST" action="{{ route('delivery.assignment.dispatch-all') }}" style="display:inline;">
      @csrf
      <button type="submit" class="btn-dispatch-all"
        onclick="return confirm('Dispatch ALL sorted parcels to their assigned riders?')">
        Dispatch all columns
      </button>
    </form>
  </div>
</div>

{{-- ── Kanban board ── --}}
<div class="dispatch-board">
  @forelse ($areas as $area)
    @php
      $parcels    = $area->parcels;
      $count      = $parcels->count();
      $rider      = $area->rider;
      $color      = $area->chart_color ?? '#2A1B4D';
      $maxParcels = 12; // just for progress bar scale
      $pct        = $maxParcels ? min(100, round(($count / $maxParcels) * 100)) : 0;
    @endphp
    <div class="dispatch-col {{ $rider ? '' : 'col-no-rider' }}" id="col-area-{{ $area->id }}">

      {{-- Column header --}}
      <div class="col-head">
        <div class="col-brgy">
          <span class="brgy-dot" style="background:{{ $color }};"></span>
          Brgy. {{ $area->name }}
        </div>

        @if ($rider)
          <div class="col-rider">
            <div class="rider-av">{{ $rider->initials }}</div>
            <div class="rider-name">{{ $rider->name }}</div>
          </div>
          <div class="parcel-count">{{ $count }} {{ Str::plural('parcel', $count) }} ready</div>
          <div class="col-progress-track">
            <div class="col-progress-fill" style="width:{{ $pct }}%;background:{{ $color }};"></div>
          </div>
        @else
          <div class="no-rider-label">No rider assigned</div>
          <div class="parcel-count" style="color:var(--color-muted-foreground);">{{ $count }} {{ Str::plural('parcel', $count) }} waiting</div>
        @endif
      </div>

      {{-- Parcel list (scrollable) --}}
      <div class="parcel-list">
        @forelse ($parcels as $parcel)
          <div class="parcel-card">
            <div class="parcel-no">{{ $parcel->tracking_no }}</div>
            <div class="parcel-addr" title="{{ $parcel->address }}">{{ $parcel->address }}</div>
            @if ($parcel->carrier)
              <span class="carrier-pill {{ $parcel->carrier }}">
                {{ match($parcel->carrier) {
                  'jnt'      => 'J&T',
                  'lbc'      => 'LBC',
                  'lalamove' => 'Lalamove',
                  default    => strtoupper($parcel->carrier),
                } }}
              </span>
            @endif
          </div>
        @empty
          <div class="empty-col">No sorted parcels waiting for this barangay</div>
        @endforelse
      </div>

      {{-- Dispatch button --}}
      <div class="col-foot">
        @if ($rider && $count > 0)
          <form method="POST" action="{{ route('delivery.assignment.dispatch-area', $area) }}">
            @csrf
            <button type="submit" class="btn-dispatch-col">
              Dispatch {{ $count }} to {{ $rider->initials }} →
            </button>
          </form>
        @else
          <button class="btn-dispatch-col" disabled>
            {{ $rider ? 'No parcels to dispatch' : 'No rider assigned' }}
          </button>
        @endif
      </div>

    </div>
  @empty
    <div style="padding:40px;text-align:center;color:var(--color-muted-foreground);font-size:13px;">
      No barangays set up yet. Add areas in Rider Management first.
    </div>
  @endforelse
</div>

@endsection

@push('scripts')
<script>
function scrollToCol(id) {
  if (!id) return;
  const el = document.getElementById(id);
  if (!el) return;
  el.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
  el.style.outline = '2px solid var(--color-primary)';
  el.style.outlineOffset = '2px';
  setTimeout(() => { el.style.outline = ''; el.style.outlineOffset = ''; }, 2000);
}
</script>
@endpush
