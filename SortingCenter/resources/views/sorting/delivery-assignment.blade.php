@extends('layouts.sorting')

@section('title', 'Delivery Assignment')
@section('subtitle', "Hand off sorted parcels to each barangay's rider for today's delivery run.")

@push('styles')
<style>
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
.da-table { width: 100%; border-collapse: collapse; }
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
    <div style="display:flex;gap:8px;">
      <button class="btn-refresh" onclick="window.location.reload()">Refresh</button>
      <form method="POST" action="{{ route('delivery.assignment.dispatch-all') }}" style="display:inline;">
        @csrf
        <button type="submit" class="btn-dispatch-all"
          onclick="return confirm('Dispatch ALL sorted parcels to their assigned riders?')">
          Dispatch all
        </button>
      </form>
    </div>
  </div>

  <div style="overflow-y:auto;max-height:560px;">
    <table class="da-table">
      <thead style="position:sticky;top:0;z-index:1;">
        <tr>
          <th style="width:18%;">Barangay</th>
          <th style="width:18%;">Assigned rider</th>
          <th style="width:8%;text-align:center;">Parcels</th>
          <th style="width:22%;">Load</th>
          <th style="width:14%;text-align:right;padding-right:18px;">Actions</th>
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
          @endphp
          <tr>
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

            {{-- Actions --}}
            <td style="text-align:right;padding-right:18px;">
              <div style="display:inline-flex;gap:6px;align-items:center;">
                @if ($count > 0)
                  <button class="btn-view"
                    onclick="openViewModal(
                      '{{ addslashes($area->name) }}',
                      '{{ $rider?->name ?? '' }}',
                      '{{ $rider?->initials ?? '' }}',
                      '{{ $color }}',
                      {{ $count }},
                      {{ json_encode($parcels->map(fn($p) => ['no' => $p->tracking_no, 'addr' => $p->address, 'carrier' => $p->carrier ?? ''])->values()) }}
                    )">
                    View
                  </button>
                @endif
                @if ($rider && $count > 0)
                  <button class="btn-dispatch"
                    onclick="openDispatchModal(
                      {{ $area->id }},
                      '{{ addslashes($area->name) }}',
                      '{{ addslashes($rider->name) }}',
                      '{{ $rider->initials }}',
                      '{{ $color }}',
                      {{ $count }},
                      {{ json_encode($parcels->map(fn($p) => ['no' => $p->tracking_no, 'addr' => $p->address, 'carrier' => $p->carrier ?? ''])->values()) }},
                      '{{ route('delivery.assignment.dispatch-area', $area) }}'
                    )">
                    Dispatch →
                  </button>
                @else
                  <button class="btn-dispatch" disabled>
                    {{ $rider ? 'Empty' : 'No rider' }}
                  </button>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" style="text-align:center;color:var(--color-muted-foreground);padding:36px;font-size:13px;">
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

{{-- ── View parcels modal ── --}}
<div id="viewModalBackdrop"
     onclick="if(event.target===this)closeViewModal()"
     style="display:none;position:fixed;inset:0;z-index:50;background:rgba(0,0,0,.45);align-items:center;justify-content:center;">
  <div style="background:var(--color-card);border-radius:16px;width:100%;max-width:460px;max-height:82vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.22);animation:dmSlideUp .18s ease-out;">
    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--color-border);flex-shrink:0;">
      <div style="display:flex;align-items:center;gap:10px;">
        <span id="vmDot" style="width:11px;height:11px;border-radius:50%;flex-shrink:0;"></span>
        <div>
          <div id="vmTitle" style="font-size:14px;font-weight:800;"></div>
          <div style="font-size:11.5px;color:var(--color-muted-foreground);">Parcels in queue</div>
        </div>
      </div>
      <button onclick="closeViewModal()" style="background:none;border:none;cursor:pointer;color:var(--color-muted-foreground);font-size:18px;line-height:1;padding:4px;">✕</button>
    </div>
    {{-- Rider strip --}}
    <div id="vmRiderStrip" style="display:flex;align-items:center;gap:10px;padding:11px 20px;border-bottom:1px solid var(--color-border);background:var(--color-muted);flex-shrink:0;">
      <div id="vmRiderAv" style="width:28px;height:28px;border-radius:50%;background:var(--color-primary);color:var(--color-primary-foreground);font-size:9px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;"></div>
      <div id="vmRiderName" style="font-size:12.5px;font-weight:700;"></div>
      <div style="margin-left:auto;font-size:11px;color:var(--color-muted-foreground);" id="vmCount"></div>
    </div>
    {{-- List --}}
    <div style="overflow-y:auto;flex:1;padding:10px 12px;scrollbar-width:thin;scrollbar-color:var(--color-border) transparent;">
      <div id="vmList" style="display:flex;flex-direction:column;gap:7px;"></div>
    </div>
    {{-- Footer --}}
    <div style="padding:12px 20px;border-top:1px solid var(--color-border);flex-shrink:0;">
      <button onclick="closeViewModal()"
        style="width:100%;border:1px solid var(--color-border);border-radius:8px;padding:10px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;background:var(--color-background);color:var(--color-foreground);">
        Close
      </button>
    </div>
  </div>
</div>

{{-- ── Dispatch confirm modal ── --}}
<div id="dispatchModalBackdrop"
     onclick="if(event.target===this)closeDispatchModal()"
     style="display:none;position:fixed;inset:0;z-index:51;background:rgba(0,0,0,.45);align-items:center;justify-content:center;">
  <div style="background:var(--color-card);border-radius:16px;width:100%;max-width:460px;max-height:82vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.22);animation:dmSlideUp .18s ease-out;">
    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--color-border);flex-shrink:0;">
      <div style="display:flex;align-items:center;gap:10px;">
        <span id="dmBrgyDot" style="width:11px;height:11px;border-radius:50%;flex-shrink:0;"></span>
        <div>
          <div id="dmTitle" style="font-size:14px;font-weight:800;"></div>
          <div style="font-size:11.5px;color:var(--color-muted-foreground);">Confirm dispatch</div>
        </div>
      </div>
      <button onclick="closeDispatchModal()" style="background:none;border:none;cursor:pointer;color:var(--color-muted-foreground);font-size:18px;line-height:1;padding:4px;">✕</button>
    </div>
    {{-- Rider strip --}}
    <div style="display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid var(--color-border);background:var(--color-muted);flex-shrink:0;">
      <div id="dmRiderAv" style="width:34px;height:34px;border-radius:50%;background:var(--color-primary);color:var(--color-primary-foreground);font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;"></div>
      <div>
        <div style="font-size:10px;font-weight:700;color:var(--color-muted-foreground);text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px;">Rider</div>
        <div id="dmRiderName" style="font-size:13px;font-weight:700;color:var(--color-foreground);"></div>
      </div>
      <div style="margin-left:auto;text-align:right;">
        <div style="font-size:10px;font-weight:700;color:var(--color-muted-foreground);text-transform:uppercase;letter-spacing:.04em;margin-bottom:1px;">Parcels</div>
        <div id="dmCount" style="font-size:22px;font-weight:800;color:var(--color-primary);line-height:1;"></div>
      </div>
    </div>
    {{-- Parcel list --}}
    <div style="overflow-y:auto;flex:1;padding:10px 12px;scrollbar-width:thin;scrollbar-color:var(--color-border) transparent;">
      <div id="dmParcelList" style="display:flex;flex-direction:column;gap:7px;"></div>
    </div>
    {{-- Footer --}}
    <div style="padding:14px 20px;border-top:1px solid var(--color-border);display:flex;gap:10px;flex-shrink:0;">
      <button onclick="closeDispatchModal()"
        style="flex:1;border:1px solid var(--color-border);border-radius:8px;padding:11px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;background:var(--color-background);color:var(--color-foreground);">
        Cancel
      </button>
      <form id="dmForm" method="POST" style="flex:2;">
        @csrf
        <button type="submit" id="dmSubmitBtn"
          style="width:100%;border:none;border-radius:8px;padding:11px;font-size:13px;font-weight:700;font-family:inherit;cursor:pointer;background:var(--color-primary);color:var(--color-primary-foreground);">
          Confirm dispatch →
        </button>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<style>
@keyframes dmSlideUp {
  from { opacity:0; transform:translateY(14px); }
  to   { opacity:1; transform:translateY(0); }
}
</style>
<script>
const carrierLabels = { jnt:'J&T', lbc:'LBC', lalamove:'Lalamove' };
const carrierClass  = { jnt:'jnt', lbc:'lbc', lalamove:'lalamove', '':'default' };

function buildParcelList(containerId, parcels) {
  const list = document.getElementById(containerId);
  list.innerHTML = '';
  parcels.forEach(p => {
    const c   = carrierClass[p.carrier] || 'default';
    const lbl = carrierLabels[p.carrier] || (p.carrier ? p.carrier.toUpperCase() : '—');
    const el  = document.createElement('div');
    el.style.cssText = 'background:var(--color-background);border:1px solid var(--color-border);border-radius:9px;padding:9px 12px;display:flex;align-items:center;justify-content:space-between;gap:8px;';
    el.innerHTML = `
      <div style="min-width:0;">
        <div style="font-size:12px;font-weight:700;color:var(--color-foreground);margin-bottom:2px;">${p.no}</div>
        <div style="font-size:11px;color:var(--color-muted-foreground);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:270px;">${p.addr}</div>
      </div>
      <span class="carrier-pill ${c}" style="flex-shrink:0;">${lbl}</span>
    `;
    list.appendChild(el);
  });
}

// ── View modal (read-only) ──
function openViewModal(areaName, riderName, riderInitials, color, count, parcels) {
  document.getElementById('vmDot').style.background   = color;
  document.getElementById('vmTitle').textContent      = 'Brgy. ' + areaName;
  document.getElementById('vmRiderAv').textContent    = riderInitials || '?';
  document.getElementById('vmRiderName').textContent  = riderName || 'No rider assigned';
  document.getElementById('vmCount').textContent      = count + ' parcel' + (count !== 1 ? 's' : '');
  buildParcelList('vmList', parcels);
  document.getElementById('viewModalBackdrop').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeViewModal() {
  document.getElementById('viewModalBackdrop').style.display = 'none';
  document.body.style.overflow = '';
}

// ── Dispatch confirm modal ──
function openDispatchModal(areaId, areaName, riderName, riderInitials, color, count, parcels, actionUrl) {
  document.getElementById('dmBrgyDot').style.background  = color;
  document.getElementById('dmTitle').textContent         = 'Brgy. ' + areaName;
  document.getElementById('dmRiderAv').textContent       = riderInitials;
  document.getElementById('dmRiderName').textContent     = riderName;
  document.getElementById('dmCount').textContent         = count;
  document.getElementById('dmForm').action               = actionUrl;
  document.getElementById('dmSubmitBtn').textContent     = `Dispatch ${count} parcel${count !== 1 ? 's' : ''} to ${riderName} →`;
  buildParcelList('dmParcelList', parcels);
  document.getElementById('dispatchModalBackdrop').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeDispatchModal() {
  document.getElementById('dispatchModalBackdrop').style.display = 'none';
  document.body.style.overflow = '';
}

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') { closeDispatchModal(); closeViewModal(); }
});
</script>
@endpush
