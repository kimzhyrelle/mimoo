@extends('layouts.sorting')

@section('title', 'Staging Bay Weigh-In Logs')

@section('content')
  <x-page-head eyebrow="Stage 03 • Staging Bay" title="Weigh-In Logs" sub="Review parcel weights and record each staging-bay measurement.">
    <a href="{{ route('parcels.sort') }}" class="btn-soft px-5">Back to Sort & Assign</a>
  </x-page-head>

  @include('partials.flash')

  <section class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
    <x-stat label="Parcels in Staging" :value="$parcels->count()" icon="box" />
    <x-stat label="Parcels Weighed" :value="$weighedCount" icon="check-circle" />
  </section>

  <section class="card overflow-hidden">
    <div class="table-scroll-region">
      <table class="tbl min-w-[760px]">
        <thead>
          <tr>
            <th>Parcel</th>
            <th>Barangay</th>
            <th>Current weight</th>
            <th>Record weigh-in</th>
            <th>Recent weigh-in history</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($parcels as $parcel)
            <tr class="align-top">
              <td>
                <span class="font-bold">{{ $parcel->tracking_no }}</span>
                <span class="mt-1 block max-w-56 text-[11px] text-muted-foreground">{{ $parcel->address }}</span>
              </td>
              <td>{{ $parcel->area?->name ?? 'Unassigned' }}</td>
              <td class="font-semibold tabular-nums">
                {{ $parcel->weight_kg !== null ? number_format($parcel->weight_kg, 2).' kg' : '—' }}
              </td>
              <td>
                <form method="POST" action="{{ route('delivery.assignment.weigh-ins.store', $parcel) }}" class="flex items-center gap-2">
                  @csrf
                  <input type="hidden" name="parcel_id" value="{{ $parcel->id }}">
                  <label class="sr-only" for="weight-{{ $parcel->id }}">Weight in kilograms for {{ $parcel->tracking_no }}</label>
                  <input id="weight-{{ $parcel->id }}" name="weight_kg" type="number" min="0.01" max="1000" step="0.01"
                         value="{{ old('parcel_id') == $parcel->id ? old('weight_kg') : $parcel->weight_kg }}" required class="field w-28 py-1.5" placeholder="kg">
                  <button class="btn-ink whitespace-nowrap px-3 py-2 text-[12px]">Save weight</button>
                </form>
              </td>
              <td>
                @forelse ($parcel->weighIns->take(3) as $weighIn)
                  <div class="mb-1 text-[11px]">
                    <span class="font-semibold">{{ number_format((float) $weighIn->weight_kg, 2) }} kg</span>
                    <span class="text-muted-foreground">· {{ $weighIn->recorded_at?->format('M j, Y g:i A') }}</span>
                    <span class="block text-muted-foreground">{{ $weighIn->user?->name ?? 'Staff' }}</span>
                  </div>
                @empty
                  <span class="text-[12px] text-muted-foreground">No weigh-in history</span>
                @endforelse
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="py-10 text-center text-muted-foreground">No parcels are waiting in the staging bay.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>
@endsection
