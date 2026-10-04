@extends('layouts.sorting')

@section('title', 'Reports')
@section('subtitle', 'Sorting and delivery performance for Santa Cruz, Laguna.')

@push('styles')
<style>
  /* ── Filters bar ── */
  .filters-bar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; padding-bottom:18px; border-bottom:1px solid var(--color-border); margin-bottom:20px; }
  .filter-group { display:flex; flex-direction:column; gap:4px; }
  .filter-label { font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--color-muted-foreground); }
  .filter-select, .filter-date {
    border:1px solid var(--color-border);
    border-radius:8px;
    padding:6px 10px;
    font-size:12.5px;
    font-family:inherit;
    background:var(--color-background);
    color:var(--color-foreground);
    outline:none;
    cursor:pointer;
    height:34px;
  }
  .filter-select:focus, .filter-date:focus { border-color:var(--color-ring); }
  .date-range { display:flex; align-items:center; gap:6px; }
  .date-sep { font-size:12px; color:var(--color-muted-foreground); }
  .export-actions { margin-left:auto; display:flex; gap:8px; }
  .btn-export {
    display:inline-flex; align-items:center; gap:6px;
    border:1px solid var(--color-border); border-radius:8px;
    padding:6px 14px; font-size:12.5px; font-weight:600;
    font-family:inherit; cursor:pointer; transition:background .15s;
    height:34px;
  }
  .btn-export.outline { background:var(--color-background); color:var(--color-foreground); }
  .btn-export.outline:hover { background:var(--color-muted); }
  .btn-export.solid { background:var(--color-primary); color:var(--color-primary-foreground); border-color:var(--color-primary); }
  .btn-export.solid:hover { opacity:.9; }

  /* ── Stat cards ── */
  .stat-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:20px; }
  .stat-card {
    background:var(--color-card);
    border:1px solid var(--color-border);
    border-radius:12px;
    padding:18px 20px;
  }
  .stat-card .val { font-size:28px; font-weight:800; letter-spacing:-1px; color:var(--color-foreground); line-height:1; font-family:'Poppins',sans-serif; }
  .stat-card .val span { font-size:16px; font-weight:600; letter-spacing:0; }
  .stat-card .delta { font-size:11.5px; font-weight:600; margin-top:8px; }
  .delta.up   { color:#16a34a; }
  .delta.down { color:#dc2626; }
  .delta.neutral { color:var(--color-muted-foreground); }

  /* ── Charts row ── */
  .charts-row { display:grid; grid-template-columns:1fr 380px; gap:14px; margin-bottom:20px; }
  .chart-card {
    background:var(--color-card);
    border:1px solid var(--color-border);
    border-radius:12px;
    padding:18px;
  }
  .chart-card h3 { font-size:13px; font-weight:700; margin-bottom:3px; }
  .chart-card .chart-sub { font-size:11.5px; color:var(--color-muted-foreground); margin-bottom:16px; }

  /* Bar chart */
  .bar-chart { display:flex; flex-direction:column; gap:9px; }
  .bar-row { display:flex; align-items:center; gap:10px; }
  .bar-name { font-size:12px; font-weight:600; width:120px; flex-shrink:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .bar-track { flex:1; height:9px; background:var(--color-muted); border-radius:5px; overflow:hidden; }
  .bar-fill { height:100%; border-radius:5px; transition:width .4s ease; }
  .bar-count { font-size:11.5px; font-weight:700; width:28px; text-align:right; color:var(--color-muted-foreground); }

  /* Donut chart (CSS) */
  .donut-wrap { display:flex; align-items:center; gap:20px; }
  .donut-legend { display:flex; flex-direction:column; gap:8px; flex:1; }
  .donut-legend-item { display:flex; align-items:center; gap:8px; font-size:12px; }
  .donut-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
  .donut-legend-label { flex:1; color:var(--color-foreground); font-weight:600; }
  .donut-legend-pct { color:var(--color-muted-foreground); font-weight:700; }

  /* ── Rider table ── */
  .report-panel {
    background:var(--color-card);
    border:1px solid var(--color-border);
    border-radius:12px;
    overflow:hidden;
  }
  .report-panel-head { padding:16px 20px; border-bottom:1px solid var(--color-border); }
  .report-panel-head h3 { font-size:13px; font-weight:700; }
  .report-panel-head .sub { font-size:11.5px; color:var(--color-muted-foreground); margin-top:2px; }
  .report-table { width:100%; table-layout:fixed; border-collapse:collapse; }
  .report-table th {
    padding:10px 16px;
    font-size:11px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.4px;
    color:var(--color-muted-foreground);
    text-align:center;
    border-bottom:1px solid var(--color-border);
    background:var(--color-muted)/30;
  }
  .report-table td { padding:12px 16px; font-size:12.5px; text-align:center; border-bottom:1px solid var(--color-border)/50; }
  .report-table tbody tr:last-child td { border-bottom:none; }
  .report-table tbody tr:hover td { background:#f8f7fd; }
  .rider-cell { display:flex; align-items:center; gap:9px; justify-content:center; }
  .rider-av { width:28px; height:28px; border-radius:50%; background:#2A1B4D; color:#fff; font-size:10px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
  .rider-name { font-size:12.5px; font-weight:700; }
  .rider-tag { font-size:11px; color:var(--color-muted-foreground); }
  .brgy-chip { display:inline-flex; align-items:center; gap:5px; font-size:11.5px; font-weight:600; color:var(--color-primary); }
  .success-bar-wrap { display:flex; align-items:center; gap:8px; justify-content:center; }
  .success-track { width:80px; height:6px; background:var(--color-muted); border-radius:3px; overflow:hidden; }
  .success-fill { height:100%; border-radius:3px; background:#16a34a; }
  .success-fill.warn { background:#f59e0b; }
  .success-fill.bad  { background:#dc2626; }
  .avg-time { font-weight:700; color:var(--color-foreground); }
  .avg-time span { font-size:10.5px; font-weight:600; color:var(--color-muted-foreground); }
  .empty-row td { text-align:center; color:var(--color-muted-foreground); padding:28px; }
</style>
@endpush

@section('content')

{{-- ── Filters bar ── --}}
<form method="GET" action="{{ route('reports.index') }}">
<div class="filters-bar">

  <div class="filter-group">
    <div class="filter-label">Barangay</div>
    <select name="barangay" class="filter-select" onchange="this.form.submit()">
      <option value="all" {{ $barangay === 'all' ? 'selected' : '' }}>All barangays</option>
      @php
        $scBarangays = [
          'Alipit','Bagumbayan','Bubukal','Calios','Duhat','Gatid',
          'Jasaan','Labuin','Malinao','Oogong','Pagsawitan','Palasan',
          'Patimbao','Poblacion I','Poblacion II','Poblacion III',
          'Poblacion IV','Poblacion V','San Jose','San Juan',
          'San Pablo Norte','San Pablo Sur','Santisima Cruz',
          'Santo Angel Central','Santo Angel Norte','Santo Angel Sur',
        ];
      @endphp
      {{-- DB areas first (with real IDs for filtering) --}}
      @foreach ($areas as $a)
        <option value="{{ $a->id }}" {{ $barangay == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
      @endforeach
      {{-- Remaining official barangays not yet in DB (display only) --}}
      @foreach ($scBarangays as $brgy)
        @if ($areas->where('name', $brgy)->isEmpty())
          <option value="{{ $brgy }}" {{ $barangay === $brgy ? 'selected' : '' }} style="color:#aaa;">{{ $brgy }}</option>
        @endif
      @endforeach
    </select>
  </div>

  <div class="filter-group">
    <div class="filter-label">Carrier</div>
    <select name="carrier" class="filter-select" onchange="this.form.submit()">
      <option value="all" {{ $carrier === 'all' ? 'selected' : '' }}>All carriers</option>
      <option value="jnt"      {{ $carrier === 'jnt'      ? 'selected' : '' }}>J&amp;T Express</option>
      <option value="lbc"      {{ $carrier === 'lbc'      ? 'selected' : '' }}>LBC</option>
      <option value="lalamove" {{ $carrier === 'lalamove' ? 'selected' : '' }}>Lalamove</option>
    </select>
  </div>

  <div class="filter-group">
    <div class="filter-label">Date range</div>
    <div class="date-range">
      <input type="date" name="date_from" value="{{ $dateFrom }}" class="filter-date" onchange="this.form.submit()">
      <span class="date-sep">to</span>
      <input type="date" name="date_to"   value="{{ $dateTo }}"   class="filter-date" onchange="this.form.submit()">
    </div>
  </div>

  <div class="export-actions">
    <button type="button" class="btn-export outline">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      Export PDF
    </button>
    <button type="button" class="btn-export solid">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      Export CSV
    </button>
  </div>

</div>
</form>

{{-- ── Stat cards ── --}}
<div class="stat-grid">

  <div class="stat-card">
    <div class="lbl">Total parcels handled</div>
    <div class="val">{{ number_format($totalHandled) }}</div>
    @if ($totalDelta !== null)
      <div class="delta {{ $totalDelta >= 0 ? 'up' : 'down' }}">
        {{ $totalDelta >= 0 ? '↑' : '↓' }} {{ abs($totalDelta) }}% vs last week
      </div>
    @endif
  </div>

  <div class="stat-card">
    <div class="lbl">On-time delivery rate</div>
    <div class="val">{{ $onTimeRate }}<span>%</span></div>
  </div>

  <div class="stat-card">
    <div class="lbl">Avg. sort-to-assign time</div>
    <div class="val">{{ $avgSortH }}<span>h</span> {{ $avgSortM }}<span>m</span></div>
  </div>

  <div class="stat-card">
    <div class="lbl">Failed deliveries</div>
    <div class="val">{{ $failed }}</div>
  </div>

</div>

{{-- ── Charts row ── --}}
<div class="charts-row">

  {{-- Volume by barangay --}}
  <div class="chart-card">
    <h3>Volume by barangay — this week</h3>
    <div class="chart-sub">Sorted and dispatched parcels per route</div>
    @php $maxVol = $volumeByArea->max('count') ?: 1; @endphp
    @if ($volumeByArea->isEmpty())
      <div style="color:var(--color-muted-foreground);font-size:13px;padding:20px 0;">No data for this period.</div>
    @else
      <div class="bar-chart">
        @foreach ($volumeByArea as $row)
          <div class="bar-row">
            <div class="bar-name">{{ $row['name'] }}</div>
            <div class="bar-track">
              <div class="bar-fill" style="width:{{ round(($row['count'] / $maxVol) * 100) }}%;background:{{ $row['color'] }};"></div>
            </div>
            <div class="bar-count">{{ $row['count'] }}</div>
          </div>
        @endforeach
      </div>
    @endif
  </div>

  {{-- Carrier mix --}}
  <div class="chart-card">
    <h3>Carrier mix</h3>
    <div class="chart-sub">Share of parcels by shipping partner</div>
    @php
      $carrierColors = ['jnt' => '#C81E1E', 'lbc' => '#2A6FBD', 'lalamove' => '#7c4fe0'];
      $carrierLabels = ['jnt' => 'J&T Express', 'lbc' => 'LBC', 'lalamove' => 'Lalamove'];
      $totalC = $carrierMix->sum() ?: 1;

      // Build conic-gradient segments
      $deg = 0;
      $segments = [];
      foreach ($carrierMix as $key => $cnt) {
          $pct = round(($cnt / $totalC) * 360);
          $color = $carrierColors[$key] ?? '#aaa';
          $segments[] = "$color {$deg}deg " . ($deg + $pct) . "deg";
          $deg += $pct;
      }
      $conicGrad = implode(', ', $segments) ?: '#e5e7eb 0deg 360deg';
    @endphp
    <div class="donut-wrap">
      <div style="width:120px;height:120px;border-radius:50%;background:conic-gradient({{ $conicGrad }});flex-shrink:0;position:relative;">
        <div style="position:absolute;inset:24px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--color-muted-foreground);">{{ $totalHandled }}</div>
      </div>
      <div class="donut-legend">
        @if ($carrierMix->isEmpty())
          <div style="color:var(--color-muted-foreground);font-size:12px;">No data</div>
        @else
          @foreach ($carrierMix as $key => $cnt)
            <div class="donut-legend-item">
              <div class="donut-dot" style="background:{{ $carrierColors[$key] ?? '#aaa' }};"></div>
              <div class="donut-legend-label">{!! $carrierLabels[$key] ?? $key !!}</div>
              <div class="donut-legend-pct">{{ round(($cnt / $totalC) * 100) }}%</div>
            </div>
          @endforeach
        @endif
      </div>
    </div>
  </div>

</div>

{{-- ── Rider performance table ── --}}
<div class="report-panel">
  <div class="report-panel-head">
    <h3>Rider performance</h3>
    <div class="sub">Per-rider delivery success rate in this period</div>
  </div>
  <table class="report-table">
    <thead>
      <tr>
        <th style="width:20%;text-align:left;padding-left:20px;">Rider</th>
        <th style="width:18%;">Barangay</th>
        <th style="width:14%;">Parcels delivered</th>
        <th style="width:24%;">Delivery success</th>
        <th style="width:14%;">Avg. delivery time</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($riders as $row)
        @php
          $fillClass = $row['success_pct'] >= 85 ? '' : ($row['success_pct'] >= 60 ? 'warn' : 'bad');
        @endphp
        <tr>
          <td style="text-align:left;padding-left:20px;">
            <div class="rider-cell" style="justify-content:flex-start;">
              <div class="rider-av">{{ $row['rider']->initials }}</div>
              <div>
                <div class="rider-name">{{ $row['rider']->name }}</div>
                <div class="rider-tag">Rider #{{ str_pad($row['rider']->id, 2, '0', STR_PAD_LEFT) }}</div>
              </div>
            </div>
          </td>
          <td>
            <span class="brgy-chip">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
              Brgy. {{ $row['area'] }}
            </span>
          </td>
          <td style="font-weight:700;">{{ $row['delivered'] }}</td>
          <td>
            <div class="success-bar-wrap">
              <div class="success-track">
                <div class="success-fill {{ $fillClass }}" style="width:{{ $row['success_pct'] }}%;"></div>
              </div>
              <span style="font-size:12px;font-weight:700;min-width:36px;text-align:left;">{{ $row['success_pct'] }}%</span>
            </div>
          </td>
          <td>
            @if ($row['avg_del_min'] > 0)
              <span class="avg-time">{{ $row['avg_del_min'] }} <span>min</span></span>
            @else
              <span style="color:var(--color-muted-foreground);">—</span>
            @endif
          </td>
        </tr>
      @empty
        <tr class="empty-row">
          <td colspan="5">No rider data for this period.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@endsection
