@extends('layouts.sorting')

@section('title', 'Dispatch List')

@php
  $details = $manifest->manifest;
@endphp

@section('content')
  <x-page-head eyebrow="Stage 03 • Dispatch Manifest" title="Dispatch List" sub="Saved dispatch snapshot for staged parcels assigned to active riders.">
    <a href="{{ route('parcels.sort') }}" class="btn-soft px-4 print:hidden">Back to Sort & Assign</a>
    <button type="button" onclick="window.print()" class="btn-ink px-4 print:hidden"><x-icon name="printer" :size="15" />Print List</button>
  </x-page-head>

  <section class="card mb-5 grid grid-cols-1 gap-4 p-5 sm:grid-cols-3">
    <div><div class="label">Manifest #</div><div class="h-display text-[17px]">{{ $manifest->id }}</div></div>
    <div><div class="label">Generated</div><div class="font-semibold">{{ $manifest->created_at?->format('M j, Y g:i A') }}</div></div>
    <div><div class="label">Dispatch total</div><div class="h-display text-[17px]">{{ $details['parcels_count'] ?? 0 }} parcels · {{ $details['clusters_count'] ?? 0 }} routes</div></div>
  </section>

  @if (!empty($details['unassigned_clusters']))
    <section class="card mb-5 border border-amber-500/30 p-4">
      <h2 class="font-bold">Still in staging</h2>
      <p class="mt-1 text-[12px] text-muted-foreground">These parcels were not included because their barangay has no active assigned rider.</p>
      <ul class="mt-3 space-y-1 text-[13px]">
        @foreach ($details['unassigned_clusters'] as $cluster)
          <li>{{ $cluster['area_name'] }} · {{ $cluster['count'] }} parcels — {{ $cluster['reason'] }}</li>
        @endforeach
      </ul>
    </section>
  @endif

  @forelse ($details['areas'] ?? [] as $area)
    <section class="card mb-5 overflow-hidden">
      <div class="flex flex-wrap items-center justify-between gap-3 border-b border-border p-4">
        <div>
          <h2 class="h-display text-[17px]">{{ $area['area_name'] }}</h2>
          <p class="text-[12px] text-muted-foreground">{{ $area['rider_name'] }} · {{ $area['parcels_count'] }} parcels</p>
        </div>
        @php $weighedTotal = collect($area['parcels'])->whereNotNull('weight_kg')->sum('weight_kg'); @endphp
        <div class="font-semibold tabular-nums">
          {{ $weighedTotal > 0 ? number_format($weighedTotal, 2).' kg measured' : 'No measured weights' }}
        </div>
      </div>
      <div class="table-scroll-region is-expanded">
        <table class="tbl min-w-[600px]">
          <thead><tr><th>Parcel</th><th>Delivery address</th><th class="text-right">Weight</th></tr></thead>
          <tbody>
            @foreach ($area['parcels'] as $parcel)
              <tr>
                <td class="font-bold">{{ $parcel['tracking_no'] }}</td>
                <td>{{ $parcel['address'] }}</td>
                <td class="text-right tabular-nums">{{ $parcel['weight_kg'] !== null ? number_format((float) $parcel['weight_kg'], 2).' kg' : '—' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </section>
  @empty
    <section class="card p-10 text-center text-muted-foreground">No staged parcels were ready for an active rider when this list was generated.</section>
  @endforelse
@endsection
