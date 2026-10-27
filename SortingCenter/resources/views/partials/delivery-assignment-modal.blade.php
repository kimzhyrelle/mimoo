@php
  $rider = $area->rider;
  $count = $parcels->count();
  $isReady = $rider && $rider->isActive() && $count > 0;

  $statusLabel = match (true) {
      ! $rider => 'No rider',
      ! $rider->isActive() => 'Rider inactive',
      $count === 0 => 'Empty',
      default => 'Ready to dispatch',
  };
  $statusTone = match (true) {
      ! $rider => 'amber',
      ! $rider->isActive() => 'red',
      $count === 0 => 'slate',
      default => 'green',
  };
  $statusIcon = match (true) {
      ! $rider => 'user',
      ! $rider->isActive() => 'alert',
      $count === 0 => 'box',
      default => 'check-circle',
  };
  $actionFormId = $isReady ? 'area-'.$area->id.'-dispatch-form' : null;
  $totalWeight = $parcels->whereNotNull('weight_kg')->sum('weight_kg');
@endphp

<x-view-modal
    :id="'view-area-'.$area->id"
    :title="'Brgy. '.$area->name"
    :subtitle="($area->code ?: 'No route code').' - '.$count.' sorted parcel'.($count === 1 ? '' : 's')"
    :status="$statusLabel"
    :status-tone="$statusTone"
    :status-icon="$statusIcon"
    :primary-label="$isReady ? 'Dispatch parcels' : null"
    :primary-form="$actionFormId"
>
  <dl class="view-modal-details">
    <div>
      <dt>Barangay</dt>
      <dd>{{ $area->name }}</dd>
    </div>
    <div>
      <dt>Route code</dt>
      <dd>{{ $area->code ?: '-' }}</dd>
    </div>
    <div>
      <dt>Assigned rider</dt>
      <dd>{{ $rider->name ?? 'No rider assigned' }}</dd>
    </div>
    <div>
      <dt>Rider status</dt>
      <dd>{{ $rider?->statusLabel() ?? '-' }}</dd>
    </div>
    <div>
      <dt>Parcels waiting</dt>
      <dd>{{ $count }}</dd>
    </div>
    <div>
      <dt>Measured weight</dt>
      <dd>{{ $totalWeight > 0 ? number_format((float) $totalWeight, 2).' kg' : 'No measured weights' }}</dd>
    </div>
    @if ($rider)
      <div>
        <dt>Vehicle</dt>
        <dd>{{ $rider->vehicleLabel() ?: '-' }}</dd>
      </div>
      <div>
        <dt>Phone</dt>
        <dd>{{ $rider->phone ?: 'Not on file' }}</dd>
      </div>
    @endif
  </dl>

  <div class="mt-5">
    <div class="flex items-center justify-between gap-3">
      <h3 class="label">Parcels in queue</h3>
      <span class="text-[11px] font-bold text-primary">{{ $count }} total</span>
    </div>

    <div class="mt-3 space-y-2">
      @forelse ($parcels as $parcel)
        <div class="flex items-start justify-between gap-3 rounded-xl border border-border bg-secondary/40 px-3 py-2">
          <div class="min-w-0">
            <div class="text-[12.5px] font-bold">{{ $parcel->tracking_no }}</div>
            <div class="text-[11.5px] text-muted-foreground">{{ $parcel->address }}</div>
          </div>
          <div class="shrink-0 text-right">
            <div class="text-[11.5px] font-semibold">{{ $parcel->weight_kg !== null ? number_format((float) $parcel->weight_kg, 2).' kg' : 'No weight' }}</div>
            <div class="text-[10.5px] uppercase tracking-wide text-muted-foreground">{{ $parcel->carrier ?: 'Standard' }}</div>
          </div>
        </div>
      @empty
        <p class="text-[12.5px] text-muted-foreground">No sorted parcels are waiting for this route.</p>
      @endforelse
    </div>
  </div>

  <div class="view-modal-actions">
    <h3 class="label">Actions</h3>

    @if ($isReady)
      <form id="{{ $actionFormId }}" method="POST" action="{{ route('delivery.assignment.dispatch-area', $area) }}"
            data-view-confirm="Dispatch {{ $count }} parcels for {{ $area->name }} to {{ $rider->name }}?"
            data-view-confirm-title="Dispatch this route?"
            data-view-confirm-label="Dispatch"
            data-view-confirm-cancel="Cancel">
        @csrf
      </form>
      <p class="mt-2 text-[12.5px] text-muted-foreground">Dispatch assigns all {{ $count }} sorted parcels to {{ $rider->name }}.</p>
    @elseif (! $rider)
      <p class="mt-2 text-[12.5px] text-muted-foreground">Assign a rider to {{ $area->name }} before dispatching this route.</p>
    @elseif (! $rider->isActive())
      <p class="mt-2 text-[12.5px] text-muted-foreground">{{ $rider->name }} is inactive. Put the rider on shift before dispatching.</p>
    @else
      <p class="mt-2 text-[12.5px] text-muted-foreground">There are no sorted parcels waiting for this route.</p>
    @endif
  </div>
</x-view-modal>
