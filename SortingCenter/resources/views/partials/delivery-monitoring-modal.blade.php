@php
  $isArchived = $p->trashed();
  $isOverdue = ! $isArchived && $p->status === 'failed';
  $isOut = ! $isArchived && $p->status === 'out_for_delivery';
  $isDelivered = ! $isArchived && $p->status === 'delivered';
  $isAssigned = ! $isArchived && $p->status === 'assigned';

  $actionLabel = null;
  $actionFormId = null;
  $actionText = null;
  $actionConfirmTitle = null;
  $actionConfirmLabel = null;
  $actionTone = 'primary';
  $actionClass = 'btn-primary';

  if (! $isPreview && ! $isArchived) {
      if ($isOverdue && ! $p->scanned_undelivered) {
          $actionLabel = 'Resolve overdue';
          $actionFormId = 'parcel-'.$p->id.'-resolve-form';
          $actionText = 'Confirm '.$p->tracking_no.' is still undelivered and resolve the overdue alert?';
          $actionConfirmTitle = 'Resolve overdue parcel?';
          $actionConfirmLabel = 'Resolve';
      } elseif ($isAssigned) {
          $actionLabel = 'Out for delivery';
          $actionFormId = 'parcel-'.$p->id.'-advance-form';
          $actionText = 'Mark '.$p->tracking_no.' as out for delivery?';
          $actionConfirmTitle = 'Start delivery?';
          $actionConfirmLabel = 'Out for delivery';
      } elseif ($isOut) {
          $actionLabel = 'Mark delivered';
          $actionFormId = 'parcel-'.$p->id.'-advance-form';
          $actionText = 'Mark '.$p->tracking_no.' as delivered?';
          $actionConfirmTitle = 'Mark parcel delivered?';
          $actionConfirmLabel = 'Mark delivered';
      } elseif ($isDelivered) {
          $actionLabel = 'Archive parcel';
          $actionFormId = 'parcel-'.$p->id.'-archive-form';
          $actionText = 'Archive '.$p->tracking_no.'? You can undo this from the confirmation toast.';
          $actionConfirmTitle = 'Archive parcel?';
          $actionConfirmLabel = 'Archive';
          $actionTone = 'danger';
          $actionClass = 'btn-danger';
      }
  }

  $events = $p->custodyEvents();
  $scanAt = $p->delivered_at ?? $p->assigned_at ?? $p->updated_at;
@endphp

<x-view-modal
    :id="'view-parcel-'.$p->id"
    :title="'Parcel '.$p->tracking_no"
    :subtitle="($p->area->name ?? 'No destination area').' - '.($carrierLabels[$p->carrier] ?? 'Standard courier')"
    :primary-label="$actionLabel"
    :primary-form="$actionFormId"
    :primary-class="$actionClass"
>
  <dl class="view-modal-details">
    <div>
      <dt>Tracking</dt>
      <dd class="font-mono">{{ $p->tracking_no }}</dd>
    </div>
    <div>
      <dt>Destination area</dt>
      <dd>{{ $p->area->name ?? 'Not detected' }}</dd>
    </div>
    <div>
      <dt>Rider</dt>
      <dd>{{ $p->rider->name ?? '—' }}</dd>
    </div>
    <div class="sm:col-span-2">
      <dt>Delivery address</dt>
      <dd>{{ $p->address ?: 'No address on file' }}</dd>
    </div>
    <div>
      <dt>Buyer</dt>
      <dd>{{ $p->buyer_name ?: 'Not on file' }}</dd>
    </div>
    <div>
      <dt>Contact</dt>
      <dd>{{ $p->buyer_phone ?: 'No contact on file' }}</dd>
    </div>
    <div>
      <dt>Carrier</dt>
      <dd>{{ $carrierLabels[$p->carrier] ?? 'Standard' }}</dd>
    </div>
    <div>
      <dt>Last scan</dt>
      <dd>{{ $scanAt?->format('M j, Y g:i A') ?? 'No scan yet' }}</dd>
    </div>
    <div>
      <dt>Assignment time</dt>
      <dd>{{ $p->assigned_at?->format('M j, Y g:i A') ?? '—' }}</dd>
    </div>
    <div>
      <dt>Delivery time</dt>
      <dd>{{ $p->delivered_at?->format('M j, Y g:i A') ?? '—' }}</dd>
    </div>
    <div>
      <dt>ETA note</dt>
      <dd>{{ $p->eta_note ?: 'No ETA recorded' }}</dd>
    </div>
  </dl>

  <div class="mt-5">
    <div class="flex items-center justify-between gap-3">
      <h3 class="label">Custody timeline</h3>
      <span class="text-[11px] font-bold text-primary">{{ $events->count() }} / 4 scan points</span>
    </div>
    <ol class="mt-3 space-y-4 border-l-2 border-border pl-4">
      @forelse ($events as $e)
        <li class="relative">
          <span class="absolute -left-[23px] top-1 size-3 rounded-full border-2 {{ $loop->first ? 'border-primary bg-card ring-4 ring-accent' : 'border-border bg-muted-foreground/40' }}"></span>
          <div class="flex items-baseline justify-between gap-2">
            <span class="h-display {{ $loop->first ? 'text-[15px]' : 'text-[13px]' }}">{{ $e['title'] }}</span>
            <span class="text-[11px] font-bold {{ $loop->first ? 'text-primary' : 'text-muted-foreground' }}">{{ $e['t']->format('h:i A') }}</span>
          </div>
          <div class="text-[12px] text-muted-foreground">{{ $e['body'] }}</div>
        </li>
      @empty
        <li class="text-[12.5px] text-muted-foreground">No scan points recorded yet.</li>
      @endforelse
    </ol>
  </div>

  <div class="view-modal-actions">
    <h3 class="label">Actions</h3>

    @if ($isPreview)
      <p class="mt-2 text-[12.5px] text-muted-foreground">Read-only preview. Delivery actions are disabled.</p>
    @elseif ($isArchived)
      <p class="mt-2 text-[12.5px] text-muted-foreground">This parcel is archived. Restore it from the archive view before changing its status.</p>
    @else
      @if ($actionFormId)
        <form id="{{ $actionFormId }}" method="POST"
              action="{{ $isDelivered ? route('shipments.archive.parcel', $p) : ($isOverdue ? route('delivery.monitoring.scan-undelivered', $p) : route('delivery.monitoring.advance', $p)) }}"
              data-view-confirm="{{ $actionText }}"
              data-view-confirm-title="{{ $actionConfirmTitle }}"
              data-view-confirm-label="{{ $actionConfirmLabel }}"
              data-view-confirm-cancel="Cancel"
              data-view-confirm-tone="{{ $actionTone }}">
          @csrf
        </form>
      @endif

      <div class="mt-3 flex flex-wrap items-center gap-2">
        @if ($p->rider && in_array($p->status, ['assigned', 'out_for_delivery', 'failed'], true))
          <a href="{{ route('chat.index') }}" class="btn-soft px-3 py-2 text-[12px]">
            <x-icon name="message" :size="13" />Contact rider
          </a>
        @endif
        <button type="button" onclick="window.print()" class="btn-soft px-3 py-2 text-[12px]">
          <x-icon name="qr" :size="13" />QR proof
        </button>
        <a href="{{ route('delivery.monitoring', array_filter(array_merge(request()->query(), ['parcel' => $p->id]))) }}" class="btn-ghost px-3 py-2 text-[12px]">
          <x-icon name="history" :size="13" />Show in timeline
        </a>
        @if ($p->buyer_phone)
          <a href="tel:{{ preg_replace('/\s+/', '', $p->buyer_phone) }}" class="btn-ghost px-3 py-2 text-[12px]">
            <x-icon name="phone" :size="13" />Call buyer
          </a>
        @endif
      </div>
    @endif
  </div>
</x-view-modal>
