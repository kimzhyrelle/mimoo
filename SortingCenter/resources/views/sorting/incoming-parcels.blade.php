@extends('layouts.sorting')

@section('title', 'Incoming Parcels')

@php
  $selected = $selected ?? null;
  $notFound = $notFound ?? null;
  $targetArea = $targetArea ?? null;
  $recentScanned = $recentScanned ?? collect();
  $shiftRate = $shiftRate ?? 0;
  $isPreview = request()->routeIs('preview.*');
  $isValid = $selected && $selected->status === 'dropped_off';
@endphp

@section('content')

  <x-page-head eyebrow="High-Speed Induction Line 02" title="Incoming Parcels & QR Scan" sub="Scan inbound parcels via optical scanner or manual input. QR codes only.">
    <div class="card flex items-center gap-6 px-5 py-3">
      <div>
        <div class="label">Shift Rate</div>
        <div class="h-display text-[18px] leading-tight">{{ $shiftRate }} <span class="text-[11px] font-medium text-muted-foreground">units/hr</span></div>
      </div>
      <span class="flex items-center gap-2 text-[12px] font-semibold"><span class="size-2 rounded-full bg-primary"></span>Scanner Ready</span>
    </div>
  </x-page-head>

  @include('partials.flash')

  <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1.75fr_1fr]">

    {{-- ═════════ Left: viewfinder + scan result ═════════ --}}
    <div class="space-y-5">

      <section class="card p-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
          <h2 class="h-display flex items-center gap-2 text-[16px]"><x-icon name="video" :size="17" class="text-primary" />Live Optical Viewfinder</h2>
          <span class="pill-blue"><span class="size-1.5 rounded-full bg-primary"></span>Camera Scanner Active (60 FPS)</span>
        </div>

        <div class="relative mx-auto flex h-72 max-w-xl items-center justify-center overflow-hidden rounded-3xl bg-ink">
          <div class="relative flex size-44 items-center justify-center rounded-2xl bg-white shadow-2xl">
            <x-qr :seed="$selected->tracking_no ?? 'MIMOO'" :size="128" class="text-ink" />
            <span class="animate-scanline pointer-events-none absolute inset-x-3 top-1/2 h-0.5 rounded-full bg-primary shadow-[0_0_14px_3px_rgba(29,78,216,.7)]"></span>
          </div>
          {{-- Reticle corners --}}
          <span class="absolute left-[27%] top-8 size-9 rounded-tl-xl border-l-2 border-t-2 border-white/80"></span>
          <span class="absolute right-[27%] top-8 size-9 rounded-tr-xl border-r-2 border-t-2 border-white/80"></span>
          <span class="absolute bottom-12 left-[27%] size-9 rounded-bl-xl border-b-2 border-l-2 border-white/80"></span>
          <span class="absolute bottom-12 right-[27%] size-9 rounded-br-xl border-b-2 border-r-2 border-white/80"></span>
          <span class="absolute bottom-3.5 rounded-full bg-white/10 px-3 py-1 text-[11px] font-medium text-white/80">Center physical QR matrix inside reticle</span>
        </div>

        <details class="mt-4 text-center" {{ $notFound ? 'open' : '' }}>
          <summary class="inline-flex cursor-pointer list-none items-center gap-2 text-[13px] font-semibold text-primary">
            <x-icon name="keyboard" :size="16" />Enter Parcel ID manually
          </summary>
          <form method="GET" class="mx-auto mt-3 flex max-w-sm gap-2">
            <input type="text" name="tracking" value="{{ $notFound }}" placeholder="e.g. #1011" class="field" autocomplete="off">
            <button class="btn-ink">Look up</button>
          </form>
        </details>
      </section>

      <section class="card p-6">
        @if ($notFound)
          <div class="flex items-center gap-3 rounded-2xl bg-red-50 p-4 text-[13.5px] font-semibold text-red-700 dark:bg-red-500/10 dark:text-red-300">
            <x-icon name="alert" :size="18" />No parcel found with ID “{{ $notFound }}”. Check the label and try again.
          </div>
        @elseif ($selected)
          <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
              <h2 class="h-display text-[20px]">Scanned Result</h2>
            </div>
            <div class="rounded-xl bg-secondary px-4 py-2 text-[12px]"><span class="label mr-2">Tracking ID:</span><span class="h-display text-[16px]">{{ $selected->tracking_no }}</span></div>
          </div>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-[auto_1fr]">
            <div class="flex flex-col items-center justify-center gap-2 rounded-2xl bg-secondary p-5">
              <div class="rounded-xl bg-white p-2"><x-qr :seed="$selected->tracking_no" :size="108" class="text-ink" /></div>
              <span class="text-[10.5px] font-medium text-muted-foreground">Verified 2D Matrix Tag</span>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div class="tile"><div class="text-[11px] text-muted-foreground">Arrival Time</div><div class="mt-0.5 text-[13.5px] font-bold">{{ $selected->received_at?->format('M j, Y — h:i A') ?? '—' }}</div></div>
              <div class="tile"><div class="text-[11px] text-muted-foreground">Contact Number</div><div class="mt-0.5 text-[13.5px] font-bold">{{ $selected->buyer_phone ?: '—' }}</div></div>
              <div class="tile col-span-2"><div class="text-[11px] text-muted-foreground">Buyer Name</div><div class="h-display mt-0.5 text-[17px]">{{ $selected->buyer_name ?: ($selected->seller_name ?: '—') }}</div></div>
              <div class="tile col-span-2"><div class="text-[11px] text-muted-foreground">Delivery Address</div><div class="mt-0.5 text-[13.5px] font-semibold">{{ $selected->address ?: '—' }}</div></div>
            </div>
          </div>

          <div class="mt-4 flex flex-wrap items-center gap-4 rounded-2xl bg-accent p-5">
            <span class="flex size-12 items-center justify-center rounded-xl bg-card text-primary"><x-icon name="inbox" :size="22" /></span>
            <div class="min-w-0 flex-1">
              <div class="text-[10px] font-bold uppercase tracking-wider text-accent-foreground/80">Automated Dispatch Route</div>
              <div class="h-display text-[24px] leading-tight">Target Bin: {{ \App\Models\Parcel::binFor($targetArea) }}</div>
              <div class="text-[12px] text-muted-foreground">{{ $targetArea ? $targetArea->name.' Station (Zone Sector '.$targetArea->code.')' : 'No barangay match yet — set it on Sort & Assign' }}</div>
            </div>
            <span class="inline-flex items-center gap-2 rounded-xl bg-card px-3.5 py-2 text-[12px] font-bold shadow-sm"><x-icon name="route" :size="14" class="text-primary" />Conveyor Chute {{ str_pad((string) ($targetArea->id ?? 0), 2, '0', STR_PAD_LEFT) }}</span>
          </div>

          <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-[1.4fr_1fr]">
            @if ($isValid && ! $isPreview)
              <form method="POST" action="{{ route('parcels.incoming.scan', $selected) }}">
                @csrf
                <button class="btn-ink w-full py-3.5 text-[14px]"><x-icon name="check-circle" :size="17" />Confirm Received</button>
              </form>
            @else
              <button class="btn-ink w-full py-3.5 text-[14px]" disabled><x-icon name="check-circle" :size="17" />Confirm Received</button>
            @endif
            <a href="{{ $isPreview ? '#' : route('chat.index') }}" class="btn-danger-ghost py-3.5 text-[14px]"><x-icon name="alert" :size="16" />Report Problem</a>
          </div>
        @else
          <div class="py-10 text-center text-[13.5px] text-muted-foreground">
            <x-icon name="qr" :size="30" class="mx-auto mb-3 opacity-50" />
            Nothing waiting at the dock. Scan a QR code or enter a parcel ID to see its details.
          </div>
        @endif
      </section>
    </div>

    {{-- ═════════ Right: recent scans ═════════ --}}
    <div class="space-y-5">
      <section class="card p-5">
        <div class="mb-4 flex items-start justify-between">
          <div>
            <h2 class="h-display text-[17px]">Last 5 Scanned Parcels</h2>
            <p class="text-[12px] text-muted-foreground">Real-time incoming feed</p>
          </div>
          <span class="flex size-9 items-center justify-center rounded-xl bg-secondary text-primary"><x-icon name="history" :size="16" /></span>
        </div>

        <div class="space-y-3">
          @forelse ($recentScanned as $r)
            <div class="rounded-2xl p-3.5 {{ $loop->first ? 'bg-accent/70' : 'bg-secondary' }}">
              <div class="flex items-center justify-between">
                <span class="h-display text-[16px]">{{ $r->tracking_no }}</span>
              </div>
              <div class="mt-1 flex items-center justify-between text-[12.5px]">
                <span class="font-semibold">{{ $r->buyer_name ?: ($r->seller_name ?: '—') }}</span>
                <span class="text-muted-foreground">{{ $r->updated_at?->format('h:i A') }}</span>
              </div>
              <div class="mt-2 flex items-center justify-between text-[11px]">
                <span class="inline-flex items-center gap-1 rounded-md bg-card px-2 py-1 font-mono font-semibold"><x-icon name="qr" :size="11" />{{ \App\Models\Parcel::binFor($r->area) }}</span>
                <span class="text-muted-foreground">{{ $r->area->name ?? 'Unsorted' }}</span>
              </div>
            </div>
          @empty
            <p class="py-6 text-center text-[12.5px] text-muted-foreground">No parcels scanned yet.</p>
          @endforelse
        </div>

        <div class="mt-4 flex items-center justify-between rounded-xl bg-secondary px-3.5 py-3 text-[12px] font-semibold">
          <span class="flex items-center gap-2"><x-icon name="sliders" :size="14" class="text-primary" />Auto-print Label Voucher</span>
          <span class="text-emerald-600">Enabled</span>
        </div>
        <a href="{{ $isPreview ? '#' : route('reports.index') }}" class="btn-soft mt-3 w-full">View Complete Inbound History ({{ $scannedToday }})</a>
      </section>

      <section class="card flex items-center gap-3 p-5">
        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-accent text-primary"><x-icon name="qr" :size="18" /></span>
        <div>
          <div class="h-display text-[15px]">Strict Symbology Policy</div>
          <div class="text-[12px] text-muted-foreground">Linear barcodes rejected. QR matrix format only.</div>
        </div>
      </section>
    </div>
  </div>

  {{-- ═════════ Pickup schedule (sorted by cutoff, warning if no rider) ═════════ --}}
  @if ($areas->isNotEmpty())
    @php
      // Sort areas by cutoff time so the earliest cutoff is at the top
      $sortedAreas = $areas->sort(function ($a, $b) {
        return strtotime($a->cutoff_time ?? '23:59 PM') - strtotime($b->cutoff_time ?? '23:59 PM');
      });
    @endphp
    <section class="card mt-5 overflow-hidden">
      <div class="p-5">
        <h2 class="h-display text-[17px]">Today's Pickup Schedule</h2>
        <p class="text-[12px] text-muted-foreground">Sorted by cutoff time — earliest first. Sellers must be ready before cutoff.</p>
      </div>
      <div class="max-h-80 overflow-auto">
        <table class="tbl">
          <thead><tr><th>Route / Barangay</th><th>Rider</th><th>Pickup window</th><th>Ready-by cutoff</th></tr></thead>
          <tbody>
            @foreach ($sortedAreas as $area)
              <tr>
                <td class="font-semibold">
                  <span class="inline-flex items-center gap-2">
                    <span class="size-2 rounded-full" style="background:{{ $area->chart_color ?? '#1d4ed8' }}" aria-hidden="true"></span>
                    {{ $area->name }}
                  </span>
                </td>
                <td>
                  @if ($area->rider)
                    <span class="inline-flex items-center gap-2">
                      <span class="avatar size-7 text-[10px]">{{ $area->rider->initials }}</span>{{ $area->rider->name }}
                    </span>
                  @else
                    <span class="text-muted-foreground">—</span>
                  @endif
                </td>
                <td class="text-muted-foreground">{{ $area->pickup_window }}</td>
                <td class="font-semibold">{{ $area->cutoff_time }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="flex justify-between border-t border-border bg-secondary/60 px-5 py-3 text-[12px] text-muted-foreground">
        <span>Parcels not ready by cutoff roll to the next pickup window</span>
        <span>{{ $areas->count() }} routes today</span>
      </div>
    </section>
  @endif

  {{-- ═════════ Drop-off queue ═════════ --}}
  <section class="card mt-5 overflow-hidden">
    <div class="flex flex-wrap items-center justify-between gap-3 p-5">
      <div>
        <h2 class="h-display text-[17px]">Drop-off queue</h2>
        <p class="text-[12px] text-muted-foreground">Parcels handed over by pickup riders at the hub door</p>
      </div>
      @unless ($isPreview)
        <form method="POST" action="{{ route('parcels.incoming.scan-all') }}">@csrf
          <button class="btn-primary" {{ $parcels->isEmpty() ? 'disabled' : '' }}>Scan all arrived</button>
        </form>
      @endunless
    </div>

    {{-- Toolbar --}}
    <div class="px-5 pb-2">
      <x-list-toolbar
        placeholder="Search parcel ID or seller…"
        :areas="$allAreas ?? collect()"
        :sorts="['oldest' => 'Oldest first', 'newest' => 'Newest first', 'seller' => 'Seller name']"
        show-dates
      />
    </div>
    <div class="max-h-96 overflow-auto">
      <table class="tbl" data-row-details="off">
        <thead><tr><th>Parcel</th><th>Seller</th><th>Dropped off by</th><th>Arrived</th><th class="text-right">Action</th></tr></thead>
        <tbody>
          @forelse ($parcels as $p)
            <tr class="h-11 {{ $selected && $selected->id === $p->id ? 'bg-accent/40' : '' }}">
              <td class="font-bold">{{ $p->tracking_no }}</td>
              <td>{{ $p->seller_name }}</td>
              <td><span class="inline-flex items-center gap-2"><span class="avatar size-7 text-[10px]">{{ $p->droppedOffBy->initials ?? '?' }}</span>{{ $p->droppedOffBy->name ?? 'Unknown rider' }}</span></td>
              <td class="text-muted-foreground">{{ $p->received_at?->format('h:i A') }}</td>
              <td class="text-right">
                @unless ($isPreview)
                  <form method="POST" action="{{ route('parcels.incoming.scan', $p) }}" class="inline">@csrf
                    <button class="btn-soft px-3.5 py-1.5 text-[12px]"><x-icon name="check" :size="13" />Scan</button>
                  </form>
                @endunless
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="py-8 text-center text-muted-foreground">No parcels waiting in the drop-off queue.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="flex justify-between border-t border-border bg-secondary/60 px-5 py-3 text-[12px] text-muted-foreground">
      <span>{{ $parcels->count() }} waiting</span><span>{{ $hubProfile->name ?? 'Sorting hub' }} - Bay 2</span>
    </div>
  </section>

@endsection
