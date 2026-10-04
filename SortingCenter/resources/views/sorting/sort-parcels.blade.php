@extends('layouts.sorting')

@section('title', 'Sort Parcels')
@section('subtitle', "This hub covers Santa Cruz, Laguna only. Determine each parcel's barangay, then assign it to that barangay's rider.")

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
  .search{display:flex;align-items:center;gap:8px;background:var(--paper);border:1px solid var(--line);border-radius:8px;padding:7px 10px;font-size:12.5px;color:var(--text-600);min-width:220px;}
  .search input{border:none;background:transparent;outline:none;font-size:12.5px;width:100%;font-family:inherit;color:var(--text-900);}
  .addr .city{font-weight:600;font-size:13px;}
  .addr .prov{font-size:11.5px;color:var(--text-600);}
  .badge.pending{background:var(--pend-100);color:var(--pend-600);}
  .area-chip{display:inline-flex;align-items:center;gap:6px;font-family:'IBM Plex Mono',monospace;font-size:11.5px;font-weight:600;padding:4px 9px;border-radius:6px;color:#fff;background:var(--ink-700);}
  .muted-dash{color:var(--text-400);font-size:13px;}
  .carrier-select{font-family:inherit;font-size:11.5px;font-weight:600;border:1px solid var(--line);border-radius:6px;padding:4px 6px;background:#fff;color:var(--text-900);cursor:pointer;}
  .carrier-select.jnt{border-color:rgba(200,30,30,.35);color:#C81E1E;background:#FDEDED;}
  .carrier-select.lbc{border-color:rgba(42,143,189,.35);color:#2A8FBD;background:#EAF5FB;}
  .carrier-select.lalamove{border-color:rgba(124,79,224,.4);color:var(--accent-600);background:#F1EAFE;}
  .rider{display:flex;align-items:center;gap:8px;}
  .rider .ravatar{width:24px;height:24px;border-radius:50%;background:var(--ink-800);color:#fff;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;}
  .rider .rname{font-size:12.5px;font-weight:600;}
  .btn{border:none;border-radius:7px;padding:7px 13px;font-size:12.5px;font-weight:600;cursor:pointer;font-family:inherit;}
  .btn-primary{background:var(--ink-900);color:#fff;}
  .btn-primary:hover{background:var(--ink-800);}
  .btn-accent{background:var(--accent-500);color:var(--ink-900);}
  .btn-accent:hover{background:var(--accent-600);}
  .btn-ghost{background:transparent;color:var(--text-600);border:1px solid var(--line);}
  .panel-foot{padding:12px 18px;font-size:12px;color:var(--text-600);border-top:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;}
  .legend{display:flex;gap:14px;flex-wrap:wrap;}
  .legend span{display:inline-flex;align-items:center;gap:6px;}
  .legend .sw{width:10px;height:10px;border-radius:3px;background:var(--ink-700);}
  .err{background:var(--bad-100);color:var(--bad-600);border:1px solid rgba(192,67,47,.3);border-radius:8px;padding:9px 14px;font-size:12.5px;margin-bottom:16px;}
</style>
@endpush

@section('content')

  <div class="stats">
    <div class="stat-card primary">
      <div class="card-top"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg><div class="k">Awaiting sort</div></div>
      <div class="val-row"><div class="v">{{ $awaitingSort }}</div></div>
      <div class="sub">In queue right now</div>
    </div>
    <div class="stat-card">
      <div class="card-top"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#7c4fe0" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg><div class="k">Barangay determined</div></div>
      <div class="val-row"><div class="v">{{ $areaDetermined }}</div>@if($areaDetermined > 0)<span class="delta-pill up">▲ {{ $areaDetermined }}</span>@endif</div>
      <div class="sub">Area assigned</div>
    </div>
    <div class="stat-card">
      <div class="card-top"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg><div class="k">Assigned to rider</div></div>
      <div class="val-row"><div class="v">{{ $assignedToday }}</div>@if($assignedToday > 0)<span class="delta-pill up">▲ {{ $assignedToday }}</span>@endif</div>
      <div class="sub">Ready to dispatch</div>
    </div>
    <div class="stat-card">
      <div class="card-top"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#b8791a" stroke-width="2"><circle cx="8" cy="8" r="4"/><path d="M2 21v-2a5 5 0 0 1 5-5h2a5 5 0 0 1 5 5v2"/><circle cx="17.5" cy="9.5" r="2.5"/></svg><div class="k">Riders on shift</div></div>
      <div class="val-row"><div class="v">{{ $ridersOnShift }}</div></div>
      <div class="sub">Available today</div>
    </div>
  </div>

  <div class="flow">
    <div class="flow-step done"><div class="dot">✓</div><div class="label">Receive parcel</div></div>
    <div class="flow-sep done"></div>
    <div class="flow-step done"><div class="dot">✓</div><div class="label">Scan parcel</div></div>
    <div class="flow-sep done"></div>
    <div class="flow-step done"><div class="dot">✓</div><div class="label">Read address</div></div>
    <div class="flow-sep {{ $areaDetermined > 0 ? 'done' : '' }}"></div>
    <div class="flow-step {{ $areaDetermined > 0 ? 'done' : '' }}"><div class="dot">✓</div><div class="label">Determine barangay</div></div>
    <div class="flow-sep {{ $areaDetermined > 0 ? 'done' : '' }}"></div>
    <div class="flow-step {{ $areaDetermined > 0 ? 'done' : '' }}"><div class="dot">✓</div><div class="label">Sort by barangay</div></div>
    <div class="flow-sep {{ $assignedToday > 0 ? 'done' : '' }}"></div>
    <div class="flow-step {{ $assignedToday > 0 ? 'done' : '' }}"><div class="dot">✓</div><div class="label">Identify rider</div></div>
    <div class="flow-sep {{ $assignedToday > 0 ? 'done' : '' }}"></div>
    <div class="flow-step {{ $assignedToday > 0 ? 'done' : '' }}"><div class="dot">✓</div><div class="label">Assign parcel</div></div>
  </div>

  @if ($errors->any())
    <div class="err">{{ $errors->first() }}</div>
  @elseif (session('status'))
    <div class="err" style="background:#EFEAFB;color:var(--accent-600);border-color:rgba(124,79,224,.4);">ⓘ {{ session('status') }}</div>
  @endif

  <div class="panel">
    <div class="panel-head">
      <div><h2>Parcel queue</h2><div class="sub">Newly arrived parcels at Santa Cruz Sorting Hub, oldest first</div></div>
      <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        <form method="GET" style="display:contents;">
          <input type="hidden" name="q" value="{{ $q }}">
          @include('partials.barangay-dropdown')
        </form>
        <form method="GET" class="search">
          <input type="hidden" name="area_id" value="{{ $area_id }}">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          <input type="text" name="q" value="{{ $q }}" placeholder="Search parcel ID or address…" onchange="this.form.submit()">
        </form>
      </div>
    </div>

    <table style="width:100%;table-layout:fixed;">
      <thead>
        <tr style="text-align:center;">
          <th style="width:15%;text-align:center;">Parcel</th>
          <th style="width:22%;text-align:center;">Delivery address</th>
          <th style="width:14%;text-align:center;">Barangay</th>
          <th style="width:18%;text-align:center;">Assigned rider</th>
          <th style="width:16%;text-align:center;">Status</th>
          <th style="width:15%;text-align:center;">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($parcels as $p)
          <tr style="text-align:center;">
            <td class="pid" style="text-align:center;">{{ $p->tracking_no }}</td>
            <td class="addr" style="text-align:center;"><div class="city">{{ $p->address }}</div><div class="prov">Santa Cruz, Laguna</div></td>
            <td style="text-align:center;">
              @if ($p->area)
                <span class="area-chip">{{ $p->area->name }}</span>
              @else
                <span class="muted-dash">— pending —</span>
              @endif
            </td>
            <td style="text-align:center;">
              @if ($p->rider)
                <div class="rider" style="justify-content:center;"><div class="ravatar">{{ $p->rider->initials }}</div><div class="rname">{{ $p->rider->name }}</div></div>
              @else
                <span class="muted-dash">Not assigned</span>
              @endif
            </td>
            <td style="text-align:center;">
              @if ($p->status === 'awaiting_sort')
                <span class="badge pending"><span class="dotb"></span>Awaiting sort</span>
              @elseif ($p->status === 'sorted')
                <span class="badge pending"><span class="dotb"></span>Sorted, needs rider</span>
              @else
                <span class="badge ok"><span class="dotb"></span>Assigned to rider</span>
              @endif
            </td>
            <td style="text-align:center;">
              <button
                onclick="openSortModal(
                  {{ $p->id }},
                  '{{ $p->tracking_no }}',
                  '{{ addslashes($p->address) }}',
                  '{{ $p->area->name ?? '' }}',
                  '{{ $p->rider->name ?? '' }}',
                  '{{ $p->carrier }}',
                  '{{ $p->status }}'
                )"
                style="border:1px solid var(--line);border-radius:6px;padding:4px 10px;background:#fff;font-size:12px;font-weight:600;color:var(--ink-700);cursor:pointer;font-family:inherit;">
                Details
              </button>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" style="text-align:center;color:var(--text-400);padding:24px;">Nothing waiting to be sorted right now.</td></tr>
        @endforelse
      </tbody>
    </table>

    <div class="panel-foot">
      <div class="legend">
        @foreach ($areas as $area)
          <span><span class="sw" style="background:{{ $area->chart_color }}"></span>{{ $area->name }}{{ $area->rider ? ' — '.$area->rider->name : '' }}</span>
        @endforeach
      </div>
      <div>{{ $parcels->count() }} parcels in queue</div>
    </div>
  </div>

  {{-- ── Sort parcel modal ── --}}
  <div id="sortModalBackdrop"
       onclick="if(event.target===this)closeSortModal()"
       style="display:none;position:fixed;inset:0;z-index:50;background:rgba(0,0,0,.45);align-items:center;justify-content:center;">

    <div style="background:#fff;border-radius:16px;width:100%;max-width:460px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.18);animation:sortSlideUp .18s ease-out;">

      {{-- Header --}}
      <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--line);">
        <div>
          <div id="smParcelNo" style="font-size:15px;font-weight:800;font-family:'Poppins',sans-serif;"></div>
          <div style="font-size:11.5px;color:var(--text-600);margin-top:2px;">Parcel sorting details</div>
        </div>
        <button onclick="closeSortModal()" style="background:none;border:none;cursor:pointer;color:var(--text-600);font-size:18px;line-height:1;padding:4px;">✕</button>
      </div>

      {{-- Body --}}
      <div style="padding:18px 20px;">

        {{-- Info tiles --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px;">
          <div style="background:#f7f6fb;border-radius:8px;padding:10px 12px;grid-column:span 2;">
            <div style="font-size:10.5px;color:var(--text-600);font-weight:600;margin-bottom:3px;">DELIVERY ADDRESS</div>
            <div id="smAddress" style="font-size:13px;font-weight:600;"></div>
          </div>
          <div style="background:#f7f6fb;border-radius:8px;padding:10px 12px;">
            <div style="font-size:10.5px;color:var(--text-600);font-weight:600;margin-bottom:3px;">BARANGAY</div>
            <div id="smAreaName" style="font-size:13px;font-weight:600;"></div>
          </div>
          <div style="background:#f7f6fb;border-radius:8px;padding:10px 12px;">
            <div style="font-size:10.5px;color:var(--text-600);font-weight:600;margin-bottom:3px;">RIDER</div>
            <div id="smRiderName" style="font-size:13px;font-weight:600;"></div>
          </div>
        </div>

        {{-- Carrier selector --}}
        <div style="margin-bottom:16px;">
          <div style="font-size:11px;font-weight:700;color:var(--text-600);margin-bottom:6px;text-transform:uppercase;letter-spacing:.04em;">Carrier</div>
          <div style="display:flex;gap:8px;">
            @foreach(['jnt' => 'J&T Express', 'lbc' => 'LBC', 'lalamove' => 'Lalamove'] as $val => $lbl)
              <button id="carrier-{{ $val }}"
                onclick="selectCarrier('{{ $val }}')"
                style="flex:1;border-radius:8px;padding:8px 4px;font-size:12px;font-weight:700;font-family:inherit;cursor:pointer;border:1.5px solid var(--line);background:#fff;transition:all .15s;">
                {{ $lbl }}
              </button>
            @endforeach
          </div>
        </div>

        {{-- Action button --}}
        <div id="smActionWrap" style="margin-bottom:8px;"></div>

        <button onclick="closeSortModal()"
          style="width:100%;border:1px solid var(--line);border-radius:8px;padding:11px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;background:#fff;color:var(--text-900);">
          Close
        </button>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
<style>
@keyframes sortSlideUp {
  from { opacity:0; transform:translateY(12px); }
  to   { opacity:1; transform:translateY(0); }
}
</style>
<script>
let smCurrentId    = null;
let smCurrentCarrier = 'jnt';
let smCurrentStatus  = null;

const carrierColors = {
  jnt:      { bg:'#fdeded', color:'#c81e1e', border:'#f5b8b8' },
  lbc:      { bg:'#eaf4fb', color:'#2a6fbd', border:'#a8d4ef' },
  lalamove: { bg:'#f1eafe', color:'#7c4fe0', border:'#c9b0f5' },
};

function openSortModal(id, trackNo, addr, area, rider, carrier, status) {
  smCurrentId      = id;
  smCurrentStatus  = status;
  smCurrentCarrier = carrier || 'jnt';

  document.getElementById('smParcelNo').textContent   = trackNo;
  document.getElementById('smAddress').textContent    = addr;
  document.getElementById('smAreaName').textContent   = area  || '— pending —';
  document.getElementById('smRiderName').textContent  = rider || 'Not assigned';

  selectCarrier(smCurrentCarrier);
  renderAction(status);

  const backdrop = document.getElementById('sortModalBackdrop');
  backdrop.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function selectCarrier(val) {
  smCurrentCarrier = val;
  ['jnt','lbc','lalamove'].forEach(c => {
    const btn = document.getElementById('carrier-' + c);
    if (c === val) {
      const cc = carrierColors[c];
      btn.style.background   = cc.bg;
      btn.style.color        = cc.color;
      btn.style.borderColor  = cc.border;
    } else {
      btn.style.background  = '#fff';
      btn.style.color       = 'var(--text-900)';
      btn.style.borderColor = 'var(--line)';
    }
  });
}

function renderAction(status) {
  const wrap = document.getElementById('smActionWrap');
  wrap.innerHTML = '';

  const mkBtn = (label, bg, color, fn) => {
    const b = document.createElement('button');
    b.textContent = label;
    b.style.cssText = `width:100%;border:none;border-radius:8px;padding:11px;font-size:13px;font-weight:700;font-family:inherit;cursor:pointer;background:${bg};color:${color};`;
    b.onclick = fn;
    wrap.appendChild(b);
  };

  if (status === 'awaiting_sort') {
    mkBtn('Determine barangay area →', '#4B2E7E', '#fff', () => {
      saveCarrierThenSubmit('determine-area');
    });
  } else if (status === 'sorted') {
    mkBtn('Identify & assign rider →', '#7c4fe0', '#fff', () => {
      saveCarrierThenSubmit('assign-rider');
    });
  } else {
    const note = document.createElement('div');
    note.textContent = 'This parcel is already assigned. No further action needed.';
    note.style.cssText = 'font-size:12.5px;color:var(--text-600);text-align:center;padding:8px 0;';
    wrap.appendChild(note);
  }
}

function saveCarrierThenSubmit(action) {
  // Save carrier first if changed, then submit the main action
  const carrierForm = document.createElement('form');
  carrierForm.method = 'POST';
  carrierForm.action = '/parcels/sort/' + smCurrentId + '/carrier';
  carrierForm.style.display = 'none';

  const csrf1 = document.createElement('input');
  csrf1.type = 'hidden'; csrf1.name = '_token';
  csrf1.value = document.querySelector('meta[name=csrf-token]').content;

  const carrierInput = document.createElement('input');
  carrierInput.type = 'hidden'; carrierInput.name = 'carrier';
  carrierInput.value = smCurrentCarrier;

  // After carrier save we need to also trigger the action — use a redirect via hidden next_action field
  // Simpler: just submit the action directly (carrier already set or will update on next visit)
  const actionForm = document.createElement('form');
  actionForm.method = 'POST';
  actionForm.action = '/parcels/sort/' + smCurrentId + '/' + action;

  const csrf2 = document.createElement('input');
  csrf2.type = 'hidden'; csrf2.name = '_token';
  csrf2.value = document.querySelector('meta[name=csrf-token]').content;

  actionForm.appendChild(csrf2);
  document.body.appendChild(actionForm);
  actionForm.submit();
}

function closeSortModal() {
  document.getElementById('sortModalBackdrop').style.display = 'none';
  document.body.style.overflow = '';
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSortModal(); });
</script>
@endpush
