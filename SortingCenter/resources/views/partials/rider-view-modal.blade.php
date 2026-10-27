@php
  $isSuspended = $r->status === 'suspended';
  $isActive = $r->isActive();
  $isOnline = $r->isOnline();
  $jobCount = $r->active_jobs_count ?? $r->activeJobsCount();
  $completedToday = $r->completed_today ?? $r->completedTodayCount();
  $routeFormId = 'rider-'.$r->id.'-route-form';
  $shiftFormId = 'rider-'.$r->id.'-shift-form';
  $statusFormId = 'rider-'.$r->id.'-status-form';
  $primaryLabel = $isPreview ? null : 'Save route';
  $primaryForm = $isPreview ? null : $routeFormId;
@endphp

<x-view-modal
    :id="'view-rider-'.$r->id"
    :title="$r->name"
    :subtitle="'MIM-RDR-'.str_pad((string) $r->id, 4, '0', STR_PAD_LEFT).' - '.($r->branch?->name ?? 'No branch')"
    :primary-label="$primaryLabel"
    :primary-form="$primaryForm"
>
  <dl class="view-modal-details">
    <div>
      <dt>Rider ID</dt>
      <dd class="font-mono">MIM-RDR-{{ str_pad((string) $r->id, 4, '0', STR_PAD_LEFT) }}</dd>
    </div>
    <div>
      <dt>Route area</dt>
      <dd>{{ $r->area->name ?? '—' }}</dd>
    </div>
    <div>
      <dt>Branch</dt>
      <dd>{{ $r->branch?->name ?? '-' }}</dd>
    </div>
    <div>
      <dt>Phone</dt>
      <dd>{{ $r->phone ?: 'Not on file' }}</dd>
    </div>
    <div>
      <dt>Vehicle</dt>
      <dd>{{ $r->vehicle_type ?: '-' }}</dd>
    </div>
    <div>
      <dt>Plate number</dt>
      <dd>{{ $r->plate_no ?: '-' }}</dd>
    </div>
    <div>
      <dt>KYC status</dt>
      <dd>{{ $r->kycStatusLabel() }}</dd>
    </div>
    <div>
      <dt>Active jobs</dt>
      <dd>{{ $jobCount }} / 3</dd>
    </div>
    <div>
      <dt>Completed today</dt>
      <dd>{{ $completedToday }}</dd>
    </div>
    <div>
      <dt>Checked in</dt>
      <dd>{{ $r->checked_in_at?->format('M j, Y g:i A') ?? 'Not checked in' }}</dd>
    </div>
    <div>
      <dt>Joined</dt>
      <dd>{{ $r->created_at?->format('M j, Y') ?? '-' }}</dd>
    </div>
  </dl>

  <div class="view-modal-actions">
    <h3 class="label">Actions</h3>

    @if ($isPreview)
      <p class="mt-2 text-[12.5px] text-muted-foreground">Read-only preview. Route and shift changes are disabled.</p>
    @else
      <div class="view-modal-action-grid mt-2">
        <form id="{{ $routeFormId }}" method="POST" action="{{ route('riders.barangay', $r) }}" class="space-y-2">
          @csrf
          <label class="block">
            <span class="label">Assigned route area</span>
            <select name="area_id" class="field mt-1.5 cursor-pointer text-[13px]">
              <option value="">Select route area</option>
              @foreach ($areas as $area)
                @php $takenBy = $area->rider && $area->rider->id !== $r->id ? $area->rider : null; @endphp
                <option value="{{ $area->id }}" @selected($r->area_id === $area->id)>
                  {{ $area->name }}{{ $takenBy ? ' (taken by '.$takenBy->initials.')' : '' }}
                </option>
              @endforeach
            </select>
          </label>
        </form>

        <div class="space-y-3">
          <form id="{{ $shiftFormId }}" method="POST" action="{{ route('riders.toggle-active', $r) }}"
                data-view-confirm="Change the shift status for {{ $r->name }}?"
                data-view-confirm-title="Change shift status?"
                data-view-confirm-label="{{ $isActive ? 'Mark inactive' : 'Mark active' }}"
                data-view-confirm-cancel="Keep current"
                data-view-confirm-tone="primary">
            @csrf
            <button type="submit" class="btn-soft w-full">
              <x-icon :name="$isActive ? 'clock' : 'check-circle'" :size="13" />
              {{ $isActive ? 'Mark inactive' : 'Mark active' }}
            </button>
          </form>

          @if ($isSuspended)
            <form id="{{ $statusFormId }}" method="POST" action="{{ route('riders.reactivate', $r) }}" class="space-y-2"
                  data-view-confirm="Reactivate {{ $r->name }} and return them to the fleet?"
                  data-view-confirm-title="Reactivate rider?"
                  data-view-confirm-label="Reactivate"
                  data-view-confirm-cancel="Cancel"
                  data-view-confirm-tone="primary">
              @csrf
              <label class="block">
                <span class="label">Reactivation reason</span>
                <textarea name="reason" required minlength="10" maxlength="300" rows="2" class="field mt-1.5 w-full text-[12.5px]" placeholder="Reason for reactivation"></textarea>
              </label>
              <button type="submit" class="btn-soft w-full">Reactivate rider</button>
            </form>
          @else
            <form id="{{ $statusFormId }}" method="POST" action="{{ route('riders.suspend', $r) }}" class="space-y-2"
                  data-view-confirm="Suspend {{ $r->name }}? Not-yet-picked-up jobs return to the Unassigned queue."
                  data-view-confirm-title="Suspend rider?"
                  data-view-confirm-label="Suspend"
                  data-view-confirm-cancel="Cancel">
              @csrf
              <label class="block">
                <span class="label">Suspension reason</span>
                <textarea name="reason" required minlength="10" maxlength="300" rows="2" class="field mt-1.5 w-full text-[12.5px]" placeholder="Reason for suspension"></textarea>
              </label>
              <button type="submit" class="btn-danger-ghost w-full">Suspend rider</button>
            </form>
          @endif
        </div>
      </div>
    @endif
  </div>
</x-view-modal>
