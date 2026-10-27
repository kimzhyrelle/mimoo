@props([
    'id',
    'title',
    'subtitle' => null,
    'closeLabel' => 'Close',
    'primaryLabel' => null,
    'primaryForm' => null,
    'primaryClass' => 'btn-primary',
])

@php
    $titleId = $id.'-title';
    $subtitleId = $id.'-subtitle';
@endphp

<div id="{{ $id }}" class="confirm-modal-backdrop hidden" role="dialog" aria-modal="true"
     aria-labelledby="{{ $titleId }}" @if (filled($subtitle)) aria-describedby="{{ $subtitleId }}" @endif
     aria-hidden="true" data-reset-on-close>
  <section class="confirm-modal-box view-modal-box">
    <div class="confirm-modal-head view-modal-head">
      <div class="min-w-0">
        <div class="view-modal-title-row">
          <h2 id="{{ $titleId }}" class="h-display text-[18px]">{{ $title }}</h2>
        </div>
        @if (filled($subtitle))
          <p id="{{ $subtitleId }}" class="view-modal-subtitle">{{ $subtitle }}</p>
        @endif
      </div>
      <button type="button" class="confirm-modal-close" data-modal-close aria-label="Close details">&#10005;</button>
    </div>

    <div class="confirm-modal-body view-modal-body">
      <div class="view-modal-content" data-view-content>
        {{ $slot }}
      </div>
    </div>

    <div class="confirm-modal-foot view-modal-foot">
      <button type="button" class="btn-ghost px-5 py-2.5" data-modal-close>{{ $closeLabel }}</button>
      @if (filled($primaryLabel))
        <button type="submit" @if (filled($primaryForm)) form="{{ $primaryForm }}" @endif
                class="{{ $primaryClass }} px-5 py-2.5" data-view-primary>
          {{ $primaryLabel }}
        </button>
      @endif
    </div>
  </section>
</div>
