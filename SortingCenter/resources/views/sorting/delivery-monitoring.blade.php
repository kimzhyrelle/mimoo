@extends('layouts.sorting')

@section('title', 'Delivery Monitoring')

@php
  $isPreview = request()->routeIs('preview.*');
  $selected = $selected ?? $parcels->first();
  $counts = $counts ?? [
    'all' => $parcels->count(),
    'assigned' => $parcels->where('status', 'assigned')->count(),
    'out' => $parcels->where('status', 'out_for_delivery')->count(),
    'delivered' => $parcels->where('status', 'delivered')->count(),
    'failed' => $parcels->where('status', 'failed')->count(),
  ];
  $activeFleet = $activeFleet ?? $parcels->pluck('rider_id')->filter()->unique()->count();
  $completion = $counts['all'] > 0 ? round($counts['delivered'] / $counts['all'] * 100, 1) : 0;
  // Archive count fallback — TODO(antigravity): wire archived parcel count
  $counts['archived'] = $counts['archived'] ?? 0;
  $carrierLabels = ['jnt' => 'J&T Express', 'lbc' => 'LBC', 'lalamove' => 'Lalamove'];
  $filters = [
    'all'       => ['All', null],
    'assigned'  => ['At Hub', null],
    'out'       => ['Out for Delivery', null],
    'delivered' => ['Delivered', null],
    'failed'    => ['Overdue', 'alert'],
    'archived'  => ['Archived', null],
  ];

  // Custody timeline for the selected parcel, newest first
  $events = collect();
  if ($selected) {
    if ($selected->delivered_at) { $events->push(['t' => $selected->delivered_at, 'title' => 'Delivered', 'body' => 'Handed over to the consignee'.($selected->rider ? ' by '.$selected->rider->name : '')]); }
    if ($selected->assigned_at) {
      $out = in_array($selected->status, ['out_for_delivery', 'delivered'], true);
      $events->push(['t' => $selected->assigned_at, 'title' => $out ? 'Dispatched' : 'Assigned', 'body' => ($out ? 'Out for delivery with ' : 'Assigned to ').($selected->rider->name ?? 'a rider')]);
    }
    if ($selected->sorted_at) { $events->push(['t' => $selected->sorted_at, 'title' => 'Sorted', 'body' => 'Allocated to '.\App\Models\Parcel::binFor($selected->area).($selected->area ? ' ('.$selected->area->name.')' : '')]); }
    if ($selected->received_at) { $events->push(['t' => $selected->received_at, 'title' => 'Inbound Scan', 'body' => 'Received at the hub dock']); }
    $events = $events->sortByDesc(fn ($e) => $e['t']->timestamp)->values();
  }
@endphp

@section('content')

  <x-page-head eyebrow="Live Dispatch Feed" title="Delivery Monitoring" sub="Track active courier deliveries and station movement across the hub's service area.">
    <x-slot:meta>{{ $hubProfile->name ?? 'Hub' }} • All lanes</x-slot:meta>
    <div class="card flex items-center divide-x divide-border">
      <div class="px-5 py-3"><div class="label">Active Fleet</div><div class="h-display text-[17px]">{{ $activeFleet }} Riders</div></div>
      <div class="px-5 py-3"><div class="label">Completion</div><div class="h-display text-[17px] text-primary">{{ $completion }}%</div></div>
      <div class="px-5 py-3"><div class="label">Tracked Parcels</div><div class="h-display text-[17px]">{{ $counts['all'] }}</div></div>
    </div>
  </x-page-head>

  @include('partials.flash')

  {{-- ── Filter pills ── --}}
  <div class="mb-4 flex flex-wrap items-center gap-2.5">
    @foreach ($filters as $key => [$label, $icon])
      @php $on = ($filter ?? 'all') === $key; @endphp
      <a href="{{ $isPreview ? '#' : route('delivery.monitoring', array_filter(['filter' => $key, 'q' => $q ?? null, 'area_id' => $area_id ?? null, 'rider' => request('rider'), 'sort' => request('sort'), 'from' => request('from'), 'to' => request('to')])) }}"
         class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-[13px] font-semibold transition
                {{ $on ? 'bg-primary text-white shadow-sm' : ($key === 'failed' ? 'bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-300' : 'bg-card text-foreground hover:bg-secondary') }}">
        @if ($icon)<x-icon :name="$icon" :size="14" />@endif
        {{ $label }}
        <span class="rounded-full px-2 py-0.5 text-[11px] {{ $on ? 'bg-white/20' : 'bg-secondary text-muted-foreground' }}">{{ $counts[$key] }}</span>
      </a>
    @endforeach
  </div>

  {{-- ── List toolbar ── --}}
  <div class="delivery-monitoring-toolbar">
    <x-list-toolbar
      placeholder="Search parcel ID, buyer, address or rider…"
      :areas="$allAreas ?? collect()"
      :riders="$riders ?? collect()"
      :sorts="['assigned_newest' => 'Newest assigned', 'assigned_oldest' => 'Oldest assigned']"
      :extra="['filter' => $filter ?? 'all']"
      show-dates
    />
  </div>

  <div class="delivery-monitoring-grid grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1.9fr)_minmax(18rem,1fr)]">
  <section class="delivery-monitoring-roster card overflow-hidden">
    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3">
      <div class="flex items-center gap-2.5">
        <h2 class="h-display text-[16px]">Live Consignment Roster</h2>
        <span class="pill-blue">Real-Time Refresh</span>
      </div>
      <span class="flex items-center gap-1.5 text-[11.5px] text-muted-foreground"><x-icon name="refresh" :size="12" />Synced {{ now()->format('h:i A') }}</span>
    </div>
    <div id="deliveryRosterViewport" class="table-scroll-region">
        <table class="tbl min-w-[700px]" data-row-details="off">
          <thead><tr><th class="w-36">Parcel ID</th><th class="w-52">Destination</th><th>Rider</th><th class="text-right w-24">Action</th></tr></thead>
          <tbody>
            @forelse ($parcels as $p)
              @php
                $isSel    = $selected && $selected->id === $p->id;
                $isArchived = $p->trashed();
                $isOver   = ! $isArchived && $p->status === 'failed';
              @endphp
              <tr class="h-11 {{ $isSel ? 'bg-accent/60' : '' }} transition-colors hover:bg-secondary/60">
                <td>
                  <span class="flex items-center gap-2">
                    <span class="h-9 w-1 rounded-full {{ $isSel ? 'bg-primary' : 'bg-transparent' }}" aria-hidden="true"></span>
                    <span>
                      <span class="block font-bold">{{ $p->tracking_no }}</span>
                      <span class="block text-[11px] text-muted-foreground">{{ $carrierLabels[$p->carrier] ?? 'Standard' }}</span>
                    </span>
                  </span>
                </td>
                <td>
                  <span class="flex items-start gap-1.5">
                    <x-icon name="pin" :size="13" class="mt-0.5 shrink-0 text-muted-foreground" />
                    <span>
                      <span class="block font-semibold leading-tight">{{ $p->area->name ?? '—' }}</span>
                      <span class="cell-truncate text-[11.5px] text-muted-foreground" title="{{ $p->address }}">{{ $p->address }}</span>
                    </span>
                  </span>
                </td>
                <td>
                  @if ($p->rider)
                    <span class="inline-flex flex-wrap items-center gap-2">
                      <span class="avatar size-7 text-[10px]" aria-hidden="true">{{ $p->rider->initials }}</span>
                      <span>
                        <span class="block font-semibold leading-tight text-[13px]">{{ $p->rider->name }}</span>
                        <span class="block text-[11px] text-muted-foreground">{{ $p->eta_note ?: 'On route' }}</span>
                      </span>
                    </span>
                  @else
                    <span class="text-muted-foreground">—

                    </span>
                  @endif
                </td>
                <td class="text-right">
                  <x-row-view-button :modal="'view-parcel-'.$p->id" :label="'View parcel '.$p->tracking_no" />
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="py-10 text-center text-muted-foreground">No parcels match this view.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-between border-t border-border bg-secondary/50 px-5 py-3 text-[12px] text-muted-foreground">
        <span>Showing {{ $parcels->count() }} of {{ $counts['all'] }} tracked parcels</span>
        <span class="no-print">Pending buyer confirmation: {{ $rail['pending_confirm'] ?? 0 }}</span>
      </div>
  </section>

  {{-- ═════════ Selected consignment ═════════ --}}
  @foreach ($parcels as $p)
    @include('partials.delivery-monitoring-modal', ['p' => $p, 'isPreview' => $isPreview, 'carrierLabels' => $carrierLabels])
  @endforeach

  <aside class="delivery-monitoring-side-panel space-y-4">
      @if ($selected)
        <section class="card p-5">
          <div class="flex items-start justify-between gap-3">
            <div>
              <div class="label">Selected Consignment</div>
              <h2 class="h-display text-[22px] leading-tight">Parcel {{ $selected->tracking_no }}</h2>
              <div class="mt-1 flex items-center gap-1.5 text-[12px] font-semibold text-primary"><x-icon name="pin" :size="13" />{{ $selected->area->name ?? $selected->address }}</div>
            </div>
            <x-qr :seed="$selected->tracking_no" :size="64" class="text-ink" />
          </div>

          <div class="mt-5 flex items-center justify-between">
            <div class="label">Custody Timeline</div>
            <span class="text-[11px] font-bold text-primary">{{ $events->count() }} / 4 Scan Points</span>
          </div>
          <ol class="mt-3 space-y-4 border-l-2 border-border pl-4">
            @forelse ($events as $e)
              <li class="relative">
                <span class="absolute -left-[23px] top-1 size-3 rounded-full border-2 {{ $loop->first ? 'border-primary bg-card ring-4 ring-accent' : 'border-border bg-muted-foreground/40' }}"></span>
                <div class="flex items-baseline justify-between gap-2">
                  <span class="h-display {{ $loop->first ? 'text-[16px]' : 'text-[14px]' }}">{{ $e['title'] }}</span>
                  <span class="text-[11px] font-bold {{ $loop->first ? 'text-primary' : 'text-muted-foreground' }}">{{ $e['t']->format('h:i A') }}</span>
                </div>
                <div class="text-[12px] text-muted-foreground">{{ $e['body'] }}</div>
                @if ($loop->first && $selected->eta_note)
                  <div class="mt-2 rounded-xl bg-secondary px-3 py-2 text-[12px]">Estimated Drop: <b>{{ $selected->eta_note }}</b></div>
                @endif
              </li>
            @empty
              <li class="text-[12.5px] text-muted-foreground">No scan points recorded yet.</li>
            @endforelse
          </ol>

          <div class="tile mt-5 rounded-2xl">
            <div class="label mb-2">Consignee Contact</div>
            <div class="flex items-center gap-3">
              <span class="avatar size-10 bg-card">{{ \Illuminate\Support\Str::of($selected->buyer_name ?: ($selected->seller_name ?: '?'))->explode(' ')->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('') }}</span>
              <div class="min-w-0 flex-1">
                <div class="h-display truncate text-[15px]">{{ $selected->buyer_name ?: ($selected->seller_name ?: '—') }}</div>
                <div class="font-mono text-[12px] text-muted-foreground">{{ $selected->buyer_phone ?: 'No contact on file' }}</div>
              </div>
              @if ($selected->buyer_phone)
                <a href="tel:{{ preg_replace('/\s+/', '', $selected->buyer_phone) }}" class="flex size-9 items-center justify-center rounded-full bg-card text-primary shadow-sm"><x-icon name="phone" :size="15" /></a>
              @endif
            </div>
          </div>

          <button type="button" onclick="window.print()" class="btn-ink mt-5 w-full py-3"><x-icon name="qr" :size="15" />Download QR Proof</button>
        </section>
      @else
        <section class="card p-8 text-center text-[13px] text-muted-foreground">Select a consignment to see its custody timeline.</section>
      @endif

    <section class="flex items-center gap-3 rounded-2xl bg-accent/60 p-5 self-start">
      <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-card text-primary"><x-icon name="headset" :size="20" /></span>
      <div class="min-w-0 flex-1">
        <div class="h-display text-[15px] leading-tight">Dispatch Desk Hotline</div>
        <div class="text-[11.5px] text-muted-foreground">Hub Channel 04</div>
      </div>
      <a href="{{ $isPreview ? '#' : route('chat.index') }}" class="rounded-lg bg-card px-3 py-1.5 text-[12px] font-bold text-primary shadow-sm">Connect</a>
    </section>
  </aside>
  </div>

@endsection
