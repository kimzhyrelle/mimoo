{{--
  Disapprove rider application modal.

  Usage:
    <x-disapprove-modal id="disapprove-{{ $app->id }}" :action="route('riders.applications.disapprove', $app)" />

  Renders through the shared <x-confirm-modal> component so it uses the same
  overlay, close buttons, scroll lock and focus handling as every other modal.
--}}
@props(['id' => null, 'action' => null])

<x-confirm-modal
    :id="$id"
    title="Disapprove Application"
    confirmLabel="Disapprove"
    confirmClass="btn-danger"
    :formAction="$action"
    formAttributes="data-disapprove-form"
>
  <p class="mb-3 text-[13px] text-muted-foreground">Please select a reason for disapproving this application. The applicant will be notified.</p>

  <div class="space-y-4">
    <label class="block">
      <span class="label">Reason</span>
      <div class="relative mt-1.5">
        <select name="reason" id="{{ $id }}-reason" class="field cursor-pointer appearance-none pr-9"
                required data-confirm-required onchange="toggleReasonOther('{{ $id }}')">
          <option value="">Select a reason&hellip;</option>
          <option value="Incomplete documents">Incomplete documents</option>
          <option value="Blurry ID">Blurry ID</option>
          <option value="Expired license">Expired license</option>
          <option value="Mismatched details">Mismatched details</option>
          <option value="Other">Other (specify below)</option>
        </select>
        <x-icon name="chevron-down" :size="15" class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-muted-foreground" />
      </div>
    </label>

    <label class="block hidden" id="{{ $id }}-other-wrap">
      <span class="label">Please specify</span>
      <textarea name="reason_detail" id="{{ $id }}-detail" rows="2" class="field mt-1.5 resize-none"
                placeholder="Enter additional details&hellip;" maxlength="500"></textarea>
    </label>
  </div>
</x-confirm-modal>

<script>
  function toggleReasonOther(modalId) {
    const modal = document.getElementById(modalId);
    const select = document.getElementById(modalId + '-reason');
    const wrap = document.getElementById(modalId + '-other-wrap');
    const detail = document.getElementById(modalId + '-detail');

    if (!modal || !select || !wrap || !detail) return;

    const isOther = select.value === 'Other';
    wrap.classList.toggle('hidden', !isOther);

    if (isOther) {
      detail.setAttribute('data-confirm-required', '');
    } else {
      detail.removeAttribute('data-confirm-required');
    }

    if (typeof window.syncConfirmState === 'function') {
      window.syncConfirmState(modal);
    }
  }
</script>
