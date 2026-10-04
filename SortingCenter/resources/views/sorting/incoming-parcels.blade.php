@extends('layouts.sorting')

@section('title', 'Incoming Parcels')
@section('subtitle', "Parcels riders have dropped off from sellers — scan each one before it moves to sorting.")

@push('styles')
@include('partials.legacy-styles')
<style>
  .stat-card.accent{border-color:rgba(124,79,224,.5);background:linear-gradient(180deg,#FAF7FF,#fff);}
  .flow{display:flex;align-items:center;gap:0;background:#fff;border:1px solid var(--line);border-radius:var(--radius);padding:14px 18px;margin-bottom:22px;overflow-x:auto;}
  .flow-step{display:flex;align-items:center;gap:8px;white-space:nowrap;padding:0 14px;position:relative;}
  .flow-step .dot{width:22px;height:22px;border-radius:50%;background:var(--paper);border:1.5px solid var(--line);color:var(--text-600);display:flex;align-items:center;justify-content:center;font-size:10.5px;font-weight:700;flex:0 0 auto;}
  .flow-step.done .dot{background:var(--ok-600);border-color:var(--ok-600);color:#fff;}
  .flow-step .label{font-size:12px;color:var(--text-600);font-weight:600;}
  .flow-step.done .label{color:var(--text-900);}
  .flow-sep{width:26px;height:1px;background:var(--line);flex:0 0 auto;}
  .flow-sep.done{background:var(--ok-600);}
  .head-actions{display:flex;align-items:center;gap:10px;}
  .search{display:flex;align-items:center;gap:8px;background:var(--paper);border:1px solid var(--line);border-radius:8px;padding:7px 10px;font-size:12.5px;color:var(--text-600);min-width:220px;}
  .search input{border:none;background:transparent;outline:none;font-size:12.5px;width:100%;font-family:inherit;color:var(--text-900);}
  .seller{font-weight:600;font-size:13px;}
  .via{display:flex;align-items:center;gap:8px;}
  .via .ravatar{width:24px;height:24px;border-radius:50%;background:var(--ink-800);color:#fff;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;}
  .via .rname{font-size:12.5px;font-weight:600;}
  .time{font-family:'IBM Plex Mono',monospace;font-size:12px;color:var(--text-600);}
  .badge.pending{background:var(--pend-100);color:var(--pend-600);}
  .btn{border:none;border-radius:7px;padding:7px 13px;font-size:12.5px;font-weight:600;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:6px;}
  .btn-primary{background:var(--ink-900);color:#fff;}
  .btn-primary:hover{background:var(--ink-800);}
  .btn-accent{background:var(--accent-500);color:var(--ink-900);}
  .btn-accent:hover{background:var(--accent-600);}
  .panel-foot{padding:12px 18px;font-size:12px;color:var(--text-600);border-top:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;}
  .note{background:#EFEAFB;color:var(--accent-600);border:1px solid rgba(124,79,224,.4);border-radius:8px;padding:10px 14px;font-size:12px;margin-bottom:16px;display:flex;align-items:center;gap:8px;}
</style>
@endpush

@section('content')

  <div class="stats">

    <div class="stat-card primary">
      <div class="card-top">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2"><path d="M21 8v8a2 2 0 0 1-1 1.73l-7 4a2 2 0 0 1-2 0l-7-4A2 2 0 0 1 3 16V8"/><path d="M3.27 6.96 12 12l8.73-5.04"/><path d="M12 22V12"/></svg>
        <div class="k">Awaiting scan</div>
      </div>
      <div class="val-row">
        <div class="v">{{ $awaitingScan }}</div>
        @if ($awaitingScan > 0)
          <span class="delta-pill" style="background:rgba(255,255,255,.2);color:#fff;">▲ {{ $awaitingScan }} waiting</span>
        @endif
      </div>
      <div class="sub">Parcels at the door</div>
    </div>

    <div class="stat-card">
      <div class="card-top">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#b8791a" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
        <div class="k">Scanned today</div>
      </div>
      <div class="val-row">
        <div class="v">{{ $scannedToday }}</div>
      </div>
      <div class="sub">In queue right now</div>
    </div>

    <div class="stat-card">
      <div class="card-top">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
        <div class="k">Riders dropping off</div>
      </div>
      <div class="val-row">
        <div class="v">{{ $ridersDroppingOff }}</div>
        @if ($ridersDroppingOff > 0)
          <span class="delta-pill up">▲ On route</span>
        @endif
      </div>
      <div class="sub">This shift</div>
    </div>

    <div class="stat-card">
      <div class="card-top">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
        <div class="k">Sent to sorting</div>
      </div>
      <div class="val-row">
        <div class="v">{{ $scannedToday }}</div>
        @if ($scannedToday === 0)
          <span class="delta-pill down">▼ {{ $awaitingScan }} pending</span>
        @endif
      </div>
      <div class="sub">Moved to sort queue</div>
    </div>

  </div>

  <div class="flow">
    <div class="flow-step done"><div class="dot">✓</div><div class="label">Rider picks up from seller</div></div>
    <div class="flow-sep done"></div>
    <div class="flow-step done"><div class="dot">✓</div><div class="label">Receive parcel</div></div>
    <div class="flow-sep {{ $scannedToday > 0 ? 'done' : '' }}"></div>
    <div class="flow-step {{ $scannedToday > 0 ? 'done' : '' }}"><div class="dot">✓</div><div class="label">Scan parcel</div></div>
    <div class="flow-sep"></div>
    <div class="flow-step"><div class="dot">4</div><div class="label">Read address</div></div>
    <div class="flow-sep"></div>
    <div class="flow-step"><div class="dot">5</div><div class="label">Send to sorting</div></div>
  </div>

  @if (session('status'))
    <div class="note">ⓘ {{ session('status') }}</div>
  @else
    <div class="note">ⓘ Once a parcel is scanned it moves to <b>Sort Parcels</b>, where it's routed by barangay and assigned to that barangay's rider.</div>
  @endif

  @if ($areas->isNotEmpty())
  <div class="panel" style="margin-bottom:16px;">
    <div class="panel-head">
      <div><h2>Today's pickup schedule</h2><div class="sub">Sellers must have parcels ready before each route's cutoff so the rider can collect them on time</div></div>
    </div>
    <table style="width:100%;table-layout:fixed;">
      <thead><tr style="text-align:center;">
        <th style="width:22%;text-align:center;">Route / Barangay</th>
        <th style="width:22%;text-align:center;">Rider</th>
        <th style="width:18%;text-align:center;">Pickup window</th>
        <th style="width:18%;text-align:center;">Ready-by cutoff</th>
        <th style="width:20%;text-align:center;">Status</th>
      </tr></thead>
      <tbody>
        @foreach ($areas as $area)
          <tr style="text-align:center;">
            <td class="seller" style="text-align:center;">{{ $area->name }}</td>
            <td style="text-align:center;">
              @if ($area->rider)
                <div class="via" style="justify-content:center;"><div class="ravatar">{{ $area->rider->initials }}</div><div class="rname">{{ $area->rider->name }}</div></div>
              @else
                <span style="color:var(--text-400);">Unassigned</span>
              @endif
            </td>
            <td class="time" style="text-align:center;">{{ $area->pickup_window }}</td>
            <td class="time" style="text-align:center;">{{ $area->cutoff_time }}</td>
            <td style="text-align:center;"><span class="badge {{ str_contains($area->pickup_status ?? '', 'not ready') ? 'pending' : 'ok' }}"><span class="dotb"></span>{{ $area->pickup_status }}</span></td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="panel-foot">
      <span>Parcels not ready by cutoff roll over to the next pickup window</span>
      <span>{{ $areas->count() }} routes today</span>
    </div>
  </div>
  @endif

  <div class="panel">
    <div class="panel-head">
      <div><h2>Drop-off queue</h2><div class="sub">Parcels handed over by pickup riders at the hub door</div></div>
      <div class="head-actions">
        <form method="GET" class="search" style="display:contents;">
          <input type="hidden" name="q" value="{{ $q }}">
          @include('partials.barangay-dropdown')
        </form>
        <form method="GET" class="search">
          <input type="hidden" name="area_id" value="{{ $area_id }}">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          <input type="text" name="q" value="{{ $q }}" placeholder="Search parcel ID or seller…" onchange="this.form.submit()">
        </form>
        <form method="POST" action="{{ route('parcels.incoming.scan-all') }}">
          @csrf
          <button class="btn btn-accent" {{ $parcels->isEmpty() ? 'disabled' : '' }}>Scan all arrived</button>
        </form>
      </div>
    </div>

    <table style="width:100%;table-layout:fixed;">
      <thead>
        <tr style="text-align:center;">
          <th style="width:16%;text-align:center;">Parcel</th>
          <th style="width:18%;text-align:center;">Seller</th>
          <th style="width:20%;text-align:center;">Dropped off by</th>
          <th style="width:14%;text-align:center;">Arrived</th>
          <th style="width:16%;text-align:center;">Status</th>
          <th style="width:16%;text-align:center;">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($parcels as $p)
          <tr style="text-align:center;">
            <td class="pid" style="text-align:center;">{{ $p->tracking_no }}</td>
            <td class="seller" style="text-align:center;">{{ $p->seller_name }}</td>
            <td style="text-align:center;"><div class="via" style="justify-content:center;"><div class="ravatar">{{ $p->droppedOffBy->initials ?? '—' }}</div><div class="rname">{{ $p->droppedOffBy->name ?? 'Unknown rider' }}</div></div></td>
            <td class="time" style="text-align:center;">{{ $p->received_at?->format('h:i A') }}</td>
            <td style="text-align:center;"><span class="badge pending"><span class="dotb"></span>Awaiting scan</span></td>
            <td style="text-align:center;">
              <button
                onclick="openScanModal({{ $p->id }}, '{{ $p->tracking_no }}', '{{ addslashes($p->seller_name) }}', '{{ addslashes($p->droppedOffBy->name ?? 'Unknown rider') }}', '{{ $p->received_at?->format('h:i A') }}')"
                style="border:1px solid var(--line);border-radius:6px;padding:4px 10px;background:#fff;font-size:12px;font-weight:600;color:var(--ink-700);cursor:pointer;font-family:inherit;">
                Details
              </button>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" style="text-align:center;color:var(--text-400);padding:24px;">No parcels waiting in the drop-off queue.</td></tr>
        @endforelse
      </tbody>
    </table>

    <div class="panel-foot">
      <span>{{ $parcels->count() }} parcels in drop-off queue</span>
      <span>Hub: Laguna Sorting Center — Bay 2</span>
    </div>
  </div>

  {{-- ── Scan parcel modal ── --}}
  <div id="scanModalBackdrop"
       onclick="if(event.target===this)closeScanModal()"
       style="display:none;position:fixed;inset:0;z-index:50;background:rgba(0,0,0,.45);align-items:center;justify-content:center;">

    <div style="background:#fff;border-radius:16px;width:100%;max-width:440px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.18);animation:slideUpIn .18s ease-out;">

      {{-- Header --}}
      <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--line);">
        <div>
          <div id="smTrackNo" style="font-size:15px;font-weight:800;font-family:'Poppins',sans-serif;"></div>
          <div style="font-size:11.5px;color:var(--text-600);margin-top:2px;">Parcel drop-off details</div>
        </div>
        <button onclick="closeScanModal()" style="background:none;border:none;cursor:pointer;color:var(--text-600);font-size:18px;line-height:1;padding:4px;">✕</button>
      </div>

      {{-- Info tiles --}}
      <div style="padding:18px 20px;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px;">
          <div style="background:#f7f6fb;border-radius:8px;padding:10px 12px;">
            <div style="font-size:10.5px;color:var(--text-600);font-weight:600;margin-bottom:3px;">SELLER</div>
            <div id="smSeller" style="font-size:13px;font-weight:600;"></div>
          </div>
          <div style="background:#f7f6fb;border-radius:8px;padding:10px 12px;">
            <div style="font-size:10.5px;color:var(--text-600);font-weight:600;margin-bottom:3px;">DROPPED OFF BY</div>
            <div id="smRider" style="font-size:13px;font-weight:600;"></div>
          </div>
          <div style="background:#f7f6fb;border-radius:8px;padding:10px 12px;grid-column:span 2;">
            <div style="font-size:10.5px;color:var(--text-600);font-weight:600;margin-bottom:3px;">ARRIVED</div>
            <div id="smTime" style="font-size:13px;font-weight:600;"></div>
          </div>
        </div>

        {{-- Status --}}
        <div style="margin-bottom:16px;padding:10px 12px;border-radius:8px;border:1px solid var(--line);display:flex;align-items:center;gap:8px;">
          <span style="width:8px;height:8px;border-radius:50%;background:#b8791a;flex-shrink:0;"></span>
          <div>
            <div style="font-size:10.5px;color:var(--text-600);font-weight:600;">CURRENT STATUS</div>
            <div style="font-size:13px;font-weight:700;color:#b8791a;">Awaiting scan</div>
          </div>
        </div>

        {{-- Scan action --}}
        <form id="smScanForm" method="POST">
          @csrf
          <button type="submit"
            style="width:100%;border:none;border-radius:8px;padding:11px;font-size:13px;font-weight:700;font-family:inherit;cursor:pointer;background:#4B2E7E;color:#fff;margin-bottom:8px;">
            ✓ Scan parcel — mark as arrived
          </button>
        </form>
        <button onclick="closeScanModal()"
          style="width:100%;border:1px solid var(--line);border-radius:8px;padding:11px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;background:#fff;color:var(--text-900);">
          Close
        </button>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
<style>
@keyframes slideUpIn {
  from { opacity:0; transform:translateY(12px); }
  to   { opacity:1; transform:translateY(0); }
}
</style>
<script>
function openScanModal(id, trackNo, seller, rider, time) {
  document.getElementById('smTrackNo').textContent = trackNo;
  document.getElementById('smSeller').textContent  = seller;
  document.getElementById('smRider').textContent   = rider;
  document.getElementById('smTime').textContent    = time;
  document.getElementById('smScanForm').action     = '/parcels/incoming/' + id + '/scan';

  const backdrop = document.getElementById('scanModalBackdrop');
  backdrop.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function closeScanModal() {
  document.getElementById('scanModalBackdrop').style.display = 'none';
  document.body.style.overflow = '';
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeScanModal(); });
</script>
@endpush
