{{--
  Shared confirmation modal.

  Props:
    id            unique DOM id (required)
    title         modal title
    confirmLabel  confirm button text (default: "Confirm")
    cancelLabel   cancel button text (default: "Cancel")
    confirmClass  button style (default: "btn-primary"; use "btn-danger" for archive/delete)
    requireCheck  require the "I have reviewed the details" checkbox
    formId        id of an external form to submit when Confirm is clicked
    formAction    render an internal POST form wrapping the modal body/footer
    formMethod    internal form method (default: POST)
    formAttributes extra attributes for the internal form

  Behaviour is handled by resources/js/modals.js. Do not add inline modal scripts.
--}}
@props([
    'id'             => null,
    'title'          => 'Confirm',
    'confirmLabel'   => 'Confirm',
    'cancelLabel'    => 'Cancel',
    'confirmClass'   => 'btn-primary',
    'requireCheck'   => false,
    'formId'         => null,
    'formAction'     => null,
    'formMethod'     => 'POST',
    'formAttributes' => '',
])

@php
    $hasForm = filled($formAction);
    $internalFormId = $formId ?: $id.'-form';
@endphp

<div id="{{ $id }}" class="confirm-modal-backdrop hidden" role="dialog" aria-modal="true"
     aria-labelledby="{{ $id }}-title" aria-hidden="true" data-modal="confirm"
     @if (! $hasForm && $formId) data-confirm-form="{{ $formId }}" @endif>
  <div class="confirm-modal-box">
    <div class="confirm-modal-head">
      <h3 id="{{ $id }}-title" class="h-display text-[18px]">{{ $title }}</h3>
      <button type="button" class="confirm-modal-close" data-modal-close aria-label="Close">&#10005;</button>
    </div>

    @if ($hasForm)
      <form method="{{ $formMethod }}" action="{{ $formAction }}" id="{{ $internalFormId }}" data-modal-form {!! $formAttributes !!}>
        @csrf
        <div class="confirm-modal-body">
          @if ($slot->isNotEmpty())
            <div class="confirm-summary">{{ $slot }}</div>
          @endif

          @if ($requireCheck)
            <label class="confirm-check-row">
              <input type="checkbox" class="row-checkbox" data-confirm-check="{{ $id }}">
              <span class="text-[13px] font-medium">I have reviewed the details</span>
            </label>
          @endif
        </div>

        <div class="confirm-modal-foot">
          <button type="button" class="btn-ghost px-5 py-2.5" data-modal-close>{{ $cancelLabel }}</button>
          <button type="submit" id="{{ $id }}-confirm" class="{{ $confirmClass }} px-5 py-2.5"
                  data-modal-confirm="true" @if ($requireCheck) disabled @endif>
            {{ $confirmLabel }}
          </button>
        </div>
      </form>
    @else
      <div class="confirm-modal-body">
        @if ($slot->isNotEmpty())
          <div class="confirm-summary">{{ $slot }}</div>
        @endif

        @if ($requireCheck)
          <label class="confirm-check-row">
            <input type="checkbox" class="row-checkbox" data-confirm-check="{{ $id }}">
            <span class="text-[13px] font-medium">I have reviewed the details</span>
          </label>
        @endif
      </div>

      <div class="confirm-modal-foot">
        <button type="button" class="btn-ghost px-5 py-2.5" data-modal-close>{{ $cancelLabel }}</button>
        <button type="button" id="{{ $id }}-confirm" class="{{ $confirmClass }} px-5 py-2.5"
                data-modal-confirm="true" @if ($requireCheck) disabled @endif>
          {{ $confirmLabel }}
        </button>
      </div>
    @endif
  </div>
</div>
