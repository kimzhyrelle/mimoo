@php
  $detectedArea = $p->area ?? $matchedArea;
  $carrierLabel = $carriers[$p->carrier] ?? '-';
  $carrierFormId = 'parcel-'.$p->id.'-carrier-form';
  $statusFormId = 'parcel-'.$p->id.'-status-form';
  $statusFormLabel = null;

  if (! $isPreview && $p->status === 'awaiting_sort') {
      $statusFormLabel = $detectedArea ? 'Sort to '.$detectedArea->name : 'Determine barangay';
  } elseif (! $isPreview && $p->status === 'sorted' && $p->area?->rider) {
      $statusFormLabel = 'Assign to '.$p->area->rider->name;
  }

  $primaryLabel = $statusFormLabel;
  $primaryForm = $statusFormLabel ? $statusFormId : null;
  if (! $isPreview && ! $statusFormLabel) {
      $primaryLabel = 'Update carrier';
      $primaryForm = $carrierFormId;
  }
@endphp

<x-view-modal
    :id="'view-parcel-'.$p->id"
    :title="'Parcel '.$p->tracking_no"
    :subtitle="$p->seller_name ?: $p->address"
    :primary-label="$primaryLabel"
    :primary-form="$primaryForm"
>
  <dl class="view-modal-details">
    <div>
      <dt>Tracking ID</dt>
      <dd class="font-mono">{{ $p->tracking_no }}</dd>
    </div>
    <div class="md:col-span-2">
      <dt>Delivery address</dt>
      <dd>{{ $p->address ?: '-' }}</dd>
    </div>
    <div>
      <dt>Barangay</dt>
      <dd>
        @if ($p->area)
          {{ $p->area->name }}
        @elseif ($detectedArea)
          {{ $detectedArea->name }} <span class="font-normal text-muted-foreground">(detected)</span>
        @else
          <span class="font-normal text-muted-foreground">—</span>
        @endif
      </dd>
    </div>
    <div>
      <dt>Assigned rider</dt>
      <dd>{{ $p->rider?->name ?? '—' }}</dd>
    </div>
    <div>
      <dt>Carrier</dt>
      <dd>{{ $carrierLabel }}</dd>
    </div>
    <div>
      <dt>Weight</dt>
      <dd>{{ $p->weight_kg ? number_format((float) $p->weight_kg, 2).' kg' : '—' }}</dd>
    </div>
    <div>
      <dt>Seller</dt>
      <dd>{{ $p->seller_name ?: '-' }}</dd>
    </div>
    <div>
      <dt>Received</dt>
      <dd>{{ $p->received_at?->format('M j, Y g:i A') ?? '-' }}</dd>
    </div>
  </dl>

  <div class="view-modal-actions">
    <h3 class="label">Actions</h3>

    @if ($isPreview)
      <p class="mt-2 text-[12.5px] text-muted-foreground">Read-only preview. Status and carrier actions are disabled.</p>
    @else
      @if ($p->status === 'awaiting_sort')
        <form id="{{ $statusFormId }}" method="POST" action="{{ route('parcels.sort.determine-area', $p) }}" class="mt-2">
          @csrf
          <p class="text-[12.5px] text-muted-foreground">
            @if ($detectedArea)
              The address matches <strong>{{ $detectedArea->name }}</strong>. Confirm to sort this parcel into that cluster.
            @else
              No barangay matches this address yet. Run the check to record the current match result.
            @endif
          </p>
        </form>
      @elseif ($p->status === 'sorted')
        @if ($p->area?->rider)
          <form id="{{ $statusFormId }}" method="POST" action="{{ route('parcels.sort.assign-rider', $p) }}" class="mt-2">
            @csrf
            <input type="hidden" name="rider_id" value="{{ $p->area->rider->id }}">
            <p class="text-[12.5px] text-muted-foreground">
              This parcel is sorted to <strong>{{ $p->area->name }}</strong> and will be assigned to
              <strong>{{ $p->area->rider->name }}</strong>.
            </p>
          </form>
        @else
          <p class="mt-2 text-[12.5px] text-amber-700 dark:text-amber-300">
            No rider is assigned to {{ $p->area?->name ?? 'this barangay' }}. Assign one in Rider Management before assigning this parcel.
          </p>
        @endif
      @endif

      <form id="{{ $carrierFormId }}" method="POST" action="{{ route('parcels.sort.carrier', $p) }}" class="mt-4 space-y-2">
        @csrf
        <label class="block">
          <span class="label">Carrier</span>
          <select name="carrier" required class="field mt-1.5 cursor-pointer text-[13px]">
            @foreach ($carriers as $value => $label)
              <option value="{{ $value }}" @selected($p->carrier === $value)>{{ $label }}</option>
            @endforeach
          </select>
        </label>

        @if ($primaryForm !== $carrierFormId)
          <button type="submit" class="btn-soft">Save carrier</button>
        @endif
      </form>
    @endif
  </div>
</x-view-modal>
