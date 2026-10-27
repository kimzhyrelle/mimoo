{{--
  <x-list-toolbar>
  A reusable search + sort + filter bar that submits GET params:
    ?q=   &sort=   &status=   &rider=   &area_id=   &from=   &to=

  Props:
    placeholder  – search input placeholder text
    :statuses    – [value => label] array for status filter (optional)
    :riders      – collection of Rider models for rider filter (optional)
    :areas       – collection of Area models for area filter (optional)
    :sorts       – [value => label] array for sort dropdown (optional)
    showDates    – whether to show date-range pickers (default false)
    :extra       – [name => value] hidden inputs to preserve (optional)

  Current values are read from request() automatically.
--}}
@props([
    'placeholder' => 'Search…',
    'statuses'    => [],
    'riders'      => null,
    'areas'       => null,
    'sorts'       => [],
    'showDates'   => false,
    'extra'       => [],
])
@php
  $q       = request('q', '');
  $sort    = request('sort', '');
  $status  = request('status', '');
  $riderId = request('rider', '');
  $areaId  = request('area_id', '');
  $from    = request('from', '');
  $to      = request('to', '');

  $hasFilters = $q || $sort || $status || $riderId || $areaId || $from || $to;
@endphp

<form method="GET" class="list-toolbar" role="search" id="listToolbarForm">
  {{-- Preserve any extra hidden params (e.g. type=parcels on reports) --}}
  @foreach ($extra as $name => $val)
    <input type="hidden" name="{{ $name }}" value="{{ $val }}">
  @endforeach

  {{-- Search --}}
  <div class="list-toolbar-search">
    <x-icon name="search" :size="14" class="list-toolbar-search-icon" />
    <input type="search"
           name="q"
           value="{{ $q }}"
           placeholder="{{ $placeholder }}"
           class="list-toolbar-input"
           autocomplete="off"
           aria-label="Search">
  </div>

  {{-- Sort dropdown --}}
  @if (!empty($sorts))
    <div class="relative">
      <select name="sort" onchange="this.form.submit()" class="list-toolbar-select pr-8" aria-label="Sort by">
        <option value="">Sort: Default</option>
        @foreach ($sorts as $val => $lbl)
          <option value="{{ $val }}" {{ $sort === $val ? 'selected' : '' }}>{{ $lbl }}</option>
        @endforeach
      </select>
      <x-icon name="chevron-down" :size="13" class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-muted-foreground" />
    </div>
  @endif

  {{-- Status filter --}}
  @if (!empty($statuses))
    <div class="relative">
      <select name="status" onchange="this.form.submit()" class="list-toolbar-select pr-8" aria-label="Filter by status">
        <option value="">All statuses</option>
        @foreach ($statuses as $val => $lbl)
          <option value="{{ $val }}" {{ $status === $val ? 'selected' : '' }}>{{ $lbl }}</option>
        @endforeach
      </select>
      <x-icon name="chevron-down" :size="13" class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-muted-foreground" />
    </div>
  @endif

  {{-- Area / Barangay filter --}}
  @if ($areas && $areas->isNotEmpty())
    <div class="relative">
      <select name="area_id" onchange="this.form.submit()" class="list-toolbar-select pr-8" aria-label="Filter by barangay">
        <option value="">All barangays</option>
        @foreach ($areas as $a)
          <option value="{{ $a->id }}" {{ $areaId == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
        @endforeach
      </select>
      <x-icon name="chevron-down" :size="13" class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-muted-foreground" />
    </div>
  @endif

  {{-- Rider filter --}}
  @if ($riders && $riders->isNotEmpty())
    <div class="relative">
      <select name="rider" onchange="this.form.submit()" class="list-toolbar-select pr-8" aria-label="Filter by rider">
        <option value="">All riders</option>
        @foreach ($riders as $r)
          <option value="{{ $r->id }}" {{ $riderId == $r->id ? 'selected' : '' }}>{{ $r->initials }} – {{ $r->name }}</option>
        @endforeach
      </select>
      <x-icon name="chevron-down" :size="13" class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-muted-foreground" />
    </div>
  @endif

  {{-- Date range --}}
  @if ($showDates)
    <div class="relative">
      <x-icon name="calendar" :size="13" class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-muted-foreground" />
      <input type="date" name="from" value="{{ $from }}" class="list-toolbar-date pl-8" aria-label="From date" onchange="this.form.submit()">
    </div>
    <span class="text-[12px] text-muted-foreground">–</span>
    <div class="relative">
      <x-icon name="calendar" :size="13" class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-muted-foreground" />
      <input type="date" name="to" value="{{ $to }}" class="list-toolbar-date pl-8" aria-label="To date" onchange="this.form.submit()">
    </div>
  @endif

  @if ($hasFilters)
    <button type="submit" class="sr-only">Apply filters</button>
  @endif
</form>

@once
  @push('scripts')
    <script>
      (function () {
        const form = document.getElementById('listToolbarForm');
        if (! form) return;
        const search = form.querySelector('input[name="q"]');
        let timer = null;

        if (search) {
          search.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () { form.requestSubmit(); }, 300);
          });
        }

        form.querySelectorAll('select, input[type="date"]').forEach(function (field) {
          field.addEventListener('change', function () { form.requestSubmit(); });
        });
      })();
    </script>
  @endpush
@endonce
