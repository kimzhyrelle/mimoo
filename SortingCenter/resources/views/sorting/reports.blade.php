@extends('layouts.sorting')

@section('title', 'Reports')

@php
  $types = [
    'parcels'  => ['Parcels Processed',    'Active Report',          'box'],
    'delivery' => ['Delivery Performance', 'Transit SLA & Drop',    'truck'],
    'riders'   => ['Rider Performance',    'Daily Runs & Turnaround','bike'],
    'branches' => ['Branch Throughput',    'Station Sorting Cap',   'network'],
    'overdue'  => ['Overdue Parcels',      'Exception Tracking',    'checklist'],
  ];
  $keep = ['date_from' => $dateFrom, 'date_to' => $dateTo, 'barangay' => $barangay, 'carrier' => $carrier];
  $chart = $report['chart'] ?? ['labels' => [], 'values' => []];
  $chartMeta = [
    'parcels'  => ['Daily Parcels Processed', 'Processed volume'],
    'delivery' => ['Delivery Rate by Barangay', 'Delivery rate'],
    'riders'   => ['Parcels Handled by Rider', 'Parcels handled'],
    'branches' => ['Parcel Volume by Barangay', 'Parcels processed'],
    'overdue'  => ['Overdue Parcels by Barangay', 'Overdue parcels'],
  ][$type] ?? ['Report Trend', 'Value'];
  $chartType = $type === 'parcels' ? 'line' : 'bar';
  $chartSuffix = $type === 'delivery' ? '%' : '';
  $tableTitle = [
    'parcels'  => 'Sorting Center Breakdown',
    'delivery' => 'Delivery Performance by Barangay',
    'riders'   => 'Rider Performance',
    'branches' => 'Branch Throughput',
    'overdue'  => 'Overdue Parcels',
  ][$type] ?? 'Report Breakdown';
  $kpiIcons = [
    'total_inbound'      => 'box',
    'daily_avg'          => 'chart',
    'peak_volume'        => 'trend-up',
    'sorting_rate'       => 'check-circle',
    'delivered_count'    => 'check-circle',
    'delivery_rate'      => 'gauge',
    'avg_delivery_time'  => 'clock',
    'buyer_confirmed'    => 'badge-check',
    'active_couriers'    => 'bike',
    'fleet_deliveries'   => 'truck',
    'avg_per_rider'      => 'user',
    'fleet_success_pct'  => 'check-circle',
    'hub_throughput'     => 'network',
    'active_zones'       => 'pin',
    'peak_cluster_share' => 'chart',
    'avg_hub_time'       => 'clock',
    'overdue_exceptions' => 'alert',
    'sla_breach_rate'    => 'alert-circle',
    'avg_sla_delay'      => 'clock',
    'pending_reassign'   => 'refresh',
  ];
  $rows = $report['rows'] ?? [];
  $rowCount = collect($rows)->count();
@endphp

@section('content')

  <x-page-head title="Reports" sub="Generate and export logistics reports.">
    <div class="relative" id="exportMenu">
      <button type="button" id="exportToggle" aria-expanded="false" aria-controls="exportDropdown"
              class="btn-ink px-5"><x-icon name="download" :size="15" />Export
        <x-icon name="chevron-down" :size="13" class="ml-1 opacity-70" /></button>
      <div id="exportDropdown" class="absolute right-0 top-full z-50 mt-1 hidden w-44 rounded-xl border border-border bg-card py-1 shadow-lg">
        <a href="{{ route('reports.index', array_merge($keep, ['type' => $type, 'format' => 'pdf'])) }}" data-report-export
           class="flex w-full items-center gap-2 px-4 py-2.5 text-[13px] font-semibold hover:bg-secondary">
          <x-icon name="download" :size="14" class="text-primary" />Export PDF
        </a>
        <a href="{{ route('reports.index', array_merge($keep, ['type' => $type, 'format' => 'csv'])) }}" data-report-export
           class="flex w-full items-center gap-2 px-4 py-2.5 text-[13px] font-semibold hover:bg-secondary">
          <x-icon name="file" :size="14" class="text-primary" />Export CSV
        </a>
        <a href="{{ route('reports.index', array_merge($keep, ['type' => $type, 'format' => 'xlsx'])) }}" data-report-export
           class="flex w-full items-center gap-2 px-4 py-2.5 text-[13px] font-semibold hover:bg-secondary">
          <x-icon name="file" :size="14" class="text-primary" />Export Excel (XLSX)
        </a>
        <button type="button" data-print
                class="flex w-full items-center gap-2 px-4 py-2.5 text-[13px] font-semibold hover:bg-secondary">
          <x-icon name="printer" :size="14" class="text-primary" />Print
        </button>
      </div>
    </div>
  </x-page-head>

  @if ($errors->any())
    <div class="mb-5 flex items-start gap-2 rounded-xl border border-destructive/30 bg-destructive/10 px-4 py-3 text-[13px] font-semibold text-destructive" role="alert">
      <x-icon name="alert" :size="15" class="mt-0.5" />
      <span>{{ $errors->first() }}</span>
    </div>
  @endif

  {{-- Report types --}}
  <section class="mb-5 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
    @foreach ($types as $key => [$title, $sub, $icon])
      @php $on = $type === $key; @endphp
      <a href="{{ route('reports.index', array_merge($keep, ['type' => $key])) }}" data-report-type="{{ $key }}"
         class="relative rounded-2xl p-4 transition {{ $on ? 'bg-accent ring-1 ring-primary/20' : 'card hover:bg-secondary' }}">
        <span class="flex size-9 items-center justify-center rounded-xl {{ $on ? 'bg-card text-ink shadow-sm' : 'bg-secondary text-muted-foreground' }}"><x-icon :name="$icon" :size="17" /></span>
        <div class="h-display mt-3 text-[14.5px] leading-tight">{{ $title }}</div>
        <div class="mt-0.5 text-[11.5px] {{ $on ? 'font-semibold text-primary' : 'text-muted-foreground' }}">{{ $on ? 'Active Report' : $sub }}</div>
        @if ($on)<span class="absolute right-3.5 top-3.5 size-2 rounded-full bg-primary"></span>@endif
      </a>
    @endforeach
  </section>

  {{-- Filters --}}
  <form method="GET" action="{{ route('reports.index') }}" class="card mb-5 p-5" id="reportFilters">
    <div class="grid grid-cols-1 items-end gap-4 sm:grid-cols-2 xl:grid-cols-[1fr_1fr_1.2fr_auto]">
      <input type="hidden" name="type" value="{{ $type }}">
      <input type="hidden" name="carrier" value="{{ $carrier }}">
      <label class="block"><span class="label">From Date</span>
        <span class="relative mt-1.5 block"><x-icon name="calendar" :size="15" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground" /><input type="date" name="date_from" value="{{ $dateFrom }}" class="field pl-10" onchange="markDirty()"></span></label>
      <label class="block"><span class="label">To Date</span>
        <span class="relative mt-1.5 block"><x-icon name="calendar" :size="15" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground" /><input type="date" name="date_to" value="{{ $dateTo }}" class="field pl-10" onchange="markDirty()"></span></label>
      <label class="block"><span class="label">Barangay</span>
        <span class="relative mt-1.5 block">
          <select name="barangay" class="field cursor-pointer appearance-none pr-9" onchange="markDirty()">
            <option value="all">All Barangays</option>
            @foreach ($areas as $a)<option value="{{ $a->id }}" {{ (string) $barangay === (string) $a->id ? 'selected' : '' }}>{{ $a->name }}</option>@endforeach
          </select>
          <x-icon name="chevron-down" :size="15" class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-muted-foreground" />
        </span></label>
      <button id="generateBtn" class="btn-primary w-full px-6 py-3 sm:col-span-2 xl:col-span-1" disabled><x-icon name="refresh" :size="15" /><span id="generateLabel">Generate Report</span></button>
    </div>
    {{-- Date presets --}}
    <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-border pt-3">
      <span class="text-[11px] font-semibold text-muted-foreground">Quick:</span>
      @foreach (['today' => 'Today', '7days' => 'Last 7 days', 'month' => 'This month'] as $preset => $label)
        <button type="button" onclick="applyPreset('{{ $preset }}')"
                class="rounded-lg px-3 py-1.5 text-[12px] font-semibold text-muted-foreground hover:bg-secondary hover:text-foreground transition">
          {{ $label }}
        </button>
      @endforeach
    </div>
  </form>

  {{-- KPIs --}}
  <section class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ($report['kpis'] ?? [] as $kpi)
      @php
        $icon = $kpiIcons[$kpi['key'] ?? ''] ?? 'chart';
        $isDanger = ($kpi['tone'] ?? '') === 'destructive';
      @endphp
      <div class="card p-5" data-kpi="{{ $kpi['key'] }}">
        <div class="flex items-center justify-between">
          <span class="label">{{ $kpi['label'] }}</span>
          <span class="flex size-8 items-center justify-center rounded-lg {{ $isDanger ? 'bg-destructive/10 text-destructive' : 'bg-secondary text-primary' }}">
            <x-icon :name="$icon" :size="16" />
          </span>
        </div>
        <div class="h-display mt-2 text-[32px] leading-none {{ $isDanger ? 'text-destructive' : '' }}">
          {{ $kpi['value'] }}
          @if (!empty($kpi['unit']))<span class="text-[13px] font-medium text-muted-foreground">{{ $kpi['unit'] }}</span>@endif
        </div>
        <div class="mt-3 flex items-center gap-1.5 text-[12px] {{ $isDanger ? 'font-semibold text-destructive' : 'text-muted-foreground' }}">
          <x-icon :name="$isDanger ? 'alert' : 'check-circle'" :size="13" />{{ $kpi['sub'] }}
        </div>
        @if ($type === 'parcels' && ($kpi['key'] ?? '') === 'total_inbound' && $totalDelta !== null)
          <div class="mt-2">
            <span class="text-[11px] text-muted-foreground">{{ $totalDelta >= 0 ? '+' : '' }}{{ $totalDelta }}% vs previous period</span>
          </div>
        @endif
      </div>
    @endforeach
  </section>

  {{-- Report chart --}}
  <section class="card relative mb-5 p-6" id="reportChartCard">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
      <div>
        <h2 class="h-display text-[19px]">{{ $chartMeta[0] }}</h2>
        <p class="text-[12px] text-muted-foreground">{{ $report['title'] }} &bull; {{ $report['range_label'] }}</p>
      </div>
      <div class="flex items-center gap-4 text-[11.5px] font-semibold">
        <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-primary"></span>{{ $chartMeta[1] }}</span>
      </div>
    </div>
    <div class="relative h-56">
      <canvas id="reportChart"></canvas>
      @if (empty($chart['labels']))
        <div class="pointer-events-none absolute inset-0 flex items-center justify-center rounded-xl bg-card/70 text-[13px] font-semibold text-muted-foreground">
          No data in this range.
        </div>
      @endif
    </div>
    <div id="reportLoading" class="absolute inset-0 hidden items-center justify-center rounded-2xl bg-card/80 backdrop-blur-sm">
      <div class="flex items-center gap-2 text-[13px] font-semibold text-muted-foreground">
        <svg class="size-5 animate-spin text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
        Generating report...
      </div>
    </div>
  </section>

  {{-- Breakdown table --}}
  <section class="card overflow-hidden">
    <div class="flex flex-wrap items-center justify-between gap-3 p-5">
      <h2 class="h-display flex items-center gap-2 text-[17px]"><x-icon name="checklist" :size="17" class="text-primary" />
        {{ $tableTitle }}</h2>
      <div class="flex flex-wrap items-center gap-2">
        @if ($rowCount > 6)
          <button type="button" class="btn-ghost px-3 py-1.5 text-[12px]"
                  data-table-expand aria-controls="reportTableViewport" aria-expanded="false">
            Show all rows
          </button>
        @endif
        <span class="inline-flex max-w-full items-center gap-2 truncate rounded-lg bg-secondary px-3 py-1.5 font-mono text-[11px] font-bold"><x-icon name="qr" :size="13" class="shrink-0" />HUB-{{ strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $hubProfile->name ?? 'HUB'), 0, 2)) }}-{{ now()->year }}-QR</span>
      </div>
    </div>

    <div id="reportTableViewport" class="table-scroll-region">
      @if ($type === 'parcels')
        <table class="tbl min-w-[720px]">
          <thead><tr><th>Date</th><th>Branch</th><th class="text-right">Parcels Processed</th><th class="text-right">Delivered</th><th class="text-right">Overdue</th></tr></thead>
          <tbody>
            @forelse ($rows as $d)
              <tr>
                <td class="font-bold">{{ $d['date']->format('M j, Y') }}</td>
                <td><span class="inline-flex items-center gap-2"><span class="size-2 rounded-full bg-primary"></span>{{ $hubProfile->name ?? 'Hub' }}</span></td>
                <td class="h-display text-right text-[15px]">{{ $d['processed'] }}</td>
                <td class="text-right font-semibold text-primary">{{ $d['delivered'] }}</td>
                <td class="text-right">{{ $d['overdue'] }}</td>
              </tr>
            @empty
              <tr><td colspan="5" class="py-8 text-center text-muted-foreground">No data in this range.</td></tr>
            @endforelse
          </tbody>
        </table>
      @elseif ($type === 'delivery' || $type === 'branches')
        <table class="tbl min-w-[720px]">
          <thead><tr><th>Barangay</th><th class="text-right">Parcels</th><th class="text-right">Delivered</th><th class="text-right">Overdue</th><th class="w-56">{{ $type === 'delivery' ? 'Delivery rate' : 'Share of volume' }}</th></tr></thead>
          <tbody>
            @forelse ($rows as $r)
              @php $w = (float) ($type === 'delivery' ? ($r['rate'] ?? 0) : ($r['share_pct'] ?? 0)); @endphp
              <tr>
                <td class="font-bold"><span class="inline-flex items-center gap-2"><span class="size-2 rounded-full" style="background: {{ $r['color'] }}"></span>{{ $r['name'] }}</span></td>
                <td class="h-display text-right text-[15px]">{{ $r['count'] }}</td>
                <td class="text-right font-semibold text-primary">{{ $r['delivered'] }}</td>
                <td class="text-right">{{ $r['overdue'] }}</td>
                <td><span class="flex items-center gap-2"><span class="h-2 flex-1 overflow-hidden rounded-full bg-accent"><span class="block h-full rounded-full bg-primary" style="width: {{ min(100, $w) }}%"></span></span><span class="w-12 text-right text-[12px] font-bold">{{ $w }}%</span></span></td>
              </tr>
            @empty
              <tr><td colspan="5" class="py-8 text-center text-muted-foreground">No barangay data in this range.</td></tr>
            @endforelse
          </tbody>
        </table>
      @elseif ($type === 'riders')
        <table class="tbl min-w-[720px]">
          <thead><tr><th>Rider</th><th>Barangay</th><th class="text-right">Parcels</th><th class="text-right">Delivered</th><th class="text-right">Avg delivery</th><th class="w-48">Success</th></tr></thead>
          <tbody>
            @forelse ($rows as $r)
              <tr>
                <td><span class="inline-flex items-center gap-2.5"><span class="avatar">{{ $r['rider']->initials }}</span><span class="font-bold">{{ $r['rider']->name }}</span></span></td>
                <td class="text-muted-foreground">{{ $r['area'] }}</td>
                <td class="h-display text-right text-[15px]">{{ $r['total'] }}</td>
                <td class="text-right font-semibold text-primary">{{ $r['delivered'] }}</td>
                <td class="text-right text-muted-foreground">{{ $r['avg_del_min'] ? $r['avg_del_min'].' min' : '-' }}</td>
                <td><span class="flex items-center gap-2"><span class="h-2 flex-1 overflow-hidden rounded-full bg-accent"><span class="block h-full rounded-full bg-primary" style="width: {{ min(100, (float) $r['success_pct']) }}%"></span></span><span class="w-12 text-right text-[12px] font-bold">{{ $r['success_pct'] }}%</span></span></td>
              </tr>
            @empty
              <tr><td colspan="6" class="py-8 text-center text-muted-foreground">No rider data in this range.</td></tr>
            @endforelse
          </tbody>
        </table>
      @else
        <table class="tbl min-w-[720px]">
          <thead><tr><th>Parcel</th><th>Barangay</th><th>Rider</th><th>Received</th></tr></thead>
          <tbody>
            @forelse ($rows as $p)
              <tr>
                <td class="font-bold">{{ $p->tracking_no }}</td>
                <td>{{ $p->area->name ?? '-' }}</td>
                <td>{{ $p->rider->name ?? '-' }}</td>
                <td class="text-muted-foreground">{{ $p->received_at?->format('M j, h:i A') }}</td>
              </tr>
            @empty
              <tr><td colspan="4" class="py-8 text-center text-muted-foreground">No parcels in this range.</td></tr>
            @endforelse
          </tbody>
        </table>
      @endif
    </div>
    <div class="border-t border-border bg-secondary/50 px-5 py-3 text-[12px] text-muted-foreground">
      @if ($type === 'parcels'){{ $rowCount }} days in range
      @elseif ($type === 'riders'){{ $rowCount }} riders
      @elseif ($type === 'overdue'){{ $rowCount }} overdue parcels
      @else{{ $rowCount }} barangays @endif
      &bull; {{ \Illuminate\Support\Carbon::parse($dateFrom)->format('M j') }} &ndash; {{ \Illuminate\Support\Carbon::parse($dateTo)->format('M j, Y') }}
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

  function markDirty() {
    const btn = document.getElementById('generateBtn');
    if (btn) {
      btn.disabled = false;
    }
    const label = document.getElementById('generateLabel');
    if (label) {
      label.textContent = 'Generate Report';
    }
  }

  function applyPreset(preset) {
    const today = new Date();
    const fmt = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    let from;
    const to = fmt(today);

    if (preset === 'today') {
      from = to;
    } else if (preset === '7days') {
      const d = new Date(today);
      d.setDate(d.getDate() - 6);
      from = fmt(d);
    } else if (preset === 'month') {
      from = fmt(new Date(today.getFullYear(), today.getMonth(), 1));
    }

    document.querySelector('[name=date_from]').value = from;
    document.querySelector('[name=date_to]').value = to;
    markDirty();
  }

  function syncFilterParams(url, includeType = true) {
    const form = document.getElementById('reportFilters');
    if (!form) {
      return url;
    }

    const next = new URL(url, window.location.origin);
    const fields = ['date_from', 'date_to', 'barangay', 'carrier'];

    if (includeType) {
      fields.push('type');
    }

    fields.forEach((name) => {
      const field = form.querySelector(`[name="${name}"]`);
      if (field) {
        next.searchParams.set(name, field.value);
      }
    });
    next.searchParams.delete('preset');

    return next.pathname + '?' + next.searchParams.toString();
  }

  const filterForm = document.getElementById('reportFilters');

  filterForm?.addEventListener('submit', () => {
    const btn = document.getElementById('generateBtn');
    const label = document.getElementById('generateLabel');
    const loading = document.getElementById('reportLoading');

    if (btn) {
      btn.disabled = true;
    }
    if (label) {
      label.textContent = 'Generating...';
    }
    if (loading) {
      loading.classList.remove('hidden');
      loading.classList.add('flex');
    }
  });

  document.querySelectorAll('[data-report-type]').forEach((link) => {
    link.addEventListener('click', () => {
      link.href = syncFilterParams(link.href, false);
    });
  });

  document.querySelectorAll('[data-report-export]').forEach((link) => {
    link.addEventListener('click', () => {
      link.href = syncFilterParams(link.href);
    });
  });

  const exportMenu = document.getElementById('exportMenu');
  const exportToggle = document.getElementById('exportToggle');
  const exportDropdown = document.getElementById('exportDropdown');

  function setExportOpen(open) {
    if (!exportDropdown || !exportToggle) {
      return;
    }
    exportDropdown.classList.toggle('hidden', !open);
    exportToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  }

  exportToggle?.addEventListener('click', (event) => {
    event.stopPropagation();
    setExportOpen(exportDropdown.classList.contains('hidden'));
  });

  document.addEventListener('click', (event) => {
    if (exportMenu && !exportMenu.contains(event.target)) {
      setExportOpen(false);
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      setExportOpen(false);
    }
  });

  document.querySelector('[data-print]')?.addEventListener('click', () => {
    setExportOpen(false);
    window.print();
  });

  // Report chart
  const reportChartData = @json($chart);
  const reportChartType = @json($chartType);
  const reportChartLabel = @json($chartMeta[1]);
  const reportChartSuffix = @json($chartSuffix);
  const rptCss = getComputedStyle(document.documentElement);
  const rptMuted = rptCss.getPropertyValue('--color-muted-foreground').trim() || '#5c6b88';
  const rptGrid = rptCss.getPropertyValue('--color-border').trim() || '#e3e9f4';
  const rptPrimary = rptCss.getPropertyValue('--color-primary').trim() || '#1d4ed8';
  const rptCard = rptCss.getPropertyValue('--color-card').trim() || '#ffffff';

  if (typeof Chart !== 'undefined' && document.getElementById('reportChart')) {
    const canvas = document.getElementById('reportChart');
    const ctx = canvas.getContext('2d');
    const fill = ctx.createLinearGradient(0, 0, 0, 224);
    fill.addColorStop(0, 'rgba(29,78,216,.25)');
    fill.addColorStop(1, 'rgba(29,78,216,0)');

    const isLine = reportChartType === 'line';
    const dataset = {
      label: reportChartLabel,
      data: reportChartData.values || [],
      borderColor: rptPrimary,
      borderWidth: isLine ? 2.5 : 0,
      tension: 0.35,
      fill: isLine,
      backgroundColor: isLine ? fill : rptPrimary,
      pointRadius: isLine ? 3 : 0,
      pointBackgroundColor: rptCard,
      pointBorderColor: rptPrimary,
      pointBorderWidth: isLine ? 2 : 0,
      pointHoverRadius: isLine ? 5 : 0,
      borderRadius: isLine ? 0 : 6,
      maxBarThickness: isLine ? undefined : 52,
    };

    new Chart(ctx, {
      type: reportChartType,
      data: {
        labels: reportChartData.labels || [],
        datasets: [dataset],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (context) => ` ${context.parsed.y}${reportChartSuffix} ${reportChartLabel.toLowerCase()}`,
            },
          },
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: {
              color: rptMuted,
              font: { size: 11, weight: '600' },
              maxRotation: isLine ? 0 : 45,
              autoSkip: true,
              maxTicksLimit: 8,
            },
          },
          y: {
            beginAtZero: true,
            grid: { color: rptGrid, borderDash: [4, 4] },
            ticks: {
              color: rptMuted,
              font: { size: 11 },
              precision: 0,
              callback: (value) => `${value}${reportChartSuffix}`,
            },
          },
        },
      },
    });
  }
</script>
@endpush
