@props([
    'modal',
    'label' => 'View details',
])

<button type="button" class="row-view-btn" data-modal-open="{{ $modal }}"
        aria-haspopup="dialog" aria-controls="{{ $modal }}" aria-label="{{ $label }}">
  View
</button>
