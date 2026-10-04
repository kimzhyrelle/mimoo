@extends('layouts.sorting')

@section('title', 'Delivery Monitoring')
@section('subtitle', "Track every assigned parcel from the sorting hub to the buyer's door.")

@push('styles')
@include('partials.legacy-styles')
<style>
  .pill.live{color:var(--ok-600);border-color:rgba(47,143,91,.35);background:var(--ok-100);}
  .pill.live .liveDot{display:inline-block;width:6px;height:6px;border-radius:50%;background:var(--ok-600);margin-right:5px;}
  .rail{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:10px;}
  .rail-card{background:#fff;border:1px solid var(--line);border-radius:12px;padding:14px 16px;position:relative;}
  .rail-card .card-top{display:flex;align-items:center;gap:5px;margin-bottom:6px;}
  .rail-card .card-top svg{opacity:.6;flex-shrink:0;}
  .rail-card .card-lbl{font-size:11px;font-weight:600;color:var(--text-600);line-height:1;}
  .rail-card .n{font-size:26px;font-weight:800;letter-spacing:-.5px;font-family:'Poppins',sans-serif;line-height:1;}
  .rail-card .bar{height:3px;border-radius:2px;margin-top:10px;}
  .rail-card.assigned .bar{background:#2A6FBD;}
  .rail-card.out .bar{background:var(--pend-600);}
  .rail-card.delivered .bar{background:var(--ok-600);}
  .rail-card.failed .bar{background:var(--bad-600);}
  .rail-card.sorted .bar{background:rgba(255,255,255,.4);}
  .rail-card.pending .bar{background:var(--text-400);}
  .rail-card.sorted{background:#4B2E7E;border-color:#4B2E7E;}
  .rail-card.sorted .n{color:#fff;}
  .rail-card.sorted .card-lbl{color:rgba(255,255,255,.7);}
  .rail-card.sorted .card-top svg{stroke:#fff !important;opacity:.8;}
  .head-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
  .filter-tabs{display:flex;gap:6px;background:var(--paper);border:1px solid var(--line);border-radius:8px;padding:3px;flex-wrap:wrap;}
  .ftab{font-size:11.5px;font-weight:600;color:var(--text-600);padding:6px 11px;border-radius:6px;cursor:pointer;text-decoration:none;}
  .ftab.active{background:#fff;color:var(--text-900);box-shadow:0 1px 2px rgba(0,0,0,.06);}
  .search{display:flex;align-items:center;gap:8px;background:var(--paper);border:1px solid var(--line);border-radius:8px;padding:7px 10px;font-size:12.5px;color:var(--text-600);min-width:200px;}
  .search input{border:none;background:transparent;outline:none;font-size:12.5px;width:100%;font-family:inherit;color:var(--text-900);}
  .addr .city{font-weight:600;font-size:13px;}
  .addr .prov{font-size:11.5px;color:var(--text-600);}
  .area-chip{display:inline-flex;align-items:center;gap:6px;font-family:'IBM Plex Mono',monospace;font-size:11px;font-weight:600;padding:3px 8px;border-radius:6px;color:#fff;background:var(--ink-700);}
  .rider{display:flex;align-items:center;gap:8px;}
  .rider .ravatar{width:24px;height:24px;border-radius:50%;background:var(--ink-800);color:#fff;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;}
  .rider .rname{font-size:12.5px;font-weight:600;}
  .badge{display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:600;padding:4px 9px;border-radius:20px;white-space:nowrap;}
  .badge.blue{background:#E6F0FB;color:#2A6FBD;}
  .badge.gold{background:var(--pend-100);color:var(--pend-600);}
  .badge.ok{background:var(--ok-100);color:var(--ok-600);}
  .badge.bad{background:var(--bad-100);color:var(--bad-600);}
  .progress{display:flex;align-items:center;gap:3px;}
  .progress .seg{width:16px;height:4px;border-radius:2px;background:var(--line);}
  .progress .seg.on{background:var(--ok-600);}
  .progress .seg.on.gold{background:var(--pend-600);}
  .progress .seg.on.bad{background:var(--bad-600);}
  .eta{font-family:'IBM Plex Mono',monospace;font-size:12px;color:var(--text-600);}
  .link-btn{font-size:12px;font-weight:600;color:var(--ink-700);cursor:pointer;background:none;border:none;font-family:inherit;}
  .note{background:#EFEAFB;color:var(--accent-600);border:1px solid rgba(124,79,224,.4);border-radius:8px;padding:10px 14px;font-size:12px;margin-bottom:16px;display:flex;align-items:center;gap:8px;}
  @media (max-width: 1100px){ .rail{grid-template-columns:repeat(3,1fr);} }
</style>
@endpush

@section('content')

  <div class="rail">
    <div class="rail-card sorted">
      <div class="card-top"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/></svg><div class="card-lbl">Sorted</div></div>
      <div class="n">{{ $rail['sorted'] }}</div><div class="bar"></div>
    </div>
    <div class="rail-card assigned">
      <div class="card-top"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#2A6FBD" stroke-width="2"><circle cx="8" cy="8" r="4"/><path d="M2 21v-2a5 5 0 0 1 5-5h2a5 5 0 0 1 5 5v2"/></svg><div class="card-lbl">Assigned to rider</div></div>
      <div class="n">{{ $rail['assigned'] }}</div><div class="bar"></div>
    </div>
    <div class="rail-card out">
      <div class="card-top"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#b8791a" stroke-width="2"><path d="M9 20l-5.447-2.724A1 1 0 0 1 3 16.382V5.618a1 1 0 0 1 1.447-.894L9 7m0 13l6-3m-6 3V7m6 10l5.447 2.724A1 1 0 0 0 21 18.618V7.618a1 1 0 0 0-.553-.894L15 4m0 13V4m0 0L9 7"/></svg><div class="card-lbl">Out for delivery</div></div>
      <div class="n">{{ $rail['out'] }}</div><div class="bar"></div>
    </div>
  </div>

  {{-- Outcome summary cards --}}
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:18px;">
    <div class="rail-card delivered">
      <div class="card-top"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#2f8f5b" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg><div class="card-lbl">Delivered</div></div>
      <div class="n">{{ $rail['delivered'] }}</div><div class="bar"></div>
    </div>
    <div class="rail-card pending">
      <div class="card-top"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#9a93ac" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><div class="card-lbl">Pending confirm</div></div>
      <div class="n">{{ $rail['pending_confirm'] }}</div><div class="bar"></div>
    </div>
    <div class="rail-card failed">
      <div class="card-top"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#c0432f" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg><div class="card-lbl">Delivery failed</div></div>
      <div class="n">{{ $rail['failed'] }}</div><div class="bar"></div>
    </div>
  </div>

  <div class="note">ⓘ A parcel isn't marked "not delivered" from the app alone — the rider must <b>scan the parcel</b> at end of day to confirm it genuinely wasn't delivered today.</div>

  <div class="panel">
    <div class="panel-head">
      <div><h2>Parcels in transit</h2><div class="sub">Status follows: Assigned → Out for delivery → Delivered → Completed</div></div>
      <div class="head-actions">
        <div class="filter-tabs">
          @foreach (['all'=>'All','assigned'=>'Assigned','out'=>'Out for delivery','delivered'=>'Delivered','failed'=>'Failed'] as $key => $label)
            <a href="{{ route('delivery.monitoring', array_filter(['filter'=>$key, 'q'=>$q, 'area_id'=>$area_id])) }}" class="ftab {{ $filter === $key ? 'active' : '' }}">{{ $label }}</a>
          @endforeach
        </div>
        <form method="GET" style="display:contents;">
          <input type="hidden" name="filter" value="{{ $filter }}">
          <input type="hidden" name="q" value="{{ $q }}">
          @include('partials.barangay-dropdown')
        </form>
        <form method="GET" class="search">
          <input type="hidden" name="filter" value="{{ $filter }}">
          <input type="hidden" name="area_id" value="{{ $area_id }}">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          <input type="text" name="q" value="{{ $q }}" placeholder="Search parcel or rider…" onchange="this.form.submit()">
        </form>
      </div>
    </div>

    <table style="width:100%;table-layout:fixed;">
      <thead>
        <tr style="text-align:center;">
          <th style="width:14%;text-align:center;">Parcel</th>
          <th style="width:18%;text-align:center;">Destination</th>
          <th style="width:12%;text-align:center;">Barangay</th>
          <th style="width:14%;text-align:center;">Rider</th>
          <th style="width:10%;text-align:center;">Progress</th>
          <th style="width:14%;text-align:center;">Status</th>
          <th style="width:9%;text-align:center;">ETA</th>
          <th style="width:9%;text-align:center;">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($parcels as $p)
          @php
            $idx = match($p->status){ 'assigned'=>0, 'out_for_delivery'=>1, 'delivered'=>2, 'failed'=>1, default=>0 };
            $segClass = $p->status==='failed' ? 'bad' : ($p->status==='out_for_delivery' ? 'gold' : '');
          @endphp
          <tr style="text-align:center;">
            <td class="pid" style="text-align:center;">{{ $p->tracking_no }}</td>
            <td class="addr" style="text-align:center;"><div class="city">{{ $p->address }}</div><div class="prov">Santa Cruz, Laguna</div></td>
            <td style="text-align:center;"><span class="area-chip">{{ $p->area->name ?? '—' }}</span></td>
            <td style="text-align:center;"><div class="rider" style="justify-content:center;"><div class="ravatar">{{ $p->rider->initials ?? '—' }}</div><div class="rname">{{ $p->rider->name ?? 'Unassigned' }}</div></div></td>
            <td style="text-align:center;">
              <div class="progress" style="justify-content:center;">
                @for ($i = 0; $i < 3; $i++)
                  <span class="seg {{ $i <= $idx ? 'on '.$segClass : '' }}"></span>
                @endfor
              </div>
            </td>
            <td style="text-align:center;">
              @if ($p->status === 'assigned')
                <span class="badge blue"><span class="dotb"></span>Assigned</span>
              @elseif ($p->status === 'out_for_delivery')
                <span class="badge gold"><span class="dotb"></span>Out for delivery</span>
              @elseif ($p->status === 'delivered')
                <span class="badge ok"><span class="dotb"></span>Delivered</span>
              @elseif ($p->scanned_undelivered)
                <span class="badge bad"><span class="dotb"></span>Confirmed undelivered</span>
              @else
                <span class="badge bad"><span class="dotb"></span>Not delivered</span>
              @endif
            </td>
            <td class="eta" style="text-align:center;">{{ $p->eta_note ?? '—' }}</td>
            <td style="text-align:center;">
              <button
                onclick="openParcelModal(
                  '{{ $p->tracking_no }}',
                  '{{ addslashes($p->address) }}',
                  '{{ $p->area->name ?? '—' }}',
                  '{{ $p->rider->name ?? 'Unassigned' }}',
                  '{{ $p->rider->initials ?? '—' }}',
                  '{{ $p->status }}',
                  '{{ (int) $p->scanned_undelivered }}',
                  '{{ $p->eta_note ?? '—' }}',
                  {{ $p->id }}
                )"
                class="link-btn" style="border:1px solid var(--line);border-radius:6px;padding:4px 10px;background:#fff;">
                Details
              </button>
            </td>
          </tr>
        @empty
          <tr><td colspan="8" style="text-align:center;color:var(--text-400);padding:24px;">No parcels match this filter.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- ── Parcel detail modal ── --}}
  <div id="parcelModalBackdrop"
       onclick="if(event.target===this)closeParcelModal()"
       style="display:none;position:fixed;inset:0;z-index:50;background:rgba(0,0,0,.45);align-items:center;justify-content:center;">

    <div style="background:#fff;border-radius:16px;width:100%;max-width:460px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.18);animation:slideUp .18s ease-out;">

      {{-- Header --}}
      <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--line);">
        <div>
          <div id="mTrackNo" style="font-size:15px;font-weight:800;font-family:'Poppins',sans-serif;"></div>
          <div style="font-size:11.5px;color:var(--text-600);margin-top:2px;">Parcel details &amp; status update</div>
        </div>
        <button onclick="closeParcelModal()" style="background:none;border:none;cursor:pointer;color:var(--text-600);font-size:18px;line-height:1;padding:4px;">✕</button>
      </div>

      {{-- Body --}}
      <div style="padding:18px 20px;">

        {{-- Info rows --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
          <div style="background:#f7f6fb;border-radius:8px;padding:10px 12px;">
            <div style="font-size:10.5px;color:var(--text-600);font-weight:600;margin-bottom:3px;">DESTINATION</div>
            <div id="mAddr" style="font-size:13px;font-weight:600;"></div>
          </div>
          <div style="background:#f7f6fb;border-radius:8px;padding:10px 12px;">
            <div style="font-size:10.5px;color:var(--text-600);font-weight:600;margin-bottom:3px;">BARANGAY</div>
            <div id="mArea" style="font-size:13px;font-weight:600;"></div>
          </div>
          <div style="background:#f7f6fb;border-radius:8px;padding:10px 12px;">
            <div style="font-size:10.5px;color:var(--text-600);font-weight:600;margin-bottom:3px;">RIDER</div>
            <div id="mRider" style="font-size:13px;font-weight:600;"></div>
          </div>
          <div style="background:#f7f6fb;border-radius:8px;padding:10px 12px;">
            <div style="font-size:10.5px;color:var(--text-600);font-weight:600;margin-bottom:3px;">ETA</div>
            <div id="mEta" style="font-size:13px;font-weight:600;"></div>
          </div>
        </div>

        {{-- Current status --}}
        <div style="margin-bottom:16px;padding:10px 12px;border-radius:8px;border:1px solid var(--line);">
          <div style="font-size:10.5px;color:var(--text-600);font-weight:600;margin-bottom:4px;">CURRENT STATUS</div>
          <div id="mStatusLabel" style="font-size:13px;font-weight:700;"></div>
        </div>

        {{-- Action buttons --}}
        <div id="mActions"></div>

      </div>
    </div>
  </div>

@endsection

@push('scripts')
<style>
@keyframes slideUp {
  from { opacity:0; transform:translateY(12px); }
  to   { opacity:1; transform:translateY(0); }
}
</style>
<script>
let currentParcelId = null;

function openParcelModal(trackNo, addr, area, riderName, riderInit, status, scanned, eta, parcelId) {
  currentParcelId = parcelId;

  document.getElementById('mTrackNo').textContent = trackNo;
  document.getElementById('mAddr').textContent = addr;
  document.getElementById('mArea').textContent = area;
  document.getElementById('mRider').textContent = riderName + ' (' + riderInit + ')';
  document.getElementById('mEta').textContent = eta;

  // Status label
  const statusMap = {
    'assigned':        { label: 'Assigned to rider',          color: '#2A6FBD' },
    'out_for_delivery':{ label: 'Out for delivery',           color: '#b8791a' },
    'delivered':       { label: 'Delivered',                  color: '#2f8f5b' },
    'failed':          { label: scanned == 1 ? 'Confirmed undelivered' : 'Not delivered — needs scan', color: '#c0432f' },
  };
  const s = statusMap[status] || { label: status, color: '#888' };
  document.getElementById('mStatusLabel').style.color = s.color;
  document.getElementById('mStatusLabel').textContent = s.label;

  // Action buttons
  const actions = document.getElementById('mActions');
  actions.innerHTML = '';

  const btn = (label, color, bg, onclick) => {
    const b = document.createElement('button');
    b.textContent = label;
    b.style.cssText = `width:100%;border:none;border-radius:8px;padding:11px;font-size:13px;font-weight:700;font-family:inherit;cursor:pointer;background:${bg};color:${color};margin-bottom:8px;`;
    b.onclick = onclick;
    return b;
  };

  if (status === 'assigned') {
    actions.appendChild(btn('Mark out for delivery →', '#fff', '#2A6FBD', () => submitAction('advance', parcelId)));
  } else if (status === 'out_for_delivery') {
    actions.appendChild(btn('Mark delivered →', '#fff', '#2f8f5b', () => submitAction('advance', parcelId)));
  } else if (status === 'delivered') {
    const note = document.createElement('div');
    note.textContent = 'Awaiting buyer confirmation. No further action needed.';
    note.style.cssText = 'font-size:12.5px;color:var(--text-600);text-align:center;padding:8px 0;';
    actions.appendChild(note);
  } else if (status === 'failed' && scanned == 0) {
    actions.appendChild(btn('Confirm as undelivered (scan) →', '#fff', '#c0432f', () => submitAction('scan-undelivered', parcelId)));
  } else {
    const note = document.createElement('div');
    note.textContent = 'Reschedule pending. No further action from this screen.';
    note.style.cssText = 'font-size:12.5px;color:var(--text-600);text-align:center;padding:8px 0;';
    actions.appendChild(note);
  }

  // Close button
  actions.appendChild(btn('Close', '#1f1b2e', '#f1eff6', closeParcelModal));

  const backdrop = document.getElementById('parcelModalBackdrop');
  backdrop.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function closeParcelModal() {
  document.getElementById('parcelModalBackdrop').style.display = 'none';
  document.body.style.overflow = '';
}

function submitAction(type, parcelId) {
  const routes = {
    'advance':          '/delivery/monitoring/' + parcelId + '/advance',
    'scan-undelivered': '/delivery/monitoring/' + parcelId + '/scan-undelivered',
  };
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = routes[type];
  const csrf = document.createElement('input');
  csrf.type = 'hidden';
  csrf.name = '_token';
  csrf.value = document.querySelector('meta[name=csrf-token]').content;
  form.appendChild(csrf);
  document.body.appendChild(form);
  form.submit();
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeParcelModal(); });
</script>
@endpush
