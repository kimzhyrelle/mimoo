@extends('layouts.sorting')

@section('title', 'Rider Management')
@section('subtitle', 'Approve rider applications and lock each barangay in Santa Cruz to exactly one rider.')

@push('styles')
@include('partials.legacy-styles')
<style>
  .stat-card.accent{border-color:rgba(124,79,224,.5);background:linear-gradient(180deg,#FAF7FF,#fff);}
  .note{background:#EFEAFB;color:var(--accent-600);border:1px solid rgba(124,79,224,.4);border-radius:8px;padding:10px 14px;font-size:12px;margin-bottom:16px;display:flex;align-items:center;gap:8px;}
  .search{display:flex;align-items:center;gap:8px;background:var(--paper);border:1px solid var(--line);border-radius:8px;padding:7px 10px;font-size:12.5px;color:var(--text-600);min-width:200px;}
  .search input{border:none;background:transparent;outline:none;font-size:12.5px;width:100%;font-family:inherit;color:var(--text-900);}
  .who-cell{display:flex;align-items:center;gap:10px;}
  .ravatar{width:30px;height:30px;border-radius:50%;background:var(--ink-800);color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex:0 0 auto;}
  .who-cell .rname{font-size:13px;font-weight:600;}
  .who-cell .rmeta{font-size:11.5px;color:var(--text-600);}
  .badge{display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:600;padding:4px 9px;border-radius:20px;white-space:nowrap;}
  .badge.ok{background:var(--ok-100);color:var(--ok-600);}
  .badge.off{background:#F1EFF6;color:var(--text-400);}
  .brgy-select{font-family:inherit;font-size:12px;font-weight:600;border:1px solid var(--line);border-radius:6px;padding:5px 8px;background:#fff;color:var(--text-900);cursor:pointer;min-width:150px;}
  .vehicle-chip{display:inline-flex;align-items:center;gap:6px;font-size:11.5px;color:var(--text-600);}
  .btn{border:none;border-radius:7px;padding:7px 13px;font-size:12.5px;font-weight:600;cursor:pointer;font-family:inherit;}
  .btn-ok{background:var(--ok-600);color:#fff;}
  .btn-ok:hover{background:#26794C;}
  .btn-bad{background:transparent;color:var(--bad-600);border:1px solid rgba(192,67,47,.35);}
  .btn-bad:hover{background:var(--bad-100);}
  .btn-row{display:flex;gap:8px;justify-content:flex-end;}
  .toggle{position:relative;width:38px;height:21px;border-radius:20px;background:var(--line);cursor:pointer;flex:0 0 auto;border:none;padding:0;}
  .toggle.on{background:var(--ok-600);}
  .toggle .knob{position:absolute;top:2px;left:2px;width:17px;height:17px;border-radius:50%;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.2);}
  .toggle.on .knob{left:19px;}
</style>
@endpush

@section('content')

  <div class="stats">
    <div class="stat-card primary">
      <div class="card-top"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg><div class="k">Barangays covered</div></div>
      <div class="val-row"><div class="v">{{ $covered }} / {{ $areas->count() }}</div></div>
      <div class="sub">Active routes</div>
    </div>
    <div class="stat-card">
      <div class="card-top"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#b8791a" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg><div class="k">Pending applications</div></div>
      <div class="val-row"><div class="v">{{ $pending->count() }}</div>@if($pending->count() > 0)<span class="delta-pill down">▼ Needs review</span>@endif</div>
      <div class="sub">Awaiting approval</div>
    </div>
    <div class="stat-card">
      <div class="card-top"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg><div class="k">On shift now</div></div>
      <div class="val-row"><div class="v">{{ $onShift }}</div>@if($onShift > 0)<span class="delta-pill up">▲ Active</span>@endif</div>
      <div class="sub">Currently riding</div>
    </div>
    <div class="stat-card">
      <div class="card-top"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><div class="k">Unassigned barangays</div></div>
      <div class="val-row"><div class="v">{{ $unassigned }}</div>@if($unassigned > 0)<span class="delta-pill down">▼ No rider</span>@endif</div>
      <div class="sub">Need a rider</div>
    </div>
  </div>

  <div class="note">ⓘ Each barangay in Santa Cruz can only have <b>one</b> assigned rider. Reassigning a barangay to a new rider automatically frees it from the previous one.</div>

  <div class="panel">
    <div class="panel-head">
      <div><h2>Pending applications</h2><div class="sub">New courier sign-ups awaiting review</div></div>
    </div>
    <table style="width:100%;table-layout:fixed;">
      <thead><tr style="text-align:center;">
        <th style="width:28%;text-align:center;">Applicant</th>
        <th style="width:22%;text-align:center;">Vehicle</th>
        <th style="width:18%;text-align:center;">Documents</th>
        <th style="width:14%;text-align:center;">Applied</th>
        <th style="width:18%;text-align:center;">Action</th>
      </tr></thead>
      <tbody>
        @forelse ($pending as $p)
          <tr style="text-align:center;">
            <td style="text-align:center;"><div class="who-cell" style="justify-content:center;"><div class="ravatar">{{ collect(explode(' ', $p->name))->map(fn($w)=>$w[0])->implode('') }}</div><div><div class="rname">{{ $p->name }}</div><div class="rmeta">Courier applicant</div></div></div></td>
            <td class="vehicle-chip" style="text-align:center;">{{ $p->vehicle_type }}{{ $p->plate_no ? ' · '.$p->plate_no : '' }}</td>
            <td class="vehicle-chip" style="text-align:center;">{{ $p->docsLabel() }}</td>
            <td class="vehicle-chip" style="text-align:center;">{{ $p->applied_on->format('M j') }}</td>
            <td style="text-align:center;">
              <div class="btn-row" style="justify-content:center;">
                <form method="POST" action="{{ route('riders.applications.approve', $p) }}">@csrf<button class="btn btn-ok">Approve</button></form>
                <form method="POST" action="{{ route('riders.applications.disapprove', $p) }}">@csrf<button class="btn btn-bad">Disapprove</button></form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" style="text-align:center;color:var(--text-400);padding:24px;">No pending applications.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="panel">
    <div class="panel-head">
      <div><h2>Active riders — barangay assignment</h2><div class="sub">One rider per barangay, per the sorting center's coverage area</div></div>
      <form method="GET" class="search">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" name="q" value="{{ $q }}" placeholder="Search rider…" onchange="this.form.submit()">
      </form>
    </div>
    <table style="width:100%;table-layout:fixed;">
      <thead><tr style="text-align:center;">
        <th style="width:24%;text-align:center;">Rider</th>
        <th style="width:20%;text-align:center;">Vehicle</th>
        <th style="width:20%;text-align:center;">Assigned barangay</th>
        <th style="width:16%;text-align:center;">Status</th>
        <th style="width:20%;text-align:center;">Action</th>
      </tr></thead>
      <tbody>
        @forelse ($riders as $r)
          <tr style="text-align:center;">
            <td style="text-align:center;"><div class="who-cell" style="justify-content:center;"><div class="ravatar">{{ $r->initials }}</div><div><div class="rname">{{ $r->name }}</div><div class="rmeta">{{ $r->initials }}</div></div></div></td>
            <td class="vehicle-chip" style="text-align:center;">{{ $r->vehicleLabel() ?: '—' }}</td>
            <td style="text-align:center;">
              @if ($r->area)
                <span style="display:inline-flex;align-items:center;gap:5px;font-size:12.5px;font-weight:600;color:var(--text-900);">
                  <span style="width:8px;height:8px;border-radius:50%;background:{{ $r->area->chart_color ?? '#2A1B4D' }};flex-shrink:0;"></span>
                  {{ $r->area->name }}
                </span>
              @else
                <span style="color:var(--text-400);font-size:12.5px;">— Unassigned —</span>
              @endif
            </td>
            <td style="text-align:center;">
              @if ($r->isActive())
                <span class="badge ok"><span class="dotb"></span>On shift</span>
              @else
                <span class="badge off"><span class="dotb"></span>Off shift</span>
              @endif
            </td>
            <td style="text-align:center;">
              <button
                onclick="openRiderManageModal(
                  {{ $r->id }},
                  '{{ $r->name }}',
                  '{{ $r->initials }}',
                  '{{ addslashes($r->vehicleLabel() ?: '—') }}',
                  {{ $r->area_id ?? 'null' }},
                  '{{ $r->isActive() ? '1' : '0' }}'
                )"
                style="border:1px solid var(--line);border-radius:6px;padding:4px 12px;background:#fff;font-size:12px;font-weight:600;color:var(--ink-700);cursor:pointer;font-family:inherit;">
                Manage
              </button>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" style="text-align:center;color:var(--text-400);padding:24px;">No riders yet — approve an application above.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- ── Rider Manage Modal ── --}}
  <div id="riderManageBackdrop"
       onclick="if(event.target===this)closeRiderManageModal()"
       style="display:none;position:fixed;inset:0;z-index:50;background:rgba(0,0,0,.45);align-items:center;justify-content:center;">

    <div style="background:#fff;border-radius:16px;width:100%;max-width:460px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.18);animation:rmSlideUp .18s ease-out;">

      {{-- Header --}}
      <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--line);">
        <div style="display:flex;align-items:center;gap:10px;">
          <div id="rmAvatar" style="width:38px;height:38px;border-radius:50%;background:#2A1B4D;color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;"></div>
          <div>
            <div id="rmName" style="font-size:15px;font-weight:800;font-family:'Poppins',sans-serif;"></div>
            <div id="rmVehicle" style="font-size:12px;color:var(--text-600);margin-top:1px;"></div>
          </div>
        </div>
        <button onclick="closeRiderManageModal()" style="background:none;border:none;cursor:pointer;color:var(--text-600);font-size:18px;line-height:1;padding:4px;">✕</button>
      </div>

      {{-- Body --}}
      <div style="padding:20px;">

        {{-- Current status --}}
        <div id="rmStatusWrap" style="display:flex;align-items:center;justify-content:space-between;background:#f7f6fb;border-radius:10px;padding:12px 16px;margin-bottom:16px;">
          <div>
            <div style="font-size:10.5px;font-weight:700;color:var(--text-600);text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;">Shift status</div>
            <div id="rmStatusLabel" style="font-size:13px;font-weight:700;"></div>
          </div>
          {{-- Toggle --}}
          <form id="rmToggleForm" method="POST">
            @csrf
            <button type="submit" id="rmToggleBtn"
              style="position:relative;width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;transition:background .2s;flex-shrink:0;">
              <span id="rmToggleKnob" style="position:absolute;top:2px;width:20px;height:20px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:left .2s;"></span>
            </button>
          </form>
        </div>

        {{-- Barangay assignment --}}
        <div style="margin-bottom:8px;">
          <div style="font-size:11px;font-weight:700;color:var(--text-600);text-transform:uppercase;letter-spacing:.04em;margin-bottom:8px;">Assigned barangay</div>
          <form id="rmBarangayForm" method="POST">
            @csrf
            <div style="display:grid;grid-template-columns:1fr auto;gap:8px;align-items:center;">
              <select id="rmAreaSelect" name="area_id"
                style="border:1.5px solid var(--line);border-radius:8px;padding:9px 12px;font-size:13px;font-family:inherit;font-weight:600;background:#fff;color:var(--text-900);cursor:pointer;outline:none;width:100%;">
                <option value="">— Unassigned —</option>
                @foreach ($areas as $area)
                  <option value="{{ $area->id }}"
                    data-color="{{ $area->chart_color ?? '#2A1B4D' }}"
                    data-taken="{{ $area->rider?->id }}">
                    {{ $area->name }}{{ $area->rider ? ' (taken by '.$area->rider->initials.')' : '' }}
                  </option>
                @endforeach
              </select>
              <button type="submit"
                style="border:none;border-radius:8px;padding:9px 16px;font-size:12.5px;font-weight:700;font-family:inherit;cursor:pointer;background:#4B2E7E;color:#fff;white-space:nowrap;">
                Save
              </button>
            </div>
          </form>
        </div>

        {{-- Warning if barangay taken --}}
        <div id="rmTakenWarn" style="display:none;margin-top:8px;padding:8px 12px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;font-size:12px;color:#92400e;">
          ⚠ This barangay is currently assigned to another rider. Saving will reassign it.
        </div>

      </div>

      {{-- Footer --}}
      <div style="padding:12px 20px;border-top:1px solid var(--line);display:flex;justify-content:flex-end;">
        <button onclick="closeRiderManageModal()"
          style="border:1px solid var(--line);border-radius:8px;padding:8px 18px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;background:#fff;color:var(--text-900);">
          Close
        </button>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
<style>
@keyframes rmSlideUp {
  from { opacity:0; transform:translateY(12px); }
  to   { opacity:1; transform:translateY(0); }
}
</style>
<script>
let rmCurrentRiderId = null;

function openRiderManageModal(id, name, initials, vehicle, areaId, isActive) {
  rmCurrentRiderId = id;

  document.getElementById('rmAvatar').textContent    = initials;
  document.getElementById('rmName').textContent      = name;
  document.getElementById('rmVehicle').textContent   = vehicle;

  // Toggle form action
  document.getElementById('rmToggleForm').action = '/riders/' + id + '/toggle-active';

  // Toggle state
  const active = isActive === '1';
  const btn    = document.getElementById('rmToggleBtn');
  const knob   = document.getElementById('rmToggleKnob');
  const lbl    = document.getElementById('rmStatusLabel');
  btn.style.background  = active ? '#16a34a' : '#d1d5db';
  knob.style.left       = active ? '22px' : '2px';
  lbl.textContent       = active ? 'On shift' : 'Off shift';
  lbl.style.color       = active ? '#16a34a' : '#9a93ac';

  // Barangay form action
  document.getElementById('rmBarangayForm').action = '/riders/' + id + '/barangay';

  // Pre-select current area
  const sel = document.getElementById('rmAreaSelect');
  sel.value = areaId ?? '';
  checkTaken();

  document.getElementById('riderManageBackdrop').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function closeRiderManageModal() {
  document.getElementById('riderManageBackdrop').style.display = 'none';
  document.body.style.overflow = '';
}

function checkTaken() {
  const sel     = document.getElementById('rmAreaSelect');
  const opt     = sel.options[sel.selectedIndex];
  const takenBy = opt ? opt.dataset.taken : null;
  const warn    = document.getElementById('rmTakenWarn');
  // Show warning if the area has a rider AND it's not the current rider
  warn.style.display = (takenBy && takenBy !== String(rmCurrentRiderId)) ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('rmAreaSelect').addEventListener('change', checkTaken);
});

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeRiderManageModal(); });
</script>
@endpush
