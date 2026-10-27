@extends('layouts.sorting')

@section('title', 'Delivery Assignment')
@section('subtitle', "Hand off sorted parcels to each barangay's rider for today's delivery run.")

@push('styles')
<style>
/* ── Stat cards ── */
.stat-row {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 12px;
  margin-bottom: 18px;
}
@media (max-width: 900px) {
  .stat-row { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 520px) {
  .stat-row { grid-template-columns: minmax(0, 1fr); }
}
.stat-card {
  background: var(--color-card);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 18px 20px;
}
.stat-card.primary { background: var(--color-primary); border-color: var(--color-primary); }
.stat-card .card-top { display: flex; align-items: center; gap: 6px; margin-bottom: 10px; }
.stat-card .card-top svg { flex-shrink: 0; opacity: .65; }
.stat-card.primary .card-top svg { opacity: .8; }
.stat-card .k { font-size: 11px; font-weight: 600; color: var(--color-muted-foreground); line-height: 1; text-transform: uppercase; letter-spacing: .06em; }
.stat-card.primary .k { color: rgba(255,255,255,.75); }
.stat-card .val-row { display: flex; align-items: flex-end; gap: 8px; }
.stat-card .v { font-size: 24px; font-weight: 700; color: var(--color-foreground); line-height: 1; font-variant-numeric: tabular-nums; }
.stat-card.primary .v { color: #fff; }
.stat-card .delta-pill { display: inline-flex; align-items: center; gap: 3px; border-radius: 6px; padding: 2px 7px; font-size: 10.5px; font-weight: 700; margin-bottom: 2px; }
.stat-card .delta-pill.up   { background: var(--color-muted); color: var(--color-primary); }
.stat-card .delta-pill.neutral { background: var(--color-muted); color: var(--color-muted-foreground); }
.stat-card.primary .delta-pill.up { background: rgba(255,255,255,.2); color: #fff; }
.stat-card .sub { font-size: 11px; color: var(--color-muted-foreground); margin-top: 8px; }
.stat-card.primary .sub { color: rgba(255,255,255,.5); }

/* ── Panel ── */
.panel { background: var(--color-card); border: 1px solid var(--color-border); border-radius: 12px; overflow: hidden; margin-bottom: 16px; }
.panel-head { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid var(--color-border); flex-wrap: wrap; gap: 10px; }
.panel-head h2 { font-size: 14px; font-weight: 700; margin: 0; }
.panel-head .sub { font-size: 12px; color: var(--color-muted-foreground); margin-top: 2px; }

/* ── Table ── */
.da-table { width: 100%; min-width: 620px; border-collapse: collapse; }
.da-table thead th {
  padding: 10px 16px;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: var(--color-muted-foreground);
  background: var(--color-muted);
  border-bottom: 1px solid var(--color-border);
  text-align: left;
  white-space: nowrap;
}
.da-table tbody td {
  padding: 12px 16px;
  border-bottom: 1px solid var(--color-border);
  font-size: 13px;
  vertical-align: middle;
}
.da-table tbody tr:last-child td { border-bottom: none; }
.da-table tbody tr:hover td { background: var(--color-muted)/30; }

/* ── Rider cell ── */
.rider-cell { display: flex; align-items: center; gap: 9px; }
.rider-av {
  width: 30px; height: 30px; border-radius: 50%;
  background: var(--color-primary); color: var(--color-primary-foreground);
  font-size: 10px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}

/* ── Parcel count pill ── */
.count-pill {
  display: inline-flex; align-items: center; justify-content: center;
  min-width: 22px; height: 22px;
  border-radius: 11px;
  font-size: 11.5px; font-weight: 700;
  padding: 0 7px;
}
.count-pill.has   { background: var(--color-primary); color: #fff; }
.count-pill.empty { background: var(--color-muted); color: var(--color-muted-foreground); }

/* ── Carrier pills ── */
.carrier-pill { display: inline-block; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; }
.carrier-pill.jnt      { background: oklch(0.96 0.03 25);  color: oklch(0.40 0.15 25); }
.carrier-pill.lbc      { background: oklch(0.95 0.03 230); color: oklch(0.40 0.12 230); }
.carrier-pill.lalamove { background: oklch(0.95 0.04 291); color: oklch(0.45 0.18 291); }
.carrier-pill.default  { background: var(--color-muted); color: var(--color-muted-foreground); }

/* ── Buttons ── */
.btn-view {
  border: 1px solid var(--color-border);
  border-radius: 7px;
  padding: 5px 12px;
  font-size: 12px;
  font-weight: 600;
  background: var(--color-background);
  color: var(--color-foreground);
  cursor: pointer;
  font-family: inherit;
  transition: background .12s;
}
.btn-view:hover { background: var(--color-muted); }
.btn-dispatch {
  border: none;
  border-radius: 7px;
  padding: 5px 14px;
  font-size: 12px;
  font-weight: 700;
  background: var(--color-primary);
  color: var(--color-primary-foreground);
  cursor: pointer;
  font-family: inherit;
  transition: opacity .12s;
  white-space: nowrap;
}
.btn-dispatch:hover { opacity: .88; }
.btn-dispatch:disabled {
  background: var(--color-muted);
  color: var(--color-muted-foreground);
  cursor: not-allowed;
  opacity: 1;
}
.btn-dispatch-all {
  border: none; border-radius: 8px; padding: 7px 16px;
  font-size: 12.5px; font-weight: 700;
  background: var(--color-primary); color: var(--color-primary-foreground);
  cursor: pointer; font-family: inherit; transition: opacity .15s;
}
.btn-dispatch-all:hover { opacity: .88; }
.btn-refresh {
  border: 1px solid var(--color-border); border-radius: 8px; padding: 7px 14px;
  font-size: 12.5px; font-weight: 600;
  background: var(--color-background); color: var(--color-foreground);
  cursor: pointer; font-family: inherit; transition: background .15s;
}
.btn-refresh:hover { background: var(--color-muted); }

/* ── Progress bar ── */
.prog-track { height: 4px; background: var(--color-muted); border-radius: 2px; overflow: hidden; min-width: 60px; }
.prog-fill  { height: 100%; border-radius: 2px; }
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

{{-- ── Dispatch board as table ── --}}
<div class="panel">
  <div class="panel-head">
    <div>
      <h2>Dispatch board</h2>
      <div class="sub">One row per barangay route — click "View" to inspect parcels, then dispatch to the rider</div>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <button type="button" class="btn-refresh" onclick="window.location.reload()">Refresh</button>
      <button type="button" class="btn-dispatch-all" data-modal-open="confirmDispatchAll">Dispatch all</button>
    </div>
  </div>

  <div class="table-scroll-region">
    <table class="tbl min-w-[760px]" data-row-details="off">
      <thead>
        <tr>
          <th>Barangay</th>
          <th>Assigned rider</th>
          <th class="text-center">Parcels</th>
          <th>Load</th>
          <th>Status</th>
          <th class="text-right">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($areas as $area)
          @php
            $parcels = $area->parcels;
            $count   = $parcels->count();
            $rider   = $area->rider;
            $color   = $area->chart_color ?? '#2A1B4D';
            $pct     = $count > 0 ? min(100, round(($count / 12) * 100)) : 0;
            $isReady = $rider && $rider->isActive() && $count > 0;
            $statusLabel = match (true) {
                ! $rider => 'No rider',
                ! $rider->isActive() => 'Rider inactive',
                $count === 0 => 'Empty',
                default => 'Ready to dispatch',
            };
            $statusTone = match (true) {
                ! $rider => 'amber',
                ! $rider->isActive() => 'red',
                $count === 0 => 'slate',
                default => 'green',
            };
            $statusIcon = match (true) {
                ! $rider => 'user',
                ! $rider->isActive() => 'alert',
                $count === 0 => 'box',
                default => 'check-circle',
            };
          @endphp
          <tr class="h-11">
            {{-- Barangay --}}
            <td>
              <div style="display:flex;align-items:center;gap:8px;">
                <span style="width:10px;height:10px;border-radius:50%;background:{{ $color }};flex-shrink:0;display:inline-block;"></span>
                <div>
                  <div style="font-weight:700;font-size:13px;">{{ $area->name }}</div>
                  <div style="font-size:11px;color:var(--color-muted-foreground);">{{ $area->code }}</div>
                </div>
              </div>
            </td>

            {{-- Rider --}}
            <td>
              @if ($rider)
                <div class="rider-cell">
                  <div class="rider-av">{{ $rider->initials }}</div>
                  <div>
                    <div style="font-weight:600;font-size:13px;">{{ $rider->name }}</div>
                    <div style="font-size:11px;color:var(--color-muted-foreground);">{{ $rider->vehicleLabel() ?: 'Rider' }}</div>
                  </div>
                </div>
              @else
                <span style="font-size:12px;color:var(--color-muted-foreground);font-style:italic;">No rider assigned</span>
              @endif
            </td>

            {{-- Parcel count --}}
            <td style="text-align:center;">
              <span class="count-pill {{ $count > 0 ? 'has' : 'empty' }}">{{ $count }}</span>
            </td>

            {{-- Load bar --}}
            <td>
              @if ($count > 0)
                <div style="display:flex;align-items:center;gap:8px;">
                  <div class="prog-track" style="flex:1;">
                    <div class="prog-fill" style="width:{{ $pct }}%;background:{{ $color }};"></div>
                  </div>
                  <span style="font-size:11px;color:var(--color-muted-foreground);white-space:nowrap;">{{ $count }} / 12</span>
                </div>
              @else
                <span style="font-size:12px;color:var(--color-muted-foreground);">—</span>
              @endif
            </td>

            <td><x-status-badge :tone="$statusTone" :icon="$statusIcon">{{ $statusLabel }}</x-status-badge></td>
            <td class="text-right">
              <x-row-view-button :modal="'view-area-'.$area->id" :label="'View route '.$area->name" />
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" style="text-align:center;color:var(--color-muted-foreground);padding:36px;font-size:13px;">
              No barangays set up yet. Add areas in Rider Management first.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="padding:10px 18px;border-top:1px solid var(--color-border);font-size:12px;color:var(--color-muted-foreground);display:flex;justify-content:space-between;">
    <span>{{ $areas->count() }} routes</span>
    <span>{{ $readyToDispatch }} parcels waiting for dispatch</span>
  </div>
</div>

@foreach ($areas as $area)
  @include('partials.delivery-assignment-modal', ['area' => $area, 'parcels' => $area->parcels])
@endforeach

<x-confirm-modal id="confirmDispatchAll" title="Dispatch all sorted parcels?" confirmLabel="Dispatch all"
                 formAction="{{ route('delivery.assignment.dispatch-all') }}">
  <ul class="confirm-summary-list">
    <li>{{ $readyToDispatch }} sorted parcels will be assigned to their area riders.</li>
    <li>Areas without an active assigned rider stay in staging.</li>
  </ul>
</x-confirm-modal>


@endsection
