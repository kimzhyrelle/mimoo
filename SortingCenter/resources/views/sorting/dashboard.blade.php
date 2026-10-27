@extends('layouts.sorting')

@section('title', 'Dashboard')

@php
  // ── Existing variables from DashboardController ──
  $receivedYesterday  = $receivedYesterday  ?? 0;
  $receivedToday      = $receivedToday      ?? 0;
  $awaitingSort       = $awaitingSort       ?? 0;
  $assignedToRiders   = $assignedToRiders   ?? 0;
  $deliveredToday     = $deliveredToday     ?? 0;
  $ridersActive       = $ridersActive       ?? 0;
  $onTimePct          = $onTimePct          ?? 100;
  $priorityCount      = $priorityCount      ?? 0;
  $deliveryFailed     = $deliveryFailed     ?? 0;
  $recentShipments    = $recentShipments    ?? collect();
  $recentActivity     = $recentActivity     ?? collect();
  $hourly             = $hourly             ?? [];
  $isPreview          = request()->routeIs('preview.*');

  // Derived
  $delta = $receivedYesterday > 0
    ? round((($receivedToday - $receivedYesterday) / $receivedYesterday) * 100, 1)
    : null;
  $peak = collect($hourly)->sortByDesc('count')->first();

  $shipmentSort = request()->query('shipment_sort', 'urgency');
  if (! in_array($shipmentSort, ['urgency', 'newest', 'oldest', 'tracking'], true)) {
    $shipmentSort = 'urgency';
  }

  // Sort-priority order for shipments table: overdue > out_for_delivery > assigned > sorted/awaiting > delivered
  $statusOrder = ['failed' => 0, 'out_for_delivery' => 1, 'assigned' => 2, 'sorted' => 3, 'awaiting_sort' => 4, 'dropped_off' => 5, 'delivered' => 6];
  $sortedShipments = match ($shipmentSort) {
    'newest' => $recentShipments->sortByDesc(fn ($parcel) => $parcel->received_at?->timestamp ?? 0)->values(),
    'oldest' => $recentShipments->sortBy(fn ($parcel) => $parcel->received_at?->timestamp ?? 0)->values(),
    'tracking' => $recentShipments->sortBy(fn ($parcel) => $parcel->tracking_no)->values(),
    default => $recentShipments->sort(function ($a, $b) use ($statusOrder) {
      $oa = $statusOrder[$a->status] ?? 99;
      $ob = $statusOrder[$b->status] ?? 99;

      return $oa !== $ob
        ? $oa - $ob
        : ($b->received_at?->timestamp ?? 0) - ($a->received_at?->timestamp ?? 0);
    })->values(),
  };
  $shipmentSortLabels = [
    'urgency' => 'urgency',
    'newest' => 'newest first',
    'oldest' => 'oldest first',
    'tracking' => 'parcel ID',
  ];

@endphp

@section('content')

  {{-- ── Dashboard header ── --}}
  <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-wrap items-center gap-2.5">
      <span class="eyebrow"><span class="size-1.5 animate-pulse rounded-full bg-primary"></span>Stream Live</span>
      <span class="text-[12px] font-medium text-muted-foreground">{{ now()->format('l, M j, Y • h:i A') }}</span>
    </div>
    <a href="{{ $isPreview ? route('preview.parcels.incoming') : route('parcels.incoming') }}" class="btn-ink px-5 py-2.5">
      <x-icon name="qr" :size="16" />Scan QR Code
    </a>
  </div>

  {{-- ── KPIs ── --}}
  <section class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <x-stat label="Parcels Today" :value="number_format($receivedToday)" icon="box">
      @if ($delta !== null)
        <span class="text-[12px] text-muted-foreground">{{ $delta > 0 ? '+' : '' }}{{ $delta }}% vs yesterday</span>
      @endif
    </x-stat>
    <x-stat label="Pending Sort" :value="number_format($awaitingSort)" icon="sort">
      <span class="text-[12px] text-muted-foreground">{{ $priorityCount }} flagged by SLA</span>
    </x-stat>
    <x-stat label="Out for Delivery" :value="number_format($assignedToRiders)" icon="bike">
      <span class="text-[12px] text-muted-foreground">{{ $ridersActive }} riders active</span>
    </x-stat>
    <x-stat label="Delivered" :value="number_format($deliveredToday)" icon="check-circle" tone="green">
      <span class="text-[12px] text-muted-foreground">{{ $onTimePct }}% on time</span>
    </x-stat>
  </section>

  {{-- ════════════════════════════════════════════════════════════
       STAGE 2-3 + 2-4 — Charts row: Spline (hourly) + Donut (status)
  ═══════════════════════════════════════════════════════════════ --}}
  <section class="mb-5 grid grid-cols-1 gap-4 xl:grid-cols-[1.7fr_1fr]">

    {{-- Parcels processed per hour (spline + gradient fill) --}}
    <div class="card p-5">
      <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
        <div>
          <h2 class="h-display text-[17px]">Parcels Processed Per Hour</h2>
          <p class="text-[12px] text-muted-foreground">Operating timeline: 06:00 – 22:00</p>
        </div>
        @if ($peak && $peak['count'] > 0)
          <span class="inline-flex items-center gap-2 rounded-full bg-ink px-3.5 py-1.5 text-[11px] font-semibold text-ink-foreground">
            <span class="size-1.5 rounded-full bg-white"></span>Peak: {{ number_format($peak['count']) }} pkgs/hr at {{ sprintf('%02d:00', $peak['hour']) }}
          </span>
        @endif
      </div>
      <div class="relative h-64"><canvas id="hourlyChart"></canvas></div>
    </div>

    <div class="flex flex-col gap-4">
      {{-- ════════════════════════════════════════════════════════
           STAGE 2-6 — Activity timeline
      ═══════════════════════════════════════════════════════════ --}}
      <div class="card flex-1 p-5">
        <div class="mb-3 flex items-center justify-between">
          <h2 class="h-display text-[17px]">Activity Timeline</h2>
          <a href="{{ $isPreview ? '#' : route('delivery.monitoring') }}" class="text-[11px] font-semibold text-primary hover:underline">View all</a>
        </div>
        <ol id="activity-log" class="relative space-y-0 border-l-2 border-border pl-4" aria-label="Recent activity">
          @forelse ($recentActivity as $a)
            @php
              $aIcon = match ($a->icon) { 'green' => 'check-circle', 'red' => 'alert', 'violet' => 'truck', default => 'inbox' };
              $aDot  = match ($a->icon) { 'green' => 'bg-emerald-500', 'red' => 'bg-red-500', 'violet' => 'bg-indigo-500', default => 'bg-primary' };
              // TODO(antigravity): wire $activity[*].url for "View" links
              $aUrl  = null;
            @endphp
            <li class="activity-item relative pb-4 last:pb-0">
              <span class="absolute -left-[21px] top-0.5 size-2.5 rounded-full ring-2 ring-card {{ $aDot }}" aria-hidden="true"></span>
              <div class="text-[13px] font-semibold leading-snug">{!! $a->description !!}</div>
              <div class="mt-0.5 flex items-center gap-2.5 text-[11px] text-muted-foreground">
                <span>{{ $a->created_at->diffForHumans() }}</span>
                @if ($aUrl)
                  <a href="{{ $aUrl }}" class="font-semibold text-primary hover:underline">View →</a>
                @endif
              </div>
            </li>
          @empty
            <li class="py-4 text-center text-[12.5px] text-muted-foreground">No activity yet.</li>
          @endforelse
        </ol>
      </div>
    </div>
  </section>

  {{-- ════════════════════════════════════════════════════════════
       STAGE 2-5 + 2-7 + 2-8 — Recent Shipments data table
       - Priority sort uses operational urgency and then receipt time
       - Scrollable table with a sticky header, 44px row height, clickable rows
       - Delivered rows have no Action button
       - Auto-refresh every 10s with Pause/Resume toggle
       - Real timestamps (no stale dates)
  ═══════════════════════════════════════════════════════════════ --}}
  <section class="card overflow-hidden" id="shipmentsCard">
    <div class="flex flex-wrap items-center justify-between gap-3 p-5">
      <div>
        <h2 class="h-display text-[19px]">Recent Shipments</h2>
        <p class="text-[12px] text-muted-foreground">Sorted by {{ $shipmentSortLabels[$shipmentSort] }}</p>
      </div>
      <div class="flex items-center gap-2.5">
        <form method="GET" action="{{ route('dashboard') }}" class="relative">
          <label class="sr-only" for="shipmentSort">Sort recent shipments</label>
          <select id="shipmentSort" name="shipment_sort" onchange="this.form.submit()"
                  class="list-toolbar-select pr-8" aria-label="Sort recent shipments">
            <option value="urgency" {{ $shipmentSort === 'urgency' ? 'selected' : '' }}>Sort: Urgency</option>
            <option value="newest" {{ $shipmentSort === 'newest' ? 'selected' : '' }}>Newest first</option>
            <option value="oldest" {{ $shipmentSort === 'oldest' ? 'selected' : '' }}>Oldest first</option>
            <option value="tracking" {{ $shipmentSort === 'tracking' ? 'selected' : '' }}>Parcel ID</option>
          </select>
          <x-icon name="chevron-down" :size="13" class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-muted-foreground" />
        </form>
        {{-- Auto-refresh Pause/Resume toggle --}}
        <button id="refreshToggle" onclick="toggleRefresh()"
                class="inline-flex items-center gap-1.5 rounded-full border border-border bg-secondary px-3 py-1.5 text-[12px] font-semibold text-muted-foreground transition hover:bg-card hover:text-foreground"
                aria-pressed="true" title="Pause auto-refresh">
          <span id="refreshIcon" aria-hidden="true">
            {{-- Pause icon --}}
            <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16" rx="1"/><rect x="14" y="4" width="4" height="16" rx="1"/></svg>
          </span>
          <span id="refreshLabel">Auto-refresh</span>
          <span id="refreshCountdown" class="tabular-nums text-primary">10s</span>
        </button>
      </div>
    </div>

    <div class="shipments-table-wrap overflow-x-auto">
      <table class="tbl min-w-[680px]" role="grid">
        <thead class="sticky top-0 z-10">
          <tr>
            <th class="w-32">Parcel ID</th>
            <th>Destination</th>
            <th>Rider</th>
            <th>Last scan</th>
            <th class="text-right">Action</th>
          </tr>
        </thead>
        <tbody id="shipments-body">
          @forelse ($sortedShipments as $p)
            @php
              $isOverdue  = $p->status === 'failed';
              $isDelivered = $p->status === 'delivered';
              $scanAt = $p->delivered_at ?? $p->assigned_at ?? $p->sorted_at ?? $p->received_at;
            @endphp
            <tr class="shipment-row cursor-pointer transition-colors hover:bg-secondary/60"
                style="height:44px"
                data-href="{{ $isPreview ? '#' : route('delivery.monitoring', ['parcel' => $p->id]) }}"
                onclick="if(!event.target.closest('button,a,form')){window.location=this.dataset.href}">
              <td class="font-bold">
                <a href="{{ $isPreview ? '#' : route('delivery.monitoring', ['parcel' => $p->id]) }}"
                   class="inline-flex items-center gap-2 hover:text-primary"
                   onclick="event.stopPropagation()">
                  <x-icon name="qr" :size="14" class="shrink-0 text-primary" />
                  <span>{{ $p->tracking_no }}</span>
                </a>
              </td>
              <td class="text-[13px]">
                <span class="block font-semibold leading-tight">{{ $p->area->name ?? '—' }}</span>
                <span class="block max-w-[160px] truncate text-[11.5px] text-muted-foreground" title="{{ $p->address }}">{{ $p->address }}</span>
              </td>
              <td>
                @if ($p->rider)
                  <span class="inline-flex items-center gap-2">
                    <span class="avatar size-7 text-[10px]" aria-hidden="true">{{ $p->rider->initials }}</span>
                    <span class="text-[13px] font-semibold">{{ $p->rider->name }}</span>
                  </span>
                @else
                  <span class="text-muted-foreground">—</span>
                @endif
              </td>
              <td class="whitespace-nowrap text-[12px] text-muted-foreground">
                {{ $scanAt?->format('h:i A') ?? '—' }}
                <div class="text-[10.5px] opacity-60">{{ $scanAt?->format('M j') ?? '' }}</div>
              </td>
              <td class="text-right" onclick="event.stopPropagation()">
                @if (!$isDelivered && !$isPreview)
                  @if ($p->status === 'assigned' || $p->status === 'out_for_delivery')
                    <form method="POST" action="{{ route('delivery.monitoring.advance', $p) }}" class="inline">
                      @csrf
                      <button class="btn-soft px-3 py-1.5 text-[12px]">
                        {{ $p->status === 'assigned' ? 'Out for delivery' : 'Mark delivered' }}
                      </button>
                    </form>
                  @elseif ($isOverdue && !$p->scanned_undelivered)
                    <form method="POST" action="{{ route('delivery.monitoring.scan-undelivered', $p) }}" class="inline">
                      @csrf
                      <button class="btn-danger-ghost px-3 py-1.5 text-[12px]">Scan undelivered</button>
                    </form>
                  @else
                    <span class="text-[12px] text-muted-foreground">—</span>
                  @endif
                @else
                  {{-- Delivered: no action button --}}
                  @if (!$isDelivered)
                    <span class="text-[12px] text-muted-foreground">—</span>
                  @endif
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="py-8 text-center text-muted-foreground">No shipments yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="flex items-center justify-between border-t border-border bg-secondary/50 px-5 py-3 text-[12px] text-muted-foreground">
      <span id="shipmentsFooterCount">{{ $sortedShipments->count() }} parcels</span>
      <span>Sorted by {{ $shipmentSortLabels[$shipmentSort] }} • <span class="font-semibold text-foreground">{{ now()->format('h:i A') }}</span></span>
    </div>
  </section>

@endsection

@push('scripts')
<script>
  // ── Read CSS tokens once ──
  const css_     = getComputedStyle(document.documentElement);
  const mutedFg  = css_.getPropertyValue('--color-muted-foreground').trim() || '#5c6b88';
  const gridCol  = css_.getPropertyValue('--color-border').trim()           || '#e3e9f4';
  const primary  = css_.getPropertyValue('--color-primary').trim()          || '#1d4ed8';
  const cardCol  = css_.getPropertyValue('--color-card').trim()             || '#ffffff';

  // ── Spline chart: Parcels per hour ──
  const hourlyData = @json($hourly);
  const counts = hourlyData.map(h => h.count);
  const peakIdx = counts.length ? counts.indexOf(Math.max(...counts)) : -1;

  if (typeof Chart !== 'undefined') {
  const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
  const gradFill = hourlyCtx.createLinearGradient(0, 0, 0, 260);
  gradFill.addColorStop(0, 'rgba(29,78,216,.30)');
  gradFill.addColorStop(0.6, 'rgba(29,78,216,.08)');
  gradFill.addColorStop(1, 'rgba(29,78,216,0)');

  const peakLinePlugin = {
    id: 'peakLine',
    afterDatasetsDraw(chart) {
      if (peakIdx < 0 || !counts[peakIdx]) { return; }
      const { ctx, chartArea } = chart;
      const x = chart.getDatasetMeta(0).data[peakIdx].x;
      ctx.save();
      ctx.setLineDash([4, 4]);
      ctx.strokeStyle = mutedFg;
      ctx.lineWidth = 1;
      ctx.beginPath();
      ctx.moveTo(x, chartArea.top);
      ctx.lineTo(x, chartArea.bottom);
      ctx.stroke();
      ctx.restore();
    }
  };

  new Chart(hourlyCtx, {
    type: 'line',
    data: {
      labels: hourlyData.map(h => String(h.hour).padStart(2, '0') + ':00'),
      datasets: [{
        data: counts,
        borderColor: primary,
        borderWidth: 2.5,
        tension: 0.42,        // smooth spline
        fill: true,
        backgroundColor: gradFill,
        pointRadius: counts.map((_, i) => (i === peakIdx && counts[i]) ? 6 : 0),
        pointBackgroundColor: cardCol,
        pointBorderColor: '#0b1b3f',
        pointBorderWidth: 2.5,
        pointHoverRadius: 5,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: { duration: 600 },
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: c => ` ${c.parsed.y} parcel${c.parsed.y !== 1 ? 's' : ''}`
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: {
            color: mutedFg,
            font: { size: 11, weight: '600' },
            maxRotation: 0,
            callback(v, i) { return i % 2 === 0 ? this.getLabelForValue(v) : ''; }
          }
        },
        y: {
          beginAtZero: true,
          grid: { color: gridCol, borderDash: [4, 4] },
          ticks: { color: mutedFg, font: { size: 11 }, precision: 0 }
        }
      }
    },
    plugins: [peakLinePlugin]
  });

  }

  // ── Auto-refresh with Pause/Resume toggle ──
  let refreshPaused = false;
  let refreshRemaining = 10;
  let refreshInterval;
  let countdownInterval;

  const pauseIcon = `<svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16" rx="1"/><rect x="14" y="4" width="4" height="16" rx="1"/></svg>`;
  const playIcon  = `<svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>`;

  function startCountdown() {
    refreshRemaining = 10;
    document.getElementById('refreshCountdown').textContent = '10s';
    clearInterval(countdownInterval);
    countdownInterval = setInterval(() => {
      if (refreshPaused) { return; }
      refreshRemaining--;
      document.getElementById('refreshCountdown').textContent = refreshRemaining + 's';
      if (refreshRemaining <= 0) { doRefresh(); }
    }, 1000);
  }

  async function doRefresh() {
    if (document.hidden || refreshPaused) { return; }
    try {
      const html = await (await fetch(location.href, { headers: { 'X-Requested-With': 'fetch' } })).text();
      const doc = new DOMParser().parseFromString(html, 'text/html');
      // Refresh activity log
      const freshLog = doc.getElementById('activity-log');
      const curLog   = document.getElementById('activity-log');
      if (freshLog && curLog) { curLog.innerHTML = freshLog.innerHTML; }
      // Refresh shipments body and count
      const freshBody = doc.getElementById('shipments-body');
      const curBody   = document.getElementById('shipments-body');
      if (freshBody && curBody) {
        curBody.innerHTML = freshBody.innerHTML;
        document.getElementById('shipmentsFooterCount').textContent =
          `${curBody.querySelectorAll('.shipment-row').length} parcels`;
      }
    } catch (_) { /* ignore network errors */ }
    startCountdown();
  }

  function toggleRefresh() {
    refreshPaused = !refreshPaused;
    const btn = document.getElementById('refreshToggle');
    btn.setAttribute('aria-pressed', (!refreshPaused).toString());
    document.getElementById('refreshIcon').innerHTML = refreshPaused ? playIcon : pauseIcon;
    document.getElementById('refreshLabel').textContent = refreshPaused ? 'Paused' : 'Auto-refresh';
    document.getElementById('refreshCountdown').style.display = refreshPaused ? 'none' : '';
    btn.title = refreshPaused ? 'Resume auto-refresh' : 'Pause auto-refresh';
  }

  startCountdown();
</script>
@endpush
