@extends('layouts.sorting')

@section('title', 'Sort & Assign')

@php
  $isPreview    = request()->routeIs('preview.*');
  $clusters     = $clusters     ?? collect();
  $clusterSort  = $clusterSort  ?? 'heaviest';
  $riders       = $riders       ?? collect();
  $stagedParcels = $stagedParcels ?? 0;
  $queueVolume  = $queueVolume ?? (($awaitingSort ?? 0) + $clusters->sum('pending'));
  $idleRiders   = $idleRiders ?? $riders->where('status', 'available')->count();
  $busyRiders   = $busyRiders ?? $riders->where('status', 'busy')->count();
  $assignedClusters = $clusters->filter(fn ($c) => $c->area->rider && $c->pending === 0)->count();
  $carriers     = ['jnt' => 'J&T Express', 'lbc' => 'LBC', 'lalamove' => 'Lalamove'];
  $viewMode     = request('view', 'table'); // 'table' or 'cards'
  $showUnassigned = request()->boolean('unassigned');
  $displayedClusters = $showUnassigned ? $clusters->filter(fn ($c) => !$c->area->rider) : $clusters;
  $riderLoads = $clusters->mapWithKeys(fn ($c) => $c->area->rider ? [$c->area->rider->id => $c->count] : []);
  $matchableAreas = $areas->sortByDesc(fn ($a) => strlen($a->name))->values();
  $matchedAreas = $parcels->mapWithKeys(function ($p) use ($matchableAreas) {
      if ($p->area) {
          return [$p->id => $p->area];
      }

      $haystack = strtolower((string) $p->address);
      $area = $matchableAreas->first(fn ($a) => str_contains($haystack, strtolower(str_replace('Brgy. ', '', $a->name))));

      return [$p->id => $area];
  });
@endphp

@section('content')

  <x-page-head eyebrow="Stage 03 • Hub Allocation">
    @unless ($isPreview)
      <form method="POST" action="{{ route('delivery.assignment.dispatch-list.generate') }}" id="dispatchListForm">@csrf</form>
    @endunless
    @if ($isPreview)
      <button type="button" onclick="window.print()" class="btn-ink px-5"><x-icon name="printer" :size="15" />Generate Dispatch List</button>
    @else
      <button type="button" onclick="openConfirmModal('confirmDispatch')" class="btn-ink px-5"><x-icon name="printer" :size="15" />Generate Dispatch List</button>
    @endif
  </x-page-head>

  {{-- Confirm: Generate Dispatch List --}}
  @unless ($isPreview)
    <x-confirm-modal id="confirmDispatch" title="Generate Dispatch List?" confirmLabel="Generate">
      <ul class="confirm-summary-list">
        <li>Saves a dispatch manifest for parcels assigned to active riders</li>
        <li>Includes parcel IDs, barangays, rider assignments, and measured weights</li>
      </ul>
    </x-confirm-modal>
  @endunless

  @include('partials.flash')

  {{-- ── KPIs ── --}}
  <section class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
    <x-stat label="Queue Volume" :value="$queueVolume" icon="box">
      <span class="flex items-center gap-1.5 text-[12px] text-muted-foreground"><span class="size-1.5 rounded-full bg-primary"></span>{{ $clusters->where('pending', '>', 0)->count() }} clusters pending</span>
    </x-stat>
    <x-stat label="Fleet Deployment" :value="($ridersOnShift ?? 0)" icon="bike">
      <span class="flex items-center gap-1.5 text-[12px] text-muted-foreground"><span class="size-1.5 rounded-full bg-primary"></span>{{ $idleRiders }} Idle • {{ $busyRiders }} In-Route</span>
    </x-stat>
    <x-stat label="Today's Progress" :value="($assignedToday ?? 0)" icon="check-circle">
      <span class="flex items-center gap-1.5 text-[12px] text-muted-foreground"><span class="size-1.5 rounded-full bg-muted-foreground"></span>runs assigned today</span>
    </x-stat>
  </section>

  {{-- ── Destination clusters header + controls ── --}}
  <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-3">
      <h2 class="h-display text-[19px]">Destination Clusters</h2>
      <span class="text-[12px] text-muted-foreground">{{ $clusters->count() }} routes</span>
    </div>
    <div class="flex flex-wrap items-center gap-2.5">
      {{-- Unassigned only filter --}}
      <a href="{{ request()->fullUrlWithQuery(['unassigned' => $showUnassigned ? null : '1']) }}"
         class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-[12.5px] font-semibold transition
                {{ $showUnassigned ? 'bg-primary text-white' : 'bg-secondary text-muted-foreground hover:bg-card' }}">
        <x-icon name="alert" :size="13" />Unassigned only
      </a>

      {{-- Segmented sort control --}}
      <div class="seg-control" role="group" aria-label="Sort clusters by">
        <a href="{{ request()->fullUrlWithQuery(['cluster_sort' => 'heaviest']) }}"
           class="{{ $clusterSort === 'heaviest' ? 'seg-active' : 'seg-inactive' }}">Heaviest First</a>
        <a href="{{ request()->fullUrlWithQuery(['cluster_sort' => 'oldest']) }}"
           class="{{ $clusterSort === 'oldest' ? 'seg-active' : 'seg-inactive' }}">Oldest First</a>
      </div>

      {{-- Card / Table toggle --}}
      <div class="seg-control" role="group" aria-label="View mode">
        <a href="{{ request()->fullUrlWithQuery(['view' => 'cards']) }}"
           class="{{ $viewMode === 'cards' ? 'seg-active' : 'seg-inactive' }}" title="Card view">
          <x-icon name="dashboard" :size="14" />
        </a>
        <a href="{{ request()->fullUrlWithQuery(['view' => 'table']) }}"
           class="{{ $viewMode === 'table' ? 'seg-active' : 'seg-inactive' }}" title="Table view">
          <x-icon name="checklist" :size="14" />
        </a>
      </div>
    </div>
  </div>

  {{-- ── Clusters: TABLE view ── --}}
  @if ($viewMode === 'table')
    <section class="card mb-6 overflow-hidden">
      @if ($displayedClusters->count() > 6)
        <div class="flex justify-end px-4 pt-3">
          <button type="button" class="btn-ghost px-3 py-1.5 text-[12px]"
                  data-table-expand aria-controls="clusterTableViewport" aria-expanded="false">
            Show all rows
          </button>
        </div>
      @endif
      <div id="clusterTableViewport" class="table-scroll-region">
        <table class="tbl min-w-[640px]" data-row-details="off">
          <thead>
            <tr>
              <th>Barangay</th>
              <th class="text-right">Parcels</th>
              <th>Weight</th>
              <th>Rider</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($displayedClusters as $c)
              @php
                $area  = $c->area;
                $rider = $area->rider;
              @endphp
              <tr class="h-11">
                <td>
                  <span class="flex items-center gap-2">
                    <span class="size-2.5 rounded-full shrink-0" style="background:{{ $area->chart_color ?? '#1d4ed8' }}" aria-hidden="true"></span>
                    <span class="font-bold">{{ $area->name }}</span>
                  </span>
                </td>
                <td class="text-right tabular-nums">
                  <span class="h-display block text-[16px]">{{ $c->pending }}</span>
                  <span class="block text-[10.5px] text-muted-foreground">
                    {{ $c->pending === 1 ? 'parcel' : 'parcels' }}
                  </span>
                </td>
                <td>
                  @if ($c->weight > 0)
                    <span class="font-semibold">{{ number_format($c->weight, 1) }} kg</span>
                  @else
                    <span class="text-muted-foreground">—</span>
                  @endif
                </td>
                <td>
                  @if ($rider)
                    <span class="inline-flex items-center gap-2">
                      <span class="avatar size-7 text-[10px]">{{ $rider->initials }}</span>
                      <span>
                        <span class="block font-semibold leading-tight">{{ $rider->name }}</span>
                        <span class="block text-[10.5px] text-muted-foreground">{{ $riderLoads[$rider->id] ?? 0 }} parcel{{ ($riderLoads[$rider->id] ?? 0) === 1 ? '' : 's' }}</span>
                      </span>
                    </span>
                  @else
                    <span class="text-muted-foreground">—</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="py-8 text-center text-muted-foreground">No destination clusters yet. Sort parcels into barangays below.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>

  {{-- ── Clusters: CARD view ── --}}
  @else
    <section class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
      @forelse ($displayedClusters as $c)
        @php
          $area  = $c->area;
          $rider = $area->rider;
          $riderActive = $rider?->isActive() ?? false;
        @endphp
        <article class="card p-5">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-semibold text-muted-foreground"><span class="text-foreground">{{ $hubProfile->name ?? 'Hub' }}</span> › Sta. Cruz</span>
          </div>
          <div class="mt-2 flex items-center justify-between gap-2">
            <h3 class="h-display truncate text-[20px]">{{ $area->name }}</h3>
            <span class="shrink-0 text-[12px] text-muted-foreground">{{ $c->count }} parcels</span>
          </div>

          <div class="tile mt-4 flex items-center justify-between rounded-2xl">
            <div>
              <div class="label">Gross Weight</div>
              @if ($c->weight > 0)
                <div class="h-display text-[20px]">{{ number_format($c->weight, 1) }} kg</div>
              @else
                <div class="text-[13px] text-muted-foreground">—
                  <a href="{{ $isPreview ? '#' : route('reports.index') }}" class="ml-1 text-[11px] font-bold text-primary hover:underline">Weigh</a>
                </div>
              @endif
            </div>
            <div class="flex items-center gap-2.5">
              <div class="text-right"><div class="label">Manifest QR</div><div class="font-mono text-[11px] font-bold">SC-{{ $area->code }}</div></div>
              <span class="rounded-lg bg-card p-1.5"><x-qr :seed="'SC-'.$area->code" :size="26" class="text-ink" /></span>
            </div>
          </div>

          <div class="label mt-4 mb-1.5">Assigned Courier</div>
          <div class="relative">
            @if ($isPreview)
              <div class="field flex items-center justify-between">{{ $rider ? $rider->name : 'Select Courier…' }}<x-icon name="chevron-down" :size="15" /></div>
            @else
              <select class="field cursor-pointer appearance-none pr-9 {{ $rider ? 'bg-accent text-accent-foreground' : '' }}"
                      data-area="{{ $area->id }}" data-current="{{ $rider->id ?? '' }}"
                      onchange="assignCourier(this)">
                <option value="">Select Courier…</option>
                @foreach ($riders as $r)
                  @php
                    $disabled = $r->status === 'offline';
                    $label = $r->initials.' – '.$r->name.' – '.($riderLoads[$r->id] ?? 0).' parcels';
                  @endphp
                  <option value="{{ $r->id }}"
                          {{ $rider && $rider->id === $r->id ? 'selected' : '' }}
                          {{ $disabled ? 'disabled' : '' }}>
                    {{ $label }}{{ $r->area_id && $r->area_id !== $area->id ? ' (on '.($r->area->name ?? 'route').')' : '' }}{{ $disabled ? ' — Off shift' : '' }}
                  </option>
                @endforeach
              </select>
              <x-icon name="chevron-down" :size="15" class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-muted-foreground" />
            @endif
          </div>

          @if ($rider && $riderActive && $c->pending > 0 && !$isPreview)
            <form method="POST" action="{{ route('delivery.assignment.dispatch-area', $area) }}" class="mt-3">@csrf
              <button class="btn-ink w-full py-2.5"><x-icon name="send" :size="14" />Dispatch {{ $c->pending }} to {{ $rider->name }}</button>
            </form>
          @endif
        </article>
      @empty
        <div class="card col-span-full p-10 text-center text-[13.5px] text-muted-foreground">
          No destination clusters yet. Sort parcels into barangays below.
        </div>
      @endforelse
    </section>
  @endif

  {{-- ── Staging bay bar ── --}}
  <section class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-ink p-5 text-ink-foreground">
    <div class="flex items-center gap-4">
      <span class="flex size-11 items-center justify-center rounded-xl bg-white/10"><x-icon name="checklist" :size="20" /></span>
      <div>
        <div class="h-display text-[17px]">Staging Bay #4 Status</div>
        <div class="text-[12px] text-white/65">{{ $assignedClusters }} of {{ $clusters->count() }} clusters assigned • {{ $stagedParcels }} parcels staged</div>
        <div class="mt-1 text-[11px] text-white/55">{{ $weighedCount ?? 0 }} staged parcels have a recorded weight</div>
      </div>
    </div>
    <div class="flex items-center gap-2.5">
      <a href="{{ route('delivery.assignment.weigh-ins') }}" class="btn bg-white/10 text-white hover:bg-white/20">Review Weigh-In Logs</a>
      @unless ($isPreview)
        <form method="POST" action="{{ route('delivery.assignment.dispatch-all') }}" id="dispatchAllForm" class="hidden">@csrf</form>
        <button type="button" onclick="document.getElementById('dispatchAllForm').action='{{ route('delivery.assignment.dispatch-all') }}'; openConfirmModal('confirmLoadingDocks')"
                class="btn bg-primary text-white hover:opacity-90"><x-icon name="send" :size="14" />Push to Loading Docks</button>
      @endunless
    </div>
  </section>

  {{-- Confirm: Push to Loading Docks --}}
  <x-confirm-modal id="confirmLoadingDocks" title="Push to Loading Docks?" confirmLabel="Push" confirmClass="btn-primary">
    <ul class="confirm-summary-list">
      <li><strong>{{ $dispatchableParcels ?? 0 }}</strong> staged parcels assigned to active riders will be dispatched</li>
      <li>{{ $assignedClusters }} of {{ $clusters->count() }} clusters are assigned</li>
      <li>Unassigned and inactive-rider clusters will remain in staging</li>
    </ul>
  </x-confirm-modal>

  {{-- Confirm: Bulk Archive --}}
  <x-confirm-modal id="confirmBulkArchive" title="Archive selected parcels?" confirmLabel="Archive" confirmClass="btn-danger">
    <ul class="confirm-summary-list" id="confirmBulkArchiveSummary">
      <li>Selected parcels will be archived</li>
    </ul>
  </x-confirm-modal>

  {{-- ── Per-parcel queue with toolbar + bulk select ── --}}
  <section class="card overflow-hidden" id="parcelQueueCard">
    <div class="flex flex-wrap items-center justify-between gap-3 p-5">
      <div>
        <h2 class="h-display text-[17px]">Parcel queue</h2>
        <p class="text-[12px] text-muted-foreground">Newly arrived parcels — determine barangay, then assign rider</p>
      </div>
    </div>

    {{-- Toolbar --}}
    <div class="px-5 pb-1">
      <x-list-toolbar
        placeholder="Search parcel ID or address…"
        :areas="$allAreas ?? collect()"
        :riders="$riders"
        :statuses="['awaiting_sort' => 'Awaiting Sort', 'sorted' => 'Sorted', 'assigned' => 'Assigned']"
        :sorts="['oldest' => 'Oldest first', 'newest' => 'Newest first', 'heaviest' => 'Heaviest first']"
        show-dates
      />
    </div>

    {{-- Bulk action bar (shown when rows are checked) --}}
    <div id="bulkBar" class="mx-5 mb-4 hidden bulk-bar" role="region" aria-live="polite" aria-label="Bulk actions">
      <span id="bulkCount" class="text-[13px] font-semibold">0 parcels selected</span>
      <div class="relative ml-auto">
        <select id="bulkRiderSelect" class="field min-w-52 cursor-pointer appearance-none bg-white/10 py-2 text-[13px] text-white placeholder:text-white/60 pr-8 border-white/20">
          <option value="">Assign to rider…</option>
          @foreach ($riders as $r)
            @php $disabled = $r->status === 'offline'; @endphp
            <option value="{{ $r->id }}" {{ $disabled ? 'disabled' : '' }}>
              {{ $r->initials }} - {{ $r->name }} - {{ $riderLoads[$r->id] ?? 0 }} parcel{{ ($riderLoads[$r->id] ?? 0) === 1 ? '' : 's' }}{{ $disabled ? ' (Unavailable)' : '' }}
            </option>
          @endforeach
        </select>
        <x-icon name="chevron-down" :size="13" class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-white/70" />
      </div>
      @unless ($isPreview)
        <button onclick="bulkAssign()" class="btn bg-white text-ink font-bold hover:opacity-90">
          <x-icon name="send" :size="14" />Assign Selected
        </button>
        <button onclick="bulkArchive()" class="btn bg-white/10 text-white hover:bg-white/20">
          <x-icon name="box" :size="14" />Archive Selected
        </button>
      @endunless
      <button onclick="clearSelection()" class="btn-ghost text-white/70 hover:text-white hover:bg-white/10">
        Cancel
      </button>
    </div>

    @if ($parcels->count() > 6)
      <div class="flex justify-end px-5 pb-2">
        <button type="button" class="btn-ghost px-3 py-1.5 text-[12px]"
                data-table-expand aria-controls="parcelQueueTableViewport" aria-expanded="false">
          Show all rows
        </button>
      </div>
    @endif
    <div id="parcelQueueTableViewport" class="table-scroll-region">
      <table class="tbl min-w-[820px]" data-row-details="off">
        <thead>
          <tr>
            <th class="w-10">
              <input type="checkbox" class="row-checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                     aria-label="Select all parcels" {{ $isPreview ? 'disabled' : '' }}>
            </th>
            <th>Parcel</th>
            <th>Delivery address</th>
            <th>Barangay</th>
            <th>Carrier</th>
            <th>Assigned rider</th>
            <th class="text-right">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($parcels as $p)
            @php
              $matchedArea = $matchedAreas[$p->id] ?? null;
              $rowArea = $p->area ?? $matchedArea;
            @endphp
            <tr data-parcel="{{ $p->id }}" class="h-11 transition-colors hover:bg-secondary/60">
              <td>
                <input type="checkbox" class="row-checkbox parcel-checkbox" value="{{ $p->id }}"
                       onchange="updateBulkBar()" aria-label="Select parcel {{ $p->tracking_no }}"
                       {{ $isPreview ? 'disabled' : '' }}>
              </td>
              <td class="font-bold">{{ $p->tracking_no }}</td>
              <td class="text-muted-foreground">
                <span class="cell-truncate" title="{{ $p->address }}">{{ $p->address }}</span>
              </td>
              <td>
                @if ($rowArea)
                  <span class="font-semibold">{{ $rowArea->name }}</span>
                  @unless ($p->area)
                    <span class="block text-[10.5px] text-muted-foreground">detected</span>
                  @endunless
                @else
                  <span class="text-muted-foreground">—</span>
                @endif
              </td>
              <td class="text-muted-foreground">{{ $carriers[$p->carrier] ?? '-' }}</td>
              <td>
                @if ($p->rider)
                  <span class="inline-flex items-center gap-2">
                    <span class="avatar size-7 text-[10px]">{{ $p->rider->initials }}</span>{{ $p->rider->name }}
                  </span>
                @else
                  <span class="text-muted-foreground">—</span>
                @endif
              </td>
              <td class="text-right">
                <x-row-view-button :modal="'view-parcel-'.$p->id" :label="'View parcel '.$p->tracking_no" />
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="py-8 text-center text-muted-foreground">Nothing waiting to be sorted right now.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @foreach ($parcels as $p)
      @include('partials.sort-parcel-modal', [
        'p' => $p,
        'matchedArea' => $matchedAreas[$p->id] ?? null,
        'carriers' => $carriers,
        'isPreview' => $isPreview,
      ])
    @endforeach

    <div class="flex flex-wrap justify-between gap-2 border-t border-border bg-secondary/60 px-5 py-3 text-[12px] text-muted-foreground">
      <span>{{ $parcels->count() }} waiting</span>
      <span>{{ $areas->count() }} routes</span>
    </div>
  </section>

@endsection

@push('scripts')
<script>
  document.querySelectorAll('[data-table-expand]').forEach(button => {
    button.addEventListener('click', () => {
      const viewport = document.getElementById(button.getAttribute('aria-controls'));
      if (!viewport) { return; }

      const expanded = button.getAttribute('aria-expanded') !== 'true';
      button.setAttribute('aria-expanded', expanded.toString());
      viewport.classList.toggle('is-expanded', expanded);
      button.textContent = expanded ? 'Show fewer rows' : 'Show all rows';
    });
  });

  // ── Courier assignment (one per barangay) ──
  function saveClusterRider(form) {
    const riderId = form.querySelector('[name="rider_id"]')?.value;
    if (!riderId) {
      showToast('Select a rider before saving.', { type: 'error' });
      return false;
    }

    form.action = '{{ url('/riders') }}/' + riderId + '/barangay';
    return true;
  }

  function assignCourier(select) {
    const areaId = select.dataset.area, current = select.dataset.current, next = select.value;
    const riderId = next || current;
    if (!riderId) { return; }
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ url('/riders') }}/' + riderId + '/barangay';
    form.innerHTML = '<input type="hidden" name="_token" value="' + document.querySelector('meta[name=csrf-token]').content + '">'
                   + '<input type="hidden" name="area_id" value="' + (next ? areaId : '') + '">';
    document.body.appendChild(form);
    form.submit();
  }

  // ── Confirm modal wiring: Push to Loading Docks ──
  document.getElementById('confirmLoadingDocks-confirm')?.addEventListener('click', function() {
    closeConfirmModal('confirmLoadingDocks');
    document.getElementById('dispatchAllForm')?.submit();
  });

  // ── Confirm modal wiring: Generate and save dispatch list ──
  document.getElementById('confirmDispatch-confirm')?.addEventListener('click', function() {
    closeConfirmModal('confirmDispatch');
    document.getElementById('dispatchListForm')?.submit();
  });

  // ── Bulk row selection ──
  function updateBulkBar() {
    const checked = [...document.querySelectorAll('.parcel-checkbox:checked')];
    const bar = document.getElementById('bulkBar');
    bar.classList.toggle('hidden', checked.length === 0);
    document.getElementById('bulkCount').textContent = checked.length + ' parcel' + (checked.length !== 1 ? 's' : '') + ' selected';
  }

  function toggleSelectAll(master) {
    document.querySelectorAll('.parcel-checkbox').forEach(cb => { cb.checked = master.checked; });
    updateBulkBar();
  }

  function clearSelection() {
    document.querySelectorAll('.parcel-checkbox, #selectAll').forEach(cb => { cb.checked = false; });
    updateBulkBar();
  }

  function bulkArchive() {
    const ids = [...document.querySelectorAll('.parcel-checkbox:checked')].map(cb => cb.value);
    if (!ids.length) { showToast('Select parcels to archive first.', { type: 'info' }); return; }
    document.getElementById('confirmBulkArchiveSummary').innerHTML =
      '<li><strong>' + ids.length + '</strong> parcel' + (ids.length !== 1 ? 's' : '') + ' will be archived</li>' +
      '<li>Archived parcels can be restored later</li>';
    document.getElementById('confirmBulkArchive-confirm').onclick = function() {
      closeConfirmModal('confirmBulkArchive');
        const form = document.createElement('form');
      form.method = 'POST';
      form.action = '{{ $isPreview ? "#" : route("parcels.sort.bulk-archive") }}';
      form.innerHTML = '<input type="hidden" name="_token" value="' + document.querySelector('meta[name=csrf-token]').content + '">'
                     + ids.map(id => '<input type="hidden" name="parcel_ids[]" value="' + id + '">').join('');
      document.body.appendChild(form);
      form.submit();
    };
    openConfirmModal('confirmBulkArchive');
  }

  function bulkAssign() {
    const riderId = document.getElementById('bulkRiderSelect').value;
    if (!riderId) { showToast('Please select a rider first.', { type: 'error' }); return; }
    const ids = [...document.querySelectorAll('.parcel-checkbox:checked')].map(cb => cb.value);
    if (!ids.length) { return; }
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ $isPreview ? "#" : route("assignments.bulk") }}';
    form.innerHTML = '<input type="hidden" name="_token" value="' + document.querySelector('meta[name=csrf-token]').content + '">'
                   + '<input type="hidden" name="rider_id" value="' + riderId + '">'
                   + ids.map(id => '<input type="hidden" name="parcel_ids[]" value="' + id + '">').join('');
    document.body.appendChild(form);
    form.submit();
  }
</script>
@endpush
